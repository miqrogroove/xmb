<?
function template ($file) {
$bordercolor = $GLOBALS["bordercolor"];
return addslashes(implode("",file("templates/$file")));
}

function postify($message, $smileyoff, $bbcodeoff, $fid, $bordercolor, $sigbbcode, $sightml, $table_words, $table_forums, $table_smilies) {
if($fid != "") {
$query = mysql_query("SELECT * FROM $table_forums WHERE fid=$fid") or die(mysql_error());
$forums = mysql_fetch_array($query);
}
else {
$forums[allowsmilies] = "yes";
$forums[allowhtml] = "yes";
$forums[allowbbcode] = "yes";
$forums[allowimgcode] = "yes";
}

if($sightml == "") {
if($forums[allowhtml] == "yes") {
$sightml = "on";
} else {
$sightml = "off";
}
}

if($sigbbcode == "") {
if($forums[allowbbcode] == "yes") {
$sigbbcode = "on";
} else {
$sigbbcode = "off";
}
}

if($sigimgcode == "") { 
if($forums[allowimgcode] == "yes") { 
$sigimgcode = "on"; 
} else { 
$sigimgcode = "off"; 
} 
}

if($forums[allowhtml] != "yes" &&  $sightml != "on") {
$message = str_replace("<","&lt;",$message);
$message = str_replace(">","&gt;",$message);
}

$message = nl2br($message);

if($smileyoff != "yes" && $forums[allowsmilies] == "yes") {
$querysmilie = mysql_query("SELECT * FROM $table_smilies WHERE type='smiley'") or die(mysql_error());
while($smilie = mysql_fetch_array($querysmilie)) {
$message = str_replace("$smilie[code]", "<img src=\"images/$smilie[url]\" border=0>",$message);
}
}

if($bbcodeoff != "yes" && $forums[allowbbcode] == "yes") {
if($sigbbcode == "on") {
$message = eregi_replace("\\[color=([^\\[]*)\\]([^\\[]*)\\[/color\\]","<font color=\"\\1\">\\2</font>",$message);
$message = eregi_replace("\\[size=([^\\[]*)\\]([^\\[]*)\\[/size\\]","<font size=\"\\1\">\\2</font>",$message);
$message = eregi_replace("\\[font=([^\\[]*)\\]([^\\[]*)\\[/font\\]","<font face=\"\\1\">\\2</font>",$message);
$message = eregi_replace("\\[align=([^\\[]*)\\]([^\\[]*)\\[/align\\]","<p align=\"\\1\">\\2</p>",$message);
$message = str_replace("[b]", "<b>", $message); 
$message = str_replace("[/b]", "</b>", $message); 
$message = str_replace("[i]", "<i>", $message); 
$message = str_replace("[/i]", "</i>", $message); 
$message = str_replace("[u]", "<u>", $message); 
$message = str_replace("[/u]", "</u>", $message); 
$message = eregi_replace("\\[email\\]([^\\[]*)\\[/email\\]", "<a href=\"mailto:\\1\">\\1</a>",$message);
$message = eregi_replace("\\[email=([^\\[]*)\\]([^\\[]*)\\[/email\\]", "<a href=\"mailto:\\1\">\\2</a>",$message);
$message = str_replace("[quote]", "<blockquote><span class=\"12px\">quote:</span><hr>", $message); 
$message = str_replace("[/quote]", "<hr></blockquote>", $message); 
$message=str_replace("[code]","<blockquote><pre><smallfont>code:</smallfont><hr>",$message);
$message=str_replace("[/code]","<hr></pre><normalfont></blockquote>",$message);
$message = eregi_replace("\\[url\\]www.([^\\[]*)\\[/url\\]", "<a href=\"http://www.\\1\" target=_blank>\\1</a>",$message); 
$message = eregi_replace("\\[url\\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" target=_blank>\\1</a>",$message); 
$message = eregi_replace("\\[url=([^\\[]*)\\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" target=_blank>\\2</a>",$message);
$message = eregi_replace("(^|[>[:space:]\n])([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])([<[:space:]\n]|$)","\\1<a href=\"\\2://\\3\\4\" target=\"_blank\">\\2://\\3\\4</a>\\5", $message);
}
}

if($forums[allowimgcode] == "yes") { 
if($sigimgcode == "on") { 
$message = eregi_replace("\\[img\\]([^\\[]*)\\[/img\\]","<img src=\"\\1\" border=0>",$message); 
$message = eregi_replace("\\[img=([^\\[]*)x([^\\[]*)\\]([^\\[]*)\\[/img\\]","<img width=\"\\1\" height=\"\\2\" src=\"\\3\" border=0>",$message); 
} 
}

$message = CensorMessage($message, $table_words);
return $message;
}


function CensorMessage($message, $table_words) {
$querycensor = mysql_query("SELECT * FROM $table_words") or die(mysql_error());
while($censor = mysql_fetch_array($querycensor)) {
$pre = "([^[:alpha:]]|^)";
$suf = "([^[:alpha:]]|$)";
$message = eregi_replace($pre.$censor[find].$suf,"\\1$censor[replace1]\\2",$message);
}
return $message;
}


function misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile) {

require "lang/$langfile.lang.php";
require "settings.php";

$bbname = $GLOBALS["bbname"];
$langfile = $GLOBALS["langfile"];
$bgcolor = $GLOBALS["bgcolor"];
$altbg1 = $GLOBALS["altbg1"];
$altbg2 = $GLOBALS["altbg2"];
$link = $GLOBALS["link"];
$header = $GLOBALS["header"];
$headertext = $GLOBALS["headertext"];
$top = $GLOBALS["top"];
$bordercolor = $GLOBALS["bordercolor"];
$tabletext = $GLOBALS["tabletext"];
$boardlogo = $GLOBALS["boardlogo"];
$text = $GLOBALS["text"];
$borderwidth = $GLOBALS["borderwidth"];
$tablewidth = $GLOBALS["tablewidth"];
$tablespace = $GLOBALS["tablespace"];
$font = $GLOBALS["font"];
$fontsize = $GLOBALS["fontsize"];
$altfont = $GLOBALS["altfont"];
$altfontsize = $GLOBALS["altfontsize"];
$replyimg = $GLOBALS["replyimg"];
$newtopicimg = $GLOBALS["newtopicimg"];
$boardimg = $GLOBALS["boardimg"];
$postscol = $GLOBALS["postscol"];

$font1 = $fontsize-1;
$font2 = $fontsize+1;
$font3 = $fontsize+3;
$font4 = $fontsize+5;

if($regstatus == "on" && $noreg != "on") { 
if($coppa == "on") { 
$reglink = "<a href=\"member.php?action=coppa\"><span class=\"navtd\">$lang[textregister]</span></a>"; 
} else { 
$reglink = "<a href=\"member.php?action=reg\"><span class=\"navtd\">$lang[textregister]</span></a>"; 
} 
$proreg = $reglink; 
} 

if($thisuser && $thisuser != '') { 
$notify = "$lang[loggedin] $thisuser"; 
$loginout = "<a href=\"misc.php?action=logout\"><span class=\"navtd\">$lang[textlogout]</span></a>"; 
$proreg = "<a href=\"member.php?action=editpro\"><span class=\"navtd\">$lang[textprofile]</span></a>"; 
$onlineuser = $thisuser; 

if($u2ustatus == "on") { 
$proreg .= " | <a href=\"#\" onclick=\"Popup('misc2.php?action1=u2u', 'Window', 550, 450);\"><span class=\"navtd\">$lang[textu2u]</span></a>"; 
} 

} else { 
$notify = "$lang[notloggedin]"; 
$loginout = "<a href=\"misc.php?action=login\"><span class=\"navtd\">$lang[textlogin]</span></a>"; 
$onlineuser = "xguest123"; 
} 

if($searchstatus == "on") { 
$searchlink = "| <a href=\"misc.php?action=search\"><span class=\"navtd\">$lang[textsearch]</span></a>"; 
} 

if($faqstatus == "on") { 
$faqlink = "| <a href=\"misc.php?action=faq\"><span class=\"navtd\">$lang[textfaq]</span></a>"; 
} 

if($memliststatus == "on") { 
$memlistlink = "| <a href=\"misc.php?action=list\"><span class=\"navtd\">$lang[textmemberlist]</span></a>"; 
} 

if($boardimg != "") { 
$logo = "<tr><td><a href=\"index.php\"><img src=\"$boardimg\" alt=\"Board logo\" border=\"0\" /></a></td><td> </td></tr>"; 
}

if($statspage == "on") {
$statslink = "| <a href=\"misc.php?action=stats\"><span class=\"navtd\">$lang[textstats]</span></a>";
}

$navigation = "<a href=\"index.php\">$lang[textindex]</a> &gt; $miscaction";
$html = template("header.html");
eval("echo stripslashes(\"$html\");");
}


function modcheck($status, $username, $fid, $table_forums) {
if($status == "Moderator") { 
$query = mysql_query("SELECT * FROM $table_forums WHERE moderator LIKE '%$username%'") or die(mysql_error());
while($mod = mysql_fetch_array($query)) {
if($mod[fid] == $fid) { 
$modgood = "yes";
} 
}

if($modgood == "yes") {
$status1 = "Moderator";
}

}

return $status1;
}


function privfcheck($hideprivate, $status, $private, $thisuser, $userlist) {
$forum[private] = $private;
$forum[userlist] = $userlist;
if($hideprivate == "on") {

if($forum[private] == "staff" && ($status == "Administrator" || $status == "Super Moderator" || $status == "Moderator")) { 
$authorization = "true";
} elseif($forum[private] != "staff" && $forum[userlist] == "") { 
$authorization = "true";
} elseif($forum[userlist] != "") {
if(eregi($thisuser."(,|$)", $forum[userlist]) && $thisuser != "") {
$authorization = "true";
} else {
$authorization = "no";
}
} else {
$authorization = "no";
}

} else { 
$authorization = "true"; 
}

return $authorization;
}


function forum($lastpost, $timeoffset, $moderator, $lastvisit2, $hideprivate, $status, $private, $posts, $threads, $altbg1, $altbg2 , $name, $fid, $desc, $timecode, $dateformat, $langfile, $thisuser, $userlist) {
$forum[lastpost] = $lastpost;
$forum[moderator] = $moderator;
$forum[private] = $private;
$forum[posts] = $posts;
$forum[threads] = $threads;
$forum[name] = $name;
$forum[fid] = $fid;
$forum[description] = $desc;
$forum[userlist] = $userlist;
require "lang/$langfile.lang.php";
$postscol = $GLOBALS["postscol"];

if($forum[lastpost] != "") {
$lastpost = explode("|", $forum[lastpost]);
$dalast = $lastpost[0];

if($lastpost[1] == $lang[textguest]) {
$lastpost[1] = "$lastpost[1]";
} else {
$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
}

$lastpostdate = gmdate("$dateformat",$lastpost[0] + ($timeoffset * 3600));
$lastposttime = gmdate("$timecode",$lastpost[0] + ($timeoffset * 3600));
$lastpost = "$lang[lastreply1] $lastpostdate $lang[textat] $lastposttime<br />$lang[textby] $lastpost[1]";
} else {
$lastpost = "$lang[textnever]";
}

$lastvisit2 -= 540;
if($lastvisit2 < $dalast) {
$folder = "<img src=\"images/red_folder.gif\">";
} else {
$folder = "<img src=\"images/folder.gif\">";
} 

if($dalast == "") {
$folder = "<img src=\"images/folder.gif\">";
}

$lastvisit2 += 540;

$authorization = privfcheck($hideprivate, $status, $forum[private], $thisuser, $forum[userlist]);

if($authorization == "true") {

if($forum[moderator] != "") {
$modz = explode(", ", $forum[moderator]);
$forum[moderator] = "";
for($num = 0; $num < count($modz); $num++) {
$thismod = "<a href=\"member.php?action=viewpro&member=$modz[$num]\">$modz[$num]</a>";

if($num == count($modz) - 1) {
$forum[moderator] .= $thismod;
} else {
$forum[moderator] .= "$thismod, ";
}
}
$forum[moderator] = "($lang[textmodby] $forum[moderator])";
} else {
$forum[moderator] = "";
}
?>

<tr> 
<td bgcolor="<?=$altbg1?>" align="center" class="tablerow"><?=$folder?></td> 
<td bgcolor="<?=$altbg2?>" class="tablerow"><font class="12px"><a href="forumdisplay.php?fid=<?=$forum[fid]?>"><?=$forum[name]?></a></font>
 &nbsp; <font class="11px"><?=$forum[moderator]?></font><br /><font class="11px"><?=$forum[description]?></font></td>

<?
if($postscol == "1col") {
?>
<td bgcolor="<?=$altbg1?>" align="center" class="tablerow"><font size="1" face="verdana"><?=$lang[texttopics]?> <?=$forum[threads]?><br /><?=$lang[textposts]?>	<?=$forum[posts]?></font></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><font size="1" face="verdana"><?=$lastpost?></font></td>
</tr> 
<?
} else {
?>
<td bgcolor="<?=$altbg1?>" align="center" class="tablerow"><font class="12px"><?=$forum[threads]?></font></td>
<td bgcolor="<?=$altbg2?>" align="center" class="tablerow"><font class="12px"><?=$forum[posts]?></font></td>
<td bgcolor="<?=$altbg1?>" class="tablerow"><font size="1" face="verdana"><?=$lastpost?></font></td>
</tr> 
<?
}

} 

$dalast = "";
$fmods = "";

}
?>