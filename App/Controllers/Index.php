<?php
namespace App\Controllers;

use \App\Models\IndexModel;
use \App\Core\Auth;

class Index extends \App\Core\Controller {
  
  const ADMIN_FLAG_ACTIVE = 1; // (1 << 0)
  const ADMIN_FLAG_EDITED = 2; // (1 << 1)
  const ADMIN_FLAG_DELETE = 4; // (1 << 2)
  const ADMIN_FLAG_CLOSED = 8; // (1 << 3)
  
  function __construct(){
    parent::__construct();
    $this->model = new IndexModel();
  }
  
  function index($input) {
    
    $this->setTitle('Тестовая страница');
    
    $allowed_field = ['status','email','name'];
    
    $field = 'status'; // default
    if (isset($input['field']) && in_array(strtolower($input['field']), $allowed_field)) {
      $field = $input['field'];
    }
    
    $sort = 'asc';
    if (isset($input['sort']) && in_array(strtolower($input['sort']), ['asc','desc'])) {
      $sort = strtolower($input['sort']);
    }
    
    $result = [];
    foreach ($allowed_field as $name) {
      $result['sorting'][$name][] = $name == $field ? '_'.$sort : '';
      $result['sorting'][$name][] = $this->setPageLink(array(
        'field' => $name,
        'sort' => ($name == $field ? ($sort == 'asc' ? 'desc' : 'asc') : $sort),
      ), true);
    }
    
    $page = new \App\Core\Pagination($this->view);
    $offset = $page->getOffset(isset($input['page_id']) ? $input['page_id'] : 1, 3);
    
    // Получаем данные из бд
    $result['select'] = $this->model->getList($offset, 3, $field, $sort);

    // Если смотрит адмит надо сгенерить хеши
    $is_admin = Auth::isAdmin();
    foreach ($result['select'] as &$row) {
      
      // Получаем статусы задач
      $row['status_mask'] = $this->getStatusMask($row['status']);
      
      if ($is_admin) {
        $row['hash'] = $this->genHash('task'.$row['task_id']);
        $row['text_hash'] = $this->genHash('text_len'.mb_strlen($row['text']));
      }
    }
    
    // Собираем пагинацию
    $page->getPagination($this->model->getCount()['cnt']);
    $result['pagination'] = $page->render('bootstrap.page.php');
    
    echo $this->view->render('index.php', 'layout.php', $result);
  }
  
  public function remove($input){
    
    if (!$input['id']) {
      $this->view->renderJSON(array('error' => 'Не указан айди задачи'));
    }
    
    if (!Auth::id()) {
      $this->view->renderJSON(array('error' => 'Для редактированя задач, необходимо авторизоваться'));
    }
    
    if (!Auth::isAdmin()) {
      $this->view->renderJSON(array('error' => 'Для удаления, вы должны быть администратом'));
    }
    
    $this->model->delete('tasks', $input['id']);
    
    $this->view->renderJSON(array('success' => true));
  }
  
  public function save($input){
   
    if (!isset($input['text']) || empty($input['text'])) {
      $this->view->renderJSON(array('error' => 'Необходимо указать текст'));
    }
    
    if ($input['id'] && !Auth::id()) {
      $this->view->renderJSON(array('error' => 'Для редактированя задач, необходимо авторизоваться'));
    }
    
    // Если пользователь не авторизован проверяем присланный емэйл
    if (!$this->user) {
      
      if (!isset($input['email']) || empty($input['email'])) {
        $this->view->renderJSON(array('error' => 'Необходимо указать email'));
      }
      
      if (!isset($input['name']) || empty($input['name'])) {
        $this->view->renderJSON(array('error' => 'Необходимо указать Имя'));
      }
      
      // проверяем есть ли такой пользователь если нет то создаём
      $login = new \App\Models\LoginModel();
      if (!($id = $login->checkEmail('id', $input['email'])['id'])) {
        
        // Создаём аккаунт без авторизации
        $id = $login->createAccount(array(
          'email' => $input['email'],
          'name' => $input['name'],
        ));
      }
    } else {
      $id = Auth::id();
    }
 
    // Если это обновение то проверяем админские права
    if ($input['id']) {
      
      // Проверяем админ ли это
      if (!Auth::isAdmin()) {
        $this->view->renderJSON(array('error' => 'Вы не являетесь администратором'));
      }
      
      // Проверяем присланный статус
      if (!($input['status'] & (self::ADMIN_FLAG_ACTIVE | self::ADMIN_FLAG_DELETE | self::ADMIN_FLAG_CLOSED))) {
        $this->view->renderJSON(array('error' => 'Неизвестный флаг статуса'));
      }
      
      $update = [];
      
      // Проверяем изменялся ли текст задачи
      if ($input['thash'] != $this->genHash('text_len'.mb_strlen($input['text']))) {
        
        // Записываем флаг админа о том что он изменил текст задачи
        $input['status'] |= self::ADMIN_FLAG_EDITED;
        
        // Записываем присланный текст
        $update['text'] = $input['text'];
      }
      
      $update['status'] = $input['status'];
      
      // Обновляем данные в задаче
      $this->model->update('tasks', $input['id'], $update);
      $result_id = $input['id'];
      
      $this->view->renderJSON(array('success' => true, 'text' => 'Заметка успешно отредактирована'));
    } else {
      $result_id = $this->model->insert('tasks', array(
        'text' => $input['text'],
        'time' => time(),
        'status' => self::ADMIN_FLAG_ACTIVE,
        'user_id' => $id
      ));
    }
    
    $this->view->renderJSON(array('success' => true, 'id' => $result_id, 'date' => date('d-m-Y H:i:s'), 'text' => 'Задача успешно добавлена'));
  }
  
  
  private function getStatusMask($mask){
    $status_text = [0 => 'Активна', 1 => 'Отр. Администратором', 2 => 'Удалена', 3 => 'Закрыта'];
    $status_cls = [0 => 'info', 1 => 'primary', 2 => 'danger', 3 => 'success'];
    $result = [];
    for ($i = 0; $i < 4; $i++) {
      if ((1 << $i) & $mask) {
        $result[] = ['cls' => $status_cls[$i], 'name' => $status_text[$i]];
      }
    }
    return $result;
  }
}