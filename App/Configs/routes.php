<?php
namespace App\Core;
return [
  new Route('/', 'index', 'index'),
  new Route('/index/save/', 'index', 'save'),
  new Route('/index/remove/', 'index', 'remove'),
  new Route('/login/', 'login', 'auth'),
  new Route('/login/auth/', 'login', 'setAuth'),
  new Route('/login/logout/', 'login', 'logout'),
  new Route('/sort/:field/:sort/', 'index', 'index'),
  new Route('/page/:page_id/', 'index', 'index'),
  new Route('/sort/:field/:sort/page/:page_id/', 'index', 'index')
];