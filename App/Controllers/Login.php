<?php

namespace App\Controllers;

use \App\Models\LoginModel;
use \App\Core\Auth;

class Login extends \App\Core\Controller {
  
  function __construct(){
    parent::__construct();
    $this->model = new LoginModel();
  }
  
  public function auth(){
    if ($this->user) {
      \App::$router->redirect('/');
    }
    
    $this->view->setProperty('header', false);
    echo $this->view->render('login.php', 'layout.php');
  }
  
  public function logout(){
    Auth::logout();
    \App::$router->redirect('/');
  }
  
  public function setAuth($input){
    
    // Проверяем присланные данные
    if (!isset($input['email']) || !isset($input['pass'])) {
      $this->view->renderJSON(['error' => 'Одно из полей не заполнено']);
    }

    // Валидация 
    if (!$this->validateEmail($input['email'])) {
      $this->view->renderJSON(['error' => 'Неправильный Email', 'errName' => 'email']);
    }
    
    if (mb_strlen($input['pass']) < 3) {
      $this->view->renderJSON(['error' => 'Пароль должен быть не менее 3 символов']);
    }
    
    // Проверяем есть ли такой пользователь
    $result = $this->model->checkUserPass($input['email'], $input['pass'], ['id','name', 'email', 'admin']);
    
    if (empty($result)) {
      $this->view->renderJSON(['error' => 'Ошибка авторизации пользователь не найден']);
    }
   
    // Авториуем пользователя
    Auth::login($result);
    
    // Возвращаем результат
    $this->view->renderJSON(['success' => true]);
  }
  
  private function validateEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }
}