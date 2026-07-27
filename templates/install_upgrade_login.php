<form method="post" action="">
    <label><?= $lang['textusername'] ?> <input type="text" name="username" /></label><br />
    <label><?= $lang['textpassword'] ?> <input type="password" name="password" /></label><br />
    <input type="hidden" name="token" value="<?= $token ?>" />
    <input type="submit" />
</form>
