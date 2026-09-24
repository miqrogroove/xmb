<div class="row">
 <div class="field icon ctrtablerow"><?= $folder ?></div>
 <div class="field description"><a href="<?= $full_url ?>forumdisplay.php?fid=<?= $forum['fid'] ?>"><span class="mediumtxt"><strong><?= $forum['name'] ?></strong></span><br /><br /><span class="smalltxt"><?= $forum['description'] ?></span></a><span class="smalltxt plainlinks"><?= $forum['moderator'] ?></span><span class="smalltxt"><?= $subforums ?></span></div>
 <div class="field thread-count mediumtxt"><?= $forum['threads'] ?></div>
 <div class="field post-count mediumtxt"><?= $forum['posts'] ?></div>
 <div class="field lastpostcell"><?= $lastpostrow ?></div>
</div>
