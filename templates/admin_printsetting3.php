   <div class="row">
    <div class="label"><?= $setname ?></div>
    <div class="field"><select <?= ($multi ? 'multiple="multiple"' : '') ?> name="<?= $boxname ?><?= ($multi ? '[]' : '') ?>"><?= $optionlist ?></select></div>
   </div>
