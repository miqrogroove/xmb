<div class="top"><span></span></div>
<div class="center-content">
    <h1><?= $lang['create_admin'] ?></h1>
    <p><?= $lang['create_admin_detail'] ?></p>
    <script type="text/javascript">
    <!--//--><![CDATA[//><!--
    function disableButton() {
        var newAttr = document.createAttribute("disabled");
        newAttr.nodeValue = "disabled";
        document.getElementById("submit1").setAttributeNode(newAttr);
        return true;
    }
    //--><!]]>
    </script>
    <form action="?step=6" method="post" onsubmit="disableButton();">
        <table cellspacing="1px">
            <tr>
                <td><?= $lang['textusername'] ?>:</td>
                <td><input type="text" name="frmUsername" size="32" /></td>
            </tr>
            <tr>
                <td><?= $lang['textpassword'] ?></td>
                <td><input type="password" name="frmPassword" size="32" /></td>
            </tr>
            <tr>
                <td><?= $lang['textpasswordcf'] ?></td>
                <td><input type="password" name="frmPasswordCfm" size="32" /></td>
            </tr>
            <tr>
                <td><?= $lang['email_add'] ?>:</td>
                <td><input type="text" name="frmEmail" size="32" /></td>
            </tr>
        </table>
        <p class="button"><input type="submit" value="<?= $lang['install_proceed'] ?> &gt;" id="submit1" /></p>
    </form>
</div>
<div class="bottom"><span></span></div>
