<?
require "./header.php";

loadtemplates('header,footer,post_notloggedin,post_loggedin,post_preview,post_attachmentbox,post_newthread,post_reply_review_toolong,post_reply_review_post,post_reply,post_edit,functions_smilieinsert,functions_smilieinsert_smilie,functions_bbcodeinsert');

if($tid && $fid) {
	$query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
	$thread = $db->fetch_array($query);
	$threadname = $thread[subject];
	$threadname = stripslashes($threadname);
    $fid = $thread[fid];
}


$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forums = $db->fetch_array($query);

if($forums[type] != "forum" && $forums[type] != "sub" && $forums[fid] != $fid) {
	$posterror = $lang_textnoforum;
}


/*
if($tid && $fid) {
	$query = $db->query("SELECT subject FROM $table_threads WHERE fid='$fid' AND tid='$tid'");
	$thread = $db->fetch_array($query);
	$threadname = $thread[subject];
	$threadname = stripslashes($threadname);
}

if($tid && $fid) {
	$query = $db->query("SELECT subject FROM $table_threads WHERE fid='$fid' AND tid='$tid'");
	$threadname = $db->result($query,0);
	$threadname = stripslashes($threadname);
}
*/

if($forums[type] == "forum") {
	$postaction = "<a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &raquo; ";
} else {
	$query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'");
	$fup = $db->fetch_array($query);
	$postaction = "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &gt; <a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &raquo; ";
}

if($action != "edit" && $tid) {
	$postaction .= "$lang_textpostreply";
}
elseif($action == "edit") {
	$postaction .= "$lang_texteditpost";
}

if($action != "edit" && !$tid) {
	if($forums[type] == "forum") {
		$postaction = "<a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &raquo; ";
	} else {
		$query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'");
		$fup = $db->fetch_array($query);
		$postaction = "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &raquo; <a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &raquo; ";
	}
}

if($action != "edit" && !$tid) {
	$postaction .= "$lang_textpostnew";
}

// Get bb code and smilie inserters ready
$bbcodeinsert = bbcodeinsert();
$smilieinsert = smilieinsert();

if($forums[attachstatus] != "off") {
	eval("\$attachfile = \"".template("post_attachmentbox")."\";");
}

if(!$xmbuser || !$xmbpw) {
	eval("\$loggedin = \"".template("post_notloggedin")."\";");
} else {
	eval("\$loggedin = \"".template("post_loggedin")."\";");
}
$navigation = "&raquo; $postaction";

if($status == "Banned") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><span class=\"mediumtxt \">$lang_bannedmessage</span></center>";
}



	$listed_icons = 0; 
if($status != "Administrator" && $status != "Super Moderator" && $status != "Moderator"){ 
	$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='picon' AND (url NOT LIKE '%rsvd%')"); 
	while($smilie = $db->fetch_array($querysmilie)) { 
		$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" />"; 
		$listed_icons += 1; 
		if($listed_icons == 9) { 
			$icons .= "<br />"; 
			$listed_icons = 0; 
		}
	}
} else {
	$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='picon'");
	while($smilie = $db->fetch_array($querysmilie)) {
		$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" />";
		$listed_icons += 1;
		if($listed_icons == 9) {
			$icons .= "<br />";
			$listed_icons = 0;
		}
	}
}



if($forums[allowimgcode] == "yes") {
	$allowimgcode = "$lang_texton";
} else {
	$allowimgcode = "$lang_textoff";
}

if($forums[allowhtml] == "yes") {
	$allowhtml = "$lang_texton";
} else {
	$allowhtml = "$lang_textoff";
}

if($forums[allowsmilies] == "yes") {
	$allowsmilies = "$lang_texton";
} else {
	$allowsmilies = "$lang_textoff";
}

if($forums[allowbbcode] == "yes") {
	$allowbbcode = "$lang_texton";
} else {
	$allowbbcode = "$lang_textoff";
}

$pperm = explode("|", $forums[postperm]);

if($pperm[0] == "1") {
	$whopost1 = $lang_whocanpost11;
} elseif($pperm[0] == "2") {
	$whopost1 = $lang_whocanpost12;
} elseif($pperm[0] == "3") {
	$whopost1 = $lang_whocanpost13;
} elseif($pperm[0] == "4") {
	$whopost1 = $lang_whocanpost14;
}

