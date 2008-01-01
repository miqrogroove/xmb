<?php
/* $Id: upgrade.php,v 1.36 2004/10/19 14:26:20 ajv Exp $ */
/*
    XMB 1.9.1 Upgrade
    © 2001 - 2003 Aventure Media & The XMB Developement Team
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
function print_header() {
?>
<html>
    <head>
        <title>XMB Upgrader</title>
        <style type="text/css">
        @import url("./install.css");.style1 {color: #2E3E55}
.style7 {
	font-size: 16px;
	color: #333333;
}
        </style>
    </head>
    <body>
        <h3 align="left" class="style1"><span class="style7">XMB 1.9.1 Upgrader</span></h3>
        <hr noshade>
  <br>
          <?php
} 

function print_footer() {
?>
</body>
</html><?php 
}

function error($head, $msg, $die=true) {
    echo "\n";
    echo '<blockquote>';
    echo '<font class="progressErr">'.$head.'</font><br />';
    echo '<font class="progressWarn">'.$msg.'</font>';
    echo '</blockquote>';
    echo "\n"; 
    ob_flush();   
    if($die) {
        exit();
    }
}

function show_act($act) {
    $act .= str_repeat('.', (75-strlen($act)));
    echo '<font class="progress">'.$act;
    ob_flush();
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
    ob_flush();
}

define('ROOT', '../');
define('X_INST_ERR', 0);
define('X_INST_WARN', 1);
define('X_INST_OK', 2);
define('X_INST_SKIP', 3);

error_reporting(E_ALL&~E_NOTICE);

$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : '0';

switch($step) {
    case 1: // confirmation you WANT to upgrade
        print_header();
        echo 'Upload all 1.9.1 files to your forum dir, and re-configure the config.php file. When you\'re ready, press next.<br />';
        echo '<b>PLEASE NOTE:</b> <i>This upgrade may temporarily use up to two times your current database size (though only in extreme circumstances)</i>';
        echo '<br /><br />';
        
?><p class="subTitle">
            Version Check Information
        </p>

        <br />
        You are being connected to an information page which displays all the latest information in regards to this (and later) xmb versions.
        <br />
        <table style="width: 75%;">
        <tr>
        <td style="width: 30%;">
        Your Version:
        </td>
        <td style="width:70%;">
            <br>
			<b><i>1.9.1 Nexus</i></b>
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
        <form action="./upgrade.php?step=2" method="post">
        <INPUT TYPE="submit" VALUE="Upgrade to XMB 1.9.1" onClick="location.href = 'upgrade.php?step=2'" />
        </form>
        <br />&nbsp;
        </p>
<?php
        print_footer();
        break;
        
    case 2: // agreement
        print_header();
        ?>
        <p style="text-align:center;">
        <table BORDER=0 CELLSPACING=0 CELLPADDING=0 COLS=1 WIDTH="80%" >
        <tr>
        <td ALIGN=CENTER VALIGN=CENTER>
        <br /><b><font face="Verdana"><font color="#1C8BCB"><font size=-2>XMB 1.9.1 Agreement</font></font></font></b>
        <br /><font face="Verdana" size="-2">Please read
        over the agreement below, and if you agree to it select the button</font>
        <br /><font face="Verdana" size=-2>at the very
        bottom. By selecting the button, you agree to the terms below.<br /><br /><br /></font>
        <textarea cols="100" rows="30"  wrap='soft' name="agreement" style= "font-family: Verdana; font-size: 8pt" readonly>
       
        XMB 1.9.1 License (Updated August 2004)
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
        <form action="./upgrade.php?step=3" method="post">
        <INPUT TYPE="submit" VALUE="I Agree To These Terms" onClick="location.href='upgrade.php?step=3'">
        </form>
        <br />&nbsp;
        <br />&nbsp;</td>
        </tr>
        </table>
        </p>
        <?php        
        break;
        
    case 3:
        while(ob_get_level() > 0) {
            ob_end_flush();
        }
        ob_implicit_flush(1);
        
        print_header();
        show_act('Checking PHP version');
        define('COMMENTOUTPUT', false);
        define('IPREG', false);
        define('IPCHECK', false);
        define('SPECQ', false);
        define('SHOWFULLINFO', false);
        define('MAXATTACHSIZE', 1000000);
        
        require ROOT.'config.php';
        
        if ( $database == 'DB_TYPE' ) {
            show_result(X_INST_ERR);
            error('Incorrect Configuration', $database. ' is not a valid database type. Please configure config.php before continuing.', true);

        }
        require ROOT."db/$database.php";
        
        // Increase the time limit for long running queries to five minutes. This should be enough, but if you need
        // more, make it more.
        set_time_limit(300); 
        
        $v = phpversion();
        $v = explode('.', $v);
        if($v[0] < 4 || ($v[0] == 4 && $v[1] < 2)) { // < 4.2.0
            show_result(X_INST_ERR);
            error('Minimal System Requirements mismatched', 'XMB noticed your system is using PHP version '.implode('.', $v).', the minimal required version to run XMB is PHP 4.2.0. Please upgrade your PHP install before continuing.', true);
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
        foreach($req['dirs'] as $dir) {
            if(!file_exists(ROOT.$dir)) {
                if($dir == './images') {
                    show_result(X_INST_WARN);
                    error('Missing Directory', 'XMB could not locate the <i>./images</i> directory. Although this directory, and its contents are not vital to the functioning of XMB, we do recommend you upload it, so you can enjoy the full look and feel of each theme.', false);
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
        foreach($req['files'] as $file) {
            if(!file_exists(ROOT.$file)) {
                show_result(X_INST_ERR);
                error('Missing File', 'XMB could not locate the file <i>/'.$file.'</i>, this file is required for XMB to work properly. Please upload this file and restart installation.', true);
            }
        }
        show_result(X_INST_OK);
        
                // double check all stuff here
        show_act('Checking Database Files');
        if(!file_exists(ROOT.'db/'.$database.'.php')) {
            show_result(X_INST_ERR);
            error('Database connection', 'XMB could not locate the <i>/db/'.$database.'.php</i> file, you have configured xmb to use this database-type. For it to work you will need to upload the file, or change the config.php file to reflect a different choice.', true);
        }
        show_result(X_INST_OK);

        show_act('Checking Database API');
        // let's check if the actual functionality exists...
        $err = false;
        switch($database) {
            case 'mysql':
                if(!defined('MYSQL_NUM')) {
                    show_result(X_INST_ERR);
                    $err = true;
                }
                break;

            case 'mysql4':
                if(!defined('MYSQLI_NUM')) {
                    show_result(X_INST_ERR);
                    $err = true;
                }
                break;

            default:
                show_result(X_INST_ERR);
                error('Database Handler', 'Unknown handler provided', true);
                break;
        }    
        if($err === true) {
            error('Database Handler', 'XMB has determined that your php installation does not support the functions required to use <i>'.$database.'</i> to store all data.', true);
            unset($err);
        }
        show_result(X_INST_OK);

        // let's check the connection itself.
        show_act('Checking Database Connection Security');
        if($dbuser == 'root') {
            show_result(X_INST_WARN);
            error('Security hazard', 'You have configured XMB to use root access to the database, this is a security hazard. If your server gets hacked, or php itself crashes, the config.php file might be available freely to anyone looking at it, and thus reveal your root username/password. Please consider making a new user for XMB to run as.', false);
        } else {
            show_result(X_INST_OK);
        }

        show_act('Checking Database Connection');
        if ($dbname == 'DB_NAME' || $dbuser == 'DB_USER' || $dbpw == 'DB_PW' ) {
            show_result(X_INST_WARN);
            error('Incorrect Configuration', 'Most likely, config.php has not been correctly configured for database name, user or password. Please check these details.', false);
        }
        
		if ( $tablepre == 'TABLEPRE' ) {
            show_result(X_INST_ERR);
            error('Incorrect Configuration', 'config.php has not been correctly configured for $tablepre. Please change $tablepre to your preferred table prefix (usually \'xmb_\')', true);
        }
        
        $mysqlver = 0;
        
        switch($database) {
            case 'mysql':
                $link = mysql_connect($dbhost, $dbuser, $dbpw);
                if(!$link) {
                    show_result(X_INST_ERR);
                    error('Database Connection', 'XMB could not connect to the specified database. The database returned "error '.mysql_errno().': '.mysql_error(), true);
                } else {
                    show_result(X_INST_OK);
                }
                $i = mysql_get_server_info($link);
                mysql_close();
                show_act('Checking Database Version');
                $mysqlver = explode('.', $i);
                
                // min = 3.20
                if( $mysqlver[0] <= 3 && $mysqlver[1] < 20 ) {
                    show_result(X_INST_ERR);
                    error('Version mismatch', 'XMB requires MySQL version 3.20 or higher to work properly.', true);
                } else {
                    show_result(X_INST_OK);
                }
                break;

            case 'mysql4':
                $link = new mysqli($dbhost, $dbuser, $dbpw, $dbname);
                if(mysqli_connect_errno()) {
                    show_result(X_INST_ERR);
                    error('Database Connection', 'XMB could not connect to the specified database. The database returned "error '.mysqli_connect_errno().': '.mysqli_connect_error(), true);
                } else {
                    show_result(X_INST_OK);
                }
                $i = $link->server_info;
                $link->close();
				$mysqlver = explode('.', $i);

                // min = 4.1
                show_act('Checking Database Version');
                if($mysqlver[0] <= 4 && $mysqlver[1] < 10 ) {
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
        if(@file($full_url.'xmb.php') === false) {
            show_result(X_INST_WARN);
            error('Configuration Notice', 'XMB could not verify that you have your $full_url correctly configured. If this is configured wrong, it will silently prevent logging in later on in the process.', false);
        } else {
            show_result(X_INST_OK);
        }
                
// do the work! :)     
            
        show_act('Starting upgrade.');
        show_result(X_INST_OK);
        error('Lengthy operation Notice', 'This may take a while, please do not disturb.', false);
                    
        $db = new dbstuff;
        $db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
        
        require './upgrade.lib.php';

        show_act('Opening XMB Upgrade Template');
            if ($tablepre == 'TABLEPRE' ) {
                show_result(X_INST_ERR);
                error('Incorrect Configuration', '$tablepre is not configured in config.php. Please configure and try again.', true);
            }
        
            $u = new Upgrade(&$db, 'XMB_1_9_1.xmb', $tablepre);
            if ( $u ) {
                $tbl = $u->getTablesByTablepre($tablepre);
                if(count($tbl) == 0) {
                    show_result(X_INST_ERR);
                    error('Could not locate a valid installation of XMB. Please check if config.php is correctly configured', true);
                }
            } else {
                show_result(X_INST_ERR);
                error('Could not open XMB templates.', true);
            }
        show_result(X_INST_OK); 
        
		show_act('Fixing necessary indexes');
		$u->fixIndex();
        show_result(X_INST_OK);
        
        $r = $db->query("SELECT u2uid FROM `".$tablepre. "u2u`");
		$nr = $db->num_rows($r);
		$db->free_result($r);

        show_act("Upgrading $nr U2Us");
        $t = $u->doU2U();
        if ($t) {
            show_result(X_INST_OK);
        } else {
            show_result(X_INST_ERR);
            error('Could not upgrade U2U table.', true);
        }
        
        show_act("Check and remove sid");
		$u->removeSid();
        show_result(X_INST_OK);
        
        show_act('Creating missing tables');
			$tbl = $u->getTablesByTablepre($tablepre);
            $u->loadTables($tbl);
            $t = $u->getMissingTables();

            foreach($t['-'] as $k=>$table) {
                $query = $u->createTableQueryByTablename($table);
                $db->query($query);
            }

            // XXX ajv - should not drop any extra tables as the user may need them for a CMS or similar
            // foreach($t['+'] as $k=>$table) {
            //  $query = "DROP TABLE `".$tablepre.$table . "`";
            //  $db->query($query);
            // }
        show_result(X_INST_OK);
        
        show_act('Changing table schemas to 1.9.1<br />');
            foreach($tbl as $t) {
                $t = substr($t, strlen($tablepre));
                $d[$t] = $u->makeDiff($t);
            }

            $diff = $u->makeIntelligentDiff($d);
            foreach($diff as $table=>$type) {
                $qs = $u->createQueryFromDiff($diff[$table], $table);
                
                foreach($qs as $k=>$q) {
                    echo $q . "<br />";
                    $db->query($q);
                }
            }
        show_result(X_INST_OK);
    
        show_act('Restructure data (may take a long time)');
        foreach($tbl as $t) {
            $t = substr($t, strlen($tablepre));
            $d[$t] = $u->makeLocationDiff($t);
        }
        
        foreach($d as $tbl=>$diff) {
            if($diff === null) {
                continue;
            }
            
            $fromTable = $tablepre . $tbl;
            $tmpTable = $tablepre . $tbl . '_tmp';
            
            $db->query("RENAME TABLE `$fromTable` TO `$tmpTable`");
            $db->query("DROP TABLE IF EXISTS `$fromTable`");
            $db->query($u->createTableQueryByTablename($tbl));
            $q = $u->createLocationChangeQuery($fromTable, $tmpTable, $diff);
            $db->query($q);
            $db->query("DROP TABLE `$tmpTable`");
        }
        show_result(X_INST_OK);

        show_act('Fixing missing posts per page values');
		$u->fixPPP($mysqlver);
		show_result(X_INST_OK);

        show_act('Updating outgoing U2U status');
		$db->query("UPDATE `".$tablepre."members` SET saveogu2u='yes'");
		show_result(X_INST_OK);
		
		show_act('Fixing forum post permissions');
		$u->fixPostPerm();
		show_result(X_INST_OK);

		show_act("Updating themes to 1.9.1 themes");
		
		// clear out earlier attempts at 1.9.1 themes
		
		$u->deleteThemeByName('Iconic');
		$u->deleteThemeByName('Windows XP Silver');
		$u->deleteThemeByName('Reborn');
		$u->deleteThemeByName('one.point9');
		$u->deleteThemeByName('Grey Alliance');
		$u->deleteThemeByName('XMB Design X');
		$u->deleteThemeByName('Calm');
		$u->deleteThemeByName('Mystic');
		
		// 1.6 and 1.8 themes
		
		$u->deleteThemeByName('');					// delete blank themes
		$u->deleteThemeByName('XMBForum.com');
		$u->deleteThemeByName('AventureMedia');
		$u->deleteThemeByName('Windows XP Blue');
		
		$db->query("INSERT INTO ".$tablepre."themes VALUES ('', 'Calm',				'#304A78', '#7C8D93', '#546A72', '#DDE2E3', '#DDE2E3', '#546A72', '#DDE2E3', '#7C8D93', '#546A72', '#DDE2E3', '#DDE2E3', '1', '85%', '4', 'Verdana, Arial, Helvetica', '10px', 'xmblogo.gif', 'images/calm', 'images/smilies', '#DDE2E3');");
		$db->query("INSERT INTO ".$tablepre."themes VALUES ('', 'Grey Alliance',    '#F3F3F3', '#E5E5E5', '#E5E5E5', '#000000', '#000000', '#949494', '#000000', '#D5D5D5', 'bar.gif', '#000000', '#000000', '1', '90%', '4', 'Verdana, Arial, Helvetica', '10px', 'header.gif', 'images/greyalliance', 'images/smilies', '#000000');");
		$db->query("INSERT INTO ".$tablepre."themes VALUES ('', 'Iconic',           '#050C16', '#0A1C31', '#081627', '#FFFFFF', '#2E3E55', '#050C16', '#FFFFFF', '#050C16', 'catbg.gif', '#FFFFFF', '#FFFFFF', '1', '90%', '5', 'Verdana, Arial, Helvetica', '10px', 'iconicheader.gif', 'images/iconic', 'images/smilies', '#FFFFFF');");
		$db->query("INSERT INTO ".$tablepre."themes VALUES ('', 'Mystic',			'#FFFAFA', '#FFFAF0', '#FFF5EE', '#000000', '#000000', '#FFFAF0', '#000000', '#FFFFFF', 'bar.jpg', '#000000', '#000000', '1', '100%', '5', 'Verdana, Arial, Helvetica', '10px', 'banner.jpg', 'images/Mystic', 'images/smilies', '#000000');");
		$db->query("INSERT INTO ".$tablepre."themes VALUES ('', 'one.point9',       'bgg.gif', '#D5E0EC', '#D5E0EC', '#000000', '#315275', '#1C8BCB', '#FFFFFF', 'bg2.gif', 'bar.gif', '#2E3E55', '#000000', '1', '85%', '4', 'Verdana, Arial, Helvetica', '10px', 'banner.gif', 'images/onepoint9', 'images/smilies', '#FFFFFF');");
		$db->query("INSERT INTO ".$tablepre."themes VALUES ('', 'Reborn',			'#F1EDED', '#FFFFFF', '#4366C1', '#000000', '#000000', '#7699F4', '#f0f0f0', '#FFFFFF', 'catbar.gif', '#000000', '#000000', '1', '85%', '4', 'Verdana, Arial, Helvetica', '10px', 'logo.gif', 'images/reborn', 'images/smilies', '#FFFFFF');");
		$db->query("INSERT INTO ".$tablepre."themes VALUES ('', 'Windows XP Silver','#FFFFFF', '#EDF0F7', '#FFFFFF', '#000000', '#C4C8D4', '#FFFFFF', '#000000', '#FFFFFF', 'silverbar.gif', '#000000', '#000000', '1', '90%', '4', 'Verdana, Arial, Helvetica', '10px', 'xplogo.gif', 'images/xpsilver', 'images/smilies', '#000000');");
		$db->query("INSERT INTO ".$tablepre."themes VALUES ('', 'XMB Design X',		'#1D4E6D', '#123F59', '#11567E', '#EABC1B', '#183C53', '#17374C', '#F4F4F4', 'desbg.gif', 'categ.gif', '#F4F4F4', '#F4F4F4', '1', '90%', '6', 'Verdana, Arial, Helvetica', '12px', 'xmbheader.gif', 'images/designx', 'images/smilies', '#F4F4F4');");
	    
	    // Force everyone back to default 1.9.1 theme as we know it exists.
		$defaultTheme = $u->findThemeIDByName('XMB Design X');
	    $db->query("UPDATE `".$tablepre."members` SET theme='0'");
	    $db->query("UPDATE `".$tablepre."forums` SET theme='0'");
        $db->query("UPDATE `".$tablepre."settings` SET theme='" . $defaultTheme. "'");
        
show_result(X_INST_OK);

            
        show_act('Inserting 1.9.1 templates');
        
        $stream = fopen(ROOT.'templates.xmb','r');
        $file   = fread($stream, filesize(ROOT.'templates.xmb'));
                  fclose($stream);
        
        $db->query("TRUNCATE TABLE `".$tablepre."templates`");
        
        $templates = explode("|#*XMB TEMPLATE FILE*#|", $file);
        foreach($templates as $key=>$val) {
            $template = explode("|#*XMB TEMPLATE*#|", $val);
            $template[1] = addslashes($template[1]);
            $db->query("INSERT INTO `".$tablepre."templates` VALUES ('', '".addslashes($template[0])."', '".addslashes($template[1])."')");
        }
        $db->query("DELETE FROM `".$tablepre."templates` WHERE name=''");
        
        show_result(X_INST_OK);
        
        show_act('Fixing special ranks');
        
        $db->query("DELETE FROM `".$tablepre."ranks` WHERE title in ('Moderator', 'Super Moderator', 'Administrator', 'Super Administrator')");
        
        $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Moderator', -1, '', 6, 'yes', '');");
        $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Super Moderator', -1, '', 7, 'yes', '');");
        $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Administrator', -1, '', 8, 'yes', '');");
        $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Super Administrator', -1, '', 9, 'yes', '');");
        
        show_result(X_INST_OK);
        
        show_act('Removing install and upgrade files.');
        
        $req['dirs'] = array('install');
        $req['files'] = array('emailfriend.php', 'upgrade.php', 'upgrade.lib.php', 'install.css', 'XMB_1_9_1.xmb', 'createFile.php', 'readme.txt', 'cplogfile.php', 'splash.gif', 'agreement.html', 'cinst.php', 'install.html', 'main_start.gif', 'versioncheck.html');

        foreach($req['dirs'] as $dir) {
            rmFromDir(ROOT.$dir);
            if (file_exists(ROOT.$dir)) {
                show_result(X_INST_WARN);
                error('Remove Directory Error', 'XMB could not remove the <i>'.$dir.'</i> directory. Please delete this directory before using your board.', false);
            } 
        }

        foreach($req['files'] as $file) {
            @unlink(ROOT.$file);
            if (file_exists(ROOT.$file)) {
                show_result(X_INST_WARN);
                error('Remove File Error', 'XMB could not remove <i>'.$file.'</i> file. Please delete this file before using your board.', false);
            }
        }        
        
        show_result(X_INST_OK);
    
        echo '<br />Installation complete. Thank you for upgrading.';
        print_footer();
        break;
        
    default:
        header('location: upgrade.php?step=1');
        break;
}

function rmFromDir($path) {
    if(is_dir($path)) {
        $stream = opendir($path);
        while(($file = readdir($stream)) !== false) {
            if($file == '.' || $file == '..') {
                continue;
            }
            rmFromDir($path.'/'.$file);
        }
        @rmdir($path);
    } elseif(is_file($path)) {
        @unlink($path);
    }
}
?>