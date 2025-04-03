<?php
declare(strict_types=1);

namespace XMB;
?>
<div class="top"><span></span></div>
<div class="center-content">
    <h1>XMB <?= $lang['config_page'] ?></h1>
    <p><?= $lang['config_form_intro'] ?></p>
    <form action="?step=4&amp;substep=create" method="post" target="_blank">
        <table cellspacing="1px">
            <tr>
                <td colspan="2">
                    <h1><?= $lang['config_form_method'] ?></h1>
                    <p><?= $lang['config_form_method_detail'] ?></p>
                    <ol>
                        <li><?= $lang['config_form_method_1_detail'] ?></li>
                        <li><?= $lang['config_form_method_2_detail'] ?></li>
                        <li><?= $lang['config_form_method_3_detail'] ?></li>
                    </ol>
                    <p>
                        <select size="1" name="method">
                            <option value="1">1)&nbsp; <?= $lang['config_form_method_1'] ?></option>
                            <option value="2" selected="selected">2)&nbsp; <?= $lang['config_form_method_2'] ?></option>
                            <option value="3">3)&nbsp; <?= $lang['config_form_method_3'] ?></option>
                        </select>
                    </p>
                </td>
            </tr>
            <tr class="category">
                <td colspan="2"><?= $lang['config_form_db'] ?></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_db_name'] ?><br /><span><?= $lang['config_form_db_name_def'] ?></span></td>
                <td><input type="text" name="db_name" size="40" /></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_db_username'] ?><br /><span><?= $lang['config_form_db_username_def'] ?></span></td>
                <td><input type="text" name="db_user" size="40" /></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_db_password'] ?><br /><span><?= $lang['config_form_db_password_def'] ?></span></td>
                <td><input type="password" name="db_pw" size="40" autocomplete="new-password" /></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_db_host'] ?><br /><span><?= $lang['config_form_db_host_def'] ?></span></td>
                <td><input type="text" name="db_host" size="40" value="localhost" /></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_db_type'] ?><br /><span><?= $lang['config_form_db_type_def'] ?></span></td>
                <td><?= $types ?></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_db_prefix'] ?><br /><span><?= $lang['config_form_db_prefix_def'] ?></span></td>
                <td><input type="text" name="table_pre" size="40" value="xmb_" /></td>
            </tr>
            <tr class="category">
                <td colspan="2"><?= $lang['config_form_forum'] ?></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_forum_fullurl'] ?><br /><span><?= $lang['config_form_forum_fullurl_def'] ?></span></td>
                <td><input type="text" name="fullurl" size="40" value="<?= attrOut($full_url) ?>" /></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_forum_showinfo'] ?><br /><span><?= $lang['config_form_forum_showinfo_def'] ?></span></td>
                <td><input type="checkbox" name="showfullinfo" value="off" /></td>
            </tr>
            <tr class="category">
                <td colspan="2"><?= $lang['config_form_email'] ?></td>
            </tr>
            <tr>
                <td colspan="2"><p><?= $lang['config_form_email_detail'] ?></p></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_email_handler'] ?><br /><span><?= $lang['config_form_email_handler_def'] ?></span></td>
                <td><select name="MAILER_TYPE"><option value="default"><?= $lang['default'] ?></option><option value="socket_SMTP"><?= $lang['config_form_smtp'] ?></option></select></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_smtp_username'] ?>:</td>
                <td><input type="text" name="MAILER_USER" value="username" /></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_smtp_password'] ?>:</td>
                <td><input type="password" name="MAILER_PASS" value="password" /></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_smtp_host'] ?>:</td>
                <td><input type="text" name="MAILER_HOST" value="mail.example.com" /></td>
            </tr>
            <tr>
                <td><?= $lang['config_form_smtp_port'] ?>:</td>
                <td><input type="text" name="MAILER_PORT" value="25" /></td>
            </tr>
        </table>
        <p class="button"><input type="submit" value="<?= $lang['config_form_save'] ?>" /></p>
    </form>
    <form action="?step=5" method="post">
        <p class="button"><input type="submit" value="<?= $lang['config_form_next'] ?>" /></p>
    </form>
</div>
<div class="bottom"><span></span></div>
