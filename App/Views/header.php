<div class="app-header ng-scope">
  <div class="collapse pos-rlt navbar-collapse box-shadow bg-white-only" style="margin-left: 0;">
    <ul class="nav navbar-nav navbar-right">
      <?if ($this->user) {?>
        <li class="dropdown">
          
          <a  href="" class="dropdown-toggle clear" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="thumb-sm avatar pull-right m-t-n-sm m-b-n-sm m-l-sm">
              <img src="/img/a0.jpg" alt="...">
              <i class="on md b-white bottom"></i>
            </span>
            <span class="hidden-sm hidden-md"><?echo !empty($this->user['name']) ? $this->user['name'] : $this->user['email']?></span> <b class="caret"></b>
          </a>
          <ul class="dropdown-menu animated fadeInRight w">
            <li>
              <a href="/login/logout/">Выход</a>
            </li>
          </ul>
        </li>
      <?} else {?>
        <li class="hidden-xs">
          <a href="/login/" class="btn "> Авторизация </a>
        </li>
      <?}?>
    </ul>
  </div>
</div>