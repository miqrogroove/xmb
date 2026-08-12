<?php

declare(strict_types=1);

namespace XMB;

?>
      </tbody>
     </table>
    </div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-label"><?= $lang['textuserlist'] ?></div>
    <div class="xmb-grid-form-field">
     <textarea rows="4" cols="30" name="userlistnew">
<?php // Linefeed required here - Do not edit!
    echo $forum['userlist'];
?></textarea>
    </div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-label">
     <div><?= $lang['forumpw'] ?></div>
    </div>
    <div class="xmb-grid-form-field vertical-center"><input type="text" name="passwordnew" value="<?= htmlEsc($forum['password'], storedData: true) ?>" /></div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-label"><?= $lang['textdeleteques'] ?></div>
    <div class="xmb-grid-form-field"><input type="checkbox" name="delete" value="<?= $forum['fid'] ?>" /></div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-field span submit"><input type="submit" name="forumsubmit" value="<?= $lang['textsubmitchanges'] ?>" class="submit" /></div>
   </div>
  </div>
 </div>
</form>
