<?php
/* $Id: misc.php,v 1.3.2.23 2006/09/24 12:33:33 Tularis Exp $ */
/*
    XMB 1.9.2
    © 2001 - 2005 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

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

// Get global settings
    require "./header.php";

/**
* url_to_text() - Convert URL to a safely displayable text element 
*
* Control XSS and censored words when you need to display a URL safely
* 
* @param    $url    URL to convert to displayable text
* @return   the sanitized url
*/
function url_to_text($url) {
    global $db, $table_forums, $table_threads, $lang, $self;
    static $permCache, $fCache, $tCache;

    if (false !== strpos($url, 'tid') && false === strpos($url, "/post.php")) {
        $temp = explode('?', $url);
        $urls = explode('&', $temp[1]);
        foreach ($urls as $key=>$val) {
            if (strpos($val, 'tid') !== false) {
                $tid = (int) substr($val, 4);
            }
        }
        if (isset($tCache[$tid])) {
            $fid = $tCache[$tid]['fid'];
            if($permCache[$fid][X_PERMS_VIEW] && $permCache[$fid][X_PERMS_USERLIST] && $permCache[$fid][X_PERMS_PASSWORD]) {
                $location = $lang['onlineviewthread'].' '.$tCache[$tid]['subject'];
            } else {
                $location = $lang['onlineviewthread'];
            }
        } else {
            $query = $db->query("SELECT f.postperm, f.userlist, f.password, t.fid, t.subject, t.tid FROM $table_forums f, $table_threads t WHERE f.fid=t.fid AND t.tid=$tid");
            $locate = $db->fetch_array($query);
            if(!isset($permCache[$locate['fid']])) {
                $permCache[$locate['fid']] = checkForumPermissions($locate);
                $tCache[$locate['tid']] = $locate;
            }
            if($permCache[$locate['fid']][X_PERMS_VIEW] && $permCache[$locate['fid']][X_PERMS_USERLIST] && $permCache[$locate['fid']][X_PERMS_PASSWORD]) {
                $location = $lang['onlineviewthread'].' '.censor($locate['subject']);
            } else {
                $location = $lang['onlineviewthread'];
            }
        }
    } elseif (false !== strpos($url, 'fid')  && false !== strpos($url, "/forumdisplay.php")) {
        $temp = explode('?', $url);
        $urls = explode('&', $temp[1]);
        foreach ($urls as $key=>$val) {
            if (strpos($val, 'fid') !== false) {
                $fid = (int) substr($val, 4);
            }
        }
        
        if (isset($fCache[$fid]) && isset($permCache[$fid])) {
            if($permCache[$fid][X_PERMS_VIEW] && $permCache[$fid][X_PERMS_USERLIST] && $permCache[$fid][X_PERMS_PASSWORD]) {
                $location = $lang['onlineforumdisplay'].' '.$fCache[$fid]['name'];
            } else {
                $location = $lang['onlineforumdisplay'];
            }
        } else {
            $query = $db->query("SELECT name, postperm, userlist, password FROM $table_forums WHERE fid=$fid");
            $locate = $db->fetch_array($query);
            if(!isset($permCache[$locate['fid']]) || !isset($fCache[$locate['fid']])) {
                $permCache[$locate['fid']] = checkForumPermissions($locate);
                $fCache[$locate['fid']] = $locate;
            }
            if($permCache[$locate['fid']][X_PERMS_VIEW] && $permCache[$locate['fid']][X_PERMS_USERLIST] && $permCache[$locate['fid']][X_PERMS_PASSWORD]) {
                $location = $lang['onlineforumdisplay'].' '.$locate['name'];
            } else {
                $location = $lang['onlineforumdisplay'];
            }
        }
    } elseif (false !== strpos($url, "/memcp.php")) {
        $location = $lang['onlinememcp'];
    } elseif (false !== strpos($url, "/cp.php") || false !== strpos($url, "/cp2.php") || false !== strpos($url, '/u2uadmin.php')) {
        $location = $lang['onlinecp'];
        if (!X_ADMIN) {
            $url = 'index.php';
        }
    } elseif (false !== strpos($url, "/editprofile.php")) {
        if (!X_SADMIN) {
            $url = 'index.php';
        }
        $location = $lang['onlineeditprofile'];
    } elseif (false !== strpos($url, "/faq.php")) {
        $location = $lang['onlinefaq'];
    } elseif (false !== strpos($url, "/index.php")) {
        $location = $lang['onlineindex'];
    } elseif (false !== strpos($url, "/member.php")) {
        if (false !== strpos($url, 'action=reg')) {
            $location = $lang['onlinereg'];
        } elseif (false !== strpos($url, 'action=viewpro')) {
            $temp = explode('?', $url);
            $urls = explode('&', $temp[1]);
            foreach ($urls as $argument) {
                if (strpos($argument, 'member') !== false) {
                    $member = str_replace('member=', '', $argument);
                }
            }
            eval("\$location = \"$lang[onlineviewpro]\";");
        } elseif (false !== strpos($url, 'action=coppa')) {
            $location = $lang['onlinecoppa'];
        }
    } elseif (false !== strpos($url, "misc.php")) {
        if (false !== strpos($url, 'login')) {
            $location = $lang['onlinelogin'];
        } elseif (false !== strpos($url, 'logout')) {
            $location = $lang['onlinelogout'];
        } elseif (false !== strpos($url, 'search')) {
            $location = $lang['onlinesearch'];
        } elseif (false !== strpos($url, 'lostpw')) {
            $location = $lang['onlinelostpw'];
        } elseif (false !== strpos($url, 'online')) {
            $location = $lang['onlinewhosonline'];
        } elseif (false !== strpos($url, 'onlinetoday')) {
            $location = $lang['onlineonlinetoday'];
        } elseif (false !== strpos($url, 'list')) {
            $location = $lang['onlinememlist'];
        }
    } elseif (false !== strpos($url, "/post.php")) {
        if (false !== strpos($url, 'action=edit')) {
            $location = $lang['onlinepostedit'];
        } elseif (false !== strpos($url, 'action=newthread')) {
            $location = $lang['onlinepostnewthread'];
        } elseif (false !== strpos($url, 'action=reply')) {
            $location = $lang['onlinepostreply'];
        }
    } elseif (false !== strpos($url, "/stats.php")) {
        $location = $lang['onlinestats'];
    } elseif (false !== strpos($url, "/today.php")) {
        $location = $lang['onlinetodaysposts'];
    } elseif (false !== strpos($url, "/tools.php")) {
        $location = $lang['onlinetools'];
    } elseif (false !== strpos($url, "/topicadmin.php")) {
        $location = $lang['onlinetopicadmin'];
    } elseif (false !== strpos($url, "/u2u.php")) {
        if (false !== strpos($url, 'action=send')) {
            $location = $lang['onlineu2usend'];
        } elseif (false !== strpos($url, 'action=delete')) {
            $location = $lang['onlineu2udelete'];
        } elseif (false !== strpos($url, 'action=ignore') || false !== strpos($url, 'action=ignoresubmit')) {
            $location = $lang['onlineu2uignore'];
        } elseif (false !== strpos($url, 'action=view')) {
            $location = $lang['onlineu2uview'];
        }
        if (!X_SADMIN) {
            $url = './u2u.php';
        }
    } else {
        $location = $url;
    }

    $location = trim($location);
    if ($location == '') {
        $url = 'about:none';
    } else {
        $location = str_replace('%20', '&nbsp;', $location);
    }

    $url = addslashes($url);
    $return = array();
    $return['url'] = checkInput($url, 'yes');
    $return['text'] = $location;

    return $return;
}

