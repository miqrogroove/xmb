<h1>XMB <?= $versionshort ?> <?= $lang['license'] ?></h1>
<p><?= $lang['license_detail'] ?></p>
<textarea style="width: 90%;" rows="30"  name="agreement" style= "font-family: Verdana; font-size: 8pt; margin-left: 4%;" readonly="readonly">
XMB <?= $versionshort ?>  License (Updated November 2007)
www.xmbforum2.com
----------------------------------------------

<?php readfile(XMB_ROOT . 'License.txt'); ?>

</textarea>
<form action="?step=4" method="post">
    <p class="button"><input type="submit" value="<?= $lang['license_agree'] ?> &gt;" /></p>
</form>
