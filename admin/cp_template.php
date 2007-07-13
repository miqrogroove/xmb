<?php
/* $Id: cp_template.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
/*
    © 2001 - 2007 The XMB Development Team
    http://www.xmbforum.com

    Financial and other support 2007- iEntry Inc
    http://www.ientry.com

    Financial and other support 2002-2007 Aventure Media 
    http://www.aventure-media.co.uk

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!defined('IN_CODE') && (defined('DEBUG') && DEBUG == false)) {
    exit ("Not allowed to run this file directly.");
}

function displayTemplatePanel() {
    global $oToken, $THEME, $db, $lang, $table_templates;
?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=templates">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="80%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['templates']?></font></strong></td>
    </tr>
    <tr>
    <td class="altbg2 tablerow">
    <input type="text" name="newtemplatename" size="30" maxlength="50" />&nbsp;&nbsp;
    <input type="submit" class="submit" name="new" value="<?php echo $lang['newtemplate']?>" />
    </td>
    </tr>
    <tr>
    <td class="altbg2 tablerow">

    <?php

    $query = $db->query("SELECT * FROM $table_templates ORDER BY name");
    echo "<select name=\"tid\"><option value=\"default\">$lang[selecttemplate]</option>";
    while ($template = $db->fetch_array($query)) {
        if (!empty ($template['name'])) {
            echo "<option value=\"$template[id]\">$template[name]</option>\r\n";
        }
    }
    echo "</select>&nbsp;&nbsp;";
?>

    </td>
    </tr>
    <tr>
    <td class="altbg2 tablerow">
    <input type="submit" class="submit"name="edit" value="<?php echo $lang['textedit']?>" />&nbsp;
    <input type="submit" class="submit"name="delete" value="<?php echo $lang['deletebutton']?>" />&nbsp;
    <input type="submit" class="submit" name="restore" value="<?php echo $lang['textrestoredeftemps']?>" />&nbsp;
    <input type="submit" class="submit" name="download" value="<?php echo $lang['textdownloadtemps']?>" />
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    </td>
    </tr>

    <?php

}

function displayTemplateRestoreConfirm() {
    global $THEME, $oToken, $lang;
?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=templates">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['templates']?></font></strong></td>
    </tr>
    <tr>
    <td class="altbg1 ctrtablerow"><?php echo $lang['templaterestoreconfirm']?></td>
    </tr>
    <tr>
    <td class="altbg2 ctrtablerow"><input type="submit" class="submit" name="restoresubmit" value="<?php echo $lang['textyes']?>" /></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    </td>
    </tr>

    <?php

}

function processTemplateRestore() {
    global $db, $lang, $table_templates, $oToken;
    
    $oToken->isValidToken();

    if (!file_exists(ROOT . 'templates.xmb')) {
        error($lang['no_templates'], false, '</td></tr></table></td></tr></table><br />');
    }
    $db->query("TRUNCATE $table_templates");

    $filesize = filesize(ROOT . 'templates.xmb');
    $fp = fopen(ROOT . 'templates.xmb', 'r');
    $templatesfile = fread($fp, $filesize);
    fclose($fp);

    $templates = explode("|#*XMB TEMPLATE FILE*#|", $templatesfile);
    while (list ($key, $val) = each($templates)) {
        $template = explode("|#*XMB TEMPLATE*#|", $val);
        if (!empty ($template[1])) {
            $template[1] = $db->escape($template[1]);
        } else {
            $template[1] = '';
        }
        $db->query("INSERT INTO $table_templates (name, template) VALUES ('" . trim(addslashes($template[0])) . "', '" . trim(addslashes($template[1])) . "')");
    }

    $db->query("DELETE FROM $table_templates WHERE name=''");
    message($lang['templatesrestoredone'], false, '', '</td></tr></table></td></tr></table><br />', 'cp2.php', false, false, false);
}

function displayTemplateEditPanel() {
    global $tid, $THEME, $db, $lang, $table_templates, $oToken; 

    if ($tid == "default") {
        error($lang['selecttemplate'], false, '</td></tr></table></td></tr></table><br />');
    }
?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=templates&amp;tid=<?php echo $tid?>">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['templates']?></font></strong></td>
    </tr>

    <?php

    $query = $db->query("SELECT * FROM $table_templates WHERE id='$tid' ORDER BY name");
    $template = $db->fetch_array($query);
    $template['template'] = stripslashes($template['template']);
    $template['template'] = htmlspecialchars($template['template']);
?>

    <tr>
    <td class="altbg2 ctrtablerow"><?php echo $lang['templatename']?>&nbsp;<strong><?php echo $template['name']?></strong></td>
    </tr>
    <tr>
    <td class="altbg1 ctrtablerow">
    <textarea cols="100" rows="30" name="templatenew"><?php echo $template['template']?></textarea></td>
    </tr>
    <tr>
    <td class="altbg2 ctrtablerow"><input type="submit" name="editsubmit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" /></strong></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    </td>
    </tr>

    <?php

}

function processTemplateEdit() {
    global $db, $lang, $tid, $table_templates, $oToken;

    $oToken->isValidToken();

    $templatenew = $db->escape(formVar('templatenew'));
    $namenew = $db->escape(formVar('namenew'));

    if ($tid == "new") {
        if (empty ($namenew)) {
            error($lang['templateempty'], false, '</td></tr></table></td></tr></table><br />');
        } else {
            $check = $db->query("SELECT name FROM $table_templates WHERE name = '$namenew'");
            if ($check && $db->num_rows($check) != 0) {
                error($lang['templateexists'], false, '</td></tr></table></td></tr></table><br />');
            } else {
                $db->query("INSERT INTO $table_templates (name, template) VALUES ('$namenew', '$templatenew')");
            }
        }
    } else {
        $db->query("UPDATE $table_templates SET template='$templatenew' WHERE id=$tid");
    }
    echo '<tr class="tablerow altbg2"><td align="center">' . $lang['templatesupdate'] . '</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}

function displayTemplateDelete() {
    global $lang, $tid, $THEME, $oToken;

    if ($tid == "default") {
        error($lang['selecttemplate'], false, '</td></tr></table></td></tr></table><br />');
    }
?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=templates&amp;tid=<?php echo $tid?>">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="category"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['templates']?></font></strong></td>
    </tr>
    <tr>
    <td class="altbg1 ctrtablerow"><?php echo $lang['templatedelconfirm']?></td>
    </tr>
    <tr>
    <td class="altbg2 ctrtablerow"><input type="submit" class="submit" name="deletesubmit" value="<?php echo $lang['textyes']?>" /></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    </td>
    </tr>

    <?php

}

function processTemplateDelete() {
    global $lang, $db, $table_templates, $tid, $oToken;
    
    $oToken->isValidToken();
    
    if ( is_numeric($tid) ) {
        $db->query("DELETE FROM $table_templates WHERE id=$tid");
        message($lang['templatesdelete'], false, '', '</td></tr></table></td></tr></table><br />', 'cp2.php', false, false, false);
    } else {
        error($lang['templatesdelete'], false, '', '</td></tr></table></td></tr></table><br />', 'cp2.php', false, false, false);  // TODO
    }
}

function displayTemplateNew() {
    global $THEME, $lang, $oToken;

    $newtemplatename = htmlentities(formVar('newtemplatename'));
?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=templates&amp;tid=new">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="category"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['templates']?></font></strong></td>
    </tr>
    <tr>
    <td class="altbg2 ctrtablerow"><?php echo $lang['templatename']?>&nbsp;<input type="text" name="namenew" size="30" value="<?php echo $newtemplatename?>" /></td>
    </tr>
    <tr>
    <td class="altbg1 ctrtablerow"><textarea cols="100" rows="30" name="templatenew"></textarea></td>
    </tr>
    <tr>
    <td class="altbg2 ctrtablerow"><input type="submit" name="editsubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    </td>
    </tr>

    <?php

}
?>