if($pperm[1] == "1") {
	$whopost2 = $lang_whocanpost21;
} elseif($pperm[1] == "2") {
	$whopost2 = $lang_whocanpost22;
} elseif($pperm[1] == "3") {
	$whopost2 = $lang_whocanpost23;
} elseif($pperm[1] == "4") {
	$whopost2 = $lang_whocanpost24;
}

if($pperm[0] == "4" && $pperm[1] == "4") {
	$whopost3 = $lang_whocanpost32;
}

if($xmbuser && $xmbuser != '') {
	if($signature != "") {
		$usesigcheck = "checked";
	}
}
if(!$xmbuser && $forums[guestposting] == "on") {
	$guestpostingmsg = $lang_guestpostingonmsg;
}

if(($forums[private] == "2" || $subf[private] == "2") && $status != "Administrator") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><span class=\"mediumtxt \">$lang_privforummsg</span></center>";
	exit;
} elseif(($forums[private] == "3" || $subf[private] == "3") && $status != "Administrator" && $status != "Moderator" && $status != "Super Moderator") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><span class=\"mediumtxt \">$lang_privforummsg</span></center>";
	exit;
} elseif(($forums[private] == "4" || $subf[private] == "4")&&(!privfcheck($forums[private], $forums[userlist]))){
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><span class=\"mediumtxt \">$lang_privforummsg</span></center>";
	exit;
}

if($posterror) {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><span class=\"mediumtxt \">$posterror</span></center>";
	exit;
}
// Start forum password check
if($forums[password] != $HTTP_COOKIE_VARS["fidpw$fid"] && $forums[password] != "") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	eval("\$pwform = \"".template("forumdisplay_password")."\";");
	echo $pwform;
	exit;
}

if($previewpost) {
	$currtime = time();
	$date = gmdate("n/j/y",$currtime + ($timeoffset * 3600));
	$time = gmdate("H:i",$currtime + ($timeoffset * 3600));
	$poston = "$lang_textposton $date $lang_textat $time";

	$subject = stripslashes($subject);
	$message = stripslashes($message);
	$message1 = postify($message, $smileyoff, $bbcodeoff, $forums[allowsmilies], $forums[allowhtml], $forums[allowbbcode], $forums[allowimgcode]);

	if($smileyoff == "yes") {
		$smileoffcheck = "checked=\"checked\"";
	}

	if($usesig == "yes") {
		$usesigcheck = "checked=\"checked\"";
	}

	if($bbcodeoff == "yes") {
		$codeoffcheck = "checked=\"checked\"";
	}

	if($subject != "") {
		$dissubject = stripslashes($subject);
	} else {
		$dissubject = "";
	}
	eval("\$preview = \"".template("post_preview")."\";");
}

