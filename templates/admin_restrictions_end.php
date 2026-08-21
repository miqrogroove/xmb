   <div class="row">
    <div class="field span">
     <div class="category-head"><?= $lang['textnewcode'] ?></div>
     <div class="new-instructions">
      <?= $lang['newrestriction'] ?><br />
      <?= $lang['newrestrictionwhy'] ?>
     </div>
     <div class="xmb-grid-wrap admin-restrictions-new-wrap">
      <div class="xmb-grid admin-restrictions-new">
       <div class="row">
        <div class="label">name:</div>
        <div class="field"><input type="text" size="30" name="newname" /></div>
       </div>
       <div class="row">
         <div class="label">case-sensitive:</div>
         <div class="field"><input type="checkbox" name="newcase" value="1" /></div>
       </div>
       <div class="row">
         <div class="label">partial-match:</div>
         <div class="field"><input type="checkbox" name="newpartial" value="1" checked="checked" /></div>
       </div>
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>
 <br />
 <div align="center"><input class="submit" type="submit" name="restrictedsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></div>
</form>
