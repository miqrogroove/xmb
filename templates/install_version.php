<div class="top"><span></span></div>
<div class="center-content">
    <h1><?= $lang['version_check_head'] ?></h1>
    <p><?= $lang['version_check_text'] ?></p>
    <ul>
        <li><?= $lang['version_check_current'] ?>: <?= $vars->versiongeneral ?></li>
        <li><?= $lang['version_check_latest'] ?>: <img src="https://www.xmbforum2.com/phpbin/xmbvc/vc.php?bg=f0f0f0&amp;fg=000000" alt="" style="position: relative; top: 8px;" /></li>
    </ul>
    <form action="?step=3" method="post">
        <p class="button"><input type="submit" value="<?= $lang['version_step'] ?> XMB <?= $vars->versionshort ?> &gt;" /></p>
    </form>
</div>
<div class="bottom"><span></span></div>
