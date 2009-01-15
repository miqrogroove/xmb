<?php
require "header.php";
loadtemplates('buddylist_edit_buddy,buddylist_edit,buddylist_buddy_online,buddylist_buddy_offline,buddylist,buddylist_message');
if($xmbuser == "") {
echo "$lang_u2unotloggedin";
exit;
}
if($action == "add") {
if($buddy == "") {
blistmsg("$lang_nobuddyselected");
exit;
}
$query = $db->query("SELECT * FROM $table_buddys WHERE username='$xmbuser'
AND buddyname='$buddy'");
if($db->fetch_array($query)) {
blistmsg("$buddy $lang_buddyalreadyonlist");
exit;
}
$db->query("INSERT INTO $table_buddys VALUES ('$xmbuser', '$buddy')");
blistmsg("<center>$buddy $lang_buddyaddedmsg<center>", "buddy.php");
}
if($action == "edit") {
if($editsubmit) {
if($newbuddy1 != "") {
$query = $db->query("SELECT * FROM $table_buddys WHERE username='$xmbuser'
AND buddyname='$newbuddy1'");
if($db->fetch_array($query)) {
blistmsg("$newbuddy1 $lang_buddyalreadyonlist");
exit;
}
$db->query("INSERT INTO $table_buddys VALUES ('$xmbuser', '$newbuddy1')");
}
if($newbuddy2 != "") {
$query = $db->query("SELECT * FROM $table_buddys WHERE username='$xmbuser'
AND buddyname='$newbuddy2'");
if($db->fetch_array($query)) {
blistmsg("$newbuddy2 $lang_buddyalreadyonlist");
exit;
}
$db->query("INSERT INTO $table_buddys VALUES ('$xmbuser', '$newbuddy2')");
}

$query = $db->query("SELECT * FROM $table_buddys WHERE username='$xmbuser'");
while($buddy = $db->fetch_array($query)) {
$delete = "delete$buddy[buddyname]";
$delete = "${$delete}";

if($delete != "") {
$db->query("DELETE FROM $table_buddys WHERE buddyname='$delete'");
}
}
blistmsg("<center>$lang_buddylistupdated<center>", "buddy.php");

}
if(!$editsubmit) {
$query = $db->query("SELECT * FROM $table_buddys WHERE username='$xmbuser'");
while($buddy = $db->fetch_array($query)) {
eval("\$buddys .= \"".template("buddylist_edit_buddy")."\";");
}
eval("\$edit = \"".template("buddylist_edit")."\";");
echo $edit;
}
}

else {
// Load Buddy List
$query = $db->query("SELECT * FROM $table_buddys WHERE username='$xmbuser'");
while($buddy = $db->fetch_array($query)) {
$query2 = $db->query("SELECT * FROM $table_whosonline WHERE username='$buddy[buddyname]'");
$onlineinfo = $db->fetch_array($query2);
if($onlineinfo) {
eval("\$buddys[online] .= \"".template("buddylist_buddy_online")."\";");
} else {
eval("\$buddys[offline] .= \"".template("buddylist_buddy_offline")."\";");
}
}
eval("\$buddylist = \"".template("buddylist")."\";");
echo $buddylist;
}
function blistmsg($message, $redirect="") {
global $bordercolor, $tablewidth, $borderwidth, $tablespace, $altbg1, $css, $bbname, $lang_textpowered;
if($redirect != "") {
$redirectjs = "<script> function redirect() { window.location.replace(\"$redirect\"); } setTimeout(\"redirect();\", 1250); </script>";
}
eval("\$blistmessage = \"".template("buddylist_message")."\";");
echo $blistmessage;
}

?>
