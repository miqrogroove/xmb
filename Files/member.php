<?php
/*
    XMB 1.8 Partagium
    © 2001 - 2003 Aventure Media & The XMB Developement Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    For license information, please read the license file which came with this edition of XMB
*/

require "./header.php";

loadtemplates('header,footer,member_coppa,member_reg_rules,member_reg_password,member_reg_avatarurl,member_reg_avatarlist,member_reg,member_profile_email,member_profile');
if($action == "reg") {
    $memberaction = "$lang_textregister";
}
if($action == "viewpro") {
    $memberaction = "$lang_textviewpro";
}
if($action == "coppa") {
    $memberaction = "$lang_textcoppa";
}

$navigation = "&raquo; $memberaction";
if($action == "coppa") {
    if($coppasubmit) {
        header("Location: member.php?action=reg");
    }else{
        eval("\$header = \"".template("header")."\";");
        echo $header;
        eval("\$page = \"".template("member_coppa")."\";");
        $page = stripslashes($page);
        echo $page;
    }
}

/*
if($action == "reg") {
    $time = time()-86400; // take the date and distract 24 hours from it
    $max = 999; // Max. amount of users allowed to register per day
    $query = $db->query("SELECT count(uid) FROM $table_members WHERE regdate > '$time'");
    // Select amount of registrations since $time, which is since 24 hours.
    while($count = $db->fetch_array($query)) {
        if($count['uid'] > $max) {
                echo "Maximum Registrations per Day Reached, please wait 24 hours.";
                exit();
        }
    }
*/

// Patch to fix Max Registrations Per Day issue

