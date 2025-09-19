<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00
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

require './header.php';

$core = Services\core();
$db = Services\db();
$smile = Services\smile();
$template = Services\template();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

$core->nav($lang['textsearch']);

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textsearch'] . ' - ';
}

$misc = '';
$multipage = '';
$template->nextlink = '';

if ($vars->settings['searchstatus'] != 'on') {
    header('HTTP/1.0 403 Forbidden');
    $header = $template->process('header.php');
    $misc = $template->process('misc_feature_notavailable.php');
    $template->footerstuff = $core->end_time();
    $footer = $template->process('footer.php');
    echo $header, $misc, $footer;
    exit;
}

$getUserName = getPhpInput('srchuname', 'g');
$captchaEnabled = X_GUEST && $vars->settings['captcha_status'] == 'on' && $vars->settings['captcha_search_status'] == 'on';

$searchsubmit = getPhpInput('searchsubmit', 'r');
$page = formInt('page');
$ppp = $vars->ppp;

if (empty($searchsubmit) && empty($page) || $getUserName != '' && $captchaEnabled) {
    $core->setCanonicalLink('search.php');

    // Users won't be able to see results without thread view permission, so also restrict the forum selector to the thread permission level.
    $template->forumselect = $core->forumList('f', multiple: true, currentfid: getInt('fid'), permLevel: 'thread');
    $template->selected = $vars::selHTML;
    $template->usernameAttr = $validate->postedVar(
        varname: 'srchuname',
        dbescape: false,
        sourcearray: 'g',
    );

    $template->captchasearchcheck = '';
    if ($captchaEnabled) {
        $Captcha = new Captcha($core, $vars);
        if ($Captcha->bCompatible !== false) {
            $template->imghash = $Captcha->GenerateCode();
            if ($vars->settings['captcha_code_casesensitive'] == 'off') {
                $lang['captchacaseon'] = '';
            }
            $template->captchasearchcheck = $template->process('search_captcha.php');
        }
    }

    $misc = $template->process('search.php');
} else {
    header('X-Robots-Tag: noindex');

    if (empty(getPhpInput('searchsubmit'))) {
        // Allow limited input from GET method
        $srchuname = $validate->postedVar('srchuname', sourcearray: 'g');
        $rawsrchuname = $getUserName;
        $srchtxt = '';
        $distinct = '';
        $srchfid = [0];
        $srchfield = '';
        $srchfrom = 0;
    } else {
        $srchuname = $validate->postedVar('srchuname');
        $rawsrchuname = getPhpInput('srchuname');
        $srchtxt = getPhpInput('srchtxt');
        $distinct = getPhpInput('distinct');
        // Value 'all' is coerced to 0 here, so 0 is now correctly interpreted as selecting all forums.
        $srchfid = $validate->postedArray(
            varname: 'f',
            valueType: 'int',
        );
        $srchfield = getPhpInput('srchfield');
        $srchfrom = formInt('srchfrom');
    }
    if (strlen($srchuname) < 3 && (empty($srchtxt) || strlen($srchtxt) < 3)) {
        $core->error($lang['nosearchq']);
    }
    if ($srchtxt !== $smile->censor($srchtxt)) {
        $core->error($lang['searchinvalid']);
    }

    if (strlen($srchuname) < 3) {
        $srchuname = '';
    }

    if ($captchaEnabled) {
        if ($page > 1) {
            $core->message($lang['searchguesterror']);
        }
        $Captcha = new Captcha($core, $vars);
        if ($Captcha->bCompatible !== false) {
            $imgcode = getPhpInput('imgcode');
            $imghash = getPhpInput('imghash');
            if ($Captcha->ValidateCode($imgcode, $imghash) !== true) {
                $core->error($lang['captchaimageinvalid']);
            }
        }
        unset($Captcha);
    }

    $template->searchresults = '';

    if ($page < 1) {
        $page = 1;
    }
    $offset = ($page - 1) * ($ppp);
    $start = $offset;
    $template->page = $page + 1;

    if ($srchfrom <= 0) {
        $srchfrom = $vars->onlinetime;
        $srchfromold = 0;
    } else {
        $srchfromold = $srchfrom;
    }
    $srchfrom = $vars->onlinetime - $srchfrom;

    $where = [];
    $ext = [];
    if (! empty($srchtxt)) {
        $srchtxtsq = explode(' ', $srchtxt);
        foreach ($srchtxtsq as $stxt) {
            $dblikebody = $db->like_escape(htmlEsc($stxt));
            $dblikesub = $db->like_escape(htmlEsc($stxt));
            if ($srchfield == 'body') {
                $where[] = "(p.message LIKE '%$dblikebody%' OR p.subject LIKE '%$dblikesub%')";
                $ext[] = 'srchfield=body';
            } else {
                $where[] = "p.subject LIKE '%$dblikesub%'";
            }
        }

        $ext[] = 'srchtxt=' . rawurlencode($srchtxt);
    }

    if ($srchuname != '') {
        $where[] = "p.author = '$srchuname'";
        $ext[] = 'srchuname=' . rawurlencode($rawsrchuname);
    }

    $forums = $core->permittedFIDsForThreadView();
    $allForums = true;
    if (count($srchfid) > 0 && ! in_array(0, $srchfid)) {
        $allForums = false;
        $forums = array_intersect($forums, $srchfid);
    }

    $srchfidcsv = implode(',', $forums);
    $where[] = "f.fid IN ($srchfidcsv)";
    
    if ($allForums) {
        $f = '0';
    } else {
        $f = $srchfidcsv;
    }

    if ($srchfrom) {
        $where[] = "p.dateline >= $srchfrom";
        $ext[] = "srchfrom=$srchfromold";
    }

    $counter = 1;
    $ppp++; // Peek at next page.

    if (strlen($srchfidcsv) == 0) {
        $results = 0;
    } else {
        $where = implode(' AND ', $where);
        $sql = "SELECT p.*, t.subject AS tsubject "
             . "FROM " . $vars->tablepre . "posts AS p INNER JOIN " . $vars->tablepre . "threads AS t USING (tid) INNER JOIN " . $vars->tablepre . "forums AS f ON f.fid = t.fid "
             . "WHERE $where "
             . "ORDER BY dateline DESC LIMIT $start, $ppp";

        $querysrch = $db->query($sql);
        $results = $db->num_rows($querysrch);
    }

    $temparray = [];
    $searchresults = '';

    while ($results != 0 && $counter < $ppp && $post = $db->fetch_array($querysrch)) {
        $counter++;
        if ($distinct != 'yes' || ! array_key_exists($post['tid'], $temparray)) {
            $temparray[$post['tid']] = true;
            $message = $post['message'];

            if (empty($srchtxt)) {
                $position = 0;
            } else {
                $position = stripos($message, htmlEsc($srchtxtsq[0]), 0);
            }

            $show_num = 100;
            $msg_leng = strlen($message);

            if ($position <= $show_num) {
                $min = 0;
                $template->add_pre = '';
            } else {
                $min = $position - $show_num;
                $template->add_pre = '...';
            }

            if (($msg_leng - $position) <= $show_num) {
                $max = $msg_leng;
                $template->add_post = '';
            } else {
                $max = $position + $show_num;
                $template->add_post = '...';
            }

            if (trim($post['subject']) == '') {
                $post['subject'] = $post['tsubject'];
            }

            $show = substr($message, $min, $max - $min);
            if (! empty($srchtxt)) {
                foreach ($srchtxtsq as $stxt) {
                    $show = str_ireplace(htmlEsc($stxt), '<b><i>'.htmlEsc($stxt).'</i></b>', $show);
                    $post['subject'] = str_ireplace(htmlEsc($stxt), '<i>'.htmlEsc($stxt).'</i>', $post['subject']);
                }
            }

            $template->show = $core->postify($show, bbcodeoff: 'yes', allowbbcode: 'no', allowimgcode: 'no');
            $post['subject'] = $core->rawHTMLsubject($post['subject']);

            $adjStamp = $core->timeKludge((int) $post['dateline']);
            $date = $core->printGmDate($adjStamp);
            $time = gmdate($vars->timecode, $adjStamp);

            $template->poston = $date.' '.$lang['textat'].' '.$time;
            $template->postby = $post['author'];
            $template->tid = $post['tid'];
            $template->pid = $post['pid'];
            $template->subject = $post['subject'];

            $template->searchresults .= $template->process('search_results_row.php');
        }
    }

    if ($results == 0) {
        $template->searchresults = $template->process('search_results_none.php');
    } elseif ($results == $ppp) {
        // create a string containing the stuff to search for
        $template->ext = implode('&', $ext);
        $template->distinct = $distinct;
        $template->nextlink = $template->process('search_nextlink.php');
    }

    $template->distinct = attrOut($distinct);
    $template->f = attrOut($f);
    $template->srchfield = attrOut($srchfield);
    $template->srchfrom = attrOut((string) $srchfromold);
    $template->srchtxt = attrOut($srchtxt);
    $template->srchuname = attrOut($rawsrchuname);
    
    $misc = $template->process('search_results.php');
}

$header = $template->process('header.php');
$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $misc, $footer;
