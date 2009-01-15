<?
require "./header.php";

if($page == "usermaint") {
$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &gt; $lang_textuserman";
} elseif($page == "using") {
$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &gt; $lang_textuseboa";
} elseif($page == "messages") {
$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &gt; $lang_textpostread";
} elseif($page == "misc") {
$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &gt; $lang_textmiscfaq";
} else {
$navigation = $lang_textfaq;
}

$navigation = "&gt; $navigation";
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
$query = $db->query("SELECT * FROM $table_ranks ORDER BY posts AND (title != 'Administrator' OR title != 'Super Moderator' OR title != 'Moderator')");
while($ranks = $db->fetch_array($query)) {
for($i = 0; $i < $ranks[stars]; $i++) {
$stars .= "<img src=\"images/star.gif\">";
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
