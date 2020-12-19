<?php

namespace App\Controllers;

class Error extends \App\Core\Controller {
  
  function __construct(){
    parent::__construct();
  }
  
  public function error404($input){
    $this->_setError(404, $input);
  }
  
  public function error500($input){
    $this->_setError(500, $input);
  }
  
  private function _setError($error, $exc){
    $this->view->setProperty('header', false);
    echo $this->view->render('error.php', 'layout.php', ['error' => $error, 'error_message' => \App\Core\Auth::isAdmin() ? $exc->getMessage() : '']);
  }
}