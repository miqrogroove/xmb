<?
/*

XMB 1.6 v2c Magic Lantern
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./xmb.php";

function template($name) {
	$tempcache = array();
	global $tempcache, $table_templates, $db;
	if (isset($tempcache[$name])) {
		$template=$tempcache[$name];
	} else {
		$query= $db->query("SELECT * FROM $table_templates WHERE name='$name'");
		$gettemplate=$db->fetch_array($query);
		$template= $gettemplate[template];
		$tempcache[$name]= $template;
	}
	$template=str_replace("\\'","'",$template);
	if($name != "phpinclude") {
		return "<!--Begin Template: $name -->\n$template\n<!-- End Template: $name -->";
	} else {
		return "$template";
	}
}

function loadtemplates($names) {
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
	if($allowhtml != "yes" || $allowhtml == "off") {
		$message=str_replace("&","&amp;",$message);
		$message = str_replace("<","&lt;",$message);
		$message = str_replace(">","&gt;",$message);
	}
	$message = nl2br($message);

	if($smileyoff != "yes" && $allowsmilies == "yes" || $allowsmilies == "on") {
		if($smiliesnum > 0) {
			reset($smiliecache);
			while(list($code, $url) = each($smiliecache)) {
				$message = str_replace("$code", "<img src=\"$smdir/$url\" align=\"absmiddle\" border=0>",$message);
			}
		}
	}

	if($bbcodeoff != "yes" && $allowbbcode == "yes" || $allowbbcode == "on") {
		$message = wordwrap($message, 75, "\n", 1);
		$message = str_replace("[b]", "<b>", $message);
		$message = str_replace("[/b]", "</b>", $message);
		$message = str_replace("[i]", "<i>", $message);
		$message = str_replace("[/i]", "</i>", $message);
		$message = str_replace("[u]", "<u>", $message);
		$message = str_replace("[/u]", "</u>", $message);
		$message = str_replace("[marquee]", "<marquee>", $message);
		$message = str_replace("[/marquee]", "</marquee>", $message);
		$message = str_replace("[blink]", "<blink>", $message);
		$message = str_replace("[/blink]", "</blink>", $message);
		$message = str_replace("[strike]", "<strike>", $message);
		$message = str_replace("[/strike]", "</strike>", $message);
		$message = str_replace("[quote]", "<blockquote><span class=\"mediumtxt\">quote:</span><hr color=$catcolor>", $message);
		$message = str_replace("[/quote]", "<hr></blockquote>", $message);
		$message= str_replace("[code]","<blockquote><pre><span class=\"mediumtxt\">code:</span><hr color=$catcolor>",$message);
		$message= str_replace("[/code]","<hr></pre></blockquote>",$message);
		$message = str_replace("[list]","<ul type=square>",$message);
		$message = str_replace("[/list]","</ul>",$message);
		$message = str_replace("[list=1]","<ol type=1>",$message);
		$message = str_replace("[list=a]","<ol type=A>",$message);
		$message = str_replace("[list=A]","<ol type=A>",$message);
		$message = str_replace("[/list=1]","</ol>",$message);
		$message = str_replace("[/list=a]","</ol>",$message);
		$message = str_replace("[/list=A]","</ol>",$message);
		$message=str_replace("[*]","<li>",$message);
		$message = eregi_replace("(^|[>[:space:]\n])([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])([<[:space:]\n]|$)","\\1<a href=\"\\2://\\3\\4\" target=\"_blank\">\\2://\\3\\4</a>\\5", $message);
		$message = eregi_replace("\\[color=([^\\[]*)\\]([^\\[]*)\\[/color\\]","<font color=\"\\1\">\\2</font>",$message);
		$message = eregi_replace("\\[size=([^\\[]*)\\]([^\\[]*)\\[/size\\]","<font size=\"\\1\">\\2</font>",$message);
		$message = eregi_replace("\\[font=([^\\[]*)\\]([^\\[]*)\\[/font\\]","<font face=\"\\1\">\\2</font>",$message);
		$message = eregi_replace("\\[align=([^\\[]*)\\]([^\\[]*)\\[/align\\]","<p align=\"\\1\">\\2</p>",$message);
		$patterns = array();
		$replacements = array();
		$patterns[0] = "#\[url\]([a-z]+?://){1}(.*?)\[/url\]#si";
		$replacements[0] = '<a href="\1\2" target="_blank">\1\2</a>';
		$patterns[1] = "#\[url\](.*?)\[/url\]#si";
		$replacements[1] = '<a href="http://\1" target="_blank">\1</a>';
		$patterns[2] = "#\[url=([a-z]+?://){1}(.*?)\](.*?)\[/url\]#si";
		$replacements[2] = '<a href="\1\2" target="_blank">\3</a>';
		$patterns[3] = "#\[url=(.*?)\](.*?)\[/url\]#si";
		$replacements[3] = '<a href="http://\1" target="_blank">\2</a>';
		$patterns[4] = "#\[email\](.*?)\[/email\]#si";
		$replacements[4] = '<a href="mailto:\1">\1</a>';
		$patterns[5] = "#\[email=(.*?){1}(.*?)\](.*?)\[/email\]#si";
		$replacements[5] = '<a href="mailto:\1\2">\3</a>';
		$message = preg_replace($patterns, $replacements, $message);

	}

	if($allowimgcode == "yes" && $bbcodeoff != "yes" || $allowimgcode == "on") {
		if (eregi("\\[img\\]http",$message) && (eregi("jpg\\[/img\\]",$message) || eregi("gif\\[/img\\]",$message) || eregi("php\\[/img\\]",$message))) {
			$message = eregi_replace("\\[img\\]([^\\[]*)\\[/img\\]","<img src=\"\\1\" border=0>",$message);
			$message = eregi_replace("\\[img=([^\\[]*)x([^\\[]*)\\]([^\\[]*)\\[/img\\]","<img width=\"\\1\" height=\"\\2\" src=\"\\3\" border=0>",$message);
		}
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
		if($lastpost[1] != "Anonymous") {
		$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
		} else {
		$lastpost[1] = "$lang_textanonymous";
		}

		$lastpostdate = gmdate("$dateformat",$lastpost[0] + ($timeoffset * 3600));
		$lastposttime = gmdate("$timecode",$lastpost[0] + ($timeoffset * 3600));
		$lastpost = "$lastpostdate $lang_textat $lastposttime<br>$lang_textby $lastpost[1]";
		eval("\$lastpostrow = \"".template("".$template."_lastpost")."\";");
	} else {
		$lastpost = "$lang_textnever";
		$lastpostrow = "$lang_textnever";
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
	$comma = "";
	if($authorization || $hideprivate == "off") {
		if($forum[moderator] != "") {
			$moderators = explode(", ", $forum[moderator]);
			$forum[moderator] = "";
			for($num = 0; $num < count($moderators); $num++) {
				$forum[moderator] .= "$comma<a href=\"member.php?action=viewpro&member=$moderators[$num]\">$moderators[$num]</a>";
				$comma = ", ";
			}
			$forum[moderator] = "($lang_textmodby $forum[moderator])";
		} else {
			$forum[moderator] = "";
		}
		eval("\$foruminfo .= \"".template("$template")."\";");
	}
	
	$foruminfo = stripslashes($foruminfo);
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
	global $imgdir, $bbinsert, $altbg1, $altbg2, $langfile;
	if($bbinsert == "on") {
		require "lang/$langfile.lang.php";
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
				$smilies .= "</tr><tr>";
				$col_smilies = 0;
			}
		}
		$smilies .= "</tr>";
		eval("\$smilieinsert .=
\"".template("functions_smilieinsert")."\";");
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
// modified -----
	while (list ($key, $val) = each ($GLOBALS))
	{
		$$key = $val;
	}
	loadtemplates("css");
	eval("\$css = \"".template("css")."\";");
// end mod -----
	eval("\$header = \"".template("header")."\";");
	echo $header;
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
	<tr><td class="mediumtxt"><center><?=$message?></center></td></tr></table>
<?php
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
}


function updateforumcount($fid) {
	global $db, $table_threads, $table_forums;
	$query = $db->query("SELECT replies FROM $table_threads WHERE fid='$fid' AND closed !='moved'");
	while($thread = $db->fetch_array($query)) {
		$threadcount++;
		$replycount = $replycount+$thread[replies]+1;
	}
	// mod count subforums threads
	$query = $db->query("SELECT fid FROM $table_forums WHERE fup='$fid'");
	while($children = $db->fetch_array($query)) {
		$query = $db->query("SELECT replies FROM $table_threads WHERE fid='$children[fid]' AND closed !='moved'");
		while($thread = $db->fetch_array($query)) {
			$threadcount++;
			$replycount = $replycount+$thread[replies]+1;
		}
	}
	// mod
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
                        $censorcache[$word[find]] = $word[replace1];
                }
        }
}
?>