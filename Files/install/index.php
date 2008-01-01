<?php
/* $Id: index.php,v 1.1.2.11 2005/09/20 12:15:01 Tularis Exp $ */
/*
    XMB 1.9.2
    © 2001 - 2005 Aventure Media & The XMB Developement Team

    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
define('ROOT', '../');

define('X_VERSION', '1.9.2');
define('X_VERSION_EXT', '1.9.2 Nexus Final');

define('X_INST_ERR', 0);
define('X_INST_WARN', 1);
define('X_INST_OK', 2);
define('X_INST_SKIP', 3);

define('COMMENTOUTPUT', false);
define('MAXATTACHSIZE', 1000000);
define('IPREG', 'on');
define('IPCHECK', 'off');
define('SPECQ', false);
define('SHOWFULLINFO', false);

@ini_set('magic_quotes_runtime', '0');
@ini_set('magic_quotes_gpc', '1');

function error($head, $msg, $die=true) {
    echo "\n";
    echo '<blockquote>';
    echo '<font class="progressErr">'.$head.'</font><br />';
    echo '<font class="progressWarn">'.$msg.'</font>';
    echo '</blockquote>';
    echo "\n";
    if ( $die) {
        exit();
    }
}

function show_act($act) {
    $act .= str_repeat('.', (75-strlen($act)));
    echo '<font class="progress">'.$act;
}

function show_result($type) {
    switch($type) {
        case 0:
            echo '<font class="progressErr">ERROR</font><br />';
            break;

        case 1:
            echo '<font class="progressWarn">WARNING</font><br />';
            break;

         case 2:
            echo '<font class="progressOk">OK</font><br />';
            break;

         case 3:
            echo '<font class="progressSkip">SKIPPED</font><br />';
            break;
    }
    echo "</font>\n";
}

if (!function_exists('file_get_contents')) {
    function file_get_contents($file) {
        $stream = fopen($file, 'r');
        $contents = fread($stream, filesize($file));
        fclose($stream);

        return $contents;
    }
}

if(isset($_GET['action']) && $_GET['action'] == 'verifyUrl') {
	echo md5($_SERVER['SCRIPT_FILENAME']);
	exit;
}

error_reporting(E_ALL&~E_NOTICE);

if (isset($_REQUEST['step']) && $_REQUEST['step'] < 7 && $_REQUEST['step'] != 4) {
?>
    <html>
        <head>
            <title>
                XMB Installer
            </title>
            <style type="text/css">
                @import url("./install.css");
            </style>
        </head>
        <body>
<?php
}
$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 0;
$substep = isset($_REQUEST['substep']) ? $_REQUEST['substep'] : 0;
$vStep = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : 0;

switch($vStep) {
    case 1: // welcome
        ?>
        <p style="text-align:center;"><a href="index.php?step=2"><img src="./splash.gif" border="0" /></a></p>
        <p style="text-align:center;">Thank you for choosing XMB as your message board.</p>
        <p style="text-align:center;">The next steps will guide you through the installation of your board.</p>
        <form action="./index.php?step=2" method="post">
        <p style="text-align:center;">
        <INPUT TYPE="submit" VALUE="Start installation &gt;" />
        </p>
        </form>
        <?php
        break;

    case 2: // versioncheck
        ?>
        <p class="subTitle">
            Version Check Information
        </p>

        <br />
        This page displays your version of XMB, and the latest version available from XMB. If there is a later version, XMB strongly recommends you do not install this version, but choose the latest stable release.
        <br /><br />
        <table style="width: 75%;">
        <tr>
        <td style="width: 30%;">
        Your Version:
        </td>
        <td style="width:70%;">
            <b>XMB <?php echo X_VERSION_EXT;?></b>
        </td>
        </tr>
        <td style="width: 30%;">
        Current Version:</td>
        <td style="width:70%;">
        <img src="http://www.xmbforum.com/phpbin/xmbvc/vc.php?bg=e9edef&fg=000000" border="0">
        </td>
        </tr>
        </table>
        <br />
        <form action="./index.php?step=3" method="post">
        <INPUT TYPE="submit" VALUE="Install XMB <?php echo X_VERSION;?> &gt;" />
        </form>
        <br />&nbsp;
        </p>
        <?php
        break;

    case 3: // agreement
        ?>
        <p style="text-align:center;">
        <table BORDER=0 CELLSPACING=0 CELLPADDING=0 COLS=1 WIDTH="80%" >
        <tr>
        <td ALIGN=CENTER VALIGN=CENTER>
        <p class="subTitle">XMB <?php echo X_VERSION;?> License Agreement</p>
        <p>Please read over the agreement below, and if you agree to it select the button</p>
        <p>at the very bottom. By selecting the button, you agree to the terms below.</p><br /><br />
        <textarea cols="100" rows="30"  wrap='soft' name="agreement" style= "font-family: Verdana; font-size: 8pt" readonly>
        XMB <?php echo X_VERSION;?>  License (Updated August 2005)
        www.aventure-media.co.uk   www.xmbforum.com
        ----------------------------------------------

            GNU GENERAL PUBLIC LICENSE
               Version 2, June 1991

 Copyright (C) 1989, 1991 Free Software Foundation, Inc.
                       59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 Everyone is permitted to copy and distribute verbatim copies
 of this license document, but changing it is not allowed.

                Preamble

  The licenses for most software are designed to take away your
freedom to share and change it.  By contrast, the GNU General Public
License is intended to guarantee your freedom to share and change free
software--to make sure the software is free for all its users.  This
General Public License applies to most of the Free Software
Foundation's software and to any other program whose authors commit to
using it.  (Some other Free Software Foundation software is covered by
the GNU Library General Public License instead.)  You can apply it to
your programs, too.

  When we speak of free software, we are referring to freedom, not
price.  Our General Public Licenses are designed to make sure that you
have the freedom to distribute copies of free software (and charge for
this service if you wish), that you receive source code or can get it
if you want it, that you can change the software or use pieces of it
in new free programs; and that you know you can do these things.

  To protect your rights, we need to make restrictions that forbid
anyone to deny you these rights or to ask you to surrender the rights.
These restrictions translate to certain responsibilities for you if you
distribute copies of the software, or if you modify it.

  For example, if you distribute copies of such a program, whether
gratis or for a fee, you must give the recipients all the rights that
you have.  You must make sure that they, too, receive or can get the
source code.  And you must show them these terms so they know their
rights.

  We protect your rights with two steps: (1) copyright the software, and
(2) offer you this license which gives you legal permission to copy,
distribute and/or modify the software.

  Also, for each author's protection and ours, we want to make certain
that everyone understands that there is no warranty for this free
software.  If the software is modified by someone else and passed on, we
want its recipients to know that what they have is not the original, so
that any problems introduced by others will not reflect on the original
authors' reputations.

  Finally, any free program is threatened constantly by software
patents.  We wish to avoid the danger that redistributors of a free
program will individually obtain patent licenses, in effect making the
program proprietary.  To prevent this, we have made it clear that any
patent must be licensed for everyone's free use or not licensed at all.

  The precise terms and conditions for copying, distribution and
modification follow.
.
            GNU GENERAL PUBLIC LICENSE
   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

  0. This License applies to any program or other work which contains
a notice placed by the copyright holder saying it may be distributed
under the terms of this General Public License.  The "Program", below,
refers to any such program or work, and a "work based on the Program"
means either the Program or any derivative work under copyright law:
that is to say, a work containing the Program or a portion of it,
either verbatim or with modifications and/or translated into another
language.  (Hereinafter, translation is included without limitation in
the term "modification".)  Each licensee is addressed as "you".

Activities other than copying, distribution and modification are not
covered by this License; they are outside its scope.  The act of
running the Program is not restricted, and the output from the Program
is covered only if its contents constitute a work based on the
Program (independent of having been made by running the Program).
Whether that is true depends on what the Program does.

  1. You may copy and distribute verbatim copies of the Program's
source code as you receive it, in any medium, provided that you
conspicuously and appropriately publish on each copy an appropriate
copyright notice and disclaimer of warranty; keep intact all the
notices that refer to this License and to the absence of any warranty;
and give any other recipients of the Program a copy of this License
along with the Program.

You may charge a fee for the physical act of transferring a copy, and
you may at your option offer warranty protection in exchange for a fee.

  2. You may modify your copy or copies of the Program or any portion
of it, thus forming a work based on the Program, and copy and
distribute such modifications or work under the terms of Section 1
above, provided that you also meet all of these conditions:

    a) You must cause the modified files to carry prominent notices
    stating that you changed the files and the date of any change.

    b) You must cause any work that you distribute or publish, that in
    whole or in part contains or is derived from the Program or any
    part thereof, to be licensed as a whole at no charge to all third
    parties under the terms of this License.

    c) If the modified program normally reads commands interactively
    when run, you must cause it, when started running for such
    interactive use in the most ordinary way, to print or display an
    announcement including an appropriate copyright notice and a
    notice that there is no warranty (or else, saying that you provide
    a warranty) and that users may redistribute the program under
    these conditions, and telling the user how to view a copy of this
    License.  (Exception: if the Program itself is interactive but
    does not normally print such an announcement, your work based on
    the Program is not required to print an announcement.)
.
These requirements apply to the modified work as a whole.  If
identifiable sections of that work are not derived from the Program,
and can be reasonably considered independent and separate works in
themselves, then this License, and its terms, do not apply to those
sections when you distribute them as separate works.  But when you
distribute the same sections as part of a whole which is a work based
on the Program, the distribution of the whole must be on the terms of
this License, whose permissions for other licensees extend to the
entire whole, and thus to each and every part regardless of who wrote it.

Thus, it is not the intent of this section to claim rights or contest
your rights to work written entirely by you; rather, the intent is to
exercise the right to control the distribution of derivative or
collective works based on the Program.

In addition, mere aggregation of another work not based on the Program
with the Program (or with a work based on the Program) on a volume of
a storage or distribution medium does not bring the other work under
the scope of this License.

  3. You may copy and distribute the Program (or a work based on it,
under Section 2) in object code or executable form under the terms of
Sections 1 and 2 above provided that you also do one of the following:

    a) Accompany it with the complete corresponding machine-readable
    source code, which must be distributed under the terms of Sections
    1 and 2 above on a medium customarily used for software interchange; or,

    b) Accompany it with a written offer, valid for at least three
    years, to give any third party, for a charge no more than your
    cost of physically performing source distribution, a complete
    machine-readable copy of the corresponding source code, to be
    distributed under the terms of Sections 1 and 2 above on a medium
    customarily used for software interchange; or,

    c) Accompany it with the information you received as to the offer
    to distribute corresponding source code.  (This alternative is
    allowed only for noncommercial distribution and only if you
    received the program in object code or executable form with such
    an offer, in accord with Subsection b above.)

The source code for a work means the preferred form of the work for
making modifications to it.  For an executable work, complete source
code means all the source code for all modules it contains, plus any
associated interface definition files, plus the scripts used to
control compilation and installation of the executable.  However, as a
special exception, the source code distributed need not include
anything that is normally distributed (in either source or binary
form) with the major components (compiler, kernel, and so on) of the
operating system on which the executable runs, unless that component
itself accompanies the executable.

If distribution of executable or object code is made by offering
access to copy from a designated place, then offering equivalent
access to copy the source code from the same place counts as
distribution of the source code, even though third parties are not
compelled to copy the source along with the object code.
.
  4. You may not copy, modify, sublicense, or distribute the Program
except as expressly provided under this License.  Any attempt
otherwise to copy, modify, sublicense or distribute the Program is
void, and will automatically terminate your rights under this License.
However, parties who have received copies, or rights, from you under
this License will not have their licenses terminated so long as such
parties remain in full compliance.

  5. You are not required to accept this License, since you have not
signed it.  However, nothing else grants you permission to modify or
distribute the Program or its derivative works.  These actions are
prohibited by law if you do not accept this License.  Therefore, by
modifying or distributing the Program (or any work based on the
Program), you indicate your acceptance of this License to do so, and
all its terms and conditions for copying, distributing or modifying
the Program or works based on it.

  6. Each time you redistribute the Program (or any work based on the
Program), the recipient automatically receives a license from the
original licensor to copy, distribute or modify the Program subject to
these terms and conditions.  You may not impose any further
restrictions on the recipients' exercise of the rights granted herein.
You are not responsible for enforcing compliance by third parties to
this License.

  7. If, as a consequence of a court judgment or allegation of patent
infringement or for any other reason (not limited to patent issues),
conditions are imposed on you (whether by court order, agreement or
otherwise) that contradict the conditions of this License, they do not
excuse you from the conditions of this License.  If you cannot
distribute so as to satisfy simultaneously your obligations under this
License and any other pertinent obligations, then as a consequence you
may not distribute the Program at all.  For example, if a patent
license would not permit royalty-free redistribution of the Program by
all those who receive copies directly or indirectly through you, then
the only way you could satisfy both it and this License would be to
refrain entirely from distribution of the Program.

If any portion of this section is held invalid or unenforceable under
any particular circumstance, the balance of the section is intended to
apply and the section as a whole is intended to apply in other
circumstances.

It is not the purpose of this section to induce you to infringe any
patents or other property right claims or to contest validity of any
such claims; this section has the sole purpose of protecting the
integrity of the free software distribution system, which is
implemented by public license practices.  Many people have made
generous contributions to the wide range of software distributed
through that system in reliance on consistent application of that
system; it is up to the author/donor to decide if he or she is willing
to distribute software through any other system and a licensee cannot
impose that choice.

This section is intended to make thoroughly clear what is believed to
be a consequence of the rest of this License.
.
  8. If the distribution and/or use of the Program is restricted in
certain countries either by patents or by copyrighted interfaces, the
original copyright holder who places the Program under this License
may add an explicit geographical distribution limitation excluding
those countries, so that distribution is permitted only in or among
countries not thus excluded.  In such case, this License incorporates
the limitation as if written in the body of this License.

  9. The Free Software Foundation may publish revised and/or new versions
of the General Public License from time to time.  Such new versions will
be similar in spirit to the present version, but may differ in detail to
address new problems or concerns.

Each version is given a distinguishing version number.  If the Program
specifies a version number of this License which applies to it and "any
later version", you have the option of following the terms and conditions
either of that version or of any later version published by the Free
Software Foundation.  If the Program does not specify a version number of
this License, you may choose any version ever published by the Free Software
Foundation.

  10. If you wish to incorporate parts of the Program into other free
programs whose distribution conditions are different, write to the author
to ask for permission.  For software which is copyrighted by the Free
Software Foundation, write to the Free Software Foundation; we sometimes
make exceptions for this.  Our decision will be guided by the two goals
of preserving the free status of all derivatives of our free software and
of promoting the sharing and reuse of software generally.

                NO WARRANTY

  11. BECAUSE THE PROGRAM IS LICENSED FREE OF CHARGE, THERE IS NO WARRANTY
FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE LAW.  EXCEPT WHEN
OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR OTHER PARTIES
PROVIDE THE PROGRAM "AS IS" WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED
OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.  THE ENTIRE RISK AS
TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU.  SHOULD THE
PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY SERVICING,
REPAIR OR CORRECTION.

  12. IN NO EVENT UNLESS REQUIRED BY APPLICABLE LAW OR AGREED TO IN WRITING
WILL ANY COPYRIGHT HOLDER, OR ANY OTHER PARTY WHO MAY MODIFY AND/OR
REDISTRIBUTE THE PROGRAM AS PERMITTED ABOVE, BE LIABLE TO YOU FOR DAMAGES,
INCLUDING ANY GENERAL, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES ARISING
OUT OF THE USE OR INABILITY TO USE THE PROGRAM (INCLUDING BUT NOT LIMITED
TO LOSS OF DATA OR DATA BEING RENDERED INACCURATE OR LOSSES SUSTAINED BY
YOU OR THIRD PARTIES OR A FAILURE OF THE PROGRAM TO OPERATE WITH ANY OTHER
PROGRAMS), EVEN IF SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE
POSSIBILITY OF SUCH DAMAGES.

             END OF TERMS AND CONDITIONS
.
        How to Apply These Terms to Your New Programs

  If you develop a new program, and you want it to be of the greatest
possible use to the public, the best way to achieve this is to make it
free software which everyone can redistribute and change under these terms.

  To do so, attach the following notices to the program.  It is safest
to attach them to the start of each source file to most effectively
convey the exclusion of warranty; and each file should have at least
the "copyright" line and a pointer to where the full notice is found.

    <one line to give the program's name and a brief idea of what it does.>
    Copyright (C) <year>  <name of author>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


Also add information on how to contact you by electronic and paper mail.

If the program is interactive, make it output a short notice like this
when it starts in an interactive mode:

    Gnomovision version 69, Copyright (C) year name of author
    Gnomovision comes with ABSOLUTELY NO WARRANTY; for details type `show w'.
    This is free software, and you are welcome to redistribute it
    under certain conditions; type `show c' for details.

The hypothetical commands `show w' and `show c' should show the appropriate
parts of the General Public License.  Of course, the commands you use may
be called something other than `show w' and `show c'; they could even be
mouse-clicks or menu items--whatever suits your program.

You should also get your employer (if you work as a programmer) or your
school, if any, to sign a "copyright disclaimer" for the program, if
necessary.  Here is a sample; alter the names:

  Yoyodyne, Inc., hereby disclaims all copyright interest in the program
  `Gnomovision' (which makes passes at compilers) written by James Hacker.

  <signature of Ty Coon>, 1 April 1989
  Ty Coon, President of Vice

This General Public License does not permit incorporating your program into
proprietary programs.  If your program is a subroutine library, you may
consider it more useful to permit linking proprietary applications with the
library.  If this is what you want to do, use the GNU Library General
Public License instead of this License.



        </textarea><br /><br /><br />
        <form action="index.php?step=4" method="post">
        <INPUT TYPE="submit" VALUE="I Agree To These Terms &gt;" />
        </form>
        <br />&nbsp;
        <br />&nbsp;</td>
        </tr>
        </table>
        </p>
        <?php
        break;

    case 4: // config.php set-up
        $vSubStep = isset($_REQUEST['substep']) ? trim($_REQUEST['substep']) : '';
        switch ($vSubStep) {
        case 'create':
            // Open config.php
            $configuration = file_get_contents('../config.php');

            // Now, replace the main text values with those given by user
            $find		= array('DB_NAME', 'DB_USER', 'DB_PW', 'DB_HOST', 'DB_TYPE', 'TABLEPRE', 'FULLURL', 'MAXATTACHSIZE', 'MAILER_TYPE', 'MAILER_USER', 'MAILER_PASS', 'MAILER_HOST', 'MAILER_PORT');
            $replace	= array($_REQUEST['db_name'], $_REQUEST['db_user'], $_REQUEST['db_pw'], $_REQUEST['db_host'], $_REQUEST['db_type'], $_REQUEST['table_pre'], $_REQUEST['fullurl'], $_REQUEST['maxattachsize'], $_REQUEST['MAILER_TYPE'], $_REQUEST['MAILER_USER'], $_REQUEST['MAILER_PASS'], $_REQUEST['MAILER_HOST'], $_REQUEST['MAILER_PORT']);
            $configuration = str_replace($find, $replace, $configuration);

            // Change Comment Output Option
            if (isset($_REQUEST['c_output'])) {
                $configuration = str_replace('COMMENTOUTPUT', 'true', $configuration);
            }else{
                $configuration = str_replace('COMMENTOUTPUT', 'false', $configuration);
            }

            // IP Reg
            if (isset($_REQUEST['ip_reg'])) {
                $configuration = str_replace('IPREG', 'on', $configuration);
            }else{
                $configuration = str_replace('IPREG', 'off', $configuration);
            }

            // IP Check
            if (isset($_REQUEST['ip_check'])) {
                $configuration = str_replace('IPCHECK', 'on', $configuration);
            }else{
                $configuration = str_replace('IPCHECK', 'off', $configuration);
            }

            // Allow Special Queries
            if (isset($_REQUEST['allowspecialq'])) {
                $configuration = str_replace('SPECQ', 'true', $configuration);
            }else{
                $configuration = str_replace('SPECQ', 'false', $configuration);
            }

            // Show Full Footer Info
            if (isset($_REQUEST['showfullinfo'])) {
                $configuration = str_replace('SHOWFULLINFO', 'true', $configuration);
            }else{
                $configuration = str_replace('SHOWFULLINFO', 'false', $configuration);
            }

            switch($_REQUEST['method']) {
                case 1: // Show configuration on screen
                    ?>

                        <html>
                            <head>
                                <title>
                                    XMB Installer
                                </title>
                                <style type="text/css">
                                    @import url("./install.css");
                                </style>
                            </head>
                            <body>
                                <img src="./splash.gif" border="0" />
                                <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH="100%" height="20">
                            <tr>
                                <td ALIGN=CENTER VALIGN=MIDDLE height="20">
                                    <p class="subTitle">XMB Configuration</p>
                                    <p>Copy the following into a new file, and call it &quot;config.php&quot;.&nbsp; Upload it to your webspace</p>
                                    <p>Then, click to continue to the final step!<br /></p>
<form action="index.php?step=5" method="post">
    <INPUT TYPE="submit" VALUE="Close Window" onClick="window.close()">
</form>
                                    </td>
                            </tr>
                            <tr>
                                <td bgcolor="#FFFFFF">
                                        <p><?php highlight_string($configuration); ?></p>
                                    </td>
                            </tr>
                        </table>

                    <?php
                    break;

                case 2:
                    ?>
                    <html>
                        <head>
                            <title>
                                XMB Installer
                            </title>
                            <style type="text/css">
                                @import url("./install.css");
                                </style>
                        </head>
                        <body>
                            <img src="./splash.gif" border="0" />
                    <?php

                    // Open a new file
                    $handle = @fopen(ROOT.'config.php', "w");
                    if (!$handle) {
                        error('Invalid Permissions', 'XMB cannot create your configuration file on the server as it does not have enough permissions to do so.  If you would like to try again, CHMOD the "config.php" file to <i>666</i> or select a different method', true);
                    }

                    // Write to file, then close file
                    fwrite($handle, $configuration);
                    fclose($handle);

                    // Continue to next step
                    echo 'Your XMB configuration has been created correctly on the server.';

                    ?>
                    <form action="index.php?step=5" method="post">
                        <INPUT TYPE="submit" VALUE="Close Window" onClick="window.close()">
                    </form>
                    <?php

                    break;

                case 3:
                    // Get size
                    $size = strlen($configuration);
                    // Put out headers for mime-type, filesize, forced-download, description and no-cache.
                    header("Content-type: application/octet-stream");
                    header("Content-length: $size");
                    header("Content-Disposition: attachment; filename=config.php");
                    header("Content-Description: XMB Configuration");
                    header("Pragma: no-cache");
                    header("Expires: 0");


                    // Start file download
                    echo $configuration;
                    exit();
                    break;

                default:
                    error('Configuration Method Missing', 'You did not specify a method of configuration.  Please go back and do so now. ', true);
                    break;
                } // for method
            break; // for substep4

        case 'continue':
            // show next button
            echo 'continue';
            break;


        default:
        // Get the DB types...
            $stream = opendir(ROOT.'db');
            while(false !== ($file = readdir($stream))) {
                if (strpos($file, '.php') && false === strpos($file, '.interface.php')) {
                    $dbs[] = $configuration = str_replace('.php', '', $file);
                }
            }
            $phpv = explode('.', phpversion());
            foreach ($dbs as $db) {
                if ( $db == 'mysql4' && $phpv[0] != 5) {
                    continue;
                }
                if ($db == 'mysql') {
                    $types[] = "<option SELECTED name=\"$db\">$db</option>";
                } else {
                    $types[] = "<option name=\"$db\">$db</option>";
                }
            }
            $types = '<select name="db_type">'.implode("\n", $types).'</select>';
            ?>

                        <html>
                        <head>
                            <title>
                            XMB Installer
                            </title>
                            <style type="text/css">
                            @import url("./install.css");
                            </style>
                        </head>
                        <body>
                        <img src="./splash.gif" border="0" />

                        <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH="769" >

                            <tr>
                                <td ALIGN=CENTER VALIGN=MIDDLE width="767">
                                    <p class="subTitle">XMB <?php echo X_VERSION;?>  Configuration</p><br />
                                    <p>Please complete the form below and choose "Configure XMB" to finish the installation with the values on this page, or choose 'Next Step' if you have already configured config.php correctly.</p>
                                    <p>If you choose "Configure XMB", a new window will pop-up.  When you return, choose 'Next Step' to continue the installation process.
                                    <p>Below, you can configure your config.php file if you haven't already done so. Please read the following before attempting to fill in the details.</p><br />
                                    <br />
                                    <br />
                                        <form action="index.php?step=4&substep=create" method="post" target="_blank">
                                            <div align="center">
                                                <center>
                                                    <table border="0" cellspacing="0" width="80%" cellpadding="0">
                                                        <tr>
                                                            <td width="101%" colspan="2"><p class="subTitle">Configuration Method</p></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="101%" colspan="2"><p>Please choose the configuration method you would like to use below<br />
                                                            1: This option will show the config.php information on screen, and you will need to copy it into your own configuration file<br /> <br />
                                                            2: This option will attempt to create config.php directly onto the server.&nbsp; For this to work, the current config.php must have a CHMOD Value of '<i>666</i>'.<br /> <br />
                                                            3: This option will let you download a complete config.php onto your computer based on the values below<br /> <br />
                                                            <select size="1" name="method">
                                                                <option value="1">1)&nbsp;  Show the  configuration on  screen</option>
                                                                <option value="2">2)&nbsp;  Attempt to create  config.php for me.</option>
                                                                <option value="3">3)&nbsp;  Download config.php  onto my computer</option>
                                                            </select><br />&nbsp;</font>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td width="101%" colspan="2"><p class="subTitle">Database Connection Settings</p></td>
                                                        </tr>
                                                        <tr>                <td width="48%">    <b>Database Name</b><br />Name of your database<br />&nbsp;</font></td>

                                                            <td width="53%"><input type="text" name="db_name" size="40"><br /> &nbsp;</td>
                                                            </font>
                                                        </tr>

                                                        <tr>
                                                            <td width="48%"><b>    Database Username</font></b>    <br />User used to access database<br />&nbsp;</font></td>
                                                            <td width="53%"><input type="text" name="db_user" size="40"><br />&nbsp;</td>
                                                        </tr>

                                                        <tr>
                                                            <td width="48%"><b>    Database Password</font></b>    <br /> Password used to access it<br />&nbsp;</font></td>
                                                            <td width="53%"><input type="password" name="db_pw" size="40"><br />&nbsp;</td>
                                                        </tr>

                                                        <tr>
                                                              <td width="48%"><b>    Database Host</font></b>    <br />Database Host, usually &quot;<i>localhost</i>&quot;<br />&nbsp;</font></td>
                                                              <td width="53%"><input type="text" name="db_host" size="40" value="localhost"><br />&nbsp;</td>
                                                        </tr>

                                                        <tr>
                                                            <td width="48%"><b>    Database Type</font></b>    <br />The type of server software run</font></td>
                                                            <td width="53%"><?php echo $types?><br />&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%">    <b>Table Prefix Setting</b><br />This setting is for the table prefix, for every board you have installed, this should be different<br />&nbsp;</font></td>
                                                            <td width="53%"><input type="text" name="table_pre" size="40" value="xmb_"><br />  &nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"></td>
                                                            <td width="53%"></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"></td>
                                                            <td width="53%"></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="101%" colspan="2"><p subTitle>Forum Settings</p></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"><b>Full URL</font></b><br />
                                                                    In this field put the full URL you see when you go to your boards, without the filename though.<br />Please remember to put in the trailing slash at  the end.&nbsp;See example.</font><br />&nbsp;&nbsp;</font></td>
                                                            <td width="53%"><input type="text" name="fullurl" size="40" value="<?php
                                                            echo 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')-7);
                                                            ?>"><br />&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"><b>    Maximum Attachment Size</font></b>
                                                                <br />    Maximum Attachment size permitted.&nbsp; (1024*1024) would be 1MB - (1024 * 1024 bytes = 1MB)</font>
                                                                                                    </td>
                                                            <td width="53%"><input name="maxattachsize" size="40" value="(1024*1024)"><br /></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"><b>    Comment Output</font></b>
                                                                    <br />This setting will turn off comments indicating templates. Tick the box to turn these on.&nbsp; Default: Off<br />&nbsp;</font>
                                                            </td>
                                                            <td width="53%"><input type="checkbox" name="c_output" value="TRUE"></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"><b>    IP Reg</font></b>    <br />Will only allow one registration per IP in 24 hours.&nbsp; Default: On<b><br />&nbsp;</b></font></td>
                                                            <td width="53%"><input type="checkbox" name="ip_reg" value="on" checked></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"><b>    IP Check</b><br /> Will check if your IP is a valid IPv4 or IPv6 type, if none of these. &nbsp;Default: Off<br /> &nbsp;</font></td>
                                                            <td width="53%"><input type="checkbox" name="ip_check" value="off" ></td>
                                                        </tr>

                                                        <tr>
                                                            <td width="48%"><b>    Allow Special Queries</b><br />This specifies if Special queries (eg. USE database and SHOW DATABASES) are allowed. &nbsp;Default: Off<br />&nbsp;</font></td>
                                                            <td width="53%"><input type="checkbox" name="allowspecialq" value="off"></td>
                                                        </tr>

                                                        <tr>
                                                            <td width="48%"><b>    Show Full Info</b><br />Will turn on/off the&quot;Alpha, Beta, Release, SP&quot; markings.&nbsp;Default: Off<br />&nbsp;</font></td>
                                                            <td width="53%"><input type="checkbox" name="showfullinfo" value="off" ></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"><b>    Mail-handler</b><br />some hosts prevent the direct use of sendmail, which php uses to send out emails by default. To get around this, we have included code which will contact a separate SMTP server of your choice, and will send the mail trough that...&nbsp;Default: 'default'<br />&nbsp;</font></td>
                                                            <td width="53%"><select name="MAILER_TYPE"><option value="default">Default</option><option value="socket_SMTP">socket SMTP</option></select></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"><b>    Mail-handler Username</b><br />Only required when the mail-handler is not set to 'default'<br />&nbsp;</font></td>
                                                            <td width="53%"><input type="text" name="MAILER_USER" value="username" /></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"><b>    Mail-handler Password</b><br />Only required when the mail-handler is not set to 'default'<br />&nbsp;</font></td>
                                                            <td width="53%"><input type="password" name="MAILER_PASS" value="password" /></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"><b>    Mail-handler Host</b><br />Only required when the mail-handler is not set to 'default'<br />&nbsp;</font></td>
                                                            <td width="53%"><input type="text" name="MAILER_HOST" value="mail.example.com" /></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="48%"><b>    Mail-handler Port</b><br />Only required when the mail-handler is not set to 'default'<br />&nbsp;</font></td>
                                                            <td width="53%"><input type="text" name="MAILER_PORT" value="25" /></td>
                                                        </tr>
                                                        <tr>
                                                            <td width="101%" colspan="2"><p align="center"><INPUT TYPE="submit" VALUE="Configure" name="submit"></form>&nbsp; <form action="index.php?step=5" method="POST"><input type="submit" value="Next Step"></form></td>
                                                        </tr>
                                                    </table>
                                                </center>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </table>

            <?php
         break;

        }

    break; // end case 4

    case 5: // Make the administrator set a username and password for the super admin user
        ?>
        <p style="text-align:center;">
        <table BORDER=0 CELLSPACING=0 CELLPADDING=0 COLS=1 WIDTH="80%" >
        <tr>
        <td ALIGN=CENTER VALIGN=CENTER>
        <br /><p class="subTitle">Create Super Administrator</p>
        <p>Please enter the username, password, and e-mail address for the Super Administrator.</p>
        <p><br /><br /><br /></font>

        <form action="index.php?step=6" method="post">
        <table>
            <tr>
                <td>Username:</td>
                <td><INPUT TYPE="Text" NAME="frmUsername" size="32"></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><INPUT TYPE="password" NAME="frmPassword" size="32"></td>
            </tr>
            <tr>
                <td>Confirm Password:</td>
                <td><INPUT TYPE="password" NAME="frmPasswordCfm" size="32"></td>
            </tr>
            <tr>
                <td>E-mail:</td>
                <td><INPUT TYPE="Text" NAME="frmEmail" size="32"></td>
            </tr>
            <tr>
                <td><input type="hidden" name="conf" value="<?php echo $conf?>"></td>
                <td><INPUT TYPE="submit" value="Next ... &gt;"></td>
            </tr>
        </table>
        </form>
        <br />&nbsp;
        <br />&nbsp;</td>
        </tr>
        </table>
        </p>
        <?php
        break;

    case 6: // remaining parts
        // first, let's check if we have right version of PHP
        show_act('Checking PHP version');
        $v = phpversion();
        $v = explode('.', $v);
        if ( $v[0] < 4 || ($v[0] == 4 && $v[1] < 1)) { // < 4.1.0
            show_result(X_INST_ERR);
            error('Minimal System Requirements mismatched', 'XMB noticed your system is using PHP version '.implode('.', $v).', the minimal required version to run XMB is PHP 4.1.0. Please upgrade your PHP install before continuing.', true);
        }
        show_result(X_INST_OK);

        // let's check if all files we need actually exist.
        $req['dirs'] = array('db', 'images', 'include');
        $req['files'] = array('buddy.php', 'config.php', 'cp.php', 'cp2.php', 'dump_attachments.php',
                                'editprofile.php', 'lang/English.lang.php', 'faq.php', 'forumdisplay.php',
                                'functions.php', 'header.php', 'index.php', 'member.php', 'memcp.php',
                                'misc.php', 'phpinfo.php', 'post.php',
                                'include/admin.user.inc.php', 'include/spelling.inc.php', 'include/u2u.inc.php',
                                'stats.php', 'templates.xmb', 'today.php', 'tools.php', 'topicadmin.php',
                                'u2u.php', 'u2uadmin.php', 'viewthread.php', 'xmb.php');

        show_act('Checking Directory Structure');
        foreach ($req['dirs'] as $dir) {
            if (!file_exists(ROOT.$dir)) {
                if ( $dir == 'images') {
                    show_result(X_INST_WARN);
                    error('Missing Directory', 'XMB could not locate the <i>/images</i> directory. Although this directory, and its contents are not vital to the functioning of XMB, we do recommend you upload it, so you can enjoy the full look and feel of each theme.', false);
                    show_act('Checking (remaining) Directory Structure');
                } else {
                    show_result(X_INST_ERR);
                    error('Missing Directory', 'XMB could not locate the <i>'.$dir.'</i> directory. Please upload this directory for XMB to proceed with the installation..', true);
                }
            } else {
                continue;
            }
        }
        show_result(X_INST_OK);

        show_act('Checking Required Files');
        foreach ($req['files'] as $file) {
            if (!file_exists(ROOT.$file)) {
                show_result(X_INST_ERR);
                error('Missing File', 'XMB could not locate the file <i>/'.$file.'</i>, this file is required for XMB to work properly. Please upload this file and restart installation.', true);
            }
        }
        show_result(X_INST_OK);

        // seems all files are here... so let's build the config.php file
        // ask if we should:
        // a) help create a config.php file
        // b) leave the configuration to be done manually offline
        // c) just continue, the forum has already been configured.

        if ( isset($conf) ) {
            switch($conf['responce']) {
                case 'create':
                    // create one
                    break;

                case 'manual':
                    // wait till the manual is done
                    // timeout here

                default:
                    break;
            }
        }

        // check db-connection.
        require ROOT.'config.php';

        // double check all stuff here
        show_act('Checking Database Files');
        if (!file_exists(ROOT.'db/'.$database.'.php')) {
            show_result(X_INST_ERR);
            error('Database connection', 'XMB could not locate the <i>/db/'.$database.'.php</i> file, you have configured xmb to use this database-type. For it to work you will need to upload the file, or change the config.php file to reflect a different choice.', true);
        }
        show_result(X_INST_OK);

        show_act('Checking Database API');
        // let's check if the actual functionality exists...
        $err = false;
        switch($database) {
            case 'mysql':
                if (!defined('MYSQL_NUM')) {
                    show_result(X_INST_ERR);
                    $err = true;
                }
                break;

            case 'mysql4':
                if (!defined('MYSQLI_NUM')) {
                    show_result(X_INST_ERR);
                    $err = true;
                }
                break;

            default:
                show_result(X_INST_ERR);
                error('Database Handler', 'Unknown handler provided', true);
                break;
        }
        if ( $err === true) {
            error('Database Handler', 'XMB has determined that your php installation does not support the functions required to use <i>'.$database.'</i> to store all data.', true);
            unset($err);
        }
        show_result(X_INST_OK);

        // let's check the connection itself.
        show_act('Checking Database Connection Security');
        if ( $dbuser == 'root') {
            show_result(X_INST_WARN);
            error('Security hazard', 'You have configured XMB to use root access to the database, this is a security hazard. If your server gets hacked, or php itself crashes, the config.php file might be available freely to anyone looking at it, and thus reveal your root username/password. Please consider making a new user for XMB to run as.', false);
        } else {
            show_result(X_INST_OK);
        }

        show_act('Checking Database Connection');
        switch($database) {
            case 'mysql':
                $link = mysql_connect($dbhost, $dbuser, $dbpw);
                if (!$link) {
                    show_result(X_INST_ERR);
                    error('Database Connection', 'XMB could not connect to the specified database. The database returned "error '.mysql_errno().': '.mysql_error(), true);
                } else {
                    show_result(X_INST_OK);
                }
                $i = mysql_get_server_info($link);
                mysql_close();
                show_act('Checking Database Version');
                $i = explode('.', $i);
                // min = 3.0
                if ( $i[0] < 3 || ($i[0] == 3 && $i[1] < 20)) {
                    show_result(X_INST_ERR);
                    error('Version mismatch', 'XMB requires MySQL version 3.20 or higher to work properly.', true);
                } else {
                    show_result(X_INST_OK);
                }
                break;

            case 'mysql4':
                $link = new mysqli($dbhost, $dbuser, $dbpw, $dbname);
                if (mysqli_connect_errno()) {
                    show_result(X_INST_ERR);
                    error('Database Connection', 'XMB could not connect to the specified database. The database returned "error '.mysqli_connect_errno().': '.mysqli_connect_error(), true);
                } else {
                    show_result(X_INST_OK);
                }
                $i = $link->server_info;
                $link->close();
                $i = explode('.', $i);
                // min = 3.0
                show_act('Checking Database Version');
                if ( $i[0] < 4 || ($i[0] == 4 && $i[1] < 1)) {
                    show_result(X_INST_ERR);
                    error('Version mismatch', 'XMB requires MySQL version 4.1 or higher to work properly with this database API. We recommend using the "mysql" API instead.', true);
                } else {
                    show_result(X_INST_OK);
                }
                break;

            default:
                show_result(X_INST_SKIP);
                break;
        }

        show_act('Checking Full Url Compliance');
        // let's check the $full_url :)
        $urlP = parse_url($full_url);
        if (($fp = @fsockopen($urlP['host'], $_SERVER['SERVER_PORT'], $errno, $errstr, 5)) === false) {
            show_result(X_INST_SKIP);
            error('Configuration Notice', 'XMB could not verify that you have your $full_url correctly configured; the connection was aborted. This test will be skipped.', false);
        } else {
        	socket_set_timeout($fp, 5);
        	$request	= array();
        	$request[]	= 'GET '.$urlP['path'].'install/index.php?action=verifyUrl HTTP/1.0';
        	$request[]	= 'Host: '.$urlP['host'];
        	$request[]	= 'Connection: close';
        	$request	= implode("\r\n", $request);
        	@fwrite($fp, $request, strlen($request));
        	$return = @fread($fp, 1024);
        	fclose($fp);
        	if($return == md5($_SERVER['SCRIPT_FILENAME'])) {
            	show_result(X_INST_OK);
            } else {
            	show_result(X_INST_WARN);
            	error('Configuration Notice', 'XMB could not verify that you have your $full_url correctly configured. If this is configured wrong, it will silently prevent logging in later on in the process. However, if you\'re sure it\'s correct, then you can safely ignore this notice.', false);
            }
        }

        // throw in all stuff then :)
        require './cinst.php';
        ?>
            <br />
            <br />
            <h3>Installation Complete</h3>
            <br />
            Please click <a href="../index.php">here</a> to go to your forum.
            <?php
        break;

    default:
        header('Location: index.php?step=1');
        exit;
}
?>
    </body>
</html>
