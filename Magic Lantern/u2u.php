<?
/*

XMB 1.6 v2b Magic Lantern Final
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./header.php";
require "./xmb.php";
loadtemplates('u2u_header,u2u_footer,u2u_row,u2u,u2u_send,u2u_ignore,u2u_view_refwdlinks,u2u_view,u2u_message');
eval("\$u2uheader = \"".template("u2u_header")."\";");
eval("\$u2ufooter = \"".template("u2u_footer")."\";");

if($xmbuser == "") {
  u2umsg("$lang_u2unotloggedin");
}

// Cache Smilies and Censored Words
smcwcache();

if(!$action || $action == "") {
  if(!$folder) {
    $folder = "inbox";
    $query = $db->query("SELECT * FROM $table_u2u WHERE msgto='$xmbuser' AND folder='$folder' ORDER BY dateline DESC");
  } else {
    $lang_textu2uinbox = $lang_textu2uoutbox;
    $lang_textfrom = $lang_textto;
    $query = $db->query("SELECT * FROM $table_u2u WHERE msgfrom='$xmbuser' AND folder='$folder' ORDER BY dateline DESC");
  }

  while($message = $db->fetch_array($query)) {
    $postdate = gmdate("$dateformat",$message[dateline] + ($timeoffset * 3600));
    $posttime = gmdate("$timecode",$message[dateline] + ($timeoffset * 3600));

    $senton = "$postdate $lang_textat $posttime";
    $message_subject = stripslashes($message[subject]);

    if($message[subject] == "") {
      $message[subject] = "&lt;$lang_textnosub&gt;";
    }
    if ($message[readstatus] == "yes") {
    	$read = "$lang_textread";
    } else {
    	$read = "$lang_textunread";
    }
    if ($folder=="outbox") {
      $message[msgfrom]=$message[msgto];
    }
    eval("\$messages .= \"".template("u2u_row")."\";");
  }
  eval("\$u2u = \"".template("u2u")."\";");
  echo $u2u;
}

if($action == "send") {
  $query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser'");
  $member = $db->fetch_array($query);
  if ($member[ban] == "u2u" || $member[ban] == "both") {
   eval("\$u2uheader = \"".template("u2u_header")."\";");
   echo $u2uheader;
   echo "<center><b>$lang_textbanfromu2u</b></center>";
   eval("\$u2ufooter = \"".template("u2u_footer")."\";");
   echo $u2ufooter;
   exit;
  }
  $query = $db->query("SELECT count(u2uid) FROM $table_u2u WHERE msgto='$xmbuser' AND (folder='inbox' OR folder='outbox')");
  $u2unum = $db->result($query, 0);
  if($u2unum >= $u2uquota) {
    u2umsg($lang_u2ureachedquota);
  } else {
    if(!$u2usubmit) {
      $touser = $username;
      if($u2uid) {
        $query = $db->query("SELECT * FROM $table_u2u WHERE u2uid='$u2uid' AND msgto='$xmbuser'");
        $u2u = $db->fetch_array($query);

        $u2u[subject] = $message = str_replace("$lang_textre ","",$u2u[subject]);
        $u2u[subject] = $message = str_replace("$lang_textfwd ","",$u2u[subject]);

        $u2u[message] = stripslashes($u2u[message]);
        if($do == "reply") {
          $subject = "$lang_textre $u2u[subject]";
          $message = "[quote]$u2u[message][/quote]";
          $touser = "$u2u[msgfrom]";
        }
        if($do == "forward") {
          $subject = "$lang_textfwd $u2u[subject]";
          $message = "[quote]$u2u[message][/quote]";
          $touser = "$u2u[msgfrom]";
        }
      }
      eval("\$u2usend = \"".template("u2u_send")."\";");
      echo $u2usend;
    }

    if($u2usubmit) {

      $query = $db->query("SELECT username FROM $table_members WHERE username='$msgto'");
      $member = $db->fetch_array($query);
      if(!$member[username]) {
        u2umsg($lang_badrcpt);
      }

      $msgto = $member[username];

      $query = $db->query("SELECT username, password FROM $table_members WHERE username='$username'");
      $member = $db->fetch_array($query);

      if(!$member[username]) {
        u2umsg($lang_badname);
      }

      $username = $member[username];

      if($password != $member[password]) {
        u2umsg($lang_textpwincorrect);
        exit;
      }

      $query = $db->query("SELECT ignoreu2u FROM $table_members WHERE username='$msgto'");
      $list = $db->fetch_array($query);

      if(eregi($username."(,|$)", $list[ignoreu2u])) {
        u2umsg($lang_u2ublocked);
        exit;
      }

      $subject = str_replace("<","&lt;", $subject);
      $subject = str_replace(">","&gt;", $subject);
      $subject = addslashes($subject);
      $message = str_replace("<","&lt;", $message);
      $message = str_replace(">","&gt;", $message);
      $message = addslashes($message);

      $db->query("INSERT INTO $table_u2u VALUES('', '$msgto', '$username', '" . time() . "', '$subject', '$message', 'inbox', 'yes', 'no')");
      $db->query("INSERT INTO $table_u2u VALUES('', '$msgto', '$username', '" . time() . "', '$subject', '$message', 'outbox', 'no', 'no')");

      u2umsg($lang_imsentmsg, "u2u.php");
    }
  }
}

if($action == "delete") {
  if($folder=="outbox") {
    $msg_field="msgfrom";
  } else {
    $msg_field="msgto";
  }
  if(!$u2uid) {
    $query = $db->query("SELECT * FROM $table_u2u WHERE ".$msg_field."='$xmbuser' AND folder='$folder' ORDER BY dateline DESC");
    while($u2u = $db->fetch_array($query)) {
      $delete = "delete$u2u[u2uid]";
      $delete = "${$delete}";
      $db->query("DELETE FROM $table_u2u WHERE ".$msg_field."='$xmbuser' AND u2uid='$delete'");
    }
  } else {
    $db->query("DELETE FROM $table_u2u WHERE ".$msg_field."='$xmbuser' AND u2uid='$u2uid'");
  }
  if($folder=="outbox") {
    u2umsg($lang_imdeletedmsg, "u2u.php?folder=outbox");
  } else {
    u2umsg($lang_imdeletedmsg, "u2u.php");
  }
}

if($action == "ignore") {
  $query = $db->query("SELECT ignoreu2u FROM $table_members WHERE username='$xmbuser'");
  $mem = $db->fetch_array($query);
  eval("\$u2uignore = \"".template("u2u_ignore")."\";");
  echo $u2uignore;
}

if($action == "ignoresubmit") {
  $db->query("UPDATE $table_members SET ignoreu2u='$ignorelist' WHERE username='$xmbuser'");
  echo "<span class=\"mediumtxt \">$lang_ignoreupdate</span>";
  u2umsg($lang_ignoreupdate);
}

if($action == "view") {
  $query = $db->query("SELECT * FROM $table_u2u WHERE u2uid='$u2uid'");
  $u2u = $db->fetch_array($query);
  $db->query("UPDATE $table_u2u SET readstatus='yes', new='no' WHERE u2uid=$u2u[u2uid]");
  $db->query("UPDATE $table_u2u SET readstatus='yes' WHERE u2uid=$u2u[u2uid]+1");
  if(($u2u[msgfrom] == $xmbuser) || ($u2u[msgto] == $xmbuser)) {
    $u2u[message] = stripslashes($u2u[message]);
    $u2u[subject] = stripslashes($u2u[subject]);
    $u2udate = gmdate("$dateformat",$u2u[dateline] + ($timeoffset * 3600));
    $u2utime = gmdate("$timecode",$u2u[dateline] + ($timeoffset * 3600));
    $dateline = "$u2udate $lang_textat $u2utime";
    $u2u[subject] = "$lang_textsubject $u2u[subject]";
    $u2u[message] = postify($u2u[message], "no", "");
    if($u2u[msgfrom] != $xmbuser) {
      eval("\$refwdlinks = \"".template("u2u_view_refwdlinks")."\";");
    }
    eval("\$view = \"".template("u2u_view")."\";");
    echo $view;
  }
}

function u2umsg($message, $redirect="") {
  global $bordercolor, $tablewidth, $borderwidth, $tablespace, $altbg1, $css, $bbname, $lang_textpowered, $u2uheader, $u2ufooter;
  if($redirect != "") {
    $redirectjs = "<script> function redirect() { window.location.replace(\"$redirect\"); } setTimeout(\"redirect();\", 1250); </script>";
  }
  eval("\$msg = \"".template("u2u_message")."\";");
  echo $msg;
  exit;
}
?>