if($action == "reg") {
    $time = time()-86400; // take the date and distract 24 hours from it
    $max = 999; // Max. amount of users allowed to register per day
    $query = $db->query("SELECT count(uid) FROM $table_members WHERE regdate > '$time'");
    $count = $db->fetch_row($query);
    // Select amount of registrations since $time, which is since 24 hours.
    if($count[0] >= $max) {
        end_time();
        eval("\$header = \"".template("header")."\";");
        eval("\$footer = \"".template("footer")."\";");

        echo $header;
        echo "<center><span class=\"mediumtxt \">$lang_max_reg</span></center>";
        echo $footer;
        exit();
    }




    if($regstatus != "on") {
        eval("\$featureoff = \"".template("misc_feature_notavailable")."\";");
        eval("\$header = \"".template("header")."\";");
              eval("\$footer = \"".template("footer")."\";");
            echo $header;
            echo $featureoff;
            echo $footer;
            exit();
        }

    if($status == "Member") {
        eval("\$featurelin = \"".template("misc_feature_not_while_loggedin")."\";");
        eval("\$header = \"".template("header")."\";");
              eval("\$footer = \"".template("footer")."\";");
            echo $header;
            echo $featurelin;
            echo $footer;
            exit();
        }

    if(!$regsubmit) {
        eval("\$header = \"".template("header")."\";");
        echo $header;
        if($bbrules == "on" && !$rulesubmit) {
            $bbrulestxt = stripslashes(stripslashes($bbrulestxt));
            $bbrulestxt = nl2br($bbrulestxt);
            eval("\$page = \"".template("member_reg_rules")."\";");
            $page = stripslashes($page);
            echo $page;
        }else{

            $newschecked = 'CHECKED';

            $currdate = gmdate($timecode, time()+ ($addtime * 3600));
            eval($lang_evaloffset);

            $themelist = "<select name=\"thememem\">\n<option value=\"\">$lang_textusedefault</option>";
            $query = $db->query("SELECT name FROM $table_themes");
            while($themeinfo = $db->fetch_array($query)) {
                $themelist .= "<option value=\"$themeinfo[name]\">$themeinfo[name]</option>\n";
            }
            $themelist  .= "</select>";


            $langfileselect = "<select name=\"newlangfile\">\n";
            $dir = opendir("lang");
            while ($thafile = readdir($dir)) {
                if (is_file("lang/$thafile") && strstr($thafile, 'lang.php')) {
                    $thafile = str_replace(".lang.php", "", $thafile);
                    if ($thafile == "$bblang") {
                        $langfileselect .= "<option value=\"$thafile\" selected=\"selected\">$thafile</option>\n";
                    }
                    else{
                        $langfileselect .= "<option value=\"$thafile\">$thafile</option>\n";
                    }
                }
            }
            $langfileselect .= "</select>";


            $dayselect = "<select name=\"day\">\n";
            $dayselect .= "<option value=\"\">&nbsp;</option>\n";
            for($num = 1; $num <= 31; $num++) {
                $dayselect .= "<option value=\"$num\">$num</option>\n";
            }
            $dayselect .= "</select>";

            if($sigbbcode == "on") {
                $bbcodeis = $lang_texton;
            }else{
                $bbcodeis = $lang_textoff;
            }

            if($sightml == "on") {
                $htmlis = $lang_texton;
            }else{
                $htmlis = $lang_textoff;
            }

            if($emailcheck != "on") {
                eval("\$pwtd = \"".template("member_reg_password")."\";");
            }

            if($avastatus == "on") {
                eval("\$avatd = \"".template("member_reg_avatarurl")."\";");
            }elseif($avastatus == "list") {
                $avatars = " <option value=\"\" />$lang_textnone</option>  ";
                $dir1 = opendir("images/avatars");
                while($avatar1 = readdir($dir1)) {
                    if(is_file("images/avatars/$avatar1")) {
                        $avatars .= " <option value=\"images/avatars/$avatar1\" />$avatar1</option>  ";
                    }
                }
                closedir($dir1);
                $avatars = str_replace("value=\"$member[avatar]\"", "value=\"$member[avatar]\" selected=\"selected\"", $avatars);

                eval("\$avatd = \"".template("member_reg_avatarlist")."\";");
            }
            eval("\$page = \"".template("member_reg")."\";");
            $page = stripslashes($page);
            echo $page;
        }
    }

    if($regsubmit) {
// Path to add extra illegal characters and correct Registration SQL injection bug

        $find = array('<', '>', '|', '"', '[', ']', '\\', '&', '#', '--');
        foreach($find as $needle) {
            if(strstr($username, $needle)) {
                eval("\$header = \"".template("header")."\";");
                echo $header;
                echo "<center><span class=\"mediumtxt \">Invalid Characters in Username (- $needle -);</span></center>";
                exit();
            }
        }

        if(!$ipreg || $ipreg != 'off') {
            $time = time()-86400;
            $query = $db->query("SELECT * FROM $table_members WHERE regip = '$onlineip' AND regdate >= '$time'");
            if($db->num_rows($query) >= '1') {

                end_time();
                $message = "<tr><td><b>$lang_error:</b> ".$lang_reg_today."</td></tr>";

                eval("\$header = \"".template("header")."\";");
                eval("\$error = \"".template("error")."\";");
                eval("\$footer = \"".template("footer")."\";");

                echo $header;
                echo $error;
                echo $footer;
                exit();
            }
        }

        if($doublee == "off" && (bool) false !== strpos($email, "@")) {
            $email = addslashes(trim($email));
            $email1 = ", email";
            $email2 = "OR email='$email'";
        }else{
            $email1 = '';
            $email2 = '';
        }

        $username = trim($username);
        $query = $db->query("SELECT username$email1 FROM $table_members WHERE username='$username' $email2");

        if($member = $db->fetch_array($query)) {
            eval("\$header = \"".template("header")."\";");

            end_time();

            eval("\$footer = \"".template("footer")."\";");

            $message = "<b>$lang_error:</b> $lang_alreadyreg";
            eval("\$error = \"".template("error")."\";");

            echo $error;
            echo $footer;
            exit();
        }

        if($emailcheck == "on") {
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
            mt_srand((double)microtime() * 1000000);
            for($get = strlen($chars); $i < 8; $i++) {
                $password .= $chars[mt_rand(0, $get)];
            }
            $password2 = $password;
        }

        if($password != $password2) {
            end_time();

            eval("\$header = \"".template("header")."\";");
            eval("\$footer = \"".template("footer")."\";");

            echo $header;
            echo "<center><span class=\"mediumtxt \">$lang_pwnomatch</span></center>";
            echo $footer;

            exit();
        }

        $query = $db->query("SELECT name FROM $table_restricted WHERE name = '$username'");
        if($member = $db->fetch_array($query)) {
            end_time();
            eval("\$footer = \"".template("footer")."\";");
            eval("\$header = \"".template("header")."\";");
            echo $header;
            echo "<center><span class=\"mediumtxt \">$lang_restricted</span></center>";
            echo $footer;
            exit();
        }

        $query = $db->query("SELECT name FROM $table_restricted WHERE name='$email'");
        if($member = $db->fetch_array($query)) {
            end_time();
            eval("\$footer = \"".template("footer")."\";");
            eval("\$header = \"".template("header")."\";");
            echo $header;
            echo "<center><span class=\"mediumtxt \">$lang_emailrestricted</span></center>";
            echo $footer;
            exit();
        }

        if(!strstr($email, "@")) {
            end_time();
            eval("\$footer = \"".template("footer")."\";");
            eval("\$header = \"".template("header")."\";");
            echo $header;
            echo "<center><span class=\"mediumtxt \">$lang_bademail</span></center>";
            echo $footer;
            exit();
        }

        if($password == "" || ereg('"', $password)|| ereg("'", $password)) {
            end_time();
            eval("\$footer = \"".template("footer")."\";");
            eval("\$header = \"".template("header")."\";");
            echo $header;
            echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
            echo $footer;
            exit();
        }

        $query = $db->query("SELECT COUNT(uid) FROM $table_members");
        $count1 = $db->result($query,0);

        if($count1 != "0") {
            $status = "Member";
        }else{
            $status = "Super Administrator";
        }

        if($showemail != "yes") {
            $showemail = "no";
        }

        if($newsletter != "yes") {
            $newsletter = "no";
        }

        $bday = "$month $day, $year";

        if($month == "" || $day == "" || $year == "") {
            $bday = "";
        }

        $avatar     = checkInput($avatar, '', '', "javascript");
        $dateformatnew  = checkInput($dateformatnew, '', '', "javascript");
        $locationnew    = checkInput($locationnew, '', '', "javascript");
        $icq        = checkInput($icq, '', '', "javascript");
        $yahoo      = checkInput($yahoo, '', '', "javascript");
        $aim        = checkInput($aim, '', '', "javascript");
        $email      = checkInput($email, '', '', "javascript");
        $site       = checkInput($site, '', '', "javascript");
        $bio        = checkInput($bio, '', '', "javascript");
        $bday       = checkInput($bday, '', '', "javascript");
        $mood       = checkInput($newmood, '', '', "javascript");
        $sig        = checkInput($_POST['sig']);

        $sig        = addslashes($sig);
        $bio        = addslashes($bio);
        $locationnew    = addslashes($locationnew);

        $password   = md5(trim($password));
        $db->query("INSERT INTO $table_members VALUES ('', '$username', '$password', '" . time() . "', '0', '$email', '$site', '$aim', '$status',  '$locationnew', '$bio', '$sig', '$showemail', '$timeoffset1', '$icq', '$avatar', '$yahoo', '', '$thememem', '$bday', '$newlangfile', '$tpp', '$ppp',  '$newsletter', '$onlineip', '$timeformatnew', '$msn', '', '$dateformatnew', '', '', '$newmood', '')");

        if($notify == "on") {
            $mailquery = $db->query("SELECT * FROM $table_members WHERE status = 'Administrator'");
            while($notify = $db->fetch_array($mailquery)) {
                mail("$notify[email]", "$lang_textnewmember", "$lang_textnewmember2", "From: $bbname <$adminemail>");
            }
        }

        if($emailcheck == "on") {
            mail($email, "$lang_textyourpw", "$lang_textyourpwis \n\n$username\n$password2", "From: $bbname <$adminemail>");
        }else{
            $currtime = time() + (86400*30);
            setcookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            setcookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
        }
        eval("\$header = \"".template("header")."\";");
        $header = stripslashes($header);
        echo $header;
        echo ($emailcheck == "on") ? "<center><span class=\"mediumtxt \">$lang_emailpw</span></center>" : "<center><span class=\"mediumtxt \">$lang_regged</span></center>";

        ?>
        <script>
        function redirect()
        {
        window.location.replace("index.php");
        }
        setTimeout("redirect();", 1250);
        </script>
        <?
    }
}


