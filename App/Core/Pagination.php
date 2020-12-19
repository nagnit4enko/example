<?php

namespace App\Core;

class Pagination {
  protected $offset = 0;
  protected $current_page = 0;
  public $page_refers_per_page = 6;
  public $limit = 0;
  
  function __construct($view){
    $this->view = $view;
  }
  
  public function getOffset($current_page, $limit){
    $this->current_page = $current_page <= 0 ? 1 : $current_page;
    $this->limit = $limit;
    $this->offset = $this->current_page > 1 ? ($this->current_page - 1) * $this->limit : 0;
    return $this->offset;
  }
  
  public function getPagination($count){

    $pages_count = ceil($count / $this->limit);
    if ( $pages_count <= 1 ) return '';

    $this->pages = [];
    $start_page = $this->current_page >= $this->page_refers_per_page ? ($this->current_page - ceil($this->page_refers_per_page / 2) ) : 1; 
    
    // Если это не первая страница то выводим сдвиг влево на страницу
    if ($this->current_page > 1) {
      $this->pages[] = $this->renderLink( $this->current_page - 1, '<i class="fa fa-chevron-left"></i>');
    }
    
    // Если страница ушла за разрешённое количество
    if ($this->current_page >= $this->page_refers_per_page) {
      
      // Выводим первую страницу
      $this->pages[] = $this->renderLink(1, 1);
      
      // Если старт пагинации начинается больше 3 страницы то выводим двоеточия
      if ($start_page > 3) {
        $this->pages[] = $this->renderLink($start_page - 1, '..');
      } else if ($start_page <= 3) {
        $this->pages[] = $this->renderLink(2, 2);
      }
    }
 
    $page_refers_per_page_count = $this->current_page + ceil($this->page_refers_per_page / 2) + 1;
    $page_refers_per_page_count = $page_refers_per_page_count > $pages_count ? $pages_count : $page_refers_per_page_count;
    for ($i = $start_page; $i <= $page_refers_per_page_count; $i++) {
      $this->pages[] = $this->renderLink($i, $i, ($i == $this->current_page));
    }

    if ($i <= $pages_count) {
      
      // Если в самом конце выводится  прим: 15 .. 17 не выводим точки ставим цифру
      if ($pages_count > $i && $pages_count - 1 != $i) {
        $this->pages[] = $this->renderLink( $i, '..');
      } else {
        $this->pages[] = $this->renderLink( $i, $i);
      }
      
      // Показываем последнюю страницу во всех случаях если она не выводилась
      if ($i < $pages_count) {
        $this->pages[] = $this->renderLink($pages_count, $pages_count);
      }
    }
    
    if ($this->current_page < $pages_count) {
      $this->pages[] = $this->renderLink($this->current_page + 1, '<i class="fa fa-chevron-right"></i>');
    }
    
    return $this->pages;
  }
  
  public function render($template){
    return $this->view->render($template, '', array('pagination' => $this->pages));
  }
  
  public function generateLink($page_id){
    return \App::$router->getLink(array(
      'page_id' => $page_id
    ), true);
  }
  
  private function renderLink($page_id, $name = '', $active = false) {
    $link = $this->generateLink($page_id);
    return [$link, $name, $active];
  }
}