<form method="post" action="<?= $full_url ?>admin/forums.php?fdetails=<?= $fdetails ?>">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="xmb-block-wrap admin-forums-detail-wrap">
  <div class="xmb-grid admin-forums-detail">
   <div class="row">
    <div class="category-head span"><?= $lang['textforumopts'] ?></div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-label"><?= $lang['textforumname'] ?></div>
    <div class="xmb-grid-form-field"><input type="text" name="namenew" size="50" value="<?= $forum['name']; ?>" /> <?= $lang['texthtmlis'] ?> <?= $lang['texton'] ?></div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-label"><?= $lang['textdesc'] ?></div>
    <div class="xmb-grid-form-field">
     <textarea rows="4" cols="30" name="descnew">
<?php // Linefeed required here - Do not edit!
    echo $forum['description'];
?></textarea> <?= $lang['texthtmlis'] ?> <?= $lang['texton'] ?>
    </div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-label"><?= $lang['textallow'] ?></div>
    <div class="xmb-grid-form-field">
     <label><input type="checkbox" name="allowsmiliesnew" value="yes" <?= $checked3 ?> /><?= $lang['textsmilies'] ?></label><br />
     <label><input type="checkbox" name="allowbbcodenew" value="yes" <?= $checked4 ?> /><?= $lang['textbbcode'] ?></label><br />
     <label><input type="checkbox" name="allowimgcodenew" value="yes" <?= $checked5 ?> /><?= $lang['textimgcode'] ?></label><br />
     <label><input type="checkbox" name="attachstatusnew" value="on" <?= $checked6 ?> /><?= $lang['attachments'] ?></label>
    </div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-label"><?= $lang['texttheme'] ?></div>
    <div class="xmb-grid-form-field"><?= $themelist ?></div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-label"><?= $lang['forumpermissions'] ?></div>
    <div class="xmb-grid-form-field">
     <table class="permissions-grid" style="width: 100%; text-align: center;">
      <colgroup span=5 />
      <thead>
       <tr>
        <td class="tablerow">&nbsp;</td>
        <th class="category-head" scope="col"><?= $lang['polls'];   ?></th>
        <th class="category-head" scope="col"><?= $lang['threads']; ?></th>
        <th class="category-head" scope="col"><?= $lang['replies']; ?></th>
        <th class="category-head" scope="col"><?= $lang['view'];    ?></th>
       </tr>
      </thead>
      <tbody>
