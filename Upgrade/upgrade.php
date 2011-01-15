<?php
require "./header.php";
?>
<html>
<head>
<style type="text/css">
    body {
        scrollbar-base-color: #0A1C31;
        scrollbar-arrow-color: #050C16;
        text-align:left;
        color: #FFFFFF;
        background-color: #050C16;
    }

    a {
        color: #FFFFFF;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    textarea, select, input, object {
        font-family: Verdana, arial, helvetica, sans-serif;
        font-size: 12px;
        font-weight: normal;
        background-color: #0A1C31;
        color: #FFFFFF;
        table-layout: fixed;
    }

    .submit {
        text-align: center;
    }

    hr {
        border: 0px;
        color: #FFCC33;
        background-color: #FFCC33;
        height: 1px;
    }

    .special {
        color: #FFCC33;
    }
</style>
</head>
<body>
<?php
switch(isset($submit)){
    case true:
        echo '&raquo; Adding 1.8SP2 fixes...<br />';
        $db->query("ALTER TABLE ".$tablepre."settings CHANGE `addtime` `addtime` DECIMAL(4,2) DEFAULT '0' NOT NULL");
        $db->query("ALTER TABLE ".$tablepre."members CHANGE `timeoffset` `timeoffset` DECIMAL(4,2) DEFAULT '0' NOT NULL");
        $db->query("ALTER TABLE ".$tablepre."u2u CHANGE `u2uid` `u2uid` INT( 6 ) NOT NULL AUTO_INCREMENT");

        echo 'All changes were succesfull. Your database now reflects the default 1.8 SP2 structure. Please remember to either manually fix your templates using the patches given, or to upload templates.xmb to your forum directory, going to Administration Panel &raquo; Templates &raquo; Restore, to restore them automatically.<br />Please, also delete this file from your server, it may be used by others if you do not.';
        break;
    case false:
        ?>
        <b><font size="+2" class="special">1.8 Final => 1.8 SP 2 Upgrade</font></b><br />
        <br />
        Thank you for using XMB 1.8 SP 2, if you are running 1.8 Final, or 1.8 SP1, you may use this script to update your database to 1.8 SP2. Simply press the Upgrade button, and upgrade will commence.
        <br />
        <br />
        <form action="upgrade.php" method="post"><input type="submit" name="submit" value="Upgrade"></form>
        <?php
        break;
}
?>