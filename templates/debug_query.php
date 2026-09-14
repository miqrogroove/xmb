<div class="xmb-grid debug-query">
 <div class='row'>
  <div class='naked-cell naked-header'>#</div>
  <div class='naked-cell naked-header'>Duration:</div>
  <div class='naked-cell naked-header'>Query:</div>
 </div>
<?php foreach ($stuff as $row) { ?>
 <div class='row'>
  <div class='naked-cell naked-header'><?= $row['number'] ?>.</div>
  <div class='naked-cell'><?= $row['time'] ?></div>
  <div class='naked-cell'><?= $row['val'] ?></div>
 </div>
<?php } ?>
</div>
