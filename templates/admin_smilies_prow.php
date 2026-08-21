   <div class="row icon-list">
    <div class="field delete"><input type="checkbox" name="pidelete[<?= $smilie['id'] ?>]" value="1" /></div>
    <div class="field span"><input type="text" name="piurl[<?= $smilie['id'] ?>]" value="<?= $smilie['url'] ?>" /></div>
    <div class="field icon"><img src="<?= $full_url ?><?= $THEME['smdir'] ?>/<?= $smilie['url'] ?>" alt="<?= $smilie['url'] ?>" /></div>
   </div>
