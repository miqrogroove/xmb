<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-3
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

const ROOT = '../';
require ROOT . 'header.php';

$core = Services\core();
$db = Services\db();
$template = Services\template();
$theme = Services\theme();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');
header('X-XSS-Protection: 0'); // Disables HTML input errors in Chrome.

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textforums']);
$core->setCanonicalLink('admin/forums.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textforums'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

$body = '';
$fdetails = getInt('fdetails');
$delete = getInt('delete', 'p');
$template->fdetails = $fdetails;
if (noSubmit('forumsubmit') && ! $fdetails) {
    $groups = [];
    $forums = [];
    $forums[0] = [];
    $forumlist = [];
    $subs = [];
    $i = 0;
    $query = $db->query("SELECT fid, type, name, displayorder, status, fup FROM " . $vars->tablepre . "forums ORDER BY fup ASC, displayorder ASC");
    while ($selForums = $db->fetch_array($query)) {
        if ($selForums['type'] == 'group') {
            $groups[$i]['fid'] = $selForums['fid'];
            $groups[$i]['name'] = $selForums['name'];
            $groups[$i]['displayorder'] = $selForums['displayorder'];
            $groups[$i]['status'] = $selForums['status'];
            $groups[$i]['fup'] = $selForums['fup'];
        } elseif ($selForums['type'] == 'forum') {
            $id = (empty($selForums['fup'])) ? 0 : $selForums['fup'];
            $forums[$id][$i]['fid'] = $selForums['fid'];
            $forums[$id][$i]['name'] = $selForums['name'];
            $forums[$id][$i]['displayorder'] = $selForums['displayorder'];
            $forums[$id][$i]['status'] = $selForums['status'];
            $forums[$id][$i]['fup'] = $selForums['fup'];
            $forumlist[$i]['fid'] = $selForums['fid'];
            $forumlist[$i]['name'] = $selForums['name'];
        } elseif ($selForums['type'] == 'sub') {
            $subs[$selForums['fup']][$i]['fid'] = $selForums['fid'];
            $subs[$selForums['fup']][$i]['name'] = $selForums['name'];
            $subs[$selForums['fup']][$i]['displayorder'] = $selForums['displayorder'];
            $subs[$selForums['fup']][$i]['status'] = $selForums['status'];
            $subs[$selForums['fup']][$i]['fup'] = $selForums['fup'];
        }
        $i++;
    }

    $template->token = $token->create('Control Panel/Forums', 'mass-edit', $vars::NONCE_FORM_EXP);

    $body = $template->process('admin_forums_list_start.php');

    $template->forumlist = $forumlist;
    $template->forums = $forums;
    $template->groups = $groups;
    $template->subs = $subs;
    $template->selHTML = $vars::selHTML;

    // Loop through ungrouped forums.
    foreach ($forums[0] as $forum) {
        if ($forum['status'] == 'on') {
            $template->off = '';
            $template->on = $vars::selHTML;
        } else {
            $template->off = $vars::selHTML;
            $template->on = '';
        }
        $template->forum = $forum;

        $body .= $template->process('admin_forums_list_ungrouped.php');

        // Loop through subforums of the ungrouped forum.
        if (array_key_exists($forum['fid'], $subs)) {
            foreach ($subs[$forum['fid']] as $subforum) {
                if ($subforum['status'] == 'on') {
                    $template->off = '';
                    $template->on = $vars::selHTML;
                } else {
                    $template->off = $vars::selHTML;
                    $template->on = '';
                }
                $template->subforum = $subforum;

                $body .= $template->process('admin_forums_list_ungrouped_subs.php');
            }
        }
    }

    // Loop through groups.
    foreach ($groups as $group) {
        if ($group['status'] == 'on') {
            $template->off = '';
            $template->on = $vars::selHTML;
        } else {
            $template->off = $vars::selHTML;
            $template->on = '';
        }
        $template->group = $group;

        $body .= $template->process('admin_forums_list_group.php');

        // Loop through grouped forums.
        if (array_key_exists($group['fid'], $forums)) {
            foreach ($forums[$group['fid']] as $forum) {
                if ($forum['status'] == 'on') {
                    $template->off = '';
                    $template->on = $vars::selHTML;
                } else {
                    $template->off = $vars::selHTML;
                    $template->on = '';
                }
                $template->forum = $forum;

                $body .= $template->process('admin_forums_list_grouped.php');

                // Loop through subforums of grouped forums.
                if (array_key_exists($forum['fid'], $subs)) {
                    foreach ($subs[$forum['fid']] as $subforum) {
                        if ($subforum['status'] == 'on') {
                            $template->off = '';
                            $template->on = $vars::selHTML;
                        } else {
                            $template->off = $vars::selHTML;
                            $template->on = '';
                        }
                        $template->subforum = $subforum;

                        $body .= $template->process('admin_forums_list_grouped_subs.php');
                    }
                }
            }
        }
    }
    $body .= $template->process('admin_forums_list_end.php');
} elseif ($fdetails && noSubmit('forumsubmit')) {
    $template->token = $token->create('Control Panel/Forums', (string) $fdetails, $vars::NONCE_FORM_EXP);

    $queryg = $db->query("SELECT * FROM " . $vars->tablepre . "forums WHERE fid='$fdetails'");
    $forum = $db->fetch_array($queryg);

    $template->themelist = $theme->selector(
        nameAttr: 'themeforumnew',
        selection: (int) $forum['theme'],
    );

    if ($forum['allowsmilies'] == "yes") {
        $template->checked3 = $vars::cheHTML;
    } else {
        $template->checked3 = '';
    }

    if ($forum['allowbbcode'] == "yes") {
        $template->checked4 = $vars::cheHTML;
    } else {
        $template->checked4 = '';
    }

    if ($forum['allowimgcode'] == "yes") {
        $template->checked5 = $vars::cheHTML;
    } else {
        $template->checked5 = '';
    }

    if ($forum['attachstatus'] == "on") {
        $template->checked6 = $vars::cheHTML;
    } else {
        $template->checked6 = '';
    }

    $template->forum = $forum;

    $body = $template->process('admin_forums_detail_start.php');

    $template->perms = explode(',', $forum['postperm']);
    
    foreach ($vars->status_enum as $key=>$val) {
        if ($key != '' && $val <= $vars->status_enum['Guest']) {
            $template->val = $val;
            $template->statusKey = $vars->status_translate[$val];
            if (! X_SADMIN && $key == 'Super Administrator') {
                $template->disabled = 'disabled="disabled"';
            } else {
                $template->disabled = '';
            }
            $template->rawPoll = $vars::PERMS_RAWPOLL;
            $template->rawThread = $vars::PERMS_RAWTHREAD;
            $template->rawReply = $vars::PERMS_RAWREPLY;
            $template->rawView = $vars::PERMS_RAWVIEW;
            $body .= $template->process('admin_forums_detail_perms.php');
        }
    }
    $body .= $template->process('admin_forums_detail_end.php');
} elseif (onSubmit('forumsubmit') && ! $fdetails) {
    $core->request_secure('Control Panel/Forums', 'mass-edit');
    $queryforum = $db->query("SELECT fid, type, fup FROM " . $vars->tablepre . "forums WHERE type='forum' OR type='sub'");
    while ($forum = $db->fetch_array($queryforum)) {
        $displayorder = formInt('displayorder'.$forum['fid']);
        $forum['status'] = formOnOff('status'.$forum['fid']);
        $name = $validate->postedVar('name' . $forum['fid']);
        $delete = formInt('delete'.$forum['fid']);
        $moveto = formInt('moveto'.$forum['fid']);

        $dsuccess = false;
        if ($delete == (int) $forum['fid']) {
            if ($db->num_rows($db->query('SELECT tid FROM ' . $vars->tablepre . 'threads WHERE fid = ' . $forum['fid'])) > 0) {
                $dsuccess = false;
            } elseif ($db->num_rows($db->query('SELECT fid FROM ' . $vars->tablepre . 'forums WHERE fup = ' . $forum['fid'])) > 0) {
                $dsuccess = false;
            } elseif ($db->num_rows($db->query('SELECT pid FROM ' . $vars->tablepre . 'posts WHERE fid = ' . $forum['fid'])) > 0) {
                $dsuccess = false;
            } else {
                $db->query("DELETE FROM " . $vars->tablepre . "forums WHERE (type = 'forum' OR type = 'sub') AND fid = " . $forum['fid']);
                $dsuccess = true;
            }
            if (!$dsuccess) {
                $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>';
                $body .= $core->message(
                    $lang['deleteaborted'] . '<br />' . $lang['forumnotempty'],
                    showheader: false,
                    die: false,
                    return_as_string: true,
                    showfooter: false,
                );
                $body .= '</td></tr>';
            }
        }

        if (! $dsuccess) {
            $settype = '';
            if ($moveto != (int) $forum['fup'] && $moveto != (int) $forum['fid'] && $forum['type'] != 'group') { // Forum is being moved
                if ($moveto == 0) {
                    $settype = ", type='forum', fup=0";
                } else {
                    $query = $db->query("SELECT type FROM " . $vars->tablepre . "forums WHERE fid = $moveto");
                    if ($frow = $db->fetch_array($query)) {
                        if ($frow['type'] == 'group') {
                            $settype = ", type='forum', fup=$moveto";
                        } elseif ($frow['type'] == 'forum') {
                            if ($forum['type'] == 'sub') {
                                $settype = ", fup=$moveto";
                            } elseif ($forum['type'] == 'forum') { // Make sure the admin didn't try to demote a parent
                                $query2 = $db->query("SELECT COUNT(*) AS subcount FROM " . $vars->tablepre . "forums WHERE fup = {$forum['fid']}");
                                $frow = $db->fetch_array($query2);
                                $db->free_result($query2);
                                if ('0' === $frow['subcount']) {
                                    $settype = ", type='sub', fup=$moveto";
                                }
                            }
                        }
                    }
                    $db->free_result($query);
                }
            }
            $db->query("UPDATE " . $vars->tablepre . "forums SET name = '$name', displayorder = $displayorder, status = '{$forum['status']}'$settype WHERE fid = '" . $forum['fid'] . "'");
        }
    }

    $querygroup = $db->query("SELECT fid FROM " . $vars->tablepre . "forums WHERE type = 'group'");
    while ($group = $db->fetch_array($querygroup)) {
        $name = $validate->postedVar('name' . $group['fid']);
        $displayorder = formInt('displayorder'.$group['fid']);
        $group['status'] = formOnOff('status'.$group['fid']);
        $delete = formInt('delete'.$group['fid']);

        if ($delete == (int) $group['fid']) {
            $query = $db->query("SELECT fid FROM " . $vars->tablepre . "forums WHERE type = 'forum' AND fup = $delete");
            if ($db->num_rows($query) > 0) {
                $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>';
                $body .= $core->message(
                    $lang['deleteaborted'] . '<br />' . $lang['forumnotempty'],
                    showheader: false,
                    die: false,
                    return_as_string: true,
                    showfooter: false,
                );
                $body .= '</td></tr>';
            } else {
                $db->query("DELETE FROM " . $vars->tablepre . "forums WHERE type = 'group' AND fid = $delete");
            }
        } else {
            $db->query("UPDATE " . $vars->tablepre . "forums SET name = '$name', displayorder = $displayorder, status = '{$group['status']}' WHERE fid = {$group['fid']}");
        }
    }

    $newgname = $validate->postedVar('newgname');
    $newfname = $validate->postedVar('newfname');
    $newsubname = $validate->postedVar('newsubname');
    $newgorder = formInt('newgorder');
    $newforder = formInt('newforder');
    $newsuborder = formInt('newsuborder');
    $newgstatus = formOnOff('newgstatus');
    $newfstatus = formOnOff('newfstatus');
    $newsubstatus = formOnOff('newsubstatus');
    $newffup = formInt('newffup');
    $newsubfup = formInt('newsubfup');

    if ($newfname !== $lang['textnewforum'] && $newfname != '') {
        $db->query("INSERT INTO " . $vars->tablepre . "forums (type, name, status, lastpost, moderator, displayorder, description, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, password) VALUES ('forum', '$newfname', '$newfstatus', '', '', $newforder, '', 'yes', 'yes', '', 0, 0, 0, $newffup, '31,31,31,63', 'yes', 'on', '')");
    }

    if ($newgname !== $lang['textnewgroup'] && $newgname != '') {
        $db->query("INSERT INTO " . $vars->tablepre . "forums (type, name, status, lastpost, moderator, displayorder, description, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, password) VALUES ('group', '$newgname', '$newgstatus', '', '', $newgorder, '', '', '', '', 0, 0, 0, 0, '', '', '', '')");
    }

    if ($newsubname !== $lang['textnewsubf'] && $newsubname != '') {
        $db->query("INSERT INTO " . $vars->tablepre . "forums (type, name, status, lastpost, moderator, displayorder, description, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, password) VALUES ('sub', '$newsubname', '$newsubstatus', '', '', $newsuborder, '', 'yes', 'yes', '', 0, 0, 0, $newsubfup, '31,31,31,63', 'yes', 'on', '')");
    }

    $link = '</p><p><a href="' . $vars->full_url . 'admin/forums.php">' . $lang['textforumslink'] . '</a>';
    $body .= '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td><p>' . $lang['textforumupdate'] . $link . '</p></td></tr>';
} else {
    $core->request_secure('Control Panel/Forums', (string) $fdetails);

    $success = true;
    if ($delete) {
        if ($delete == $fdetails) {
            if ($db->num_rows($db->query('SELECT tid FROM ' . $vars->tablepre . 'threads WHERE fid = ' . $fdetails)) > 0) {
                $success = false;
            } elseif ($db->num_rows($db->query('SELECT fid FROM ' . $vars->tablepre . 'forums WHERE fup = ' . $fdetails)) > 0) {
                $success = false;
            } elseif ($db->num_rows($db->query('SELECT pid FROM ' . $vars->tablepre . 'posts WHERE fid = ' . $fdetails)) > 0) {
                $success = false;
            } else {
                $db->query("DELETE FROM " . $vars->tablepre . "forums WHERE (type = 'forum' OR type = 'sub') AND fid = " . $fdetails);
            }
        }
    } else {
        $namenew = $validate->postedVar('namenew');
        $descnew = $validate->postedVar('descnew');
        $allowsmiliesnew = formYesNo('allowsmiliesnew');
        $allowbbcodenew = formYesNo('allowbbcodenew');
        $allowimgcodenew = formYesNo('allowimgcodenew');
        $attachstatusnew = formOnOff('attachstatusnew');
        $themeforumnew = formInt('themeforumnew');
        $userlistnew = $validate->postedVar('userlistnew');
        $passwordnew = $validate->postedVar('passwordnew', htmlencode: false);
        $delete = formInt('delete');

        $overrule = [0,0,0,0];
        if (! X_SADMIN) {
            $forum = $db->fetch_array($db->query("SELECT postperm FROM " . $vars->tablepre . "forums WHERE fid = $fdetails"));
            $parts = explode(',', $forum['postperm']);
            foreach ($parts as $p=>$v) {
                if ($v & 1 == 1) {
                    // super admin status set
                    $overrule[$p] = 1;
                }
            }
        }

        $perms = array(0,0,0,0);
        foreach ($_POST['permsNew'] as $key=>$val) {
            $perms[$key] = array_sum($_POST['permsNew'][$key]);
            $perms[$key] |= $overrule[$key];
        }
        $perms = implode(',', $perms);

        $db->query("UPDATE " . $vars->tablepre . "forums SET
            name = '$namenew',
            description = '$descnew',
            allowsmilies = '$allowsmiliesnew',
            allowbbcode = '$allowbbcodenew',
            theme = '$themeforumnew',
            userlist = '$userlistnew',
            postperm = '$perms',
            allowimgcode = '$allowimgcodenew',
            attachstatus = '$attachstatusnew',
            password = '$passwordnew'
            WHERE fid = '$fdetails'"
        );
    }

    if ($success) {
        $link = '</p><p><a href="' . $vars->full_url . 'admin/forums.php">' . $lang['textforumslink'] . '</a>';
        $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td><p>' . $lang['textforumupdate'] . $link . '</p></td></tr>';
    } else {
        $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>';
        $body .= $core->message(
            $lang['deleteaborted'] . '<br />' . $lang['forumnotempty'],
            showheader: false,
            die: false,
            return_as_string: true,
            showfooter: false,
        );
        $body .= '</td></tr>';
    }
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
