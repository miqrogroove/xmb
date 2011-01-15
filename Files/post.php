<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

require './header.php';
loadtemplates('header,footer,post_notloggedin,post_loggedin,post_preview,post_attachmentbox,post_newthread,post_reply_review_toolong,post_reply_review_post,post_reply,post_edit,functions_smilieinsert,functions_smilieinsert_smilie,functions_bbcodeinsert,forumdisplay_password');

if(!$ppp || $ppp == '') {
	if($postperpage){
		$ppp = $postperpage;
	}else{
		$ppp = 30;
	}
}

if($tid) {
	$query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid' LIMIT 1");
	if($db->num_rows($query) == 1){
		$thread = $db->fetch_array($query);
		$threadname = $thread['subject'];
		$threadname = stripslashes($threadname);
		$fid = $thread['fid'];
	}else{
		eval("\$header = \"".template("header")."\";");
		echo $header;
		echo "<center><b>$lang_textnothread</b></center>";
	
		end_time();
		eval("\$footer = \"".template("footer")."\";");
		echo $footer;
		exit();		
	}
}


$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forums = $db->fetch_array($query);
$forums[name] = stripslashes($forums[name]);

if($forums[type] != "forum" && $forums[type] != "sub" && $forums[fid] != $fid) {
	$posterror = $lang_textnoforum;
}


if($forums[type] == "forum") {
	$postaction = "<a href=\"forumdisplay.php?fid=$fid\">".stripslashes($forums[name])."</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &raquo; ";
} else {
	$query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'");
	$fup = $db->fetch_array($query);
	$postaction = "<a href=\"forumdisplay.php?fid=$fup[fid]\">".stripslashes($fup[name])."</a> &raquo; <a href=\"forumdisplay.php?fid=$fid\">".stripslashes($forums[name])."</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &raquo; ";
}

if($action != "edit" && $tid) {
	$postaction .= "$lang_textpostreply";
}
elseif($action == "edit") {
	$postaction .= "$lang_texteditpost";
}

if($action != "edit" && !$tid) {
	if($forums[type] == "forum") {
		$postaction = "<a href=\"forumdisplay.php?fid=$fid\">".stripslashes($forums[name]) ."</a> &raquo; ";
	} else {
		$query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'");
		$fup = $db->fetch_array($query);
		$postaction = "<a href=\"forumdisplay.php?fid=$fup[fid]\">".stripslashes($fup[name])."</a> &raquo; <a href=\"forumdisplay.php?fid=$fid\">".stripslashes($forums[name])."</a> &raquo; ";
	}
}

if($action != "edit" && !$tid) {
	$postaction .= "$lang_textpostnew";
}

// Get bb code and smilie inserters ready
smcwcache();
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

$query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser'");
$member = $db->fetch_array($query);
if ($member[ban] == "posts" || $member[ban] == "both") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><b>$lang_textbanfrompost</b></center>";

	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}

if($status == "Banned") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><span class=\"mediumtxt \">$lang_bannedmessage</span></center>";
}

$listed_icons = 0;

if($status != "Administrator" && $status != "Super Administrator" && $status != "Super Moderator" && $status != "Moderator"){
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
	if($sig != "") {
		$usesigcheck = "checked";
	}
}
if(!$xmbuser && $forums[guestposting] == "on") {
	$guestpostingmsg = $lang_guestpostingonmsg;
}

if(($forums[private] == "2" || $subf[private] == "2") && $status != "Administrator" && $status != "Super Administrator") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	
	echo "<center><span class=\"mediumtxt \">$lang_privforummsg</span></center>";
	
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
} elseif(($forums[private] == "3" || $subf[private] == "3") && $status != "Administrator" && $status != "Super Administrator" && $status != "Moderator" && $status != "Super Moderator") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><span class=\"mediumtxt \">$lang_privforummsg</span></center>";
	
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
} elseif(($forums[private] == "4" || $subf[private] == "4")&&(!privfcheck($forums[private], $forums[userlist]))){
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><span class=\"mediumtxt \">$lang_privforummsg</span></center>";
	
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}

