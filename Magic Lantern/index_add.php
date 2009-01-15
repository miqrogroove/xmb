<?
/*

XMB 1.6 v2c Magic Lantern
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./xmb.php";

if (empty($analized)){
$agt=$HTTP_USER_AGENT;
$ip= $REMOTE_ADDR;
$host=gethostbyaddr($REMOTE_ADDR);
$ref=$HTTP_REFERER;
$cip=$HTTP_CLIENT_IP;
$cookie=$HTTP_COOKIE;
//.....................................
$time= date("d/m/Y H:i:s");
$from=$DOCUMENT_ROOT;
$fp=fopen("index_log.log", "r");
$raw=fgets($fp, filesize("index_log.log"));
fclose($fp);
$new=explode("|", $raw);
$nb=count($new);
$new[$nb]="<tr bordercolor=\"#FFFFFF\"><td>$ip<br>$cip</td><td>$host</td><td>$agt<br><b>$ref      $cookie</b></td><td>$time</td></tr> ";
$raw=implode("|", $new);
$fp1=fopen("index_log.log", "w+");
fwrite($fp1, $raw);
fclose($fp1);
setcookie("Analized", "XMB Index Log" ,time()+900);
};
?>
