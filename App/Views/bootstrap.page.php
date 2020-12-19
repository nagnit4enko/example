<div class="text-center m-t-lg m-b-lg">
  <ul class="pagination pagination-md">
    <?if ($pagination) foreach ($pagination as $row) {?>
       <li <?=($row[2] ? 'class="active"' : '')?>><a href="<?=$row[0]?>"><?=$row[1]?></a></li>
    <?}?>
  </ul>
</div>