if($posterror) {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><span class=\"mediumtxt \">$posterror</span></center>";
	
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}
// Start forum password check
if($forums[password] != ${'fidpw'.$fid} && $forums[password] != "" && $status != 'Super Administrator') {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	eval("\$pwform = \"".template("forumdisplay_password")."\";");
	echo $pwform;
	
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forum = $db->fetch_array($query);
$authorization = privfcheck($forum[private], $forum[userlist]);
if(!$authorization) {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><span class=\"mediumtxt \">$lang_privforummsg</span></center>";

	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}

eval('$bbcodescript = "'.template('functions_bbcode').'";');
if($previewpost) {
	$currtime = time();
	$date = gmdate("n/j/y",$currtime + ($timeoffset * 3600) + ($addtime * 3600));
	$time = gmdate("H:i",$currtime + ($timeoffset * 3600) + ($addtime * 3600));
	$poston = "$lang_textposton $date $lang_textat $time";

	if($posticon != '') {
		$thread[icon] = "<img src=\"$smdir/$posticon\" />";
	}

	$subject = checkInput(stripslashes($subject));
	$message = checkInput(stripslashes($message));
	
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
	$preview = stripslashes($preview);
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

		if($status == "Administrator" || $status1 == "Moderator" || $status == "Super Administrator") {
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
// Banned User Post Fix
			$query = $db->query("SELECT username, password, status, ban FROM $table_members WHERE username='$username'");
			$member = $db->fetch_array($query);
			$status = $member[status];
			$ban = $member[ban];
// End


			if(!$member[username]) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
	
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}

			$username = $member[username];

			if($password != $member[password]) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
	
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}

			if($status == "Banned") {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_bannedmessage</span></center>";
	
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}
		} else {
			$username = "Anonymous";
		}
		if($forums[guestposting] != "on" && $username == "Anonymous") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_textnoguestposting</span></center>";
			
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}
		if(trim($subject) == "") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_textnosubject</span></center>";
			
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}

		$pperm = explode("|", $forums[postperm]);

		if($pperm[0] == "2" && $status != "Administrator" && $status != "Super Administrator") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		} elseif($pperm[0] == "3" && $status != "Administrator" && $status != "Super Administrator" && $status != "Moderator" && $status != "Super Moderator") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span></center>";
			
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		} elseif($pperm[0] == "4") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span><center>";
			
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
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
	
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}
		}

		$message = addslashes($message);
		$subject = addslashes($subject);
		
		if($usesig != "yes") {
			$usesig = "no";
		}

		if($forums[pollstatus] != "off") {
			$pollanswers = checkInput($pollanswers);
			$pollops = explode("\n", $pollanswers);
			$pollanswers = "";
			$pnumnum = count($pollops);
			for($pnum = 0; $pnum < $pnumnum; $pnum++) {
				if($pollops[$pnum] != "") {
					$pollanswers .= "$pollops[$pnum]||~|~|| 0#|#";
				}
			}

			$pollanswers = str_replace("\n", '', $pollanswers);
		}

                // Check PostIcon validity... 
		if ($posticon != "") 
	        { 
      		$sql = "SELECT id FROM ".$table_smilies." WHERE type='picon' AND url='".$posticon."'"; 
                	$query = $db->query($sql); 
                	if (!($db->result($query, 0, "id"))) 
                	{ 
	        	   die(); 
    	        	}     
                } 

		// End PostIcon validity

		
		if($posticon != "") {
			$query = $db->query("SELECT id FROM $table_smilies WHERE type='picon' AND url='$posticon'"); 
			
			if(!$db->result($query, 0)) {
				exit();
			}
		}
		
		$thatime = time();
		$subject = checkInput($subject);
		
		$db->query("INSERT INTO $table_threads VALUES ('', '$fid', '$subject', '$posticon', '$thatime|$username', '0', '0', '$username', '', '', '$pollanswers')");
		$tid = $db->insert_id();
		
		$message = checkInput($message);
		
		$db->query("INSERT INTO $table_posts VALUES ('$fid', '$tid', '', '$username', '$message', '$subject', '$thatime', '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
		$pid = $db->insert_id();
		
		// Insert Attachment if there is one
		$attach = $_FILES['attach'];
		if($attach['name'] != "none" && !empty($attach['name']) && $forums[attachstatus] != "off" && is_uploaded_file($attach['tmp_name'])) {
			$attachedfile = addslashes(fread(fopen($attach['tmp_name'], 'r'), filesize($attach['tmp_name'])));
					
			// Max attach size = 0.95MB
			if($attach['size'] > 1000000) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_attachtoobig</span></center>";
			
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}
			
			$ext = array('bz2', 'gz', 'zip', 'bz', 'tar', 'exe');
			$extention = substr(strrchr($attach['name'],"."),1);
			foreach($ext as $name => $var) {
				if($var == $extension){
					$attachedfile = base64_decode($attachedfile);
				}	
			}
						
			$db->query("INSERT INTO $table_attachments VALUES ('', '$tid', '$pid', '$attach[name]', '$attach[type]', '$attach[size]', '$attachedfile', '0')");
		}
		
		// Check if forum is subforum, if so, make lastpost on fup-forum
		if($forum[type] == 'sub'){
			$db->query("UPDATE $table_forums SET lastpost='$thatime|$username', threads=threads+1, posts=posts+1 WHERE fid='$for[fup]'");
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


		$db->query("UPDATE $table_members SET postnum=postnum+1 WHERE username like '$username'");

		if(($status == "Administrator" || $status == "Super Administrator" || $status == "Super Moderator" || $status == "Moderator") && $toptopic == "yes") {
			$db->query("UPDATE $table_threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
		}
		if(($status == "Administrator" || $status == "Super Administrator") && $Announcement == "yes") {
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
		
		
		$query = $db->query("SELECT count(*) FROM $table_posts WHERE tid='$tid'"); 
		$posts = $db->result($query, 0);
					
		if($posts > $ppp) {
			$topicpages = $posts / $ppp; 
			$topicpages = ceil($topicpages);
		}else{
			$topicpages=1;
		}
		?>
		<script>
		function redirect() {
		window.location.replace("viewthread.php?tid=<?=$tid?>&page=<?=$topicpages?>#pid<?=$pid?>");
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
			$message = censor($thaquote[message]);

			$authorization = privfcheck($thaquote[fprivate], $thaquote[fuserlist]);
			if(!$authorization) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_privforummsg</span></center>";
	
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}

			$message = "[quote][i]$lang_origpostedby $thaquote[author][/i]\n$message [/quote]";
			$message = stripslashes($message);
		}
		// Start Topic/Thread Review
		$querytop = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid='$tid'");
		$replynum = $db->result($querytop, 0);
		
		if($replynum >= $ppp) {
			$threadlink = "viewthread.php?fid=$fid&tid=$tid";
			eval($lang_evaltrevlt);
			eval("\$posts .= \"".template("post_reply_review_toolong")."\";");
		}else{
			$thisbg = $altbg1;
			$query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC");
			while($post = $db->fetch_array($query)) {
				$date = gmdate($dateformat, $post[dateline] + ($timeoffset * 3600) + ($addtime * 3600));
				$time = gmdate($timecode, $post[dateline] + ($timeoffset * 3600) + ($addtime * 3600));

				$poston = "$lang_textposton $date $lang_textat $time";
				if($post[icon] != "") {
					$post[icon] = "<img src=\"$smdir/$post[icon]\" alt=\"Icon depicting mood of post\" />";
				}

				$post[message] = stripslashes($post[message]);
				$post[message] = postify($post[message], $post[smileyoff], $post[bbcodeoff], $forums[allowsmilies], $forums[allowhtml], $forums[allowbbcode], $forums[allowimgcode]);
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
		echo stripslashes($postform);
	}
	if($replysubmit) {
		if(!$subject && !$message){
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postnothing</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}
		if($username != "") {
			if(!$xmbuser && !$xmbpw) {
				$password = md5($password);
			}
//Banned User Post Fix
			$query = $db->query("SELECT username, password, status, ban FROM $table_members WHERE username='$username'");
			$member = $db->fetch_array($query);
			$status = $member[status];
			$ban = $member[ban];

			if(!$member[username]) {

// End


				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
	
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}

			$username = $member[username];

			if($password != $member[password]) {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
		
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}


			if($status == "Banned") {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_bannedmessage</span></center>";
	
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}
//Banned User Post Fix

if ($ban == "posts" || $ban == "both") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><b>$lang_textbanfrompost</b></center>";

	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit;
}
// End
		} else {
		$username = "Anonymous";
		}
		if($forums[guestposting] != "on" && $username == "Anonymous") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_textnoguestposting</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}


		$pperm = explode("|", $forums[postperm]);

		if($pperm[1] == "2" && $status != "Administrator" && $status != "Super Administrator") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		} elseif($pperm[1] == "3" && $status != "Administrator" && $status != "Super Administrator" && $status != "Moderator" && $status != "Super Moderator") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		} elseif($pperm[1] == "4") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_postpermerr</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}
		
		if($posticon != "") {
			$query = $db->query("SELECT id FROM $table_smilies WHERE type='picon' AND url='$posticon'"); 
			
			if(!$db->result($query, 0)) {
				exit();
			}
		}
		
		$query = $db->query("SELECT lastpost, type, fup FROM $table_forums WHERE fid='$fid'");
		$for = $db->fetch_array($query);
		$last = $for['lastpost'];

		if($last != "") {
			$lastpost = explode("|", $last);
			$rightnow = time() - $floodctrl;

			if($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
				$floodlink = "<a href=\"viewthread.php?fid=$fid&tid=$tid\">Click here</a>";
				eval("\$header = \"".template("header")."\";");
				echo $header;
				echo "<center><span class=\"mediumtxt \">$lang_floodprotect $floodlink $lang_tocont</span></center>";
	
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}
		}
		$message = addslashes($message);

		if($usesig != "yes") {
			$usesig = "no";
		}

		$subject = str_replace("<", "&lt;", $subject);
		$subject = str_replace(">", "&raquo;", $subject);
		$subject = addslashes($subject);

		$query = $db->query("SELECT closed,topped FROM $table_threads WHERE fid=$fid AND tid=$tid");
		$closed1 = $db->fetch_array($query);
		$closed = $closed1[closed];
		if($closed1[topped] == "2"){
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">No posting is allowed on announcements!</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}
		if($closed == "yes" && $status != "Administrator" && $status != "Super Administrator" && $status != "Super Moderator" && $status != "Moderator") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_closedmsg</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		} else {
			$thatime = time();
			$subject = checkInput($subject);
			$message = checkInput($message);

			// Start Subsciptions
			
			$query = $db->query("SELECT dateline FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 1");
			$lp = $db->fetch_array($query);
			$threadurl = $boardurl;
			$threadurl .= "viewthread.php?tid=$tid";

			$subquery = $db->query("SELECT * FROM $table_favorites f, $table_members m WHERE f.type='subscription' AND f.tid='$tid' AND m.username=f.username AND f.username != '$xmbuser'");
			while($subs = $db->fetch_array($subquery)) {
				mail("$subs[email]", "$lang_textsubsubject $threadname", "$username $lang_textsubbody \n$threadurl", "From: $bbname <$adminemail>");
			}
			
			// End Subscriptions
			// Auto subscribe options
			if($emailnotify == "yes") {
				$query = $db->query("SELECT tid FROM $table_favorites WHERE tid='$tid' AND username='$xmbuser' AND type='subscription'");
				if($db->num_rows($query) < 1) {
					$db->query("INSERT INTO $table_favorites VALUES ('$tid', '$username', 'subscription')");
				}
			}
			
			$db->query("INSERT INTO $table_posts VALUES ('$fid', '$tid', '', '$username', '$message', '$subject', '$thatime', '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
			$pid = $db->insert_id();

			// Insert Attachment if there is on
			$attach = $_FILES['attach'];
			if($attach['name'] != "none" && !empty($attach['name']) && $forums[attachstatus] != "off" && is_uploaded_file($attach['tmp_name'])) {
				$attachedfile = addslashes(fread(fopen($attach['tmp_name'], 'r'), filesize($attach['tmp_name'])));
							
				// Max attach size = 0.95MB
				if($attach['size'] > 1000000) {
					eval("\$header = \"".template("header")."\";");
					echo $header;
					echo "<center><span class=\"mediumtxt \">$lang_attachtoobig</span></center>";
				
					end_time();
					eval("\$footer = \"".template("footer")."\";");
					echo $footer;
					exit();
				}
				
				$ext = array('bz2', 'gz', 'zip', 'bz', 'tar', 'exe');
				$extention = substr(strrchr($attach['name'],"."),1);
				foreach($ext as $name => $var) {
					if($var == $extension){
						$attachedfile = base64_decode($attachedfile);
					}	
				}
							
				$db->query("INSERT INTO $table_attachments VALUES ('', '$tid', '$pid', '$attach[name]', '$attach[type]', '$attach[size]', '$attachedfile', '0')");
			}
			
			$db->query("UPDATE $table_threads SET lastpost='$thatime|$username', replies=replies+1 WHERE (tid='$tid' AND fid='$fid') OR closed='moved|$tid'");
			
			if($for[type] == 'sub'){
				$db->query("UPDATE $table_forums SET lastpost='$thatime|$username', posts=posts+1 WHERE fid='$for[fup]'");
			}
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
		
		$query = $db->query("SELECT count(*) FROM $table_posts WHERE tid='$tid'"); 
		$posts = $db->result($query, 0);
							
		if($posts > $ppp) {
			$topicpages = $posts / $ppp; 
			$topicpages = ceil($topicpages);
		}else{
			$topicpages=1;
		}
		
		?>
		<script>
			function redirect() {
			window.location.replace("viewthread.php?tid=<?=$tid?>&page=<?=$topicpages?>#pid<?=$pid?>");
			}
			setTimeout("redirect();", 1250);
		</script>
		<?php
	}
}

