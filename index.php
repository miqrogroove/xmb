<?php
/*

XMB 1.8 Partagium
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/

require "./header.php";
require "./xmb.php";
loadtemplates('header,footer,index_whosonline,index_category,index_forum,index,index_welcome_member,index_welcome_guest,index_forum_lastpost,index_ticker');

if($tickerstatus == "on"){
	$news = explode("\r\n", $tickercontents);
	for($i=0;$i<count($news);$i++){
		$contents .= "tickercontents[$i]='$news[$i]'\n";
	}
	eval("\$ticker = \"".template("index_ticker")."\";");
}


if($gid) {
	$whosonlinestatus = "off";
	$query = $db->query("SELECT name FROM $table_forums WHERE fid='$gid' AND type='group'");
	$cat = $db->fetch_array($query);
	$navigation = "&raquo; $cat[name]";
	$lang_stats4 = "";
}

eval("\$header = \"".template("header")."\";");
echo $header;
if(!$gid) {

	if($xmbuser) {
		eval("\$welcome = \"".template("index_welcome_member")."\";");
	} else {
		eval("\$welcome = \"".template("index_welcome_guest")."\";");
	}
	// Start Whos Online and Stats
	$query = $db->query("SELECT username FROM $table_members ORDER BY regdate DESC");
	$lastmem = $db->fetch_array($query);
	$lastmember = $lastmem[username];
	$members = $db->num_rows($query);

	$query = $db->query("SELECT COUNT(*) FROM $table_threads");
	$threads = $db->result($query, 0);

	$query = $db->query("SELECT COUNT(*) FROM $table_posts");
	$posts = $db->result($query, 0);

	$memhtml = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastmember)."\"><b>$lastmember</b></a>.";
	eval($lang_evalindexstats);

	if($members == "0") {
		$memhtml = "<b>$lang_textnoone</b>";
	}

	if($whosonlinestatus == "on") {
		$time = time();
		$newtime = $time - 600;
		$membercount = 0;
		$query = $db->query("SELECT m.status, m.username, w.* FROM $table_whosonline w LEFT JOIN $table_members m ON m.username=w.username ORDER BY w.username");
		while($online = $db->fetch_array($query)) {
			switch($online[username]) {
				case xguest123:
				$guestcount++;
				break;

				default:
				$member[$membercount] = $online;
				$membercount++;
				break;
			}
		}

		if(!$guestcount) {
			$guestcount = "0";
		}
		if(!$membercount) {
			$membercount = "0";
		}
		$onlinenum = $guestcount + $membercount;

		$guestcount = "0";
		$membercount = "0";
		for($mnum=0; $mnum<$onlinenum; $mnum++) {
		$online = $member[$mnum];
		if($online[username] == "") {
		$guestcount++;
		}
		else{
		$membercount++;
		}
		}


		if($membercount==0){
			$membern = "no Members";
		}elseif($membercount==1){
			$membern = "1 Member";
		}else{
			$membern = "$membercount Members";
		}

		if($guestcount==0){
			$guestn = "No Guests";
		}elseif($guestcount==1){
			$guestn = "1 Guest";
		}else{
			$guestn = "$guestcount Guests";
		}

		eval($lang_whosoneval);
		$memonmsg = "<span class=\"smalltxt\">$lang_whosonmsg</span>";

		$memtally = "";
		$num = 1;
		$comma = "";
		for($mnum=0; $mnum<$membercount; $mnum++) {
			$online = $member[$mnum];
			if($online[status] == "Administrator") {
			$pre = "<b><u>";
			$suf = "</b></u>";
			}
			elseif($online[status] == "Super Administrator") {
			$pre = "<i><b><u>";
			$suf = "</i></b></u>";
			}
			elseif($online[status] == "Super Moderator") {
			$pre = "<i><b>";
			$suf = "</i></b>";
			}
			elseif($online[status] == "Moderator") {
			$pre = "<b>";
			$suf = "</b>";
			}
			else {
				$pre = "";
				$suf = "";
			}
			$memtally .= "$comma <a href=\"member.php?action=viewpro&member=".rawurlencode($online[username])."\">$pre$online[username]$suf</a>";
			$comma = ", ";
			$num++;
		}

		if($memtally == "") {
			$memtally = "&nbsp;";
		}

	$datecut = mktime(0,0,0,date("m"), date("d"), date("y"));
	$query = $db->query("SELECT username FROM $table_members WHERE lastvisit>='$datecut' ORDER BY username DESC LIMIT 0, 50");
	$todaymembersnum = $db->num_rows($query, 0);

	if ($todaymembersnum == 1) {
	$memontoday = $todaymembersnum . $lang_textmembertoday;
	} else {
	$memontoday = $todaymembersnum . $lang_textmemberstoday;
	}

	$todaymembers = ""; while ($memberstoday = $db->fetch_array($query)) {
    	$todaymembers .= "$comma2<a href=\"member.php?action=viewpro&member=".rawurlencode($memberstoday['username'])."\">".$memberstoday['username']."</a>";
    	$comma2 = ", ";
	}

		eval("\$whosonline = \"".template("index_whosonline")."\";");
	}
	// End Whosonline and Stats

	// Start Getting Forums and Groups

	$queryg = $db->query("SELECT * FROM $table_forums WHERE status='on' AND fup='' OR fup='0' ORDER BY displayorder");
}
else {
	$queryg = $db->query("SELECT * FROM $table_forums WHERE type='group' AND fid='$gid' AND status='on' ORDER BY displayorder");
}

while($group = $db->fetch_array($queryg)) {
	$tempforumlist = '';
	if($group[type] == "group") {
		if($catsonly != "on" || $gid) {
			$query = $db->query("SELECT * FROM $table_forums WHERE type='forum' AND status='on' AND fup='$group[fid]' ORDER BY displayorder");
			while($forum = $db->fetch_array($query)) {
				$tempforumlist .= forum($forum, "index_forum");
			}
		}
		if($catsonly != 'on' && !empty($tempforumlist)){
			eval("\$forumlist .= \"".template("index_category")."\";");
			$forumlist .= $tempforumlist;
		}elseif($catsonly == 'on'){
			eval("\$forumlist .= \"".template("index_category")."\";");
		}
	} else {
		$forumlist .= forum($group, "index_forum");
	}
}

eval("\$index = \"".template("index")."\";");
$index = stripslashes($index);
echo $index;

end_time();

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
