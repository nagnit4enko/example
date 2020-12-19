
<div class="col-sm-12">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      Созданные задачи
      <div class="pull-right">
        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#myModal">Создать заметку</button>
      </div>
    </div>
    <table class="table table-striped m-b-none dataTable no-footer">
      <thead>
        <tr>
          <th style="width:2px;" class="text-center">ID</th>
          <th style="width: 300px;" class="text-center">Задача</th>
          <th style="width:90px;">Дата создания</th>
          <th style="width:80px;" class="sorting<?=$sorting['status'][0]?>" onclick="location.href='<?=$sorting['status'][1]?>'">Статус задачи</th>
          <th style="width:90px;" class="sorting<?=$sorting['name'][0]?>" onclick="location.href='<?=$sorting['name'][1]?>'">Пользователь</th>
          <th style="width:70px;" class="sorting<?=$sorting['email'][0]?>" onclick="location.href='<?=$sorting['email'][1]?>'">Email</th>
          <?if ($this->user['admin']) {?>
            <th style="width:90px;">Изменить</th>
            <th style="width:70px;">Удалить</th>
          <?}?>
        </tr>
      </thead>
      <?foreach ($select as $row) {?>
          <tr id="task<?=$row['task_id']?>">
            <td><?=$row['task_id']?></td>
            <td><?=$row['text']?></td>
            <td><?=date('d-m-Y H:i:s', $row['time'])?></td>
            <td data-status="<?=$row['status']?>">
              <?
                foreach ($row['status_mask'] as $rel) {
                  ?><span class="label bg-<?=$rel['cls']?>"><?=$rel['name']?></span> <?
                }
              ?>
            </td>
            <td><?=$row['name']?></td>
            <td><?=$row['email']?></td>
            <?if (isset($row['hash'])) {?>
              <td><a onclick="edit(<?=$row['task_id']?>, '<?=$row['hash']?>', '<?=$row['text_hash']?>');" data-toggle="modal" data-target="#myModal"><i class="fa  fa-edit text-success"></i></a></td>
              <td><a onclick="remove(<?=$row['task_id']?>, '<?=$row['hash']?>');"><i class="fa fa-times text-success "></i></a></td>
            <?}?>
          </tr>
      <?}?>
    </table>
  </div>
  <?=$pagination?>
</div>
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content" >
      <div class="ng-scope">
        <div class="modal-header ng-scope">
          <h3 class="modal-title">Создание задачи</h3>
        </div>
        <div class="modal-body ng-scope">
          <div class="panel-body">
            <div id="result-info" class="text-danger wrapper text-center ng-binding hide"></div>
            <div id="user-data" class="form-group pull-in clearfix">
              <div class="col-sm-6">
                <label>Ваше имя</label>
                <input class="form-control " name="name" placeholder="Имя" type="text">
              </div>
              <div class="col-sm-6">
                <label>Email</label>
                <input class="form-control" name="email" placeholder="Email" type="email">
              </div>
            </div>
            <div id="status" class="form-group pull-in clearfix" style="display: none">
              <div class="col-sm-12">
                <label>Статус</label>
                <select name="status" class="form-control m-b">
                  <option value="1">Активна</option>
                  <option value="4">Удалена</option>
                  <option value="8">Завершена</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label>Текст задачи</label>
              <textarea class="form-control" name="text" rows="6" placeholder="Введите описание задачи"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer ng-scope">                  
          <button class="btn btn-default">Отмена</button>
          <button id="save-task" class="btn btn-primary">Сохранить</button>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$('#save-task').on('click', save);
$('#myModal').on('hidden.bs.modal', function (e) {
  $('#save-task').on('click', save);
  var el = $('#myModal');
  $('[name="text"]', el).val('');
  $('#user-data', el).show();
  $('#status', el).hide();
  $('.modal-title', el).text('Создание задачи');
});
  
  function setInput(el, name, value){
    $('<input />', {type: 'hidden', name: name, value: value}).appendTo(el);
  }
  function getQueryFormParams (el) {
    var query = {};
    $.each(el, function(i, v){
      query[v.name] = v.value;
    });
    return query;
  }
  
  function edit (id, hash, thash) {
    var el = $('#myModal');
    setInput(el, 'id', id);
    setInput(el, 'hash', hash);
    setInput(el, 'thash', thash);
    $('#status', el).show();
    $('#user-data', el).hide();
    $('[name="text"]', el).val($('td', $('#task' + id)).eq(1).text());
    $('.modal-title', el).text('Изменение задачи');
    
    $('#save-task').off('click').on('click', function (){
      var query = getQueryFormParams($('#myModal [name]'));
      var mask = 0;
      var old_mask = $('td', $('#task' + id)).eq(3).data('status');
      if ((1 << 1) & old_mask) {
        query.status = parseInt(query.status);
        query.status += (1 << 1);
      }

      if (!query.text.length) return curInput($('[name="text"]').get(0));
      $.post('/index/save/', query, function(res){
        if (res.error) {
          if (res.errName) {
            return curInput($('[name="' + res.errName + '"]').get(0));
          }
          return $('#result-info').removeClass('text-success').removeClass('hide').addClass('text-danger').html(res.error);
        }
        $('#result-info').removeClass('text-danger').removeClass('hide').addClass('text-success').html(res.text);
      });
    });
  }
  
  function remove (id, hash){
    $.post('/index/remove/', {id: id, hash: hash}, function(res){
      if (res.error) return alert(res.error); 
      $('#task' + id).remove();
    });
  }
  function save(){
    var query = getQueryFormParams($('#myModal [name]'));
    if (!query.name.length) return curInput($('[name="name"]').get(0));
    if (!query.email.length || !validateEmail(query.email)) return curInput($('[name="email"]').get(0));
    if (!query.text.length) return curInput($('[name="text"]').get(0));
    $.post('/index/save/', query, function(res){
      if (res.error) {
        return curInput($('[name="' + res.errName + '"]').get(0));
      }
      
      var html = $('<tr id="task' + res.id + '">\
            <td>' + res.id + '</td>\
            <td>' + query.text + '</td>\
            <td>' + res.date + '</td>\
            <td><span class="label bg-info">Активна</span></td>\
            <td>' + query.name + '</td>\
            <td>' + query.email + '</td>\
            <?if ($this->user['admin']) {?>\
              <td><span onclick="edit(' + res.id + ');"><i class="fa  fa-edit text-success text-active"></i></span></td>\
              <td><span onclick="remove(' + res.id + ');"><i class="fa fa-times text-success text-active"></i></span></td>\
            <?}?>\
          </tr>');
      html.appendTo($('table>tbody'));
      $('#result-info').removeClass('text-danger').removeClass('hide').addClass('text-success').html(res.text);
    });
  }
</script>