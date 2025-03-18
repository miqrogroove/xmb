<h1><?= $version ?> <?= $lang['upgrade_header'] ?></h1>

<p><?= str_replace('$ver', $version, $lang['upgrade_intro']) ?>

<h2><?= $lang['instructions'] ?></h2>
<ol>
<li><?= $lang['upgrade_step_1'] ?>
<li><?= $lang['upgrade_step_2'] ?>
<li><?= $lang['upgrade_step_3'] ?>
<li><?= $lang['upgrade_step_4'] ?>
<li><?= str_replace('$ver', $version, $lang['upgrade_step_5']) ?>
<li><?= $lang['upgrade_step_6'] ?>
<li><?= $lang['upgrade_step_7'] ?>
</ol>

<form method="get" onsubmit="this.submit1.disabled=true; return true;">
<input type="hidden" name="step" value="2" />
<p><?= $lang['upgrade_ready'] ?> <input type="submit" value="<?= $lang['upgrade_begin'] ?>" id="submit1" />.
</form>
