<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?=self::getTitle()?></title>
    <meta name="description" content="app, web app, responsive, responsive layout, admin, admin panel, admin dashboard, flat, flat ui, ui kit, AngularJS, ui route, charts, widgets, components" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="stylesheet" href="/css/bootstrap.css" type="text/css" />
    <link rel="stylesheet" href="/css/font-awesome.min.css" type="text/css" />
    <link rel="stylesheet" href="/css/simple-line-icons.css" type="text/css" />
    <link rel="stylesheet" href="/css/font.css" type="text/css" />
    <link rel="stylesheet" href="/css/app.css" type="text/css" />
    <link rel="stylesheet" href="/css/dataTables.bootstrap.css" type="text/css" />
    
		<script src="/js/jquery.min.js" type="text/javascript"></script>
		<script src="/js/bootstrap.js" type="text/javascript"></script>
		<script src="/js/common.js" type="text/javascript"></script>
		<script type="text/javascript">

		</script>
  </head>
  <body>
    <div class="app ng-scope app-header-fixed" id="app" style="">
      <?
      if ($this->getProperty('header')) {
        echo $this->render('header.php');
      }
      ?>
      <div class="wrapper-md ng-scope">
        <div class="row">
          <?require_once ROOT .'/App/Views/'.$content_view?>
        </div>
      </div>
    </div>

  </body>
</html>