// pre-Load templates (saves queries)
    loadtemplates('functions_smilieinsert', 'functions_smilieinsert_smilie', 'misc_feature_not_while_loggedin', 'misc_feature_notavailable', 'misc_feature_notavailable', 'misc_login', 'misc_lostpw', 'misc_mlist', 'misc_mlist_admin', 'misc_mlist_multipage', 'misc_mlist_results_none', 'misc_mlist_row', 'misc_mlist_row_email', 'misc_mlist_row_site', 'misc_mlist_separator', 'misc_online', 'misc_online_admin', 'misc_online_row', 'misc_online_row_admin', 'misc_online_today', 'misc_online_u2ufield', 'misc_search', 'misc_search_nextlink', 'misc_search_results', 'misc_search_results_none', 'misc_search_results_row', 'misc_smilies', 'popup_footer', 'popup_header', 'misc_login_incorrectdetails');

    smcwcache();
    eval('$css = "'.template('css').'";');

// Create navigation
    switch ($action) {
        case 'login':
            nav($lang['textlogin']);
            break;

        case 'logout':
            nav($lang['textlogout']);
            break;

        case 'search':
            nav($lang['textsearch']);
            break;

        case 'lostpw':
            nav($lang['textlostpw']);
            break;

        case 'online':
            nav($lang['whosonline']);
            break;

        case 'list':
            nav($lang['textmemberlist']);
            break;

        case 'onlinetoday':
            nav($lang['whosonlinetoday']);
            break;

        default:
            nav($lang['error']);
            break;
    }

