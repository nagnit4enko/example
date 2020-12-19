<?php
namespace App\Core;

class Router {
  protected $input = [];
  private $routes = [];
  private $request = [];
  private $uri = '';
  
  public function init($routes, $uri) {
    $this->routes = $routes;
    
    /*
      Обрезаем по вопросу сопутсвующие аргументы их добавим позднее
		*/
    list($uri, $request) = explode('?', $uri);
    
    // Если есть аргументы преобразим их в массив
    if (!empty($request)) {
      mb_parse_str($request, $this->request);
    }
    
    // Прогоняем все пути через регулярку
		foreach ($routes as $route) {
      $this->current_path = $route->path;
      
			$pattern = $this->createPattern($this->current_path);

			/*
				Проверяем адрес URI на соответствие регулярке
			*/
			if (preg_match($pattern, $uri, $params)) {
        
        // очищаем параметры от элементов с числовыми ключами
				$this->input = $this->clearParams($params);
        
        // ќбрабатываем все аргументы на предмет XSS
        $this->request = array_merge($this->request, $this->_secure($_REQUEST));
        $this->request = $this->_secure($this->request);
        $this->input = $this->_secure($this->input);

        // соедин¤ем с request аргументами
        $this->input = array_merge($this->request, $this->input);

        return $this->initController($route->controller, $route->action, $this->input);
			}
		}
    
    throw new \App\Exceptions\InvalidRouteException('Страница не найдена');
	}
	
  public function initController($className, $action, $input = []){
    
    // собираем класс
    $fullName = "\\App\\Controllers\\".ucfirst($className);
    
    try {
      $controller = new $fullName;
      if (method_exists($controller, $action)) {
        $controller->$action($input);
      } else {
        throw new \App\Exceptions\InvalidRouteException('Страница не найдена');
      }
    } catch (\Exception $e) {
      exit($e->getMessage());
    }
  }
	
  public function getLink($input, $new_uri = false){
   
    $path = $this->current_path;
    $input = array_merge($this->input, $input);

    if ($new_uri) {
      $path = $this->findPath($input);
    }

    $path = preg_replace_callback('#:([^/]+)#', function ($m) use ($input) {
      return $input[$m[1]];
    }, $path);

    if (!empty($this->request)) {
      $path .= '?'.http_build_query($this->request);
    }

    return $path;
  }
  
  public static function Error404(){
    header('HTTP/1.1 404 Not Found');
    
  }
  
  public function redirect($loc){
    header('location: '.$loc).exit;
  }
  
  public function checkVaritabe($key, $type_validate, $method = 0){
    if ($method == 1) {
      $value = isset($_POST[$key]) ? $_POST[$key] : false;
    } elseif ($method == 2) {
      $value = isset($_COOKIE[$key]) ? $_COOKIE[$key] : false; 
    } elseif ($method == 3) {
      $value = $key;
    } else {
      $value = isset($_REQUEST[$key]) ? $_REQUEST[$key] : false;
    }
    
    if ($type_validate == 'i') {
      return (int)$value;
    } elseif ($type_validate == 'st' && $value != false) {
      $find = '/data:|about:|vbscript:|onclick|onload|onunload|onabort|onerror|onblur|onchange|onfocus|onreset|onsubmit|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmouseup|onmouseover|onmouseout|onselect|javascript/i';
      return preg_replace_callback($find, function($m){
        return strtr($m[0], [
          'T' => '&#84;',
          't' => '&#116;',
          'O' => '&#79;',
          'o' => '&#111;'
        ]);
      }, htmlspecialchars(trim(addslashes($value))));
    } elseif ($type_validate == 'fl') {
      return floatval($value); 
    } elseif ($type_validate == 'b') {
      return $value ? true : false;
    }
    return false;
  }
  
  /*
    Приватные функции класса
  */
  
  private function findPath($input){
    
    $arr = [];
    
    // Собираем паттерн по которой будем искать
    $regex = ':'.implode('|:', array_keys($input));
    
    // Прогоняем все пути 
    foreach ($this->routes as $pos => $route) {
      $count = preg_match_all('@('.$regex.')@i', $route->path);
      $arr[$pos] = [$route->path, $count, substr_count($route->path, ':')];
    }
    
    // Сортируем по наиболее подходящему количеству вхождений и по количеству аргументов
    usort($arr, function($a, $b){
      return $a[1] <= $b[1] && $a[1] <= $a[2] && $b[1] >= $b[2];
    });
    
    // Возвращаем последний наиболее подходящий паттерн
    return $arr[0][0];
  }
  
	private function clearParams($params) {
		$result = [];
			
		foreach ($params as $key => $param) {
			if (!is_int($key)) {
				$result[$key] = $param;
			}
		}
			
		return $result;
	}
  
  private function createPattern($path) {
		return '#^' . preg_replace('#/:([^/]+)#', '/(?<$1>[^/]+)', $path) . '/?$#';
	}
  
  private function _secure($data){
    $input = [];
    if (count($data) > 0) {
      foreach ($data as $key => $value) {
      
        $key = $this->checkVaritabe($key, 'st', 3);
        
        // array recursive
        if (is_array($value)) {
          $input[$key] = $this->_secure($value);
        } 
        // int | float
        else if (is_numeric($value)) {
          if (intval($value) != $value) {
            $input[$key] = floatval($value);
          } else {
            $input[$key] = (int)$value;
          }
        } 
        // boolean
        else if ($value == 'false' || $value == 'true'){
          $input[$key] = ($value == 'true');
        } 
        // string
        else {
          $input[$key] = $this->checkVaritabe($value, 'st', 3);
        }
      }
    }
    return $input;
  }
}