if($action == "viewpro") {
    eval("\$header = \"".template("header")."\";");
    echo $header;
    if(!$member) {
        echo $lang_nomember;
    }else{
        $query = $db->query("SELECT * FROM $table_members WHERE username='$member'");
        $memberinfo = $db->fetch_array($query);

        $daysreg = (time() - $memberinfo[regdate]) / (24*3600);
        $ppd = $memberinfo[postnum] / $daysreg;
        $ppd = round($ppd, 2);

        $memberinfo[regdate] = gmdate("n/j/y",$memberinfo[regdate] + ($addtime * 3600) + ($timeoffset * 3600));

        $memberinfo[site] = str_replace("http://", "", $memberinfo[site]);
        $memberinfo[site] = "http://$memberinfo[site]";

        if($memberinfo[site] != "http://") {
            $site = "$memberinfo[site]";
        }

        if($memberinfo[email] != "" && $memberinfo[showemail] == "yes") {
            $email = $memberinfo[email];
        }

        if($memberinfo[avatar] != "") {
            if(isset($site)) {
                $avatar = "<a href=\"$site\"><img src=\"$memberinfo[avatar]\" border=\"0\" /></a>";
            }elseif(!isset($site)) {
                $avatar = "<img src=\"$memberinfo[avatar]\" border=\"0\" />";
            }
        }

        $lastvisitdate = gmdate("$dateformat",$memberinfo[lastvisit] + ($timeoffset * 3600) + ($addtime * 3600));
        $lastvisittime = gmdate("$timecode",$memberinfo[lastvisit] + ($timeoffset * 3600) + ($addtime * 3600));
        $lastmembervisittext = "$lastvisitdate $lang_textat $lastvisittime";


        $query = $db->query("SELECT COUNT(pid) FROM $table_posts");
        $posts = $db->result($query, 0);

        $query = $db->query("SELECT COUNT(tid) FROM $table_threads");
        $threads = $db->result($query, 0);

        $posttot = $posts;
        if($posttot == 0) {
            $percent = "0";
        }else{
            $percent = $memberinfo[postnum]*100/$posttot;
            $percent = round($percent, 2);
        }

        $memberinfo[bio] = stripslashes($memberinfo[bio]);
        $memberinfo[bio] = nl2br($memberinfo[bio]);
        $encodeuser = rawurlencode($memberinfo[username]);

        if($memberinfo[showemail] == "yes") {
            eval("\$emailblock = \"".template("member_profile_email")."\";");
        }

        if($self[status] == "Super Administrator") {
            $admin_edit = "<br />$lang_adminoption <a href=\"./editprofile.php?user=$memberinfo[username]\">$lang_admin_edituseraccount</a>";
        }else{
            $admin_edit = NULL;
        }

        $lang_searchusermsg = str_replace('*USER*', $memberinfo[username], $lang_searchusermsg);

        eval("\$page = \"".template("member_profile")."\";");
        $page = stripslashes($page);
        echo $page;

    }
}

end_time();

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>