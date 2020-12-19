<?php
namespace App\Core;

use \App\Core\View;
use \App\Core\Auth;

class Controller {
  
  public $user;
  
  function __construct(){
    session_start();
    
    // Проверяем сессию пользователя
    if (Auth::checkSession()) {
      $this->user = Auth::getUserInfo();
    }
    
    $this->view = new View($this->user);
  }
  
  public function setTitle($title){
    $this->view->setTitle($title);
  }
  
  public function genHash($key, $for_ip = true, $for_user_aget = false){
    $user_id = Auth::id();
    $hash = $user_id.$key;
    if($for_ip){
      $hash .= $_SERVER['HTTP_X_REAL_IP'];
    }
    if ($for_user_aget) {
      $hash .= $_SERVER['HTTP_USER_AGENT'];
    }
    return $this->genLiteHash($hash);
  }

  public function genLiteHash($key){
    return substr(md5(md5($key)), 0, 18);
  }
  
  public function setPageLink($opts){
    return \App::$router->getLink($opts, true);
  }
}