if($action == "newthread") {
	$priv = privfcheck($private, $userlist);
	if(!$topicsubmit) {
		eval("\$header = \"".template("header")."\";");
		echo $header;
		$status1 = modcheck($status, $xmbuser, $fid);
		if($status == "Super Moderator") {
			$status1 = "Moderator";
		}

		if($status == "Administrator" || $status1 == "Moderator") {
			$topoption = "<br /><input type=\"checkbox\" name=\"toptopic\" value=\"yes\" />$lang_topmsgques<br />";
		}

		if($poll == "yes" && $forums[pollstatus] != "off") {
			eval("\$postform = \"".template("post_newpoll")."\";");
			echo $postform;
		} else {
			eval("\$postform = \"".template("post_newthread")."\";");
			echo $postform;
		}
	}
	if($topicsubmit) {
		if($username != "") {
			if(!$xmbuser && !$xmbpw) {
				$password = md5($password);
			}
			$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$username'");
			$member = $db->fetch_array($query);

			if(!$member[username]) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
				exit;
			}

			$username = $member[username];

			if($password != $member[password]) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
				exit;
			}

			if($status == "Banned") {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_bannedmessage</span></center>";
				exit;
			}
		} else {
		$username = "Anonymous";
		}
		if($forums[guestposting] != "on" && $username == "Anonymous") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_textnoguestposting</span></center>";
			exit;
		}
		if($subject == "" || ereg("^ *$", $subject)) {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_textnosubject</span></center>";
			exit;
		}

		$pperm = explode("|", $forums[postperm]);

		if($pperm[0] == "2" && $status != "Administrator") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span></center>";
			exit;
		} elseif($pperm[0] == "3" && $status != "Administrator" && $status != "Moderator" && $status != "Super Moderator") {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span></center>";
				exit;
		} elseif($pperm[0] == "4") {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span><center>";
				exit;
		}

		$query = $db->query("SELECT lastpost, type, fup FROM $table_forums WHERE fid='$fid'");
		$for = $db->fetch_array($query);

		if($for[lastpost] != "") {
			$lastpost = explode("|", $for[lastpost]);
			$rightnow = time() - $floodctrl;

			if($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
				$floodlink = "<a href=\"forumdisplay.php?fid=$fid\">Click here</a>";
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_floodprotect $floodlink $lang_tocont</span></center>";
				exit;
			}
		}

		$subject = str_replace("<", "&lt;", $subject);
		$subject = str_replace(">", "&gt;", $subject);
		$message = addslashes($message);
		$subject = addslashes($subject);

		if($attach != "none" && $attach != "" && $forums[attachstatus] != "off") {
			$attachedfile = addslashes(fread(fopen($attach, "r"), filesize($attach)));
			if($attach_size > 1000000) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_attachtoobig</span></center>";
				exit;
			}
		}
		if($usesig != "yes") {
			$usesig = "no";
		}

		if($forums[pollstatus] != "off") {
			$pollops = explode("\n", $pollanswers);
			$pollanswers = "";
			for($pnum = 0; $pnum < 10; $pnum++) {
				if($pollops[$pnum] != "") {
					$pollanswers .= "$pollops[$pnum]||~|~|| 0#|#";
				}
			}

			$pollanswers = str_replace("\n", "", $pollanswers);
		}

		$thatime = time();
		$db->query("INSERT INTO $table_threads VALUES ('', '$fid', '$subject', '$posticon', '$thatime|$username', '0', '0', '$username', '', '', '$pollanswers')");
		$tid = $db->insert_id();
		$db->query("INSERT INTO $table_posts VALUES ('$fid', '$tid', '', '$username', '$message', '$subject', '$thatime', '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
		$pid = $db->insert_id();
		// Insert Attachment if there is one
		if($attach != "none" && $attach != "" && $forums[attachstatus] != "off") {
		if($attach_type=="application/x-gzip-compressed" || $attach_type=="application/zip"){
			$attachedfile = base64_decode($attachedfile);
		}
			$db->query("INSERT INTO $table_attachments VALUES ('', '$tid', '$pid', '$attach_name', '$attach_type', '$attach_size', '$attachedfile', '0')");
		}
		$db->query("UPDATE $table_forums SET lastpost='$thatime|$username', threads=threads+1, posts=posts+1 WHERE fid='$fid'");

		// Auto subscribe options
		if($emailnotify == "yes") {
			$query = $db->query("SELECT tid FROM $table_favorites WHERE tid='$tid' AND username='$xmbuser' AND type='subscription'");
			$thread = $db->fetch_array($query);
			if(!$thread) {
				$db->query("INSERT INTO $table_favorites VALUES ('$tid', '$username', 'subscription')");
			}
		}


		if($for[type] == "sub") {
			$db->query("UPDATE $table_forums SET lastpost='$thatime|$username', threads=threads+1, posts=posts+1 WHERE fid='$for[fup]'");
		}

		$db->query("UPDATE $table_members SET postnum=postnum+1 WHERE username like '$username'");

		if(($status == "Administrator" || $status == "Super Moderator" || $status == "Moderator") && $toptopic == "yes") {
			$db->query("UPDATE $table_threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
		}
		if(($status == "Administrator") && $Announcement == "yes") {
			$db->query("UPDATE $table_threads SET topped='2' WHERE tid='$tid' AND fid='$fid'");
		}
		if(!$xmbuser || !$xmbpw) {
			$currtime = time() + (86400*30);
			setcookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
			setcookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
		}
		eval("\$header = \"".template("header")."\";");
		echo $header;
		echo "<center><span class=\"mediumtxt \">$lang_postmsg</span></center>";
		?>
		<script>
		function redirect() {
		window.location.replace("viewthread.php?tid=<?=$tid?>");
		}
		setTimeout("redirect();", 1250);
		</script>
		<?
	}
}