if($action == "edit") {
	if(!$editsubmit) {
		eval("\$header = \"".template("header")."\";");
		echo $header;
		$queryextra = $db->query("SELECT f.* FROM $table_forums f LEFT JOIN $table_posts p ON (f.fid = p.fid) WHERE p.tid='$tid' AND p.pid='$pid'");
		$forum = $db->fetch_array($queryextra);

		$authorization = privfcheck($forum[private], $forum[userlist]);
		if(!$authorization) {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "$lang_privforummsg";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}

		if($forum[password] != ${"fidpw$fid"} && $forum[password] != "" && $status != 'Super Administrator') {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			$url = "viewthread.php?tid=$tid&action=pwverify";
			eval("\$pwform = \"".template("forumdisplay_password")."\";");
			echo $pwform;
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}
		
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
		
		if($status != 'Super Administrator'){
			$postinfo[subject] = censor($postinfo[subject]);
		}
		
		$icons = "";
		$listed_icons = 0;
		
		if(!($status == "Administrator" || $status == "Super Administrator" || $status == "Super Moderator" || $status == "Moderator")){
			$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='picon' AND (url NOT LIKE '%rsvd%')");
			while($smilie = $db->fetch_array($querysmilie)) {
		                $posticoncheck = "";
		                if($posticon == $smilie[url]) {
					$posticoncheck = "checked=\"checked\"";
				}
				$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" $posticoncheck/><img src=\"$smdir/$smilie[url]\" />";
				$listed_icons++;
				if($listed_icons == 9) {
					$icons .= "<br />";
					$listed_icons = 0;
				}
			}
		} else {
			$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='picon'");
			while($smilie = $db->fetch_array($querysmilie)) {
		                $posticoncheck = "";
		                if($posticon == $smilie[url]) {
		                        $posticoncheck = "checked=\"checked\"";
		                }
				$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" $posticoncheck/><img src=\"$smdir/$smilie[url]\" />";
				$listed_icons += 1;
				if($listed_icons == 9) {
					$icons .= "<br />";
					$listed_icons = 0;
				}
			}
		}
		
		if($previewpost) {
			$postinfo[message] = $message;
			$message = censor($message);
		}
		eval("\$edit = \"".template("post_edit")."\";");
		echo $edit;
	}else{
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
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}

		$username = $member[username];

		if($password != $member[password]) {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}

		if($status == "Banned") {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_bannedmessage</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}
//Banned User Post Fix

if ($ban == "posts" || $ban == "both") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "<center><b>$lang_textbanfrompost</b></center>";

	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit;
}
// End


		$date = gmdate($dateformat);
		if ($editedby == "on"){
			$message .= "\n\n[$lang_textediton $date $lang_textby $username]";
		}

		$subject = addslashes($subject);

		
		$status1 = modcheck($status, $username, $fid, $table_forums);
		if($status == "Super Moderator") {
			$status1 = "Moderator";
		}
		
		if($posticon != "") {
			$query = $db->query("SELECT id FROM $table_smilies WHERE type='picon' AND url='$posticon'"); 
			
			if(!$db->result($query, 0)) {
				exit();
			}
		}
		
		$query = $db->query("SELECT pid FROM $table_posts WHERE tid='$tid' ORDER BY dateline LIMIT 1");
		$isfirstpost = $db->fetch_array($query);
		
		if(trim($subject) == '' && $pid == $isfirstpost['pid']){
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_textnosubject</span></center>";
					
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}
		
		$subject = checkOutput($subject, 'on');
	  	$subject = checkInput($subject);
	  	$message = checkOutput($message, 'on');
	  	$message = checkInput($message);
	  	$posticon = htmlspecialchars($posticon);

		$query = $db->query("SELECT author FROM $table_posts WHERE pid='$pid' AND tid='$tid' AND fid='$fid'");
		$orig = $db->fetch_array($query);

		$message = addslashes($message);
		if($status == "Administrator" || $status == "Super Administrator"|| $status1 == "Moderator" || $username == $orig[author]) {
			if($isfirstpost[pid] == $pid) {
				$db->query("UPDATE $table_threads SET icon='$posticon', subject='$subject' WHERE tid='$tid'");
			}
			if($delete != "yes") {
				$db->query("UPDATE $table_posts SET message='$message', usesig='$usesig', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', icon='$posticon', subject='$subject' WHERE pid='$pid'");
			} elseif($delete == "yes" && !($isfirstpost[pid] == $pid)) {
				$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$orig[author]'");
				$db->query("DELETE FROM $table_attachments WHERE pid='$pid'");
				$db->query("DELETE FROM $table_posts WHERE pid='$pid'");
				updateforumcount($fid);
				updatethreadcount($tid);
				
			} elseif($delete == "yes" && $isfirstpost[pid] == $pid) {
				if($status == "Administrator" || $status == "Super Administrator" || $status == "Moderator" || $username == $orig[author]) {

					$query = $db->query("SELECT author FROM $table_posts WHERE tid='$tid'");
					while($result = $db->fetch_array($query)) {
						$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$result[author]'");
					}
					$db->query("DELETE FROM $table_threads WHERE tid='$tid'");
					$threaddelete = 'yes';
		    			$db->query("DELETE FROM $table_attachments WHERE tid='$tid'");
		    			$db->query("DELETE FROM $table_posts WHERE tid='$tid'");
					updateforumcount($fid);
					updatethreadcount($tid);
				}
			}
		} else {
			eval("\$header = \"".template("header")."\";");
			echo $header;
			echo "<center><span class=\"mediumtxt \">$lang_noedit</span></center>";
	
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}
		eval("\$header = \"".template("header")."\";");
		echo $header;
		echo "<center><span class=\"mediumtxt \">$lang_editpostmsg</span></center>";
		
		if($threaddelete != 'yes'){
			$query =$db->query("SELECT COUNT(*) FROM $table_posts WHERE pid<=$pid AND tid='$tid' AND fid='$fid'");
			$post = $db->result($query,0);
			$postsnum = $post; 
		
			if($postsnum > $ppp) { 
				$posts = $postsnum; 
				$topicpages = $posts / $ppp; 
				$topicpages = ceil($topicpages); 
			} 
	
			?>
			<script>
			function redirect()
			{
			window.location.replace("viewthread.php?tid=<?=$tid?>&page=<?=$topicpages?>#pid<?=$pid?>");
			}
			setTimeout("redirect();", 1250);
			</script>
			<?
		}else{
			?>
			<script>
			function redirect()
			{
			window.location.replace("forumdisplay.php?fid=<?=$fid?>");
			}
			setTimeout("redirect();", 1250);
			</script>
			<?
		}
	}
}

end_time();

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>