   <div class="row">
    <div class="field delete"><input type="checkbox" name="smdelete[<?= $smilie['id'] ?>]" value="1" /></div>
    <div class="field"><input type="text" name="smcode[<?= $smilie['id'] ?>]" value="<?= $smilie['code'] ?>" /></div>
    <div class="field"><input type="text" name="smurl[<?= $smilie['id'] ?>]" value="<?= $smilie['url'] ?>" /></div>
    <div class="field icon"><img src="<?= $full_url ?><?= $THEME['smdir'] ?>/<?= $smilie['url'] ?>" alt="<?= $smilie['code'] ?>" /></div>
   </div>
