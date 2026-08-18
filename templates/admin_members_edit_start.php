<script type="text/javascript">var delmem = Array();</script>
<form method="post" action="<?= $full_url ?>admin/members.php">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="xmb-block-wrap admin-members-result-wrap">
  <div class="xmb-grid admin-members-result">
   <div class="row">
    <div class="category-head"><?= $lang['textdeleteques'] ?></div>
    <div class="category-head"><?= $lang['textusername'] ?></div>
    <div class="category-head"><?= $lang['textnewpassword'] ?></div>
    <div class="category-head"><?= $lang['textposts'] ?></div>
    <div class="category-head"><?= $lang['textstatus'] ?></div>
    <div class="category-head"><?= $lang['textcusstatus'] ?> <span class="normal-text">[<?= $lang['texthtmlis'] ?> <?= $lang['texton']?>]</span></div>
    <div class="category-head"><?= $lang['textbanfrom'] ?></div>
   </div>
