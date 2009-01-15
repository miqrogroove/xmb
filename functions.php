<?

function template($name) {
global $tempcache, $table_templates, $db;
if (isset($tempcache[$name])) {
    $template=$tempcache[$name];
  } else {
    $query= $db->query("SELECT * FROM $table_templates WHERE name='$name'");
    $gettemplate=$db->fetch_array($query);
    $template= $gettemplate[template];
    $tempcache[$name]= $template;
  }

  #$template=addslashes($template);
  $template=str_replace("\\'","'",$template);
  if($name != "phpinclude") {
  return "<!--Begin Template: $name -->\n$template\n<!-- End Template: $name -->";
  } else {
  return "$template";
  }
}

function loadtemplates($names) {
// A big thanks to John Miller for his code and time on this!
    global $db,$tempcache,$table_templates;
    $namesarray = explode(",",$names);
    while (list($key,$title) = each($namesarray)) {
        if ($sql != "") {
            $sql .= ",";
        }
        $sql .= "'$title'";
    }
$query = $db->query("SELECT * FROM $table_templates WHERE name IN ($sql)");
while($template = $db->fetch_array($query)) {
$tempcache[$template[name]] = $template[template];
}
}

function postify($message, $smileyoff, $bbcodeoff, $allowsmilies="yes", $allowhtml="yes", $allowbbcode="yes", $allowimgcode="yes") {
global $imgdir, $bordercolor, $table_words, $table_forums, $table_smilies, $db, $smdir, $smiliecache, $censorcache, $smiliesnum, $wordsnum;

if($allowhtml != "yes" || $allowhtml != "off") {
$message=str_replace("&lt;","&amp;lt;",$message);
$message=str_replace("&gt;","&amp;gt;",$message);
$message = str_replace("<","&lt;",$message);
$message = str_replace(">","&gt;",$message);
}

$message = nl2br($message);

if($smileyoff != "yes" && $allowsmilies == "yes" || $allowsmilies == "on") {
if($smiliesnum > 0) {
reset($smiliecache);
while(list($code, $url) = each($smiliecache)) {
$message = str_replace("$code", "<img src=\"$smdir/$url\" border=0>",$message);
}
}
}

if($bbcodeoff != "yes" && $allowbbcode == "yes" || $allowbbcode == "on") {
$message = str_replace("[b]", "<b>", $message);
$message = str_replace("[/b]", "</b>", $message);
$message = str_replace("[i]", "<i>", $message);
$message = str_replace("[/i]", "</i>", $message);
$message = str_replace("[u]", "<u>", $message);
$message = str_replace("[/u]", "</u>", $message);
$message = eregi_replace("\\[email\\]([^\\[]*)\\[/email\\]", "<a href=\"mailto:\\1\">\\1</a>",$message);
$message = eregi_replace("\\[email=([^\\[]*)\\]([^\\[]*)\\[/email\\]", "<a href=\"mailto:\\1\">\\2</a>",$message);
$message = str_replace("[quote]", "<blockquote><span class=\"mediumtxt\">quote:</span><hr>", $message);
$message = str_replace("[/quote]", "<hr></blockquote>", $message);
$message=str_replace("[code]","<blockquote><pre><smallfont>code:</smallfont><hr>",$message);
$message=str_replace("[/code]","<hr></pre><normalfont></blockquote>",$message);
$message = eregi_replace("\\[url\\]www.([^\\[]*)\\[/url\\]", "<a href=\"http://www.\\1\" target=_blank>\\1</a>",$message);
$message = eregi_replace("\\[url\\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" target=_blank>\\1</a>",$message);
$message = eregi_replace("\\[url=([^\\[]*)\\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" target=_blank>\\2</a>",$message);
$message = str_replace("[list]","<ul type=square>",$message);
$message=str_replace("[*]","<li>",$message);
$message = str_replace("[/list]","</ul>",$message);
$message = eregi_replace("(^|[>[:space:]\n])([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])([<[:space:]\n]|$)","\\1<a href=\"\\2://\\3\\4\" target=\"_blank\">\\2://\\3\\4</a>\\5", $message);
}

if($allowimgcode == "yes" && $bbcodeoff != "yes" || $allowimgcode == "on") {
$message = eregi_replace("\\[img\\]([^\\[]*)\\[/img\\]","<img src=\"\\1\" border=0>",$message);
$message = eregi_replace("\\[img=([^\\[]*)x([^\\[]*)\\]([^\\[]*)\\[/img\\]","<img width=\"\\1\" height=\"\\2\" src=\"\\3\" border=0>",$message);
}
$pre = "([^[:alpha:]]|^)";
$suf = "([^[:alpha:]]|$)";
if($wordsnum > 0) {
reset($censorcache);
while(list($find, $replace) = each($censorcache)) {
$message = eregi_replace($pre.$find.$suf,"\\1$replace\\2",$message);
}
}
return $message;
}

function modcheck($status, $username, $fid) {
global $db, $table_forums;
if($status == "Moderator") {
$query = $db->query("SELECT * FROM $table_forums WHERE moderator LIKE '%$username%'");
while($mod = $db->fetch_array($query)) {
if($mod[fid] == $fid) {
$modgood = "yes";
}
}

if($modgood == "yes") {
$status1 = "Moderator";
}

}

return $status1;
}


function privfcheck($private, $userlist) {
global $status, $xmbuser, $hideprivate;
#if($hideprivate == "on") {
	if($private == "2" && $status == "Administrator") {
		return true;
	} elseif($private == "3" && ($status == "Administrator" || $status == "Moderator" || $status == "Super Moderator")) {
		return true;
	} elseif($private == "1" && $userlist == "") {
		return true;
	} elseif($userlist != "") {
		if(eregi($xmbuser."(,|$)", $userlist) && $xmbuser != "") {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}

#} else {
#return true;
#}
}


function forum($forum, $template) {
global $timecode, $dateformat, $langfile, $xmbuser, $status, $lastvisit2, $timeoffset, $hideprivate;
require "lang/$langfile.lang.php";
$altbg1 = $GLOBALS["altbg1"];
$altbg2 = $GLOBALS["altbg2"];
$imgdir = $GLOBALS["imgdir"];

if($forum[lastpost] != "") {
$lastpost = explode("|", $forum[lastpost]);
$dalast = $lastpost[0];

$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";

$lastpostdate = gmdate("$dateformat",$lastpost[0] + ($timeoffset * 3600));
$lastposttime = gmdate("$timecode",$lastpost[0] + ($timeoffset * 3600));
$lastpost = "$lang_lastreply1 $lastpostdate $lang_textat $lastposttime<br />$lang_textby $lastpost[1]";
} else {
$lastpost = "$lang_textnever";
}

$lastvisit2 -= 540;
if($lastvisit2 < $dalast) {
$folder = "<img src=\"$imgdir/red_folder.gif\">";
} else {
$folder = "<img src=\"$imgdir/folder.gif\">";
}

if($dalast == "") {
$folder = "<img src=\"$imgdir/folder.gif\">";
}

$lastvisit2 += 540;
$authorization = privfcheck($forum[private], $forum[userlist]);

if($authorization) {
if($forum[moderator] != "") {
$modz = explode(", ", $forum[moderator]);
$forum[moderator] = "";
for($num = 0; $num < count($modz); $num++) {
$thismod = "<a href=\"member.php?action=viewpro&member=$modz[$num]\">$modz[$num]</a>";

if($num == count($modz) - 1) {
$forum[moderator] .= $thismod;
} else {
$forum[moderator] .= "$thismod, ";
}
}
$forum[moderator] = "($lang_textmodby $forum[moderator])";
} else {
$forum[moderator] = "";
}
eval("\$foruminfo .= \"".template("$template")."\";");
}

$dalast = "";
$fmods = "";
$authorization = "";
return $foruminfo;
}


function multi($num, $perpage, $page, $mpurl) {
if($num > $perpage) {
$pages = $num / $perpage;
$pages = ceil($pages);

if($page == $pages) {
$to = $pages;
} elseif($page == $pages-1) {
$to = $page+1;
} elseif($page == $pages-2) {
$to = $page+2;
} else {
$to = $page+3;
}

if($page == 1 || $page == 2 || $page == 3) {
$from = 1;
} else {
$from = $page-3;
}
$fwd_back .= "<a href=\"$mpurl&page=1\"><<</a>";

for($i = $from; $i <= $to; $i++) {
if($i != $page) {
$fwd_back .= "&nbsp;&nbsp;<a href=\"$mpurl&page=$i\">$i</a>&nbsp;&nbsp;";
} else {
$fwd_back .= "&nbsp;&nbsp;<u><b>$i</b></u>&nbsp;&nbsp;";
}
}

$fwd_back .= "<a href=\"$mpurl&page=$pages\">>></a>";
$multipage = $fwd_back;
}
return $multipage;
}
function bbcodeinsert() {
global $imgdir, $bbinsert;
if($bbinsert == "on") {
eval("\$bbcode = \"".template("functions_bbcodeinsert")."\";");
}
return $bbcode;
}
function smilieinsert() {
global $imgdir, $smdir, $table_smilies, $db, $smileyinsert, $smcols, $smtotal;

if($smileyinsert == "on" && $smtotal != "" && $smcols != "") {
$col_smilies = 0;
$smilies .= "<tr>";
$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='smiley' LIMIT 0, $smtotal") or die(mysql_error());
while($smilie = $db->fetch_array($querysmilie)) {
eval("\$smilies .= \"".template("functions_smilieinsert_smilie")."\";");
$col_smilies += 1;
if($col_smilies == $smcols) {
$smilies .= "</tr>";
$col_smilies = 0;
}
}
eval("\$smilieinsert .= \"".template("functions_smilieinsert")."\";");
}
return $smilieinsert;
}

function printsetting1($setname, $varname, $check1, $check2) {
global $langfile;
require "lang/$langfile.lang.php";
$altbg1 = $GLOBALS["altbg1"];
$altbg2 = $GLOBALS["altbg2"];
?>
<tr><td class="tablerow" bgcolor="<?=$altbg1?>"><?=$setname?></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><select name="<?=$varname?>">
<option value="on" <?=$check1?>><?=$lang_texton?></option><option value="off" <?=$check2?>><?=$lang_textoff?></option>
</select></td></tr>
<?php
}

function printsetting2($setname, $varname, $value, $size) {
$altbg1 = $GLOBALS["altbg1"];
$altbg2 = $GLOBALS["altbg2"];
?>
<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$setname?></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><input type="text"  size="<?=$size?>" value="<?=$value?>" name="<?=$varname?>" /></td>
</tr>
<?php
}

function noaccess($message) {
$tablewidth = $GLOBALS["tablewidth"];
eval("\$header = \"".template("header")."\";");
?>
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td class="mediumtxt"><?=$message?></td></tr></table>
<?php
}

function updateforumcount($fid) {
	global $db, $table_threads, $table_forums;
	$query = $db->query("SELECT replies FROM $table_threads WHERE fid='$fid' AND closed !='moved'");
	while($thread = $db->fetch_array($query)) {
		$threadcount++;
		$replycount = $replycount+$thread[replies]+1;
	}
	$query = $db->query("SELECT lastpost FROM $table_threads WHERE fid='$fid' ORDER BY lastpost DESC LIMIT 1");
	$lp = $db->fetch_array($query);
	$db->query("UPDATE $table_forums SET posts='$replycount', threads='$threadcount', lastpost='$lp[lastpost]' WHERE fid='$fid'");
}

function updatethreadcount($tid) {
	global $db, $table_threads, $table_posts;
	$query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid'");
	$replycount = $db->num_rows($query);
	$replycount = $replycount-1;
	$query = $db->query("SELECT dateline, author FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 1");
	$lp = $db->fetch_array($query);
	$lastpost = "$lp[dateline]|$lp[author]";
	$db->query("UPDATE $table_threads SET replies='$replycount', lastpost='$lastpost' WHERE tid='$tid'");
}

function smcwcache() {
	// Cache smilies and censored words (idea by John Miller, coded by surf)
	global $db, $table_smilies, $table_words, $smiliecache, $censorcache, $smiliesnum, $wordsnum;

	$query = $db->query("SELECT count(*) FROM $table_smilies WHERE type='smiley'");
	$smiliesnum = $db->result($query, 0);
	$query = $db->query("SELECT count(*) FROM $table_words");
	$wordsnum = $db->result($query, 0);

	if($smiliesnum) {
		$query = $db->query("SELECT * FROM $table_smilies WHERE type='smiley'");
		while($smilie = $db->fetch_array($query)) {
			$smiliecache[$smilie[code]] = $smilie[url];
		}
	}
	if($wordsnum) {
		$query = $db->query("SELECT * FROM $table_words");
		while($word = $db->fetch_array($query)) {
			$censorcache[$word[find]] = $word[replace];
		}
	}
}
?>