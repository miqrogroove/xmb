<?

/*

XMB 1.8 Partagium
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/

require ('./header.php');

if ($tempcache['footer'] != "") { 
	header("Location: faq.php"); 
	exit; 
} 

loadtemplates('header,faq,faq_usermaint,faq_using,faq_messages,faq_messages_smiley,faq_messages_smilierow,faq_misc,faq_misc_rankrow,footer');

if($page == "usermaint") {
		$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textuserman";
	} elseif (
		$page == "using") {
		$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textuseboa";
	} elseif (
		$page == "messages") {
		$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textpostread";
	} elseif (
		$page == "misc") {
		$navigation = "<a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textmiscfaq";
	} else {
		$navigation = $lang_textfaq;
}

$navigation = "&raquo; $navigation";

if($faqstatus != "on") {
	eval("\$featureoff = \"".template("misc_feature_notavailable")."\";");
	echo $featureoff;
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
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
	$query = $db->query("SELECT * FROM $table_ranks WHERE title!='Moderator' AND title!='Super Moderator' AND title!='Super Administrator' AND title!='Administrator' ORDER BY posts");
		while($ranks = $db->fetch_array($query)) {
			for($i = 0; $i < $ranks['stars']; $i++) {
				$stars .= "<img src=\"$imgdir/star.gif\">";
			}
			eval("\$rankrows .= \"".template("faq_misc_rankrow")."\";");
			$stars = "";
		}
	eval("\$faq = \"".template("faq_misc")."\";");
}

end_time();

echo $faq;
eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
