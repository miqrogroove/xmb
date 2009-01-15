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
if ($tempcache[footer] != "") { 
header("Location: faq.php"); 
exit; 
} 

if($page == "usermaint") {
$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textuserman";
} elseif($page == "using") {
$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textuseboa";
} elseif($page == "messages") {
$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textpostread";
} elseif($page == "misc") {
$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textmiscfaq";
} else {
$navigation = $lang_textfaq;
}

$navigation = "&raquo; $navigation";
if($faqstatus != "on") {
echo $lang_faqoff;
exit;
}
eval("\$header = \"".template("header")."\";");
echo $header;
if(!$page) {


eval("\$faq = \"".template("faq")."\";");
}

if($page == "usermaint") {


eval("\$faq = \"".template("faq_usermaint")."\";");
}

if($page == "using") {


eval("\$faq = \"".template("faq_using")."\";");
}

if($page == "messages") {
$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='smiley'") or die($db->error());
while($smilie = $db->fetch_array($querysmilie)) {
eval("\$smilierows .= \"".template("faq_messages_smilierow")."\";");
}


eval("\$faq = \"".template("faq_messages")."\";");
}

if($page == "misc") {
$query = $db->query("SELECT * FROM $table_ranks WHERE title!='Moderator' AND title!='Super Moderator' AND title!='Administrator' ORDER BY posts");
while($ranks = $db->fetch_array($query)) {
for($i = 0; $i < $ranks[stars]; $i++) {
$stars .= "<img src=\"$imgdir/star.gif\">";
}
eval("\$rankrows .= \"".template("faq_misc_rankrow")."\";");
$stars = "";
}


eval("\$faq = \"".template("faq_misc")."\";");
}

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);
echo $faq;
eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
