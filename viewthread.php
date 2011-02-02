<?php
/*

XMB 1.8 Partagium
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/

require "./header.php";

if(!$ppp || $ppp == '') {
	$ppp = $postperpage;
}


if($goto == "lastpost") {
	if($tid) {
		$query = $db->query("SELECT count(*) FROM $table_posts WHERE tid='$tid'");
		$posts = $db->result($query, 0);

	}elseif($fid) {
		$query = $db->query("SELECT tid FROM $table_posts WHERE fid='$fid' ORDER by dateline DESC LIMIT 0,1");
		$tid = $db->result($query, 0);

		$query = $db->query("SELECT count(*) FROM $table_posts WHERE tid='$tid'");
		$posts = $db->result($query, 0);
	}


	if($posts > $ppp) {
		$topicpages = $posts / $ppp;
		$topicpages = ceil($topicpages);
	}else{
		$topicpages=1;
	}

	header("Location: viewthread.php?tid=$tid&page=$topicpages#bottom");
	exit;
}

loadtemplates('header,footer,viewthread,viewthread_newtopic,viewthread_newpoll,viewthread_reply,forumdisplay_password,viewthread_poll_options_view,viewthread_poll_options,viewthread_poll_submitbutton,viewthread_poll,viewthread_post_email,viewthread_post_site,viewthread_post_repquote,viewthread_post_edit,viewthread_post_search,viewthread_post_profile,viewthread_post_u2u,viewthread_post_ip,viewthread_post_report,viewthread_post_attachment,viewthread_post,viewthread_invalid,viewthread_modoptions,viewthread_printable,viewthread_printable_row,viewthread_post_yahoo');


if(!strstr($oldtopics, "|$tid|")) {
        $oldtopics .= "|$tid| ";
        $expire = time() + 600;
        setcookie("oldtopics", $oldtopics, $expire, $cookiepath, $cookiedomain);
}


// Cache Smilies and Censored Words
smcwcache();

$query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
$thread = $db->fetch_array($query);
$fid = $thread['fid'];
if($thread['tid'] != $tid) {
	$notexist = $lang_textnothread;
}

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forum = $db->fetch_array($query);

if($forum['type'] != "forum" && $forum['type'] != "sub" && $forum['fid'] != $fid) {
	$notexist = $lang_textnoforum;
}


if($forum['type'] == "forum") {
	$navigation .= "&raquo; <a href=\"forumdisplay.php?fid=$fid\"> ".stripslashes($forum['name'])."</a> &raquo; ".stripslashes($thread['subject']);
} else {
	$query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forum[fup]'");
	$fup = $db->fetch_array($query);
	$navigation .= "&raquo; <a href=\"forumdisplay.php?fid=$fup[fid]\">".stripslashes($fup['name'])."</a> &raquo; <a href=\"forumdisplay.php?fid=$fid\">".stripslashes($forum['name'])."</a> &raquo; ".stripslashes($thread['subject']);
}

$authorization = privfcheck($forum['private'], $forum['userlist']);
if(!$authorization && $status != "Super Administrator") {

	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo "$lang_privforummsg";
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit;
}
if($forum[password] != $HTTP_COOKIE_VARS["fidpw$fid"] && $forum['password'] != "" && $status != "Super Administrator") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	$url = "forumdisplay.php?fid=$fid&action=pwverify";
	eval("\$pwform = \"".template("forumdisplay_password")."\";");
	echo $pwform;

	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit;
}

if(!$action) {
	eval("\$header = \"".template("header")."\";");
	echo $header;

	eval("\$newtopiclink = \"".template("viewthread_newtopic")."\";");

	if($forum[pollstatus] != "off") {
		eval("\$newpolllink = \"".template("viewthread_newpoll")."\";");
	}

	if($thread[closed] == "yes") {
		$replylink = "";
		$closeopen = "$lang_textopenthread";
	} else {
		$closeopen = "$lang_textclosethread";
		eval("\$replylink = \"".template("viewthread_reply")."\";");
	}

	if($thread[topped] == 1) {
		$topuntop = "$lang_textuntopthread";
	} else {
		$topuntop = "$lang_texttopthread";
        }

        if($page) {
                $start_limit = ($page-1) * $ppp;
        } else {
                $start_limit = 0;
                $page = 1;
        }
//Query for user ranks. We do this only once now.  -Aharon

        $queryranks = $db->query("SELECT id,title,posts,stars,allowavatars,avatarrank FROM $table_ranks ORDER BY posts DESC");
        while($query = $db->fetch_row($queryranks)) {
                $id = $query[0];
                $title = $query[1];
                $rposts = $query[2];
                $stars = $query[3];
                $allowavatars = $query[4];
                $avatarrank = $query[5];
                $ranktitle[$title] = "$id,$title,$posts,$stars,$allowavatars,$avatarrank";
                $rankposts[$rposts] = "$id,$title,$posts,$stars,$allowavatars,$avatarrank";
        }

        //End user rank query.

        $db->query("UPDATE $table_threads SET views=views+1 WHERE tid='$tid'");
        $query = $db->query("SELECT count(pid) FROM $table_posts WHERE fid='$fid' AND tid='$tid'");
        $num = $db->result($query, 0);

        $mpurl = "viewthread.php?tid=$tid";
        $multipage = multi($num, $ppp, $page, $mpurl);

// Start polls
if($thread[pollopts] != "" && $forum[pollstatus] != "off" && $thread[closed] != "yes") {
        $thread[pollopts] = str_replace("\n", "", $thread[pollopts]);
        $pollops = explode("#|#", $thread[pollopts]);

	if(strstr($thread[pollopts]." ", " ".$xmbuser." ") || $viewresults == "yes") {
                if(strstr($thread[pollopts]." ", " ".$xmbuser." ")) {
                        $results = "";
                }else{
                        $results = "<a href=\"./viewthread.php?tid=$tid\">$lang_backtovote</a>";
                }
                for($pnum = 0; $pnum < 10; $pnum++) {
                        if($pollops[$pnum] != "" && substr($pollops[$pnum],0,1)!=" ") {
                                $thispollnum = eregi_replace(".*\|\|~\|~\|\| ", "", $pollops[$pnum]);
		    $thispoll[0] = postify($thispoll[0], "no", "no", "yes", "no", "yes", "yes");
                                $totpollvotes += $thispollnum;
                        }
                }
                for($pnum = 0; $pnum < 10; $pnum++) {
                        if($pollops[$pnum] != "" && substr($pollops[$pnum],0,1)!=" ") {
                                $thispoll = explode("||~|~|| ", $pollops[$pnum]);

                                if($totpollvotes != 0) {
                                        $thisnum = $thispoll[1]*100/$totpollvotes;
                                } else {
                                        $thisnum = "0";
                                }

                                if($thisnum != "0") {
                                        $thisnum = round($thisnum, 2);
                                        $pollimgnum = round($thisnum)/3;
                                        for($num = 0; $num < $pollimgnum; $num++) {
                                                $pollbar .= "<img src=\"$imgdir/pollbar.gif\">";
                                        }
                                }

                                $thisnum .= "%";

                                if($thisnum == "0%") {
                                        $pollbar = "";
                                }


                                eval("\$pollhtml .= \"".template("viewthread_poll_options_view")."\";");
                                $pollbar = "";
                        }
                }
        } else {
	$results = "<a href=\"./viewthread.php?tid=$tid&viewresults=yes\">$lang_viewresults</a>";
                for($pnum = 0; $pnum < 10; $pnum++) {
                        if($pollops[$pnum] != "" && substr($pollops[$pnum],0,1)!=" ") {
                                $thispoll = explode("||~|~|| ", $pollops[$pnum]);
                                eval("\$pollhtml .= \"".template("viewthread_poll_options")."\";");
                        }
                }
        }

        if(strstr($thread[pollopts]." ", " ".$xmbuser." ")) {
                $buttoncode = "";
        } else {
                eval("\$buttoncode = \"".template("viewthread_poll_submitbutton")."\";");
        }
        eval("\$poll = \"".template("viewthread_poll")."\";");
}
// End Polls



	$thisbg = $altbg2;
	$querypost = $db->query("SELECT a.*, p.*, m.*,w.time FROM $table_posts p LEFT JOIN $table_members m ON m.username=p.author LEFT JOIN $table_attachments a ON a.pid=p.pid LEFT JOIN $table_whosonline w ON p.author=w.username WHERE p.fid='$fid' AND p.tid='$tid' ORDER BY dateline LIMIT $start_limit, $ppp");
	while($post = $db->fetch_array($querypost)) {
	$post[avatar] = eregi_replace("javascript:", "java script:", $post[avatar]);

		if($post[time] != "" && $post[author] != "xguest123"){
			$onlinenow = $lang_memberison;
		}else{
			$onlinenow = $lang_memberisoff;
		}
		$date = date("$dateformat", $post[dateline] + ($timeoffset * 3600));
                $time = date("$timecode", $post[dateline] + ($timeoffset * 3600));

                $poston = "$lang_textposton $date $lang_textat $time";

                if($post[icon] != "") {
                        $post[icon] = "<img src=\"$smdir/$post[icon]\" />";
                }

                if($post[author] != "Anonymous") {
                        if($post[showemail] == "yes") {
                                eval("\$email = \"".template("viewthread_post_email")."\";");
                        } else {
                                $email = "";
                        }
                        if($post[personstatus] != "" && $personstaton == "on") {
                                $personstatus = substr_replace($personstatus, ' ', 20, 0);
                                $personstatus = substr_replace($personstatus, ' ', 41, 0);
                                $personstatus = substr_replace($personstatus, ' ', 62, 0);
                                $personstatus = substr_replace($personstatus, ' ', 83, 0);
                                $personstatus .= "<br />";
                        } else {
                                $personstatus = "";
                        }
                        if($post[site] == "") {
                                $site = "";
                        } else {
                                $post[site] = str_replace("http://", "", $post[site]);
                                $post[site] = "http://$post[site]";
                                eval("\$site = \"".template("viewthread_post_site")."\";");
                        }


                        $encodename = urlencode($post[author]);
                        if($post[icq] == "") {
                        	$icq = "";
                        } else {
                        	eval("\$icq = \"".template("viewthread_post_icq")."\";");
                        }

                        if($post[aim] == "") {
                        	$aim = "";
                        } else {
				eval("\$aim = \"".template("viewthread_post_aim")."\";");
                        }

                        if($post[msn] == "") {
                        	$msn = "";
                        } else {
				eval("\$msn = \"".template("viewthread_post_msn")."\";");
                        }

                        if($post[yahoo] == "") {
				$yahoo = "";
			}else{
				eval("\$yahoo = \"".template("viewthread_post_yahoo")."\";");
                        }

                        eval("\$search = \"".template("viewthread_post_search")."\";");
                        eval("\$profile = \"".template("viewthread_post_profile")."\";");
                        eval("\$u2u = \"".template("viewthread_post_u2u")."\";");
                        $showtitle = $post[status];
                        if($post[status] == "Administrator" || $post[status] == "Super Administrator" || $post[status] == "Super Moderator" || $post[status] == "Moderator") {
                                $rankinfo = explode(",", $ranktitle["$post[status]"]);
                                $rank[allowavatars]=$rankinfo[4];
                                $rank[title]=$rankinfo[1];
                                $rank[stars]=$rankinfo[3];
                                $rank[avatarrank]=$rankinfo[5];
                        } else {
                                foreach($rankposts as $key => $v) {
                                        if ($post[postnum] >= $key) {
                                                $rankinfo = explode(",", $rankposts["$key"]);
                                                $rank[allowavatars]=$rankinfo[4];
                                                $rank[title]=$rankinfo[1];
                                                $rank[stars]=$rankinfo[3];
                                                $rank[avatarrank]=$rankinfo[5];
                                                break;
                                        }
                                }
                        }
                        $allowavatars = $rank[allowavatars];
                        $showtitle = $rank[title];
                        $stars = "";
                        for($i = 0; $i < $rank[stars]; $i++) {
                                $stars .= "<img src=\"$imgdir/star.gif\">";
                        }

                        if($rank[avatarrank] != "") {
                                $avarank = $rank[avatarrank];
                        }

                        if($post[status] == "Banned"){
                                $showtitle = "$lang_textbanned";
                                $stars = "";
                        }

                        if($post[customstatus] != "") {
                                $showtitle = $post[customstatus];
                                $showtitle .= "<br />";
                        } else {
                                $showtitle = $showtitle;
                                $showtitle .= "<br />";
                                $custitle = "";
                        }

                        $tharegdate = date("$dateformat", $post[regdate] + ($timeoffset * 3600));
                        $stars .= "<br />";

                        if($avastatus == "on" || $avastatus == "list") {
                                if($post[avatar] != "" && $allowavatars == "yes") {
                                    if(strstr($post[avatar], ",")) {
                                                $flashavatar = explode(",",$post[avatar]);
                                                $avatar = "<OBJECT classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0\" ID=main WIDTH=$flashavatar[1] HEIGHT=$flashavatar[2]>
                                                        <PARAM NAME=movie VALUE=\"$flashavatar[0]\">
                                                        <PARAM NAME=loop VALUE=false>
                                                        <PARAM NAME=menu VALUE=false>
                                                        <PARAM NAME=quality VALUE=best>
                                                        <EMBED src=\"$flashavatar[0]\" loop=false menu=false quality=best WIDTH=$flashavatar[1] HEIGHT=$flashavatar[2] TYPE=\"application/x-shockwave-flash\" PLUGINSPAGE=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\">
                                                        </EMBED>
                                                        </OBJECT>";
                                        }else{
                                                        $avatar = "<img src=\"$post[avatar]\">";
                                                }
                                }elseif($post[avatar] == "" && $avarank != ""){
                                                $avatar = "<img src=\"$avarank\">";
                                }else{
                                        $avatar = "";
                                }
                        }else{
                                $avatar = "";
                        }



                        if($status != "Administrator" && $status != "Super Administrator" && $status != "Moderator" && $status != "Super Moderator") {
                                $ip = "";
                        } else {
                                eval("\$ip = \"".template("viewthread_post_ip")."\";");
                        }
                        if($post[location] != "") {
                                $location = "<br>$lang_textlocation $post[location]";
                        } else {
                                $location = "";
                        }
                } else {
                        $post[author] = "$lang_textanonymous";
                        $showtitle = "$lang_textunregistered<br>";
                        $stars = "";
                        $avatar = "";
                        $post[postnum] = "N/A";
                        $tharegdate = "N/A";
                        $email = "";
                        $site = "";
                        $icq = "";
                        $msn="";
                        $aim = "";
                        $profile = "";
                        $search = "";
                        $u2u = "";
                        $location = "";
                }
                if($thread[closed] == "yes") {
                        $repquote = "";
                } else {
                        eval("\$repquote = \"".template("viewthread_post_repquote")."\";");
                }

                if($xmbuser != "" && $reportpost != "off") {
                        eval("\$reportlink = \"".template("viewthread_post_report")."\";");
                } else {
                        $reportlink = "";
                }
                if($post[subject] != ""){
                        $post[subject] = "$post[subject]<br /><br />";
                }

                eval("\$edit = \"".template("viewthread_post_edit")."\";");
                $bbcodeoff = $post[bbcodeoff];
                $smileyoff = $post[smileyoff];
                $post[message] = postify($post[message], $smileyoff, $bbcodeoff, $forum[allowsmilies], $forum[allowhtml], $forum[allowbbcode], $forum[allowimgcode]);

                // Deal with the attachment if there is one
                if($post[filename] != "" && $forum[attachstatus] != "off") {
                        $extention = strtolower(substr(strrchr($post[filename],"."),1));
                        if($attachimgpost == "on" && ($extention == "jpg" || $extention == "jpeg" || $extention == "jpe" || $extention == "gif" || $extention == "png" || $extention == "bmp")) {
                                eval("\$post[message] .= \"".template("viewthread_post_attachmentimage")."\";");
                        } else {
                                $attachsize = $post[filesize];
                                if($attachsize >= 1073741824){
                                	$attachsize = round($attachsize / 1073741824 * 100) / 100 . "gb";
                                }elseif($attachsize >= 1048576){
                                	$attachsize = round($attachsize / 1048576 * 100) / 100 . "mb";
                               	}elseif($attachsize >= 1024){
					$attachsize = round($attachsize / 1024 * 100) / 100 . "kb";
				}else{
					$attachsize = $attachsize . "b";
				}

                                $downloadcount = $post[downloads];
                                if($downloadcount == "") {
                                        $downloadcount = 0;
                                }
                                eval("\$post[message] .= \"".template("viewthread_post_attachment")."\";");
                        }
                }

                if($post[usesig] == "yes") {
			$post[sig] = CheckInput($post[sig], 'no', $sightml);
			$post[sig] = postify($post[sig], '', $sigbbcode, $forum[allowsmilies], $sightml, '', $forum[allowimgcode]);
			eval("\$post[message] .= \"".template("viewthread_post_sig")."\";");
                }


                if(!$notexist) {
                        eval("\$posts .= \"".template("viewthread_post")."\";");
                } else {
                        eval("\$posts = \"".template("viewthread_invalid")."\";");
                }

                if($thisbg == $altbg2) {
                        $thisbg = $altbg1;
                }
                else {
                        $thisbg = $altbg2;
                }

        }

        if($status == "Administrator" || $status == "Super Administrator" || $status == "Super Moderator" || $status == "Moderator") {
                eval("\$modoptions = \"".template("viewthread_modoptions")."\";");
        } else {
                $modoptions = "";
        }
        eval("\$viewthread = \"".template("viewthread")."\";");
        $viewthread = stripslashes($viewthread);
        echo $viewthread;

        end_time();

        eval("\$footer = \"".template("footer")."\";");
        $footer = stripslashes($footer);
        echo $footer;
}


if($action == "attachment" && $forum[attachstatus] != "off") {
  $query = $db->query("SELECT * FROM $table_attachments WHERE pid='$pid' and tid='$tid'");
  $file = $db->fetch_array($query);
  $db->query("UPDATE $table_attachments SET downloads=downloads+1 WHERE pid='$pid'");

  if($file[filesize] != strlen($file[attachment])){
	echo "file not the same!!";
  }

  $type = $file[filetype];
  $name = $file[filename];
  $size = $file[filesize];
  header("Content-type: $type");
  header("Content-length: $size");
  header("Content-Disposition: inline; filename=$name");
  header("Content-Description: PHP Generated Data");
  header("Pragma: no-cache");
  header("Expires: 0");
  echo $file[attachment];
}


if($action == "printable") {

        $querypost = $db->query("SELECT * FROM $table_posts WHERE fid='$fid' AND tid='$tid' ORDER BY dateline");
        while($post = $db->fetch_array($querypost)) {

                $date = date("$dateformat",$post[dateline] + ($timeoffset * 3600));
                $time = date("$timecode",$post[dateline] + ($timeoffset * 3600));
                $poston = "$date $lang_textat $time";
                $post[message] = stripslashes($post[message]);

                $bbcodeoff = $post[bbcodeoff];
                $smileyoff = $post[smileyoff];
                $post[message] = postify($post[message], $smileyoff, $bbcodeoff, $forum[allowsmilies], $forum[allowhtml], $forum[allowbbcode], $forum[allowimgcode]);

                eval("\$posts .= \"".template("viewthread_printable_row")."\";");
        }
        eval("\$printable = \"".template("viewthread_printable")."\";");
        $printable = stripslashes($printable);
        echo $printable;
}
?>
