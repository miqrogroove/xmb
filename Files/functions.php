<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

function template($name){
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
	$sql = '';
	while (list($key,$title) = each($namesarray)) {
		if ($sql != '') {
			$sql .= ",";
		}
		$sql .= "'$title'";
	}
	$query = $db->query("SELECT * FROM $table_templates WHERE name IN ($sql)");
	while($template = $db->fetch_array($query)) {
		$name			= $template['name'];
		$tempcache[$name] 	= $template['template'];
		}
}

function censor($txt){
	global $censorcache;
	
	if(is_array($censorcache)){
		if(count($censorcache) > 0){
			reset($censorcache);
			while(list($find, $replace) = each($censorcache)) {
				$txt = preg_replace("#\b($find)\b#si",$replace, $txt);
			}
		}
	}
	
	return $txt;
}

function postify($message, $smileyoff='no', $bbcodeoff='no', $allowsmilies="yes", $allowhtml="yes", $allowbbcode="yes", $allowimgcode="yes") {
	global $imgdir, $bordercolor, $table_words, $table_forums, $table_smilies, $db, $smdir, $smiliecache, $censorcache, $smiliesnum, $wordsnum;
	
	$message = checkOutput($message, $allowhtml);

// U2U Security Fix

	$remove = '"';
	$bbcode = Array('url','email','color','size');
	foreach($bbcode as $code){
		$patterns = "#\[$code(.*?)$remove(.*?)\](.*?)\[/$code\]#si";
		$replacements = '&#91;'.$code.'\1'.$remove.'\2&#93;\3&#91;/'.$code.'&#93;';
		$message = preg_replace($patterns, $replacements, $message);
	}

// End

	$message = nl2br($message);
	$message = wordwrap($message, 150, "\n", 1);
	$message = censor($message);
	
	if(!($allowbbcode == 'no' || $allowbbcode == 'off') && !($bbcodeoff == 'yes' || $bbcodeoff == 'off')) {
		$message = stripslashes($message);

		$message = str_replace("[b]", "<b>", $message);
		$message = str_replace("[/b]", "</b>", $message);
		$message = str_replace("[i]", "<i>", $message);
		$message = str_replace("[/i]", "</i>", $message);
		$message = str_replace("[poem]", "<center><i>", $message);
		$message = str_replace("[/poem]", "</center></i>", $message);
		$message = str_replace("[u]", "<u>", $message);
		$message = str_replace("[/u]", "</u>", $message);
		$message = str_replace("[marquee]", "<marquee>", $message);
		$message = str_replace("[/marquee]", "</marquee>", $message);
		$message = str_replace("[blink]", "<blink>", $message);
		$message = str_replace("[/blink]", "</blink>", $message);
		$message = str_replace("[strike]", "<strike>", $message);
		$message = str_replace("[/strike]", "</strike>", $message);
		$message = str_replace("[vinfo]","<b>".strrev("muigatraP - 8.1 BMX")."</b>",$message);
		$message = str_replace("[quote]", "<blockquote><span class=\"mediumtxt\">quote:</span><hr color=$catcolor>", $message);
		$message = str_replace("[/quote]", "<hr /></blockquote>", $message);
		$message = str_replace("[code]","<blockquote><pre><span class=\"mediumtxt\">code:</span><hr color=$catcolor>",$message);
		$message = str_replace("[/code]","<hr /></pre></blockquote>",$message);
		$message = str_replace("[list]","<ul type=square>",$message);
		$message = str_replace("[/list]","</ul>",$message);
		$message = str_replace("[list=1]","<ol type=1>",$message);
		$message = str_replace("[list=a]","<ol type=A>",$message);
		$message = str_replace("[list=A]","<ol type=A>",$message);
		$message = str_replace("[/list=1]","</ol>",$message);
		$message = str_replace("[/list=a]","</ol>",$message);
		$message = str_replace("[/list=A]","</ol>",$message);
		$message = str_replace("[credits]", "XMB 1.8 Main Developers - Tularis, Richard, IT, RevMac For More Information On Other Staff - Visit XMBForum.com",$message);
		$message = str_replace("[*]","<li>",$message);
		$message = str_replace("<br />"," <br />",$message); 
		$message = str_replace("[buildedition]", "<b>Build ID: ".$versionbuild."</b>", $message);

		$message = eregi_replace("(^|[>[:space:]\n])([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])([<[:space:]\n]|$)","\\1<a href=\"\\2://\\3\\4\" target=\"_blank\">\\2://\\3\\4</a>\\5", $message);
		
		$message = eregi_replace("\\[color=([^\\[]*)\\]([^\\[]*)\\[/color\\]","<font color=\\1>\\2</font>",$message);
		$message = eregi_replace("\\[size=([^\\[]*)\\]([^\\[]*)\\[/size\\]","<font size=\\1>\\2</font>",$message);
		$message = eregi_replace("\\[font=([^\\[]*)\\]([^\\[]*)\\[/font\\]","<font face=\\1>\\2</font>",$message);
		$message = eregi_replace("\\[align=([^\\[]*)\\]([^\\[]*)\\[/align\\]","<p align=\\1>\\2</p>",$message);

		if(($allowsmilies != 'no' && $allowsmilies != 'off') && ($smileyoff != 'yes' && $smileyoff != 'off')) {
			if($smiliesnum > 0) {
				reset($smiliecache);
				foreach($smiliecache as $code=>$url){
					$message = str_replace($code, "<img src=".$smdir."/".$url." align=\"absmiddle\" border=0 />",$message);
				}
			}
		}
		
		$patterns = array();
		$replacements = array();
		
		if(($allowimgcode != 'no' && $allowimgcode != 'off') && ($allowbbcode != 'no' && $allowbbcode != 'off')) {
			if (stristr($message, 'jpg[/img]') || stristr($message, 'jpeg[/img]') || stristr($message, 'gif[/img]') || stristr($message, 'png[/img]') || stristr($message, 'bmp[/img]') || stristr($message, 'php[/img]')){
				$patterns[]	= "#\[img\]([^\[]*)\[/img\]#si";
				$replacements[]	= '<img src="\1" border=0 />';
				
				$patterns[]	= "#\[img=([^\[]*)x([^\[]*)\]([^\[]*)\[/img\]#si";
				$replacements[]	= '<img width="\1" height="\2" src="\3" border=0 />';
			}
				
			$patterns[]	= "#\[flash=([0-9].*?){1}x([0-9]*?)\](.*?)\[/flash\]#si";
			$replacements[]	= '<OBJECT classid=clsid:D27CDB6E-AE6D-11cf-96B8-444553540000 codebase=http://active.macromedia.com/flash2/cabs/swflash.cab#version=6,0,0,0 ID=main WIDTH=\1 HEIGHT=\2><PARAM NAME=movie VALUE=\3><PARAM NAME=loop VALUE=false><PARAM NAME=menu VALUE=false><PARAM NAME=quality VALUE=best><EMBED src=\3 loop=false menu=false quality=best WIDTH=\1 HEIGHT=\2 TYPE=application/x-shockwave-flash PLUGINSPAGE=http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash></EMBED></OBJECT>';
		}

		$patterns[]	= "#\[url\]([a-z]+?://){1}(.*?)\[/url\]#si";
		$replacements[] = '<a href="\1\2" target="_blank">\1\2</a>';
		
		$patterns[]	= "#\[url\](.*?)\[/url\]#si";
		$replacements[] = '<a href="http://\1" target="_blank">\1</a>';
		
		$patterns[]	= "#\[url=([a-z]+?://){1}(.*?)\](.*?)\[/url\]#si";
		$replacements[] = '<a href="\1\2" target="_blank">\3</a>';
		
		$patterns[]	= "#\[url=(.*?)\](.*?)\[/url\]#si";
		$replacements[] = '<a href="http://\1" target="_blank">\2</a>';
		
		$patterns[]	= "#\[email\](.*?)\[/email\]#si";
		$replacements[] = '<a href="mailto:\1">\1</a>';
		
		$patterns[]	= "#\[email=(.*?){1}(.*?)\](.*?)\[/email\]#si";
		$replacements[] = '<a href="mailto:\1\2">\3</a>';
		
		$message = preg_replace($patterns, $replacements, $message);
		
		$message = addslashes($message);
	}else{
		if(($allowsmilies != 'no' && $allowsmilies != 'off') && ($smileyoff != 'yes' && $smileyoff != 'off')) {
			if($smiliesnum > 0) {
				reset($smiliecache);
				foreach($smiliecache as $code=>$url){
					$message = str_replace($code, "<img src=".$smdir."/".$url." align=\"absmiddle\" border=0 />",$message);
				}
			}
		}
	}
	
	return $message;
}

