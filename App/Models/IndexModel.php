<?php
namespace App\Models;
use \App\Core\Model;

class IndexModel extends Model {
	
	public function getList($offset = 0, $limit = 10, $sort_field = 'id', $sort = 'ASC'){
		return $this->select("SELECT tb1.id as task_id, text, status, time, tb2.id,name,email,admin FROM tasks tb1 LEFT JOIN users tb2 ON tb1.user_id=tb2.id  ORDER BY {$sort_field} {$sort}, task_id {$sort} LIMIT {$offset},{$limit}", 1);
	}
  
  public function getCount(){
    return $this->select("SELECT COUNT(id) as cnt FROM tasks ");
  }
  
  public function update($table, $id, $params){
    if (!$id || empty($params)) {
      throw new App\Exceptions\IndexHandler('Не установлен айди, или параметры таблицы');
    }
    $query = $this->prepare($params);
    return $this->set("UPDATE `{$table}` SET {$query} WHERE id='{$id}'");
  }
  
  public function insert($table, $params){
    $query = implode(',', $this->prepareQuery($params));
    return $this->set("INSERT INTO `{$table}` SET {$query} ", 1);
  }
  
  public function delete($table, $id){
    return $this->set("DELETE FROM `{$table}` WHERE id='{$id}' ");
  }
  
  public function prepare($params){
    $query = [];
    foreach ($params as $key => $value) {
      $query[] = "`{$key}`='{$value}'";
    }
    return implode(',', $query);
  }
}
