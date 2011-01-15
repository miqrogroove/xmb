<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

// Fetch global stuff
	require './header.php';

// Pre-load templates (saves queries)
	loadtemplates('header,faq,faq_usermaint,faq_using,faq_messages,faq_messages_smiley,faq_messages_smilierow,faq_misc,faq_misc_rankrow,footer');

// Check if the faq is actually turned on in the settings
	if($faqstatus != "on") {
		eval("\$header = \"".template("header")."\";");
		eval("\$featureoff = \"".template("misc_feature_notavailable")."\";");
		eval("\$footer = \"".template("footer")."\";");
		
		end_time();
		
		echo $header;
		echo $featureoff;
		echo $footer;
		
		exit();
	}

// Create navigation, and create page
	switch($page){
		case 'usermaint';
			$navigation = "&raquo; <a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textuserman";
			eval("\$faq = \"".template("faq_usermaint")."\";");
			break;
			
		case 'using';
			$navigation = "&raquo; <a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textuseboa";
			eval("\$faq = \"".template("faq_using")."\";");
			break;
			
		case 'messages';
			$navigation = "&raquo; <a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textpostread";
			
			// Create smiley-table
			$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='smiley'") or die($db->error());
			while($smilie = $db->fetch_array($querysmilie)) {
				eval("\$smilierows .= \"".template("faq_messages_smilierow")."\";");
			}
			
			eval("\$faq = \"".template("faq_messages")."\";");
			break;
			
		case 'misc';
			$navigation = "&raquo; <a href=\"faq.php\">$lang_textfaq</a> &raquo; $lang_textmiscfaq";
			
			// Create table with ranks
			$query = $db->query("SELECT * FROM $table_ranks WHERE title!='Moderator' AND title!='Super Moderator' AND title!='Super Administrator' AND title!='Administrator' ORDER BY posts");
			while($ranks = $db->fetch_array($query)) {
				for($i = 0; $i < $ranks['stars']; $i++) {
					$stars .= "<img src=\"$imgdir/star.gif\" />";
				}
				eval("\$rankrows .= \"".template("faq_misc_rankrow")."\";");
				$stars = '';
			}
			
			eval("\$faq = \"".template("faq_misc")."\";");
			break;
			
		default;
			$navigation = "&raquo; ".$lang_textfaq;
			eval("\$faq = \"".template("faq")."\";");
			break;
	}

// Load and show the header, and then show the rest
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo $faq;

// End page loading, and show footer
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
?>