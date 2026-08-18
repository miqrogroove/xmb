   <div class="row">
    <div class="field span submit">
     <input type="submit" class="submit" name="membersubmit" value="<?= $lang['textsubmitchanges']; ?>" onclick="return confirmUserDel('<?= $lang['confirmDeleteUser']; ?>');" />
     <input type="hidden" name="srchmem" value="<?= $srchmem; ?>" />
     <input type="hidden" name="srchemail" value="<?= $srchemail; ?>" />
     <input type="hidden" name="srchip" value="<?= $srchip; ?>" />
     <input type="hidden" name="srchstatus" value="<?= $srchstatus; ?>" />
    </div>
   </div>
  </div>
 </div>
</form>