// Modcheck Patch


function modcheck($status, $username, $fid) {
	global $db, $table_forums;
	if($status == "Moderator") {
		$query = $db->query("SELECT moderator FROM $table_forums WHERE fid = '$fid'LIMIT 1 ");
		$mods = $db->fetch_array($query);
		$modlist = explode(",",$mods[moderator]);
		for($i = 0; $i < count($modlist); $i++) {
			if(trim($modlist[$i]) == $username) {
				$status1 = "Moderator";
				break;
			}
		}
	}
	return $status1;
}

function privfcheck($private, $userlist) {
	global $status, $xmbuser, $hideprivate;
	
	if($private == "2" && ($status == "Administrator" || $status == "Super Administrator")) {
		return true;
	} elseif($private == "3" && ($status == "Administrator" || $status == "Moderator" || $status == "Super Moderator" || $status == "Super Administrator")) {
		return true;
	} elseif($private == "1" && $userlist == "") {
		return true;
	} elseif($userlist != "") {
		$user = explode(",", $userlist);
		for($i=0;$i<count($user);$i++){
			if(((strtolower($xmbuser) == strtolower(trim($user[$i]))) && trim($user[$i]) != '') || $status == 'Super Administrator'){
				return true;
			}
		}
	}
	return false;
}


function forum($forum, $template) {
	global $timecode, $dateformat, $langfile, $xmbuser, $status, $lastvisit2, $timeoffset, $hideprivate, $addtime;
	require "lang/$langfile.lang.php";
	$altbg1 = $GLOBALS['altbg1'];
	$altbg2 = $GLOBALS['altbg2'];
	$imgdir = $GLOBALS['imgdir'];

	if($forum['lastpost'] != '') {
		$lastpost = explode("|", $forum[lastpost]);
		$dalast = $lastpost[0];
		if($lastpost[1] != "Anonymous" && $lastpost[1] != "") {
			$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
		} else {
			$lastpost[1] = "$lang_textanonymous";
		}

		$lastpostdate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
		$lastposttime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
		$lastpost = "$lastpostdate $lang_textat $lastposttime<br />$lang_textby $lastpost[1]";
		eval("\$lastpostrow = \"".template("".$template."_lastpost")."\";");
	} else {
		$dalast		= 0;
		$lastpost	= $lang_textnever;
		eval("\$lastpostrow = \"".template(''.$template.'_nolastpost')."\";");
	}
	
	$lastvisit2 -= 540;
	if($lastvisit2 < $dalast) {
		$folder = "<img src=\"$imgdir/red_folder.gif\" />";
	} else {
		$folder = "<img src=\"$imgdir/folder.gif\" />";
	}

	if($dalast == "") {
		$folder = "<img src=\"$imgdir/folder.gif\" />";
	}

	$lastvisit2 += 540;
	$authorization = privfcheck($forum['private'], $forum['userlist']);
	$comma = "";
	if($authorization || $hideprivate == "off" || $status == "Super Administrator") {
		if($forum['moderator'] != '') {
			$moderators = explode(", ", $forum[moderator]);
			$forum['moderator'] = '';
			for($num = 0; $num < count($moderators); $num++) {
				$forum[moderator] .= "$comma<a href=\"member.php?action=viewpro&member=$moderators[$num]\">$moderators[$num]</a>";
				$comma = ", ";
			}
			$forum['moderator'] = "($lang_textmodby $forum[moderator])";
		} else {
			$forum['moderator'] = "";
		}
		eval("\$foruminfo = \"".template("$template")."\";");
	}
	
	$foruminfo = stripslashes($foruminfo);
	$dalast = "";
	$fmods = "";
	$authorization = "";
	
	return $foruminfo;
}


