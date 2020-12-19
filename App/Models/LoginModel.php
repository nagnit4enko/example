<?php

namespace App\Models;

class LoginModel extends \App\Core\Model {
  
  public function checkUserPass($email, $pass, $query){
    $pass = $this->hashPass($pass);
    $query = implode(',', $query);
    return $this->select("SELECT {$query} FROM `users` WHERE email='{$email}' AND pass='{$pass}'");
  }
  
  public function checkEmail($query, $email){
    return $this->select("SELECT {$query} FROM `users` WHERE email='{$email}'");
  }
  
  public function createAccount($opts){
    if (isset($opts['pass'])) {
      $opts['pass'] = $this->hashPass($opts['pass']);
    }
    $query = implode(',', $this->prepareQuery($opts));
    return $this->set("INSERT INTO `users` SET {$query}", 1);
  }
  
  protected function hashPass($pass){
    return md5($pass);
  }
}