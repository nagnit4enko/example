<?php
namespace App\Core;

class Model {
	private static $link;
  
  private function __connect() {
    if (!self::$link) {
      
      // Подключаем конфиг
      $config = require_once ROOT. '/App/Configs/DB.php';
      
      // Создаём запрос с бд
      if (!(self::$link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['db'], $config['port']))) {
        throw new \App\Exceptions\DbException('Не удалось установить соединение с базой данных "'.mysqli_connect_error().'"');
      }
      
      // Устанавливаем кодировку
      mysqli_query(self::$link, "SET NAMES '{$config['charset']}'");
		}
  }
  
  public function select($query, $multiple = false){

    if (!$query) {
      throw new \App\Exceptions\DbException("Error not set query", 1);
    }
    $query_id = $this->set($query);
    if( !$multiple ){
      $result = mysqli_fetch_array($query_id, MYSQLI_ASSOC);
    }else{
      $result = array();
      while($row = mysqli_fetch_array($query_id, MYSQLI_ASSOC)){
        $result[] = $row;
      }
    }
    return $result;
  }

  public function set($data_query, $insert_id = false){
    if (self::$link === null) {
      $this->__connect();
    }
    if (($query_id = mysqli_query( self::$link, $data_query ))) {
      if ($insert_id) {
        return mysqli_insert_id(self::$link);
      }
      return $query_id;
    }
    throw new \App\Exceptions\DbException('MYSQL ERROR ' . mysqli_error ( self::$link ) . ', SQL Query: ' . $data_query, 1);
    return false;
  }
	
  public function prepareLimit($limit){
    if (is_integer($limit)) return $limit;
    return implode(',', $limit);
  }
  
  // order = [['id' => 'asc']]
  public function prepareOrder($order){
    $query = [];
    foreach ($order as $row) {
      foreach ($row as $k => $v){
        $query[] = $k.' '.$v;
      }
    }
    return $query;
  }
  
  public function prepareQuery($query_args){
    $query = [];
    foreach ($query_args as $key => $value) {
      $query[] = "`{$key}`='".$value."'";
    }

    if (empty($query)) {
      throw new Exception("Нельзя указать запрос без параметров");
    }

    return $query;
  }

  public function prepareWhere($args) {
    if (is_numeric($args)) {
      return "`id`='{$args}'";
    }
    // Собираем запрос
    $delemiter = array('+','-','>=','=<','>','<','!','=');
    $preg_match_regex = preg_quote(implode('|', $delemiter));
    $query_where = [];
    foreach ($args as $key => $value) {
      if (is_array($value)) {
        if (empty($value)) {
          throw new Exception("Запрос массивом может быть только с передачей INT параметров");
        }
        $query_where[] = "`{$key}` IN (".implode(',', $value).")";
      } else if (preg_match('/\w+(.+)?/i', $key, $delim)) {
        $key = str_replace($delemiter, '', $key);
        $query_where[] = "`$key`{$delim[1]}'{$value}'";
      } else {
        $query_where[] = "`{$key}`='{$value}'";
      }
    }
    return $query_where;
  }
}