if($action == "reply") {
	$priv = privfcheck($private, $userlist);
	if(!$replysubmit) {
		eval("\$header = \"".template("header")."\";");
		echo $header;
		// Start Reply With Quote
		if($repquote) {
			$query = $db->query("SELECT p.message, p.fid, p.author, f.private AS fprivate, f.userlist AS fuserlist FROM $table_posts p, $table_forums f WHERE pid='$repquote' AND f.fid=p.fid");
			$thaquote = $db->fetch_array($query);
			$quotefid = $thaquote[fid];
			$message = $thaquote[message];

			$authorization = privfcheck($thaquote[fprivate], $thaquote[fuserlist]);
			if(!$authorization) {
				echo "<center><span class=\"mediumtxt \">$lang_privforummsg</span></center>";
				exit;
			}

			$message = stripslashes($message);
			$message = "[quote][i]$lang_origpostedby $thaquote[author][/i]\n$message [/quote]";
		}
		// Start Topic/Thread Review
		if(!$ppp || $ppp == '') {
			$ppp = $postperpage;
		}
		$querytop = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid='$tid'");
		$replynum = $db->result($querytop, 0);
		if($replynum >= $ppp) {
			$threadlink = "viewthread.php?fid=$fid&tid=$tid";
			eval($lang_evaltrevlt);
			eval("\$posts .= \"".template("post_reply_review_toolong")."\";");
		}
		else {
			$thisbg = $altbg1;
			$query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC");
			while($post = $db->fetch_array($query)) {
				$date = gmdate($dateformat, $post[dateline] + ($timeoffset * 3600));
				$time = gmdate($timecode, $post[dateline] + ($timeoffset * 3600));

				$poston = "$lang_textposton $date $lang_textat $time";
				if($post[icon] != "") {
					$post[icon] = "<img src=\"$smdir/$post[icon]\" alt=\"Icon depicting mood of post\" />";
				}

				$bbcodeoff = $post[bbcodeoff];
				$smileyoff = $post[smileyoff];
				$post[message] = stripslashes($post[message]);
				$post[message] = postify($post[message], $smileyoff, $bbcodeoff, $forums[allowsmilies], $forums[allowhtml], $forums[allowbbcode], $forums[allowimgcode]);
				eval("\$posts .= \"".template("post_reply_review_post")."\";");
				if($thisbg == $altbg2) {
					$thisbg = $altbg1;
				} else {
					$thisbg = $altbg2;
				}
			}
		}
		// Start Displaying the Post form
		if($forums[attachstatus] != "off") {
			eval("\$attachfile = \"".template("post_attachmentbox")."\";");
		}
		eval("\$postform = \"".template("post_reply")."\";");
		echo $postform;
	}
	if($replysubmit) {
		if($username != "") {
			if(!$xmbuser && !$xmbpw) {
				$password = md5($password);
			}
			$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$username'");
			$member = $db->fetch_array($query);

			if(!$member[username]) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
				exit;
			}

			$username = $member[username];

			if($password != $member[password]) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
				exit;
			}

			if($status == "Banned") {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_bannedmessage</span></center>";
				exit;
			}
		} else {
		$username = "Anonymous";
		}
		if($forums[guestposting] != "on" && $username == "Anonymous") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_textnoguestposting</span></center>";
			exit;
		}


		$pperm = explode("|", $forums[postperm]);

		if($pperm[1] == "2" && $status != "Administrator") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span></center>";
			exit;
		} elseif($pperm[1] == "3" && $status != "Administrator" && $status != "Moderator" && $status != "Super Moderator") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span></center>";
			exit;
		} elseif($pperm[1] == "4") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span></center>";
			exit;
		}


		$query = $db->query("SELECT lastpost FROM $table_forums WHERE fid='$fid'");
		$last = $db->result($query, 0);

		if($last != "") {
			$lastpost = explode("|", $last);
			$rightnow = time() - $floodctrl;

			if($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
				$floodlink = "<a href=\"viewthread.php?fid=$fid&tid=$tid\">Click here</a>";
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_floodprotect $floodlink $lang_tocont</span></center>";
				exit;
			}
		}
		$message = addslashes($message);

		if($usesig != "yes") {
			$usesig = "no";
		}

		$subject = str_replace("<", "&lt;", $subject);
		$subject = str_replace(">", "&gt;", $subject);
		$subject = addslashes($subject);

		if($attach != "none" && $attach != "" && $forums[attachstatus] != "off") {
			$attachedfile = addslashes(fread(fopen($attach, "r"), filesize($attach)));
			if($attach_size > 1000000) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_attachtoobig</span></center>";
				exit;
			}
		}

		$query = $db->query("SELECT closed,topped FROM $table_threads WHERE fid=$fid AND tid=$tid");
		$closed1 = $db->fetch_array($query);
		$closed = $closed1[closed];
		if($closed1[topped] == "2"){
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">No posting is allowed on announcements!</span></center>";
			exit;
		}
		if($closed == "yes" && $status != "Administrator" && $status != "Super Moderator" && $status != "Moderator") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_closedmsg</span></center>";
			exit;
		} else {
			$thatime = time();

			// Start Subsciptions
			$query = $db->query("SELECT dateline FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 1");
			$lp = $db->fetch_array($query);
			$threadurl = $boardurl;
			$threadurl .= "viewthread.php?tid=$tid";

			$subquery = $db->query("SELECT * FROM $table_favorites f, $table_members m WHERE f.type='subscription' AND f.tid='$tid' AND m.username=f.username AND f.username != '$username' AND m.lastvisit>'$lp[dateline]'");
			while($subs = $db->fetch_array($subquery)) {
				mail("$subs[email]", "$lang_textsubsubject $threadname", "$username $lang_textsubbody \n\n$threadurl $lang_textsubaol <a href=\"$threadurl\">$threadurl</a>", "From: $bbname");
			}
			// End Subscriptions
			// Auto subscribe options
			if($emailnotify == "yes") {
				$query = $db->query("SELECT tid FROM $table_favorites WHERE tid='$tid' AND username='$xmbuser' AND type='subscription'");
				$thread = $db->fetch_array($query);
				if(!$thread) {
					$db->query("INSERT INTO $table_favorites VALUES ('$tid', '$username', 'subscription')");
				}
			}
			$db->query("INSERT INTO $table_posts VALUES ('$fid', '$tid', '', '$username', '$message', '$subject', '$thatime', '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
			$pid = $db->insert_id();

			// Insert Attachment if there is on
			if($attach != "none" && $attach != "" && $forums[attachstatus] != "off") {


				$db->query("INSERT INTO $table_attachments VALUES ('', '$tid', '$pid', '$attach_name', '$attach_type', '$attach_size', '$attachedfile', '0')");
			}
			$db->query("UPDATE $table_threads SET lastpost='$thatime|$username', replies=replies+1 WHERE (tid='$tid' AND fid='$fid') OR closed='moved|$tid'");
			$db->query("UPDATE $table_forums SET lastpost='$thatime|$username', posts=posts+1 WHERE fid='$fid'");

			$db->query("UPDATE $table_members SET postnum=postnum+1 WHERE username='$username'");

		}

		if(!$xmbuser || !$xmbpw) {
			$currtime = time() + (86400*30);
			setcookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
			setcookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
		}
		eval("\$header = \"".template("header")."\";");
		echo $header;
		echo "<center><span class=\"mediumtxt \">$lang_replymsg</span></center>";
		?>
		<script>
		function redirect() {
		window.location.replace("viewthread.php?tid=<?=$tid?>&pid=<?=$pid?>#pid<?=$pid?>");
		}
		setTimeout("redirect();", 1250);
		</script>
		<?
	}
}