// Based on the action, choose what to do
    $misc = '';
    $multipage = '';
    $nextlink = '';

    switch ($action) {
        case 'login':
            if (!isset($loginsubmit)) {
                eval("\$misc = \"".template("misc_login")."\";");
                $misc = stripslashes($misc);
            } else {
                $password = md5(trim($password));
                $username = addslashes($username);
                $query = $db->query("SELECT username FROM $table_members WHERE username='$username' AND password='$password'");
                if ($query && $db->num_rows($query) == 1) {
                    $member = $db->fetch_array($query);
                    $db->query("DELETE FROM $table_whosonline WHERE ip='$onlineip' && username='Anonymous'");
                    $currtime = time() + (86400*30);
                    $username = $member['username'];

                    if (isset($hide)) {
                        $db->query("UPDATE $table_members SET invisible='1' WHERE username='$username'");
                    } else {
                        $db->query("UPDATE $table_members SET invisible='0' WHERE username='$username'");
                    }
                    if ($server == 'Mic') {
                        $misc = '<script>
                            function put_cookie(name, value, expires, path, domain, secure) {
                                var curCookie = name + "=" + escape(value) +
                                ((expires) ? "; expires=" + expires.toGMTString() : "") +
                                ((path) ? "; path=" + path : "") +
                                ((domain) ? "; domain=" + domain : "") +
                                ((secure) ? "; secure" : "");
                                document.cookie = curCookie;
                            }

                            var now = new Date();
                            now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000);

                            put_cookie("xmbuser", "'.$username.'", now, "'.$cookiepath.'", "'.$cookiedomain.'");
                            put_cookie("xmbpw", "'.$password.'", now, "'.$cookiepath.'", "'.$cookiedomain.'");

                            window.location="index.php";
                        </script>';
                    } else {
                        if (isset($secure) && $secure == 'yes') {
                            put_cookie("xmbuser", $username);
                            put_cookie("xmbpw", $password);
                        } else {
                            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
                            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
                        }
                        redirect("index.php", 0);
                        $misc = '';
                    }

                } else {
                    end_time();

                    eval("echo (\"".template('header')."\");");
                    eval("\$incorrectpassword = \"".template("misc_login_incorrectdetails")."\";");
                    echo $incorrectpassword;
                    eval("echo (\"".template('footer')."\");");

                    exit();
                }
            }
            break;

        case 'logout':
            // check to see if we're already logged out, or browsing as guest
            if ( X_GUEST ) {
                redirect("index.php", 0);
                break;
            }

            $currtime = time() - (86400*30);
            $query = $db->query("DELETE FROM $table_whosonline WHERE username='$xmbuser'");

            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbuser", '');
            put_cookie("xmbpw", '');
            
            // loop trough all password-forum-cookies and remove them
            foreach($_COOKIE as $key=>$val) {
                if (preg_match('#^forumPW\[([0-9]+)\]$#', $key)) {
                    put_cookie($key, '');
                }
            }
            redirect("index.php", 0);
            break;

        case 'search':
            if ($searchstatus != "on") {
                eval("echo (\"".template('header')."\");");

                eval("\$featureoff = \"".template("misc_feature_notavailable")."\";");
                $featureoff = stripslashes($featureoff);
                echo $featureoff;
                end_time();

                eval("echo (\"".template('footer')."\");");
                exit();
            }

            if (!isset($searchsubmit) && !isset($page)) {
                $fids = array();
                if (X_SADMIN) {
                    $q = $db->query("SELECT fid FROM $table_forums WHERE status = 'on'");
                    while($f = $db->fetch_array($q)) {
                        $fids[] = $f['fid'];
                    }
                } else {
                    $fCache = array();
                    $q = $db->query("SELECT fid, postperm, userlist, password, type, fup FROM $table_forums WHERE status = 'on' AND type != 'group' ORDER BY type ASC");
                    while($forum = $db->fetch_array($q)) {
                        $perms = checkForumPermissions($forum);
                        $fCache[$forum['fid']] = $perms;
                        
                        if($perms[X_PERMS_VIEW] && $perms[X_PERMS_USERLIST] && $perms[X_PERMS_PASSWORD]) {
                            if($forum['type'] == 'sub') {
                                // also check above forum!
                                $parentP = $fCache[$forum['fup']];
                                if($parentP[X_PERMS_VIEW] && $parentP[X_PERMS_USERLIST] && $parentP[X_PERMS_PASSWORD]) {
                                    $fids[] = $forum['fid'];
                                }
                            } else {
                                $fids[] = $forum['fid'];
                            }
                        }
                    }
                }

                if(count($fids) == 0) {
                    $forumselect = '<select name="srchfid"></select>';
                } else {
                    $fids = implode(',', $fids);
                    $restrict = ' fid IN('.$fids.')';
                    
                    $forumselect = "<select name=\"srchfid\">\n";
                    $forumselect .= '<option value="all">'.$lang['textallforumsandsubs']."</option>\n";

                    $queryfor = $db->query("SELECT fid, name FROM $table_forums WHERE $restrict AND fup='' AND type='forum' ORDER BY displayorder");

                    while ($forum = $db->fetch_array($queryfor)) {
                        $forumselect .= "<option value=\"$forum[fid]\"> &nbsp; &raquo; $forum[name]</option>";
                        $querysub = $db->query("SELECT fid, name FROM $table_forums WHERE $restrict AND fup='$forum[fid]' AND type='sub' ORDER BY displayorder");

                        while ($sub = $db->fetch_array($querysub)) {
                            $forumselect .= "<option value=\"$sub[fid]\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; $sub[name]</option>";
                        }
    
                        $forumselect .= "<option value=\"\" disabled=\"disabled\">&nbsp;</option>";
                    }

                    $querygrp = $db->query("SELECT fid, name FROM $table_forums WHERE type='group' ORDER BY displayorder");
                    while ($group = $db->fetch_array($querygrp)) {
                        $forumselect2 = "<option value=\"$group[fid]\"disabled=\"disabled\">$group[name]</option>";
    
                        $forumselect3 = '';
                        $queryfor = $db->query("SELECT fid, name FROM $table_forums WHERE $restrict AND fup='$group[fid]' AND type='forum' ORDER BY displayorder");
                        while ($forum = $db->fetch_array($queryfor)) {
                            $forumselect3 .= "<option value=\"$forum[fid]\"> &nbsp; &raquo; $forum[name]</option>";
    
                            $querysub = $db->query("SELECT fid, name FROM $table_forums WHERE $restrict AND fup='$forum[fid]' AND type='sub' ORDER BY displayorder");
                            while ($sub = $db->fetch_array($querysub)) {
                                $forumselect3 .= "<option value=\"$sub[fid]\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; $sub[name]</option>";
                            }
                        }
                        if($forumselect3 !== '') {
                            $forumselect .= $forumselect2.$forumselect3;
                        }
    
                        $forumselect .= "<option value=\"\" disabled=\"disabled\">&nbsp;</option>";
                    }
    
                    $forumselect .= "</select>";
                }

                eval("\$search = \"".template("misc_search")."\";");
                $misc = stripslashes($search);
            
            } else {
                if (strlen($srchtxt) < 3 && strlen($srchuname) < 3) {
                    error('nothing to search for...');
                }
                if ($searchsubmit || $page ) {
                    if (!isset($page)) {
                        $page = 1;
                        $offset = 0;
                        $start = 0;
                        $end = ((isset($ppp) && $ppp > 0) ? $ppp : (isset($postperpage) && $postperpage > 0 ? $postperpage : 20));

                    } else {
                        if ( $page < 1 ) {
                            $page = 1;
                        }

                        $offset = ($page-1) * ((isset($ppp) && $ppp > 0) ? $ppp : (isset($postperpage) && $postperpage > 0 ? $postperpage : 20));
                        $start = $offset;
                        $end = ((isset($ppp) && $ppp > 0) ? $ppp : (isset($postperpage) && $postperpage > 0 ? $postperpage : 20));
                    }

                    $sql = "SELECT count(p.tid), p.*, t.tid AS ttid, t.subject AS tsubject, f.fid, f.postperm, f.userlist, f.password FROM $table_posts p, $table_threads t LEFT JOIN $table_forums f ON  f.fid=t.fid WHERE p.tid=t.tid";

                    if ($srchfrom == 0) {
                        $srchfrom = time();
                        $srchfromold = 0;
                    } else {
                        $srchfromold = $srchfrom;
                    }
                    $ext = array();

                    $srchfrom = time() - (int) $srchfrom;
                    if ($srchtxt) {
                        $srchtxtsq = addslashes(str_replace(array('%', '_'), array('\%', '\_'), $srchtxt));
                        $sql .= " AND (p.message LIKE '%$srchtxtsq%' OR p.subject LIKE '%$srchtxtsq%' OR t.subject LIKE '%$srchtxtsq')";
                        $ext[] = 'srchtxt='.$srchtxt;
                    }
                    if ($srchuname != "") {
                        $sql .= " AND p.author='".addslashes($srchuname)."'";
                        $ext[] = 'srchuname='.$srchuname;
                    }
                    if ($srchfid != "all" && $srchfid != "") {
                        $sql .= " AND p.fid='".(int)$srchfid."'";
                        $ext[] = 'srchfid='.((int) $srchfid);
                    }
                    if ($srchfrom) {
                        $sql .= " AND p.dateline >= '$srchfrom'";
                        $ext[] = 'srchfrom'.((int) $srchfromold);
                    }
                    $sql .=" GROUP BY dateline ORDER BY dateline DESC LIMIT $start,$end";
                    if (!isset($page) || $page < 1) {
                        $pagenum = 2;
                    } else {
                        $pagenum = $page+1;
                    }
                    $querysrch = $db->query($sql);
                    $results = 0;
                    $results = $db->num_rows($querysrch);

                    if ($srchuname != '') {
                        $srchtxt = '\0';
                    }

                    if ($filter_distinct == 'yes') {
                        $temparray = array();
                        $searchresults = '';
                        
                        $forumCache = array();
                        while ($post = $db->fetch_array($querysrch)) {
                            $forumPerms = array();
                            
                            if(isset($forumCache[$post['fid']])) {
                                $forumPerms = $forumCache[$post['fid']];
                            } else {
                                $forumPerms = checkForumPermissions($post);
                                $forumCache[$post['fid']] = $forumPerms;
                            }
                            
                            if ($forumPerms[X_PERMS_VIEW] && $forumPerms[X_PERMS_USERLIST] && $forumPerms[X_PERMS_PASSWORD]) {
                                if (!array_key_exists($post['ttid'], $temparray)) {
                                    $tid = $post['ttid'];
                                    $temparray[$tid] = true;
                                    $message = $post['message'];

                                    $srchtxt = str_replace(array('_ ', ' _','% ', ' %'), '', $srchtxt);
                                    $position = strpos($message, $srchtxt, 0);
                                    $show_num = 100;
                                    $msg_leng = strlen($message);

                                    if ($position <= $show_num) {
                                        $min = 0;
                                        $add_pre = '';
                                    } else {
                                        $min = $position - $show_num;
                                        $add_pre = '...';
                                    }

                                    if (($msg_leng - $position) <= $show_num) {
                                        $max = $msg_leng;
                                        $add_post = '';
                                    } else {
                                            $max = $position + $show_num;
                                        $add_post = '...';
                                    }

                                    $show = substr($message, $min, $max);
                                    $show = str_replace($srchtxt, '<b><i>'.$srchtxt.'</i></b>', $show);
                                    $show = postify($show, 'no', 'yes', 'yes', 'no', 'no', 'no');

                                    $date = printGmDate($post['dateline']);
                                    $time = printGmTime($post['dateline']);
                                    
                                    $poston = $date.' '.$lang['textat'].' '.$time;
                                    $postby = $post['author'];

                                    $post['tsubject'] = stripslashes(censor($post['tsubject']));
                                    if (trim($post['subject']) == '') {
                                        $post['subject'] = $post['tsubject'];
                                    }

                                    $post['subject'] = censor($post['subject']);
                                    eval("\$searchresults .= \"".template("misc_search_results_row")."\";");
                                }
                            }
                        }
                    } else {
                        $forumCache = array();
                        while ($post = $db->fetch_array($querysrch)) {
                            $forumPerms = array();
                            
                            if(isset($forumCache[$post['fid']])) {
                                $forumPerms = $forumCache[$post['fid']];
                            } else {
                                $forumPerms = checkForumPermissions($post);
                                $forumCache[$post['fid']] = $forumPerms;
                            }
                            
                            if ($forumPerms[X_PERMS_VIEW] && $forumPerms[X_PERMS_USERLIST] && $forumPerms[X_PERMS_PASSWORD]) {
                                $tid = $post['ttid'];
                                $message = $post['message'];

                                $srchtxt = str_replace(array('_ ', ' _','% ', ' %'), '', $srchtxt);
                                $position = strpos($message, $srchtxt, 0);
                                $show_num = 100;
                                $msg_leng = strlen($message);

                                if ($position <= $show_num) {
                                $min = 0;
                                    $add_pre = '';
                                } else {
                                    $min = $position - $show_num;
                                    $add_pre = '...';
                                }

                                if (($msg_leng - $position) <= $show_num) {
                                    $max = $msg_leng;
                                    $add_post = '';
                                } else {
                                    $max = $position + $show_num;
                                    $add_post = '...';
                                }

                                $show = substr($message, $min, $max);
                                $show = str_replace($srchtxt, '<b><i>'.$srchtxt.'</i></b>', $show);
                                $show = postify($show, 'no', 'yes', 'yes', 'no', 'no', 'no');

                                $date = printGmDate($post['dateline']);
                                $time = printGmTime($post['dateline']);
                                
                                $poston = $date.' '.$lang['textat'].' '.$time;
                                $postby = $post['author'];

                                $post['tsubject'] = stripslashes(censor($post['tsubject']));
                                if (trim($post['subject']) == '') {
                                    $post['subject'] = $post['tsubject'];
                                } else {
                                    $post['tsubject'] = $post['subject'];
                                }

                                eval("\$searchresults .= \"".template("misc_search_results_row")."\";");
                            }
                        }
                    }
                }
                if ($results == 0) {
                    eval("\$searchresults = \"".template("misc_search_results_none")."\";");
                } elseif ($results == ((isset($ppp) && $ppp > 0) ? $ppp : (isset($postperpage) && $postperpage > 0 ? $postperpage : 20))) {
                    // create a string containing the stuff to search for
                    $ext = htmlspecialchars(implode('&', $ext));
                    eval("\$nextlink = \"".template("misc_search_nextlink")."\";");
                }

                eval("\$search = \"".template("misc_search_results")."\";");
                $misc = stripslashes($search);
            }
            break;

        case 'lostpw':
            if (!$lostpwsubmit) {
                eval("\$misc = \"".template("misc_lostpw")."\";");
                $misc = stripslashes($misc);
            } else {
                $username = addslashes($username);
                $email = addslashes($email);
                $query = $db->query("SELECT username, email, pwdate FROM $table_members WHERE username='$username' AND email='$email'");
                $member = $db->fetch_array($query);

                $time = time()-86400;
                if ($member['pwdate'] > $time) {
                    error($lang['lostpw_in24hrs']);
                }

                if (!$member['username']) {
                    error($lang['badinfo']);
                }

                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
                $newpass = '';
                mt_srand((double)microtime() * 1000000);
                $max = mt_rand(8, 12);
                for ($get=strlen($chars), $i=0; $i < $max; $i++) {
                    $newpass .= $chars[mt_rand(0, $get)];
                }
                $newmd5pass = md5($newpass);

                $db->query("UPDATE $table_members SET password='$newmd5pass', pwdate='".time()."' WHERE username='$member[username]' AND email='$member[email]'");

                altMail($member['email'], '['.$bbname.'] '.$lang['textyourpw'], $lang['textyourpwis']."\n\n".$member['username']."\n".$newpass, "From: ".$bbname." <".$adminemail.">");

                $misc .= '<span class="mediumtxt"><center>'.$lang['emailpw'].'</span></center><br />';
                $misc .='<script>function redirect() {window.location.replace("index.php");}setTimeout("redirect();", 1250);</script>';
            }
            break;

        case 'online':
             // Check for status of whosonline
            if ($whosonlinestatus != "on") {
                eval('echo "'.template('header').'";');
                eval('echo stripslashes("'.template('misc_feature_notavailable').'");');
                end_time();
                eval('echo "'.template('footer').'";');
                exit();
            }

            if (X_ADMIN) {
                $query = $db->query("SELECT `username`, `ip`, `time`, `location`, `invisible` FROM $table_whosonline ORDER BY `time` DESC");
            } else {
                $query = $db->query("SELECT `username`, `ip`, `time`, `location`, `invisible` FROM $table_whosonline WHERE invisible = '0' OR (invisible='1' AND username='$xmbuser') ORDER BY `time` DESC");
            }
            $onlineusers = '';

            while ($online = $db->fetch_array($query)) {
                $array = url_to_text($online['location']);


                $onlinetime = printGmTime($online['time']);
                $username = str_replace("Anonymous", $lang['textguest1'], $online['username']);

                $online['location'] = $array['text'];
                if (X_STAFF) {
                    $online['location'] = "<a href=\"$array[url]\">$array[text]</a>";
                    $online['location'] = stripslashes($online['location']);
                }

                if ($online['invisible'] == 1 && (X_ADMIN || $online['username'] == $xmbuser)) {
                    $hidden = " ($lang[hidden])";
                } else {
                    $hidden = '';
                }

                if (X_SADMIN && $online['username'] != 'Anonymous' && $online['username'] != $lang['textguest1']) {
                    $online['username'] = "<a href=\"member.php?action=viewpro&amp;member=$online[username]\">$username</a>$hidden";
                    $showu2u = true;
                } else {
                    $online['username'] = $username;
                    $showu2u = false;
                }

                if (X_ADMIN) {
                    if ($showu2u) {
                        eval("\$u2uthing = \"".template("misc_online_u2ufield")."\";");
                    } else {
                        $u2uthing = '&nbsp;';
                    }
                    eval("\$onlineusers .= \"".template("misc_online_row_admin")."\";");
                } else {
                    eval("\$onlineusers .= \"".template("misc_online_row")."\";");
                }
            }

            if (X_ADMIN) {
                eval("\$misc = \"".template("misc_online_admin")."\";");
            } else {
                eval("\$misc = \"".template("misc_online")."\";");
            }

            $misc = stripslashes($misc);
            break;

        case 'onlinetoday':
            // Check for status of whosonline
            if ($whosonlinestatus != "on") {
                eval("echo (\"".template('header')."\");");
                eval('echo stripslashes("'.template('misc_feature_notavailable').'");');
                end_time();
                eval("echo (\"".template('footer')."\");");
                exit();
            }

            $datecut = time() - (3600 * 24);

            if (X_ADMIN) {
                $query = $db->query("SELECT username FROM $table_members WHERE lastvisit >= '$datecut' ORDER BY username ASC");
            } else {
                $query = $db->query("SELECT username FROM $table_members WHERE lastvisit >= '$datecut' AND invisible != '1' ORDER BY username ASC");
            }

            $todaymembersnum = 0;
            $todaymembers = '';
            $comma = '';

            while ($memberstoday = $db->fetch_array($query)) {
                $todaymembers .= $comma.'<a href="member.php?action=viewpro&amp;member='.rawurlencode($memberstoday['username']).'">'.$memberstoday['username'].'</a>';
                ++$todaymembersnum;
                $comma = ", ";
            }

            if ($todaymembersnum == 1) {
                $memontoday = $todaymembersnum. $lang['textmembertoday'];
            } else {
                $memontoday = $todaymembersnum. $lang['textmemberstoday'];
            }

            eval("\$misc = \"".template("misc_online_today")."\";");
            $misc = stripslashes($misc);
            break;

        case 'list':
            // Check for status of member-list
            if ($memliststatus != "on") {
                eval("echo (\"".template('header')."\");");
                eval('echo stripslashes("'.template('misc_feature_notavailable').'");');
                end_time();
                eval("echo (\"".template('footer')."\");");
                exit();
            }

            if (!isset($desc) || strtolower($desc) != 'desc') {
                $desc = 'asc';
            }

            if (isset($page) && $page > 0) {
                $start_limit = ($page-1) * $memberperpage;
            } else {
                $start_limit = 0;
                $page = 1;
            }

            $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

            if (!isset($order) || ($order != "username" && $order != "postnum" && $order != 'status')) {
                $orderby = "uid";
                $order = 'uid';
            } elseif ($order == 'status') {
                $orderby = "if (status='Super Administrator',1, if (status='Administrator', 2, if (status='Super Moderator', 3, if (status='Moderator', 4, if (status='member', 5, if (status='banned',6,7))))))";
            } else {
                $orderby = $order;
            }

            if (!X_ADMIN) {
                $srchip = '';
                $srchemail = '';
                $misc_mlist_template = 'misc_mlist';
                $where = array();
            } else {
                $where = array();
                $misc_mlist_template = 'misc_mlist_admin';
            }

            $ext = array('&order='.$order);

            if (isset($srchemail) && $srchemail != '') {
                if (!X_SADMIN) {
                    $where[] = " email LIKE '%".$srchemail."%'";
                    $where[] = " showemail = 'yes'";
                } else {
                    $where[] = " email LIKE '%".$srchemail."%'";
                }
                $ext[] = 'srchemail='.$srchemail;
                $srchemail = htmlspecialchars($srchemail);
            } else {
                $srchemail = '';
            }

            if (isset($srchip) && $srchip != '') {
                $where[] = " regip LIKE '%".$srchip."%'";
                $ext[] = 'srchip='.$srchip;
                $srchip = htmlspecialchars($srchip);
            } else {
                $srchip = '';
            }

            if (isset($srchmem) && $srchmem != '') {
                $where[] = " username LIKE '%".addslashes(str_replace(array('%', '_'), array('\%', '\_'), $srchmem))."%'";
                $ext[] = 'srchmem='.$srchmem;
                $srchmem = htmlspecialchars($srchmem);
            } else {
                $srchmem = '';
            }

            if (isset($where) && isset($where[0]) && $where[0] != '') {
                $q = implode(' AND', $where);
                $querymem = $db->query("SELECT * FROM $table_members WHERE $q ORDER BY $orderby $desc LIMIT $start_limit, $memberperpage");
                $num = $db->result($db->query("SELECT count(uid) FROM $table_members WHERE $q"), 0);
            } else {
                $querymem = $db->query("SELECT * FROM $table_members ORDER BY $orderby $desc LIMIT $start_limit, $memberperpage");
                $num = $db->result($db->query("SELECT count(uid) FROM $table_members"), 0);
            }

            $ext = htmlspecialchars(implode('&', $ext));

            $replace = array('http://', 'https://', 'ftp://');
            $members = '';
            $oldst = '';
            if ($db->num_rows($querymem) == 0) {
                eval("\$members = \"".template("misc_mlist_results_none")."\";");
            } else {
                while ($member = $db->fetch_array($querymem)) {
                    $member['regdate'] = printGmDate($member['regdate']);

                    if ($member['email'] != "" && $member['showemail'] == "yes") {
                        eval("\$email = \"".template("misc_mlist_row_email")."\";");
                    } else {
                        $email = "&nbsp;";
                    }

                    $member['site'] = str_replace($replace, '', $member['site']);
                    $member['site'] = "http://$member[site]";

                    if ($member['site'] == "http://") {
                        $site = "&nbsp;";
                    } else {
                        eval("\$site = \"".template("misc_mlist_row_site")."\";");
                    }

                    if ($member['location'] != '') {
                        $member['location'] = censor($member['location']);
                    } else {
                        $member['location'] = "&nbsp;";
                    }

                    $memurl = rawurlencode($member['username']);
                    if ($order == 'status') {
                        if ($oldst != $member['status']) {
                            $oldst = $member['status'];
                            $seperator_text = (trim($member['status']) == '' ? $lang['onlineother'] : $member['status']);
                            eval("\$members .= \"".template('misc_mlist_separator')."\";");
                        }
                    }
                    eval("\$members .= \"".template("misc_mlist_row")."\";");
                }
            }

            if (!$memberperpage) {
                $memberperpage=$postperpage;
            }

            if (($multipage = multi($num, $memberperpage, $page, 'misc.php?action=list&amp;desc='.$desc.$ext)) === false) {
                $multipage = '';
            } else {
                eval('$multipage = "'.template('misc_mlist_multipage').'";');
            }

            if ($desc == 'desc') {
                $init['ascdesc'] = 'asc';
                $ascdesc = $lang['asc'];
            } else {
                $init['ascdesc'] = 'desc';
                $ascdesc = $lang['desc'];
            }

            eval("\$memlist = \"".template($misc_mlist_template)."\";");
            $misc = stripslashes($memlist);
            break;

        case 'smilies':
            $header = '';
            eval("\$css = \"".template("css")."\";");
            eval("\$header = \"".template("popup_header")."\";");
            eval("\$footer = \"".template("popup_footer")."\";");
            $smtotal = 0;   // makes sure that the total # of smilies isn't limited here!
            $smilies = smilieinsert();

            eval("\$misc = \"".template("misc_smilies")."\";");

            echo $header;
            echo $misc;
            echo $footer;
            exit();

            break;

        default:
            error($lang['textnoaction']);
            break;
    }

// Show the created page
    eval("\$header = \"".template("header")."\";");
    end_time();
    eval("\$footer = \"".template("footer")."\";");
    echo stripslashes($header . $misc . $footer);
