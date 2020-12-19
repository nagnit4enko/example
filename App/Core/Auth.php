<?php

namespace App\Core;

class Auth {
  public static function checkSession() {
    return isset($_SESSION['user_data']);
  }
  
  public static function login($data){
    $_SESSION['user_data'] = $data;
  }
  
  public static function logout(){
    unset($_SESSION['user_data']);
  }
  
  public static function getUserInfo(){
    return $_SESSION['user_data'];
  } 
  
  public static function id(){
    return isset($_SESSION['user_data']) ? $_SESSION['user_data']['id'] : 0;
  } 
  
  public static function isAdmin(){
    return $_SESSION['user_data']['admin'];
  } 
}