if($action == "edit") {
	if(!$editsubmit) {
		eval("\$header = \"".template("header")."\";");
		echo $header;
		$query = $db->query("SELECT * FROM $table_posts WHERE pid='$pid' AND tid='$tid' AND fid='$fid'");
		$postinfo = $db->fetch_array($query);

		if($postinfo[usesig] == "yes") {
			$checked = "checked=\"checked\"";
		}

		$postinfo[message] = stripslashes($postinfo[message]);

		if($postinfo[bbcodeoff] == "yes") {
			$offcheck1 = "checked=\"checked\"";
		}

		if($postinfo[smileyoff] == "yes") {
			$offcheck2 = "checked=\"checked\"";
		}

		if($postinfo[usesig] == "yes") {
			$offcheck3 = "checked=\"checked\"";
		}

		$postinfo[subject] = stripslashes($postinfo[subject]);
		$postinfo[subject] = str_replace('"', "&quot;", $postinfo[subject]);
		$postinfo[message] = htmlspecialchars($postinfo[message]);
		$icons = "";
		$listed_icons = 0;
		$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='picon'");
		while($smilie = $db->fetch_array($querysmilie)) {
			if($postinfo[icon] == $smilie[url]) {
				$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\"checked=\"checked\" /><img src=\"$smdir/$smilie[url]\" />";
			} else {
				$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" />";
			}
			$listed_icons += 1;
			if($listed_icons == 9) {
				$icons .= "<br />";
				$listed_icons = 0;
			}
		}
		if($previewpost) {
			$postinfo[message] = $message;
		}
		eval("\$edit = \"".template("post_edit")."\";");
		echo $edit;
	}

	if($editsubmit) {
		if(!$xmbuser && !$xmbpw) {
			$password = md5($password);
		}
		$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$username'");
		$member = $db->fetch_array($query);
		$status = $member[status];

		if(!$member[username]) {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
			exit;
		}

		$username = $member[username];

		if($password != $member[password]) {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
			exit;
		}

		if($status == "Banned") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_bannedmessage</span></center>";
			exit;
		}

		$date = gmdate($dateformat);
		if ($editedby == "on"){
			$message .= "\n\n[$lang_textediton $date $lang_textby $username]";
		}

		$subject = str_replace("<", "&lt;", $subject);
		$subject = str_replace(">", "&gt;", $subject);
		$subject = addslashes($subject);

		$status1 = modcheck($status, $username, $fid, $table_forums);
		if($status == "Super Moderator") {
			$status1 = "Moderator";
		}

		$query= $db->query("SELECT pid FROM $table_posts WHERE tid='$tid' ORDER BY dateline LIMIT 1");
		$isfirstpost = $db->fetch_array($query);
		if($isfirstpost[pid] == $pid) {
			$db->query("UPDATE $table_threads SET icon='$posticon', subject='$subject' WHERE tid='$tid'");
		}
		$query = $db->query("SELECT author FROM $table_posts WHERE pid='$pid' AND tid='$tid' AND fid='$fid'");
		$orig = $db->fetch_array($query);

		$message = addslashes($message);
		if($status == "Administrator" || $status1 == "Moderator" || $username == $orig[author]) {
			if($delete != "yes") {
				$db->query("UPDATE $table_posts SET message='$message', usesig='$usesig', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', icon='$posticon', subject='$subject' WHERE pid='$pid'");
			} elseif($delete == "yes" && !($isfirstpost[pid] == $pid)) {
				$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$orig[author]'");
				$db->query("DELETE FROM $table_attachments WHERE pid='$pid'");
                $db->query("DELETE FROM $table_posts WHERE pid='$pid'");
                updateforumcount($fid);
				updatethreadcount($tid);
			} elseif($delete == "yes" && $isfirstpost[pid] == $pid) {
				if($status == "Administrator" || $status == "Moderator" || $username == $orig[author]) {

					$query = $db->query("SELECT author FROM $table_posts WHERE tid='$tid'");
					while($result = $db->fetch_array($query)) {
						$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$result[author]'");
					}
					$db->query("DELETE FROM $table_threads WHERE tid='$tid'");
                    $db->query("DELETE FROM $table_attachments WHERE tid='$tid'");
                    $db->query("DELETE FROM $table_posts WHERE tid='$tid'");
					updateforumcount($fid);
				}
			}
		} else {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_noedit</span></center>";
			exit;
		}
		eval("\$header = \"".template("header")."\";");
		echo $header;
		echo "<center><span class=\"mediumtxt \">$lang_editpostmsg</span></center>";
		?>
		<script>
		function redirect()
		{
		window.location.replace("viewthread.php?tid=<?=$tid?>");
		}
		setTimeout("redirect();", 1250);
		</script>
		<?
	}
}



$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