function multi($num, $perpage, $page, $mpurl) {
	if(($num > $perpage) && $num != 0) {
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
		eval("\$smilieinsert .= \"".template("functions_smilieinsert")."\";");
	}
	return $smilieinsert;
}

function printsetting1($setname, $varname, $check1, $check2) {
	global $langfile, $altbg1, $altbg2;
	require "lang/$langfile.lang.php";

	?>
	<tr><td class="tablerow" bgcolor="<?=$altbg1?>"><?=$setname?></td>
	<td class="tablerow" bgcolor="<?=$altbg2?>"><select name="<?=$varname?>">
	<option value="on" <?=$check1?>><?=$lang_texton?></option><option value="off" <?=$check2?>><?=$lang_textoff?></option>
	</select></td></tr>
	<?php
}

function printsetting2($setname, $varname, $value, $size) {
	global $altbg1, $altbg2;

	?>
	<tr>
	<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$setname?></td>
	<td class="tablerow" bgcolor="<?=$altbg2?>"><input type="text"  size="<?=$size?>" value="<?=$value?>" name="<?=$varname?>" /></td>
	</tr>
	<?php
}

function noaccess($message) {

	while(list($key, $val) = each($GLOBALS)){
		$$key = $val;
	}
	
	loadtemplates("css");
	eval("\$css = \"".template("css")."\";");

	eval("\$header = \"".template("header")."\";");
	echo $header;
	?>
	
	<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
	<tr><td class="mediumtxt"><center><?=$message?></center></td></tr></table>
	
	<?php
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
}


