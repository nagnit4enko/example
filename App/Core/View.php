<?php
namespace App\Core;

class View {
  public $user = [];
  public $property = [
    'header' => true
  ];
  
  function __construct($user){
    $this->user = $user;
  }
  
  function render($content_view, $template_view = null, $data = null) {
    
    $theme = ($template_view ? $template_view : $content_view);
  
    // Если есть такой шаблон то обробатываем и выводим
    if (file_exists(ROOT .'/App/Views/'.$theme)) {
      
      if(is_array($data)) {
        extract($data);
      }
      
      ob_start();
      require_once ROOT .'/App/Views/'.$theme;
      return ob_get_clean();
    } else {
      throw new \App\Exceptions\TemplateExeption('Отсутсвует шаблон '.$theme);
    }
  }
  
  function renderJSON($json){
    $result = json_encode($json);

    header('content-type: application/json; charset=utf-8');
    header('Connection: close');
    header("Content-Length: ".mb_strlen($result));
    header("Content-Encoding: none");
    header("Accept-Ranges: bytes");

    exit($result);
  }
  
  function setTitle($title){
    $this->setProperty('title', $title);
  }
  
  function setProperty($key, $value) {
    $this->property[$key] = $value;
  }
  
  function getProperty($key) {
    if (isset($this->property[$key])) {
      return $this->property[$key];
    }
    return '';
  }
  
  function getTitle(){
    return $this->property['title'];
  }
}