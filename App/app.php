<?php

class App {

  public static $router;
  public static $model;
    
  public static function init(){
    spl_autoload_register(['static','load']);
    set_exception_handler(['App','exceptionHandler']);
  
    static::$router = new App\Core\Router();
    static::$model = new App\Core\Model();
    
    $config = require_once ROOT . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Configs' . DIRECTORY_SEPARATOR . 'routes.php';
    static::$router->init($config, $_SERVER['REQUEST_URI']);
  }
    
  public static function load ($className){ 
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    require_once ROOT . DIRECTORY_SEPARATOR . $className.'.php'; 
  }
  
  public static function exceptionHandler (Throwable $e) {
    if($e instanceof \App\Exceptions\InvalidRouteException) {
      static::$router->initController('Error', 'error404', $e);
    }else{
      static::$router->initController('Error', 'error500', $e);  
    }
  }
}