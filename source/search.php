<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2012, The XMB Group
 * http://www.xmbforum2.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/

define('X_SCRIPT', 'search.php');

require 'header.php';

loadtemplates(
'misc_feature_notavailable',
'search',
'search_captcha',
'search_nextlink',
'search_results',
'search_results_none',
'search_results_row'
);

smcwcache();
eval('$css = "'.template('css').'";');
nav($lang['textsearch']);

$misc = $multipage = $nextlink = '';

if ($SETTINGS['searchstatus'] != 'on') {
    header('HTTP/1.0 403 Forbidden');
    eval('echo "'.template('header').'";');
    eval('echo "'.template('misc_feature_notavailable').'";');
    end_time();
    eval('echo "'.template('footer').'";');
    exit();
}

if (!isset($searchsubmit) && !isset($page)) {
// Common XSS Protection: XMB disallows '<' and unencoded ':/' in all URLs.
    $url_check = Array('%3c', '<', ':/');
    foreach($url_check as $name) {
        if (strpos(strtolower($url), $name) !== FALSE) {
            header('HTTP/1.0 403 Forbidden');
            exit('403 Forbidden - URL rejected by XMB');
        }
    }
    unset($url_check);
    
    setCanonicalLink('search.php');

    $forumselect = forumList('f', TRUE, TRUE, getInt('fid'));

    $captchasearchcheck = '';
    if (X_GUEST) {
        if ($SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_search_status'] == 'on') {
            require ROOT.'include/captcha.inc.php';
            $Captcha = new Captcha();
            if ($Captcha->bCompatible !== false) {
                $imghash = $Captcha->GenerateCode();
                if ($SETTINGS['captcha_code_casesensitive'] == 'off') {
                    $lang['captchacaseon'] = '';
                }
                eval('$captchasearchcheck = "'.template('search_captcha').'";');
            }
        }
    }

    eval('$search = "'.template('search').'";');
    $misc = $search;
} else {
    header('X-Robots-Tag: noindex');

    $srchtxt = postedVar('srchtxt', '', FALSE, FALSE, FALSE, 'g');
    $srchuname = postedVar('srchuname', '', TRUE, TRUE, FALSE, 'g');
    $rawsrchuname = postedVar('srchuname', '', FALSE, FALSE, FALSE, 'g');
    $filter_distinct = postedVar('filter_distinct', '', FALSE, FALSE, FALSE, 'g');
    $srchfid = postedArray('f', 'int', '', FALSE, FALSE, FALSE, 'g');
    $srchfield = postedVar('srchfield', '', FALSE, FALSE, FALSE, 'g');
    $page = getInt('page');
    $srchfrom = getInt('srchfrom');
    if (strlen($srchuname) < 3 && (empty($srchtxt) || strlen($srchtxt) < 3)) {
        error($lang['nosearchq']);
    }
    if (!X_STAFF) {
        // Common XSS Protection: XMB disallows '<' and unencoded ':/' in all URLs.
        if ($srchtxt != censor($srchtxt) Or strpos($srchtxt, '<') !== FALSE Or strpos($srchuname, '<') !== FALSE) {
            error($lang['searchinvalid']);
        }
        $url_check = Array('%3c', '<', ':/');
        foreach($url_check as $name) {
            if (strpos(strtolower($url), $name) !== FALSE) {
                header('HTTP/1.0 403 Forbidden');
                exit('403 Forbidden - URL rejected by XMB');
            }
        }
        unset($url_check);
    }

    if (strlen($srchuname) < 3) {
        $srchuname = '';
    }

    if (X_GUEST) {
        if ($SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_search_status'] == 'on') {
            if ($page > 1) {
                error($lang['searchguesterror']);
            }
            require ROOT.'include/captcha.inc.php';
            $Captcha = new Captcha();
            if ($Captcha->bCompatible !== false) {
                $imgcode = postedVar('imgcode', '', FALSE, FALSE, FALSE, 'g');
                $imghash = postedVar('imghash', '', TRUE, TRUE, FALSE, 'g');
                if ($Captcha->ValidateCode($imgcode, $imghash) !== TRUE) {
                    error($lang['captchaimageinvalid']);
                }
            }
            unset($Captcha);
        }
    }

    validatePpp();

    $searchresults = '';

    if ($page < 1) {
        $page = 1;
    }
    $offset = ($page-1) * ($ppp);
    $start = $offset;
    $pagenum = $page+1;

    $forums = permittedForums(forumCache(), 'thread', 'csv');
    $sql = "SELECT p.*, t.subject AS tsubject "
         . "FROM ".X_PREFIX."posts AS p INNER JOIN ".X_PREFIX."threads AS t USING(tid) INNER JOIN ".X_PREFIX."forums AS f ON f.fid=t.fid "
         . "WHERE f.fid IN($forums)";

    if ($srchfrom <= 0) {
        $srchfrom = $onlinetime;
        $srchfromold = 0;
    } else {
        $srchfromold = $srchfrom;
    }
    $srchfrom = $onlinetime - $srchfrom;

    $ext = array();
    if (!empty($srchtxt)) {
        $sqlsrch = array();
        $srchtxtsq = explode(' ', $srchtxt);
        $sql .= ' AND (';
        foreach($srchtxtsq as $stxt) {
            $dblikebody = $db->like_escape(addslashes(cdataOut($stxt)));  //Messages are historically double-slashed.
            $dblikesub = $db->like_escape(addslashes(attrOut($stxt)));
            if ($srchfield == 'body') {
                $sqlsrch[] = "p.message LIKE '%$dblikebody%' OR p.subject LIKE '%$dblikesub%'";
                $ext[] = 'srchfield=body';
            } else {
                $sqlsrch[] = "p.subject LIKE '%$dblikesub%'";
            }
        }

        $sql .= implode(') AND (', $sqlsrch);
        $sql .= ')';
        $ext[] = 'srchtxt='.rawurlencode($srchtxt);
    }

    if ($srchuname != '') {
        $sql .= " AND p.author='$srchuname'";
        $ext[] = 'srchuname='.rawurlencode($rawsrchuname);
    }

    if (count($srchfid) > 0) {
        if ($srchfid[0] != 'all') {
            $srchfidcsv = implode(',', $srchfid);
            $sql .= " AND f.fid IN ($srchfidcsv)";
            $ext[] = "f=$srchfidcsv";
        }
    }

    if ($srchfrom) {
        $sql .= " AND p.dateline >= $srchfrom";
        $ext[] = "srchfrom=$srchfromold";
    }

    $counter = 1;
    $ppp++; // Peek at next page.
    $sql .=" ORDER BY dateline DESC LIMIT $start, $ppp";

    if (strlen($forums) == 0) {
        $results = 0;
    } else {
        $querysrch = $db->query($sql);
        $results = $db->num_rows($querysrch);
    }

    $temparray = array();
    $searchresults = '';

    while($results != 0 And $counter < $ppp And $post = $db->fetch_array($querysrch)) {
        $counter++;
        if ($filter_distinct != 'yes' Or !array_key_exists($post['tid'], $temparray)) {
            $temparray[$post['tid']] = true;
            $message = stripslashes($post['message']);

            if (empty($srchtxt)) {
                $position = 0;
            } else {
                $position = stripos($message, cdataOut($srchtxtsq[0]), 0);
            }

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

            if (trim($post['subject']) == '') {
                $post['subject'] = $post['tsubject'];
            }

            $show = substr($message, $min, $max - $min);
            $post['subject'] = stripslashes($post['subject']);
            if (!empty($srchtxt)) {
                foreach($srchtxtsq as $stxt) {
                    $show = str_ireplace(cdataOut($stxt), '<b><i>'.cdataOut($stxt).'</i></b>', $show);
                    $post['subject'] = str_ireplace(attrOut($stxt), '<i>'.attrOut($stxt).'</i>', $post['subject']);
                }
            }

            $show = postify($show, 'no', 'yes', 'yes', 'no', 'no', 'no');
            $post['subject'] = rawHTMLsubject($post['subject']);

            $date = gmdate($dateformat, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
            $time = gmdate($timecode, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));

            $poston = $date.' '.$lang['textat'].' '.$time;
            $postby = $post['author'];
            eval('$searchresults .= "'.template('search_results_row').'";');
        }
    }

    if ($results == 0) {
        eval('$searchresults = "'.template('search_results_none').'";');
    } else if ($results == $ppp) {
        // create a string containing the stuff to search for
        $ext = implode('&', $ext);
        eval('$nextlink = "'.template('search_nextlink').'";');
    }

    eval('$search = "'.template('search_results').'";');
    $misc = $search;
}

eval('$header = "'.template('header').'";');
end_time();
eval('$footer = "'.template('footer').'";');
echo $header, $misc, $footer;
?>