function updateforumcount($fid) {
	global $db, $table_posts, $table_forums, $table_threads;
	$postcount = 0;
	$threadcount = 0;
	
	$pquery = $db->query("SELECT count(pid) FROM $table_posts WHERE fid='$fid'");
	$postcount = $db->result($pquery, 0);
	
	$tquery = $db->query("SELECT count(tid) FROM $table_threads WHERE (fid='$fid' AND closed != 'moved')");
	$threadcount = $db->result($tquery, 0);
	
	// Count posts in subforums.
	$queryc = $db->query("SELECT fid FROM $table_forums WHERE fup='$fid'");
	while($children = $db->fetch_array($queryc)) {
		$chquery1 = '';
		$chquery2 = '';
		$chquery1 = $db->query("SELECT count(pid) FROM $table_posts WHERE fid='$children[fid]'");
		$postcount += $db->result($chquery1, 0);
	
		$chquery2 = $db->query("SELECT count(tid) FROM $table_threads WHERE fid='$children[fid]' AND closed != 'moved'");
		$threadcount += $db->result($chquery2, 0);
	}

	$query = $db->query("SELECT lastpost FROM $table_threads WHERE fid='$fid' ORDER BY lastpost DESC LIMIT 0,1");
	$lp = $db->fetch_array($query);
	$db->query("UPDATE $table_forums SET posts='$postcount', threads='$threadcount', lastpost='$lp[lastpost]' WHERE fid='$fid'");
}

function updatethreadcount($tid) {
	global $db, $table_threads, $table_posts;
	$query1 = $db->query("SELECT * FROM $table_posts WHERE tid='$tid'");
	$replycount = $db->num_rows($query1);
	$replycount = $replycount-1;
	$query2 = $db->query("SELECT dateline, author FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 1");
	$lp = $db->fetch_array($query2);
	$lastpost = "$lp[dateline]|$lp[author]";
	$db->query("UPDATE $table_threads SET replies='$replycount', lastpost='$lastpost' WHERE tid='$tid'");
}

function smcwcache() {
	global $db, $table_smilies, $table_words, $smiliecache, $censorcache, $smiliesnum, $wordsnum;


	$smquery = $db->query("SELECT * FROM $table_smilies WHERE type='smiley'");
	$smiliesnum = $db->num_rows($smquery);
	$wquery = $db->query("SELECT * FROM $table_words");
	$wordsnum = $db->num_rows($wquery);

	if($smiliesnum > 0) {
		while($smilie = $db->fetch_array($smquery)) {
			$code = $smilie['code'];
			$smiliecache[$code] = $smilie['url'];
		}
	}
	if($wordsnum > 0) {
		while($word = $db->fetch_array($wquery)) {
			$find = $word['find'];
			$censorcache[$find] = $word['replace1'];
		}
	}
}

function checkInput($input, $striptags='no', $allowhtml='no', $word=''){
	// Function generously donated by FiXato
	
	$input = trim($input);
	if($striptags != 'no'){
		$input = strip_tags($input);
	}
	
	if($allowhtml != 'yes' && $allowhtml != 'on'){
		$input = htmlspecialchars($input, ENT_QUOTES);
	}
	if($word != '')	{
		$input = str_replace($word, "_".$word, $input);
	}
	
	return $input;
}

function checkOutput($output, $allowhtml='no', $word=''){
	
	$output = trim($output);
	if($allowhtml == 'yes' || $allowhtml == 'on'){
		$output = htmlspecialchars_decode($output);
	}
	if($word != '')	{
		$output = str_replace($word, "_".$word, $output);
	}
	
	return $output;
}

function htmlspecialchars_decode($string){
	$array = array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES));
	return strtr($string, $array);
}

function htmlentities_decode($string){
	$array = array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES));
	return strtr($string, $array);
}



function end_time() {
	global $starttime, $totaltime;
	
	$mtime2 = explode(" ", microtime());
	$endtime = $mtime2[1] + $mtime2[0];
	$totaltime = ($endtime - $starttime);
	$totaltime = number_format($totaltime, 7);
	
	return $totaltime;
}
?>