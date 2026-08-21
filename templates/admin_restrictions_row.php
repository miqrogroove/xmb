   <div class="row">
    <div class="field delete"><input type="checkbox" name="delete<?= $restricted['id'] ?>" value="<?= $restricted['id'] ?>" /></div>
    <div class="field"><input type="text" size="30" name="name<?= $restricted['id'] ?>" value="<?= $restricted['name'] ?>" /></div>
    <div class="field check"><input type="checkbox" name="case<?= $restricted['id'] ?>" value="<?= $restricted['id'] ?>" <?= $case_check ?> /></div>
    <div class="field check"><input type="checkbox" name="partial<?= $restricted['id'] ?>" value="<?= $restricted['id'] ?>" <?= $partial_check ?> /></div>
   </div>
