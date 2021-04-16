<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2021, The XMB Group
 * https://www.xmbforum2.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 **/

// Script Parameters
define('X_VERSION', '1.9.12');
define('X_VERSION_EXT', '1.9.12.03');
define('MYSQL_MIN_VER', '4.1.7');
define('PHP_MIN_VER', '7.0.0');
define('COPY_YEAR', '2001-2021');
$req['dirs'] = array('db', 'fonts', 'images', 'include', 'js', 'lang');
$req['files'] = array(
    'buddy.php',
    'config.php',
    'cp.php',
    'cp2.php',
    'css.php',
    'db/mysqli.php',
    'editprofile.php',
    'faq.php',
    'files.php',
    'forumdisplay.php',
    'header.php',
    'include/admin.inc.php',
    'include/attach.inc.php',
    'include/buddy.inc.php',
    'include/captcha.inc.php',
    'include/debug.inc.php',
    'include/functions.inc.php',
    'include/global.inc.php',
    'include/online.inc.php',
    'include/schema.inc.php',
    'include/sessions.inc.php',
    'include/smtp.inc.php',
    'include/spelling.inc.php',
    'include/tokens.inc.php',
    'include/translation.inc.php',
    'include/u2u.inc.php',
    'include/validate.inc.php',
    'include/validate-email.inc.php',
    'index.php',
    'install/cinst.php',
    'lang/English.lang.php',
    'lost.php',
    'member.php',
    'memcp.php',
    'misc.php',
    'post.php',
    'search.php',
    'stats.php',
    'templates.xmb',
    'today.php',
    'tools.php',
    'topicadmin.php',
    'u2u.php',
    'viewthread.php',
    'vtmisc.php'
);

// Script Constants
define('ROOT', '../');
define('DEBUG', TRUE);
define('LOG_MYSQL_ERRORS', FALSE);
define('X_INST_ERR', 0);
define('X_INST_WARN', 1);
define('X_INST_OK', 2);
define('X_INST_SKIP', 3);
define('COMMENTOUTPUT', false);
define('MAXATTACHSIZE', 256000);
define('IPREG', 'on');
define('IPCHECK', 'off');
define('SPECQ', false);
define('SHOWFULLINFO', false);
define('IN_CODE', true);

function error($head, $msg, $die=true) {
    echo "\n";
    echo '<h1 class="progressErr">'.$head.'</h1>';
    echo '<span class="progressWarn">'.$msg.'</span><br />';
    echo "\n";
    if ($die) {
        echo '
            </div>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="footer">
        <div class="top"><span></span></div>
        <div class="center-content">
            <span><a href="https://www.xmbforum2.com/" onclick="window.open(this.href); return false;"><strong><abbr title="eXtreme Message Board">XMB</abbr>
            Forum Software</strong></a>&nbsp;&copy; '.COPY_YEAR.' The XMB Group</span>
        </div>
        <div class="bottom"><span></span></div>
    </div>
</div>';
        exit();
    }
}

function show_act($act) {
    $act .= str_repeat('.', (75-strlen($act)));
    echo '<span class="progress">'.$act;
}

function show_result($type) {
    switch($type) {
    case 0:
        echo '<span class="progressErr">ERROR</span><br />';
        break;
    case 1:
        echo '<span class="progressWarn">WARNING</span><br />';
        break;
    case 2:
        echo '<span class="progressOk">OK</span><br />';
        break;
    case 3:
        echo '<span class="progressSkip">SKIPPED</span><br />';
        break;
    }
    echo "</span>\n";
}

/**
 * Haults the script if XMB is already installed.
 *
 * @since 1.9.11.09
 * @param string $database
 * @param string $dbhost
 * @param string $dbuser
 * @param string $dbpw
 * @param string $dbname
 * @param bool   $pconnect
 * @param string $tablepre
 */
function already_installed( $database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre ) {
    if ( 'mysql' != $database && 'mysqli' != $database ) return;

    // Force upgrade to mysqli
    if ( 'mysql' === $database ) $database = 'mysqli';

    if ( ! is_readable( ROOT."db/$database.php" ) ) return;

    if ( 'mysql' == $database ) {

        $link = @mysql_connect( $dbhost, $dbuser, $dbpw );
        if ( false === $link ) return;

        $result = mysql_select_db( $dbname );
        if ( false !== $result ) {
            $result = mysql_query( "SHOW TABLES LIKE '{$tablepre}settings'", $link );
            if ( false !== $result ) {
                $count = mysql_num_rows( $result );
                mysql_free_result( $result );
                if ( 1 === $count ) {
                    error( 'XMB Already Installed', 'An existing installation of XMB has been detected. Please <a href="../index.php">click here to go to your forum.</a><br />If you wish to overwrite this installation, please drop your settings table. To install another forum on the same database, enter a different table prefix in config.php.' );
                }
            }
        }
        mysql_close( $link );

    } else if ( 'mysqli' == $database ) {

        $link = @new mysqli( $dbhost, $dbuser, $dbpw, $dbname );
        if ( mysqli_connect_error() ) return;

        $result = $link->query( "SHOW TABLES LIKE '{$tablepre}settings'" );
        if ( false !== $result ) {
            $count = $result->num_rows;
            $result->free();
            if ( 1 === $count ) {
                error( 'XMB Already Installed', 'An existing installation of XMB has been detected. Please <a href="../index.php">click here to go to your forum.</a><br />If you wish to overwrite this installation, please drop your settings table. To install another forum on the same database, enter a different table prefix in config.php.' );
            }
        }
        $link->close();
    }
}

error_reporting(E_ALL&~E_NOTICE);

if (isset($_REQUEST['step']) && $_REQUEST['step'] < 7 && $_REQUEST['step'] != 4) {
    header("Content-type: text/html;charset=ISO-8859-1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>XMB Installer</title>
    <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
    <link rel="stylesheet" href="../images/install/install.css" type="text/css" media="screen"/>
</head>
<body>
<div id="main">
    <div id="header">
        <img src="../images/install/logo.png" alt="XMB" title="XMB" />
    </div>
<?php
}

if (is_readable(ROOT.'config.php')) {
    require(ROOT.'config.php');
    if (isset($database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre)) {
        already_installed( $database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre );
    }
}

$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 0;
$substep = isset($_REQUEST['substep']) ? $_REQUEST['substep'] : 0;
$vStep = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : 0;

switch($vStep) {
    case 1: // welcome
?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li class="current">Welcome</li>
                <li>Version Check</li>
                <li>License Agreement</li>
                <li>Configuration</li>
                <li>Create Super Administrator Account</li>
                <li>Install</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>Welcome to the XMB Installer</h1>
            <p>Welcome to the installer for XMB, the popular open-source lightweight message board software. Thank you for chosing XMB to foster your new community. The next steps will guide you through the installation of your XMB Powered Message Board.</p>
            <form action="./index.php?step=2" method="post">
                    <p class="button"><input type="submit" value="Start Installation &gt;" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
        break;

    case 2: // versioncheck
        ?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li class="current">Version Check</li>
                <li>License Agreement</li>
                <li>Configuration</li>
                <li>Create Super Administrator Account</li>
                <li>Install</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>Version Check Information</h1>
            <p>This page displays your version of XMB, and the latest version available from XMB. If there is a later version, XMB strongly recommends you do not install this version, but choose the latest stable release.</p>
            <ul>
                <li>Install This Version: XMB <?php echo X_VERSION_EXT;?></li>
                <li>Latest Available Version: <img src="https://www.xmbforum2.com/phpbin/xmbvc/vc.php?bg=f0f0f0&amp;fg=000000" alt="XMB Version Cant Be Found" style="position: relative; top: 8px;" /></li>
            </ul>
            <form action="./index.php?step=3" method="post">
                <p class="button"><input type="submit" value="Install XMB <?php echo X_VERSION;?> &gt;" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
        <?php
        break;

    case 3: // agreement
        ?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li>Version Check</li>
                <li class="current">License Agreement</li>
                <li>Configuration</li>
                <li>Create Super Administrator Account</li>
                <li>Install</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>XMB <?php echo X_VERSION;?> License Agreement</h1>
            <p>Please read over the agreement below, and if you agree to it select the button at the very bottom. By selecting the button, you agree to the terms below.</p>
            <textarea style="width: 90%;" rows="30"  name="agreement" style= "font-family: Verdana; font-size: 8pt; margin-left: 4%;" readonly="readonly">
XMB <?php echo X_VERSION;?>  License (Updated November 2007)
www.xmbforum2.com
----------------------------------------------

        GNU GENERAL PUBLIC LICENSE
           Version 3, 29 June 2007

Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
Everyone is permitted to copy and distribute verbatim copies
of this license document, but changing it is not allowed.

                Preamble

The GNU General Public License is a free, copyleft license for
software and other kinds of works.

The licenses for most software and other practical works are designed
to take away your freedom to share and change the works.  By contrast,
the GNU General Public License is intended to guarantee your freedom to
share and change all versions of a program--to make sure it remains free
software for all its users.  We, the Free Software Foundation, use the
GNU General Public License for most of our software; it applies also to
any other work released this way by its authors.  You can apply it to
your programs, too.

When we speak of free software, we are referring to freedom, not
price.  Our General Public Licenses are designed to make sure that you
have the freedom to distribute copies of free software (and charge for
them if you wish), that you receive source code or can get it if you
want it, that you can change the software or use pieces of it in new
free programs, and that you know you can do these things.

To protect your rights, we need to prevent others from denying you
these rights or asking you to surrender the rights.  Therefore, you have
certain responsibilities if you distribute copies of the software, or if
you modify it: responsibilities to respect the freedom of others.

For example, if you distribute copies of such a program, whether
gratis or for a fee, you must pass on to the recipients the same
freedoms that you received.  You must make sure that they, too, receive
or can get the source code.  And you must show them these terms so they
know their rights.

Developers that use the GNU GPL protect your rights with two steps:
(1) assert copyright on the software, and (2) offer you this License
giving you legal permission to copy, distribute and/or modify it.

For the developers' and authors' protection, the GPL clearly explains
that there is no warranty for this free software.  For both users' and
authors' sake, the GPL requires that modified versions be marked as
changed, so that their problems will not be attributed erroneously to
authors of previous versions.

Some devices are designed to deny users access to install or run
modified versions of the software inside them, although the manufacturer
can do so.  This is fundamentally incompatible with the aim of
protecting users' freedom to change the software.  The systematic
pattern of such abuse occurs in the area of products for individuals to
use, which is precisely where it is most unacceptable.  Therefore, we
have designed this version of the GPL to prohibit the practice for those
products.  If such problems arise substantially in other domains, we
stand ready to extend this provision to those domains in future versions
of the GPL, as needed to protect the freedom of users.

Finally, every program is threatened constantly by software patents.
States should not allow patents to restrict development and use of
software on general-purpose computers, but in those that do, we wish to
avoid the special danger that patents applied to a free program could
make it effectively proprietary.  To prevent this, the GPL assures that
patents cannot be used to render the program non-free.

The precise terms and conditions for copying, distribution and
modification follow.

           TERMS AND CONDITIONS

0. Definitions.

"This License" refers to version 3 of the GNU General Public License.

"Copyright" also means copyright-like laws that apply to other kinds of
works, such as semiconductor masks.

"The Program" refers to any copyrightable work licensed under this
License.  Each licensee is addressed as "you".  "Licensees" and
"recipients" may be individuals or organizations.

To "modify" a work means to copy from or adapt all or part of the work
in a fashion requiring copyright permission, other than the making of an
exact copy.  The resulting work is called a "modified version" of the
earlier work or a work "based on" the earlier work.

A "covered work" means either the unmodified Program or a work based
on the Program.

To "propagate" a work means to do anything with it that, without
permission, would make you directly or secondarily liable for
infringement under applicable copyright law, except executing it on a
computer or modifying a private copy.  Propagation includes copying,
distribution (with or without modification), making available to the
public, and in some countries other activities as well.

To "convey" a work means any kind of propagation that enables other
parties to make or receive copies.  Mere interaction with a user through
a computer network, with no transfer of a copy, is not conveying.

An interactive user interface displays "Appropriate Legal Notices"
to the extent that it includes a convenient and prominently visible
feature that (1) displays an appropriate copyright notice, and (2)
tells the user that there is no warranty for the work (except to the
extent that warranties are provided), that licensees may convey the
work under this License, and how to view a copy of this License.  If
the interface presents a list of user commands or options, such as a
menu, a prominent item in the list meets this criterion.

1. Source Code.

The "source code" for a work means the preferred form of the work
for making modifications to it.  "Object code" means any non-source
form of a work.

A "Standard Interface" means an interface that either is an official
standard defined by a recognized standards body, or, in the case of
interfaces specified for a particular programming language, one that
is widely used among developers working in that language.

The "System Libraries" of an executable work include anything, other
than the work as a whole, that (a) is included in the normal form of
packaging a Major Component, but which is not part of that Major
Component, and (b) serves only to enable use of the work with that
Major Component, or to implement a Standard Interface for which an
implementation is available to the public in source code form.  A
"Major Component", in this context, means a major essential component
(kernel, window system, and so on) of the specific operating system
(if any) on which the executable work runs, or a compiler used to
produce the work, or an object code interpreter used to run it.

The "Corresponding Source" for a work in object code form means all
the source code needed to generate, install, and (for an executable
work) run the object code and to modify the work, including scripts to
control those activities.  However, it does not include the work's
System Libraries, or general-purpose tools or generally available free
programs which are used unmodified in performing those activities but
which are not part of the work.  For example, Corresponding Source
includes interface definition files associated with source files for
the work, and the source code for shared libraries and dynamically
linked subprograms that the work is specifically designed to require,
such as by intimate data communication or control flow between those
subprograms and other parts of the work.

The Corresponding Source need not include anything that users
can regenerate automatically from other parts of the Corresponding
Source.

The Corresponding Source for a work in source code form is that
same work.

2. Basic Permissions.

All rights granted under this License are granted for the term of
copyright on the Program, and are irrevocable provided the stated
conditions are met.  This License explicitly affirms your unlimited
permission to run the unmodified Program.  The output from running a
covered work is covered by this License only if the output, given its
content, constitutes a covered work.  This License acknowledges your
rights of fair use or other equivalent, as provided by copyright law.

You may make, run and propagate covered works that you do not
convey, without conditions so long as your license otherwise remains
in force.  You may convey covered works to others for the sole purpose
of having them make modifications exclusively for you, or provide you
with facilities for running those works, provided that you comply with
the terms of this License in conveying all material for which you do
not control copyright.  Those thus making or running the covered works
for you must do so exclusively on your behalf, under your direction
and control, on terms that prohibit them from making any copies of
your copyrighted material outside their relationship with you.

Conveying under any other circumstances is permitted solely under
the conditions stated below.  Sublicensing is not allowed; section 10
makes it unnecessary.

3. Protecting Users' Legal Rights From Anti-Circumvention Law.

No covered work shall be deemed part of an effective technological
measure under any applicable law fulfilling obligations under article
11 of the WIPO copyright treaty adopted on 20 December 1996, or
similar laws prohibiting or restricting circumvention of such
measures.

When you convey a covered work, you waive any legal power to forbid
circumvention of technological measures to the extent such circumvention
is effected by exercising rights under this License with respect to
the covered work, and you disclaim any intention to limit operation or
modification of the work as a means of enforcing, against the work's
users, your or third parties' legal rights to forbid circumvention of
technological measures.

4. Conveying Verbatim Copies.

You may convey verbatim copies of the Program's source code as you
receive it, in any medium, provided that you conspicuously and
appropriately publish on each copy an appropriate copyright notice;
keep intact all notices stating that this License and any
non-permissive terms added in accord with section 7 apply to the code;
keep intact all notices of the absence of any warranty; and give all
recipients a copy of this License along with the Program.

You may charge any price or no price for each copy that you convey,
and you may offer support or warranty protection for a fee.

5. Conveying Modified Source Versions.

You may convey a work based on the Program, or the modifications to
produce it from the Program, in the form of source code under the
terms of section 4, provided that you also meet all of these conditions:

a) The work must carry prominent notices stating that you modified
it, and giving a relevant date.

b) The work must carry prominent notices stating that it is
released under this License and any conditions added under section
7.  This requirement modifies the requirement in section 4 to
"keep intact all notices".

c) You must license the entire work, as a whole, under this
License to anyone who comes into possession of a copy.  This
License will therefore apply, along with any applicable section 7
additional terms, to the whole of the work, and all its parts,
regardless of how they are packaged.  This License gives no
permission to license the work in any other way, but it does not
invalidate such permission if you have separately received it.

d) If the work has interactive user interfaces, each must display
Appropriate Legal Notices; however, if the Program has interactive
interfaces that do not display Appropriate Legal Notices, your
work need not make them do so.

A compilation of a covered work with other separate and independent
works, which are not by their nature extensions of the covered work,
and which are not combined with it such as to form a larger program,
in or on a volume of a storage or distribution medium, is called an
"aggregate" if the compilation and its resulting copyright are not
used to limit the access or legal rights of the compilation's users
beyond what the individual works permit.  Inclusion of a covered work
in an aggregate does not cause this License to apply to the other
parts of the aggregate.

6. Conveying Non-Source Forms.

You may convey a covered work in object code form under the terms
of sections 4 and 5, provided that you also convey the
machine-readable Corresponding Source under the terms of this License,
in one of these ways:

a) Convey the object code in, or embodied in, a physical product
(including a physical distribution medium), accompanied by the
Corresponding Source fixed on a durable physical medium
customarily used for software interchange.

b) Convey the object code in, or embodied in, a physical product
(including a physical distribution medium), accompanied by a
written offer, valid for at least three years and valid for as
long as you offer spare parts or customer support for that product
model, to give anyone who possesses the object code either (1) a
copy of the Corresponding Source for all the software in the
product that is covered by this License, on a durable physical
medium customarily used for software interchange, for a price no
more than your reasonable cost of physically performing this
conveying of source, or (2) access to copy the
Corresponding Source from a network server at no charge.

c) Convey individual copies of the object code with a copy of the
written offer to provide the Corresponding Source.  This
alternative is allowed only occasionally and noncommercially, and
only if you received the object code with such an offer, in accord
with subsection 6b.

d) Convey the object code by offering access from a designated
place (gratis or for a charge), and offer equivalent access to the
Corresponding Source in the same way through the same place at no
further charge.  You need not require recipients to copy the
Corresponding Source along with the object code.  If the place to
copy the object code is a network server, the Corresponding Source
may be on a different server (operated by you or a third party)
that supports equivalent copying facilities, provided you maintain
clear directions next to the object code saying where to find the
Corresponding Source.  Regardless of what server hosts the
Corresponding Source, you remain obligated to ensure that it is
available for as long as needed to satisfy these requirements.

e) Convey the object code using peer-to-peer transmission, provided
you inform other peers where the object code and Corresponding
Source of the work are being offered to the general public at no
charge under subsection 6d.

A separable portion of the object code, whose source code is excluded
from the Corresponding Source as a System Library, need not be
included in conveying the object code work.

A "User Product" is either (1) a "consumer product", which means any
tangible personal property which is normally used for personal, family,
or household purposes, or (2) anything designed or sold for incorporation
into a dwelling.  In determining whether a product is a consumer product,
doubtful cases shall be resolved in favor of coverage.  For a particular
product received by a particular user, "normally used" refers to a
typical or common use of that class of product, regardless of the status
of the particular user or of the way in which the particular user
actually uses, or expects or is expected to use, the product.  A product
is a consumer product regardless of whether the product has substantial
commercial, industrial or non-consumer uses, unless such uses represent
the only significant mode of use of the product.

"Installation Information" for a User Product means any methods,
procedures, authorization keys, or other information required to install
and execute modified versions of a covered work in that User Product from
a modified version of its Corresponding Source.  The information must
suffice to ensure that the continued functioning of the modified object
code is in no case prevented or interfered with solely because
modification has been made.

If you convey an object code work under this section in, or with, or
specifically for use in, a User Product, and the conveying occurs as
part of a transaction in which the right of possession and use of the
User Product is transferred to the recipient in perpetuity or for a
fixed term (regardless of how the transaction is characterized), the
Corresponding Source conveyed under this section must be accompanied
by the Installation Information.  But this requirement does not apply
if neither you nor any third party retains the ability to install
modified object code on the User Product (for example, the work has
been installed in ROM).

The requirement to provide Installation Information does not include a
requirement to continue to provide support service, warranty, or updates
for a work that has been modified or installed by the recipient, or for
the User Product in which it has been modified or installed.  Access to a
network may be denied when the modification itself materially and
adversely affects the operation of the network or violates the rules and
protocols for communication across the network.

Corresponding Source conveyed, and Installation Information provided,
in accord with this section must be in a format that is publicly
documented (and with an implementation available to the public in
source code form), and must require no special password or key for
unpacking, reading or copying.

7. Additional Terms.

"Additional permissions" are terms that supplement the terms of this
License by making exceptions from one or more of its conditions.
Additional permissions that are applicable to the entire Program shall
be treated as though they were included in this License, to the extent
that they are valid under applicable law.  If additional permissions
apply only to part of the Program, that part may be used separately
under those permissions, but the entire Program remains governed by
this License without regard to the additional permissions.

When you convey a copy of a covered work, you may at your option
remove any additional permissions from that copy, or from any part of
it.  (Additional permissions may be written to require their own
removal in certain cases when you modify the work.)  You may place
additional permissions on material, added by you to a covered work,
for which you have or can give appropriate copyright permission.

Notwithstanding any other provision of this License, for material you
add to a covered work, you may (if authorized by the copyright holders of
that material) supplement the terms of this License with terms:

a) Disclaiming warranty or limiting liability differently from the
terms of sections 15 and 16 of this License; or

b) Requiring preservation of specified reasonable legal notices or
author attributions in that material or in the Appropriate Legal
Notices displayed by works containing it; or

c) Prohibiting misrepresentation of the origin of that material, or
requiring that modified versions of such material be marked in
reasonable ways as different from the original version; or

d) Limiting the use for publicity purposes of names of licensors or
authors of the material; or

e) Declining to grant rights under trademark law for use of some
trade names, trademarks, or service marks; or

f) Requiring indemnification of licensors and authors of that
material by anyone who conveys the material (or modified versions of
it) with contractual assumptions of liability to the recipient, for
any liability that these contractual assumptions directly impose on
those licensors and authors.

All other non-permissive additional terms are considered "further
restrictions" within the meaning of section 10.  If the Program as you
received it, or any part of it, contains a notice stating that it is
governed by this License along with a term that is a further
restriction, you may remove that term.  If a license document contains
a further restriction but permits relicensing or conveying under this
License, you may add to a covered work material governed by the terms
of that license document, provided that the further restriction does
not survive such relicensing or conveying.

If you add terms to a covered work in accord with this section, you
must place, in the relevant source files, a statement of the
additional terms that apply to those files, or a notice indicating
where to find the applicable terms.

Additional terms, permissive or non-permissive, may be stated in the
form of a separately written license, or stated as exceptions;
the above requirements apply either way.

8. Termination.

You may not propagate or modify a covered work except as expressly
provided under this License.  Any attempt otherwise to propagate or
modify it is void, and will automatically terminate your rights under
this License (including any patent licenses granted under the third
paragraph of section 11).

However, if you cease all violation of this License, then your
license from a particular copyright holder is reinstated (a)
provisionally, unless and until the copyright holder explicitly and
finally terminates your license, and (b) permanently, if the copyright
holder fails to notify you of the violation by some reasonable means
prior to 60 days after the cessation.

Moreover, your license from a particular copyright holder is
reinstated permanently if the copyright holder notifies you of the
violation by some reasonable means, this is the first time you have
received notice of violation of this License (for any work) from that
copyright holder, and you cure the violation prior to 30 days after
your receipt of the notice.

Termination of your rights under this section does not terminate the
licenses of parties who have received copies or rights from you under
this License.  If your rights have been terminated and not permanently
reinstated, you do not qualify to receive new licenses for the same
material under section 10.

9. Acceptance Not Required for Having Copies.

You are not required to accept this License in order to receive or
run a copy of the Program.  Ancillary propagation of a covered work
occurring solely as a consequence of using peer-to-peer transmission
to receive a copy likewise does not require acceptance.  However,
nothing other than this License grants you permission to propagate or
modify any covered work.  These actions infringe copyright if you do
not accept this License.  Therefore, by modifying or propagating a
covered work, you indicate your acceptance of this License to do so.

10. Automatic Licensing of Downstream Recipients.

Each time you convey a covered work, the recipient automatically
receives a license from the original licensors, to run, modify and
propagate that work, subject to this License.  You are not responsible
for enforcing compliance by third parties with this License.

An "entity transaction" is a transaction transferring control of an
organization, or substantially all assets of one, or subdividing an
organization, or merging organizations.  If propagation of a covered
work results from an entity transaction, each party to that
transaction who receives a copy of the work also receives whatever
licenses to the work the party's predecessor in interest had or could
give under the previous paragraph, plus a right to possession of the
Corresponding Source of the work from the predecessor in interest, if
the predecessor has it or can get it with reasonable efforts.

You may not impose any further restrictions on the exercise of the
rights granted or affirmed under this License.  For example, you may
not impose a license fee, royalty, or other charge for exercise of
rights granted under this License, and you may not initiate litigation
(including a cross-claim or counterclaim in a lawsuit) alleging that
any patent claim is infringed by making, using, selling, offering for
sale, or importing the Program or any portion of it.

11. Patents.

A "contributor" is a copyright holder who authorizes use under this
License of the Program or a work on which the Program is based.  The
work thus licensed is called the contributor's "contributor version".

A contributor's "essential patent claims" are all patent claims
owned or controlled by the contributor, whether already acquired or
hereafter acquired, that would be infringed by some manner, permitted
by this License, of making, using, or selling its contributor version,
but do not include claims that would be infringed only as a
consequence of further modification of the contributor version.  For
purposes of this definition, "control" includes the right to grant
patent sublicenses in a manner consistent with the requirements of
this License.

Each contributor grants you a non-exclusive, worldwide, royalty-free
patent license under the contributor's essential patent claims, to
make, use, sell, offer for sale, import and otherwise run, modify and
propagate the contents of its contributor version.

In the following three paragraphs, a "patent license" is any express
agreement or commitment, however denominated, not to enforce a patent
(such as an express permission to practice a patent or covenant not to
sue for patent infringement).  To "grant" such a patent license to a
party means to make such an agreement or commitment not to enforce a
patent against the party.

If you convey a covered work, knowingly relying on a patent license,
and the Corresponding Source of the work is not available for anyone
to copy, free of charge and under the terms of this License, through a
publicly available network server or other readily accessible means,
then you must either (1) cause the Corresponding Source to be so
available, or (2) arrange to deprive yourself of the benefit of the
patent license for this particular work, or (3) arrange, in a manner
consistent with the requirements of this License, to extend the patent
license to downstream recipients.  "Knowingly relying" means you have
actual knowledge that, but for the patent license, your conveying the
covered work in a country, or your recipient's use of the covered work
in a country, would infringe one or more identifiable patents in that
country that you have reason to believe are valid.

If, pursuant to or in connection with a single transaction or
arrangement, you convey, or propagate by procuring conveyance of, a
covered work, and grant a patent license to some of the parties
receiving the covered work authorizing them to use, propagate, modify
or convey a specific copy of the covered work, then the patent license
you grant is automatically extended to all recipients of the covered
work and works based on it.

A patent license is "discriminatory" if it does not include within
the scope of its coverage, prohibits the exercise of, or is
conditioned on the non-exercise of one or more of the rights that are
specifically granted under this License.  You may not convey a covered
work if you are a party to an arrangement with a third party that is
in the business of distributing software, under which you make payment
to the third party based on the extent of your activity of conveying
the work, and under which the third party grants, to any of the
parties who would receive the covered work from you, a discriminatory
patent license (a) in connection with copies of the covered work
conveyed by you (or copies made from those copies), or (b) primarily
for and in connection with specific products or compilations that
contain the covered work, unless you entered into that arrangement,
or that patent license was granted, prior to 28 March 2007.

Nothing in this License shall be construed as excluding or limiting
any implied license or other defenses to infringement that may
otherwise be available to you under applicable patent law.

12. No Surrender of Others' Freedom.

If conditions are imposed on you (whether by court order, agreement or
otherwise) that contradict the conditions of this License, they do not
excuse you from the conditions of this License.  If you cannot convey a
covered work so as to satisfy simultaneously your obligations under this
License and any other pertinent obligations, then as a consequence you may
not convey it at all.  For example, if you agree to terms that obligate you
to collect a royalty for further conveying from those to whom you convey
the Program, the only way you could satisfy both those terms and this
License would be to refrain entirely from conveying the Program.

13. Use with the GNU Affero General Public License.

Notwithstanding any other provision of this License, you have
permission to link or combine any covered work with a work licensed
under version 3 of the GNU Affero General Public License into a single
combined work, and to convey the resulting work.  The terms of this
License will continue to apply to the part which is the covered work,
but the special requirements of the GNU Affero General Public License,
section 13, concerning interaction through a network will apply to the
combination as such.

14. Revised Versions of this License.

The Free Software Foundation may publish revised and/or new versions of
General Public License from time to time.  Such new versions will
be similar in spirit to the present version, but may differ in detail to
address new problems or concerns.

Each version is given a distinguishing version number.  If the
Program specifies that a certain numbered version of the GNU General
Public License "or any later version" applies to it, you have the
option of following the terms and conditions either of that numbered
version or of any later version published by the Free Software
Foundation.  If the Program does not specify a version number of the
GNU General Public License, you may choose any version ever published
by the Free Software Foundation.

If the Program specifies that a proxy can decide which future
versions of the GNU General Public License can be used, that proxy's
public statement of acceptance of a version permanently authorizes you
to choose that version for the Program.

Later license versions may give you additional or different
permissions.  However, no additional obligations are imposed on any
author or copyright holder as a result of your choosing to follow a
later version.

15. Disclaimer of Warranty.

THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY
APPLICABLE LAW.  EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT
HOLDERS AND/OR OTHER PARTIES PROVIDE THE PROGRAM "AS IS" WITHOUT WARRANTY
OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO,
THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE.  THE ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM
IS WITH YOU.  SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF
ALL NECESSARY SERVICING, REPAIR OR CORRECTION.

16. Limitation of Liability.

IN NO EVENT UNLESS REQUIRED BY APPLICABLE LAW OR AGREED TO IN WRITING
WILL ANY COPYRIGHT HOLDER, OR ANY OTHER PARTY WHO MODIFIES AND/OR CONVEYS
THE PROGRAM AS PERMITTED ABOVE, BE LIABLE TO YOU FOR DAMAGES, INCLUDING ANY
GENERAL, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES ARISING OUT OF THE
USE OR INABILITY TO USE THE PROGRAM (INCLUDING BUT NOT LIMITED TO LOSS OF
DATA OR DATA BEING RENDERED INACCURATE OR LOSSES SUSTAINED BY YOU OR THIRD
PARTIES OR A FAILURE OF THE PROGRAM TO OPERATE WITH ANY OTHER PROGRAMS),
EVEN IF SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE POSSIBILITY OF
SUCH DAMAGES.

17. Interpretation of Sections 15 and 16.

If the disclaimer of warranty and limitation of liability provided
above cannot be given local legal effect according to their terms,
reviewing courts shall apply local law that most closely approximates
an absolute waiver of all civil liability in connection with the
Program, unless a warranty or assumption of liability accompanies a
copy of the Program in return for a fee.

         END OF TERMS AND CONDITIONS

How to Apply These Terms to Your New Programs

If you develop a new program, and you want it to be of the greatest
possible use to the public, the best way to achieve this is to make it
free software which everyone can redistribute and change under these terms.

To do so, attach the following notices to the program.  It is safest
to attach them to the start of each source file to most effectively
state the exclusion of warranty; and each file should have at least
the "copyright" line and a pointer to where the full notice is found.

<one line to give the program's name and a brief idea of what it does.>
Copyright (C) <year>  <name of author>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Also add information on how to contact you by electronic and paper mail.

If the program does terminal interaction, make it output a short
notice like this when it starts in an interactive mode:

<program>  Copyright (C) <year>  <name of author>
This program comes with ABSOLUTELY NO WARRANTY; for details type `show w'.
This is free software, and you are welcome to redistribute it
under certain conditions; type `show c' for details.

The hypothetical commands `show w' and `show c' should show the appropriate
parts of the General Public License.  Of course, your program's commands
might be different; for a GUI interface, you would use an "about box".

You should also get your employer (if you work as a programmer) or school,
if any, to sign a "copyright disclaimer" for the program, if necessary.
For more information on this, and how to apply and follow the GNU GPL, see
<http://www.gnu.org/licenses/>.

The GNU General Public License does not permit incorporating your program
into proprietary programs.  If your program is a subroutine library, you
may consider it more useful to permit linking proprietary applications with
the library.  If this is what you want to do, use the GNU Lesser General
Public License instead of this License.  But first, please read
<http://www.gnu.org/philosophy/why-not-lgpl.html>.

            </textarea>
            <form action="index.php?step=4" method="post">
                <p class="button"><input type="submit" value="I Agree To These Terms &gt;" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
        <?php
        break;

    case 4: // config.php set-up
        $vSubStep = isset($_REQUEST['substep']) ? trim($_REQUEST['substep']) : '';
        switch($vSubStep) {
        case 'create':
            // Open config.php
            $configuration = file_get_contents(ROOT.'config.php');

            // Now, replace the main text values with those given by user
            $find = array('DB/NAME', 'DB/USER', 'DB/PW', "= 'localhost';", 'TABLE/PRE', 'FULLURL', "= 'default';", 'MAILER_USER', 'MAILER_PASS', 'MAILER_HOST', 'MAILER_PORT');
            $replace = array($_REQUEST['db_name'], $_REQUEST['db_user'], $_REQUEST['db_pw'], "= '{$_REQUEST['db_host']}';", $_REQUEST['table_pre'], $_REQUEST['fullurl'], "= '{$_REQUEST['MAILER_TYPE']}';", $_REQUEST['MAILER_USER'], $_REQUEST['MAILER_PASS'], $_REQUEST['MAILER_HOST'], $_REQUEST['MAILER_PORT']);
            $configuration = str_replace($find, $replace, $configuration);

            // Change Comment Output Option
            if (isset($_REQUEST['c_output'])) {
                $configuration = str_replace("comment_output = FALSE;", "comment_output = TRUE;", $configuration);
            }

            // IP Check
            if (isset($_REQUEST['ip_check'])) {
                $configuration = str_replace("ipcheck        = 'off';", "ipcheck        = 'on';", $configuration);
            }

            // Allow Special Queries
            if (isset($_REQUEST['allowspecialq'])) {
                $configuration = str_replace("allow_spec_q   = FALSE;", "allow_spec_q   = TRUE;", $configuration);
            }

            // Show Full Footer Info
            if (!isset($_REQUEST['showfullinfo'])) {
                $configuration = str_replace("show_full_info = TRUE;", "show_full_info = FALSE;", $configuration);
            }

            switch($_REQUEST['method']) {
                case 1: // Show configuration on screen
                    header("Content-type: text/html;charset=ISO-8859-1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>XMB Installer</title>
    <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <link rel="stylesheet" href="../images/install/install.css" type="text/css" media="screen"/>
</head>
<body>
<div id="main">
    <div id="header">
        <img src="../images/install/logo.png" alt="XMB" title="XMB" />
    </div>
    <div id="configure">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>XMB Configuration</h1>
            <p>Copy the following into to a new file, and call it &quot;config.php&quot;.&nbsp; Upload it to the root of your XMB directory. Then, click to continue to the next steps.</p>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="config">
        <div class="top"><span></span></div>
        <div class="center-content">
            <textarea readonly="readonly" style="width: 90%;" rows="100"><?php echo($configuration); ?></textarea>
            <form action="index.php?step=5" method="post">
                <p class="button"><input type="submit" value="Close Window" onclick="window.close()"></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
                    break;

                case 2:
                    header("Content-type: text/html;charset=ISO-8859-1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>XMB Installer</title>
    <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <link rel="stylesheet" href="../images/install/install.css" type="text/css" media="screen, projection" />
</head>
<body>
<div id="main">
    <div id="header">
        <img src="../images/install/logo.png" alt="XMB" title="XMB" />
    </div>
    <div id="configure">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>XMB Configuration</h1>
            <div>
                <p>
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
                </p>
            </div>
            <form action="index.php?step=5" method="post">
                <p class="button"><input type="submit" value="Close Window" onclick="window.close()"></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
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
            header("Content-type: text/html;charset=ISO-8859-1");
            $scheme = 'http';
            if ( ! empty( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {
                $scheme = 'https';
            }
            // Get the DB types...
            $types = '<select name="db_type"><option selected="selected" value="mysql">mysql</option></select>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
    <title>XMB Installer</title>
    <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <link rel="stylesheet" href="../images/install/install.css" type="text/css" media="screen, projection" />
</head>
<body>
<div id="main">
    <div id="header">
        <img src="../images/install/logo.png" alt="XMB" title="XMB" />
    </div>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li>Version Check</li>
                <li>License Agreement</li>
                <li class="current">Configuration</li>
                <li>Create Super Administrator Account</li>
                <li>Install</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>XMB Configuration</h1>
            <p>If you have not configured your config.php file, complete the form below and select "Configure XMB". If you have already configured config.php correctly, you may skip this process and select "Next Step" below. If you select "Configure XMB", a new window will pop up. When you return, select "Next Step" to continue the installation process.</p>
            <form action="index.php?step=4&amp;substep=create" method="post" target="_blank">
                <table cellspacing="1px">
                    <tr>
                        <td colspan="2">
                            <h1>Configuration Method</h1>
                            <p>Please choose the Configuration Method you would like to use below.</p>
                            <ol>
                                <li>Show the configuration on screen: This option will show the config.php information on screen so that you can copy it into your own config.php file</li>
                                <li>Attempt to create config.php: This option will attempt to create config.php directly onto the server. For this to work, the current config.php must have a CHMOD Value of 666</li>
                                <li>Download config.php: This option will create config.php and allow you to download the complete file onto your computer. Once downloaded you will need to upload the file to the root of your XMB directory</li>
                            </ol>
                            <p>
                                <select size="1" name="method">
                                    <option value="1">1)&nbsp;  Show the  configuration on  screen</option>
                                    <option value="2">2)&nbsp;  Attempt to create  config.php for me.</option>
                                    <option value="3">3)&nbsp;  Download config.php  onto my computer</option>
                                </select>
                            </p>
                        </td>
                    </tr>
                    <tr class="category">
                        <td colspan="2">Database Connection Settings</td>
                    </tr>
                    <tr>
                        <td>Database Name<br /><span>Name of your database</span></td>
                        <td><input type="text" name="db_name" size="40" /></td>
                    </tr>
                    <tr>
                        <td>Database Username<br /><span>User used to access database</span></td>
                        <td><input type="text" name="db_user" size="40" /></td>
                    </tr>
                    <tr>
                        <td>Database Password<br /><span>Password for the Database User</span></td>
                        <td><input type="password" name="db_pw" size="40" /></td>
                    </tr>
                    <tr>
                        <td>Database Host<br /><span>Database host location, usually "localhost"</span></td>
                        <td><input type="text" name="db_host" size="40" value="localhost" /></td>
                    </tr>
                    <tr>
                        <td>Database Type<br /><span>The type of database server run. At this time, only mysql is supported</span></td>
                        <td><?php echo $types?></td>
                    </tr>
                    <tr>
                        <td>Database Table Prefix<br /><span>Specify a prefix for this board's database tables.</span></td>
                        <td><input type="text" name="table_pre" size="40" value="xmb_" /></td>
                    </tr>
                    <tr class="category">
                        <td colspan="2">Forum Settings</td>
                    </tr>
                    <tr>
                        <td>Full URL<br /><span>Put the full URL of your boards here, without any file names. Be sure to include a slash at the end.</span></td>
                        <td><input type="text" name="fullurl" size="40" value="<?php echo "$scheme://".$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')-7);?>" /></td>
                    </tr>
                    <tr>
                        <td>Maximum Attachment Size<br /><span>Enter the maximum allowed attachment size for your board here. (250*1024) for example, would be 250KB</span></td>
                        <td><input name="maxattachsize" size="40" value="(250*1024)" /></td>
                    </tr>
                    <tr>
                        <td>Comment Output<br /><span>This setting will allow you to chose whether you want comments indicating templates. Default: Off</span></td>
                        <td><input type="checkbox" name="c_output" value="TRUE" /></td>
                    </tr>
                    <tr>
                        <td>Restrict User Registration Per IP<br /><span>This will restrict registration of users to one per IP address every 24 hours. Default: On</span></td>
                        <td><input type="checkbox" name="ip_reg" value="on" checked="checked" /></td>
                    </tr>
                    <tr>
                        <td>IP Validation Check<br /><span>This will check users' IP addresses are valid IPv4 or IPv6 type, if none of these. Default: Off</span></td>
                        <td><input type="checkbox" name="ip_check" value="off" /></td>
                    </tr>
                    <tr>
                        <td>Allow Special Queries<br /><span>This specifies whether special database queries such as USE database and SHOW database are allowed. Default: off</span></td>
                        <td><input type="checkbox" name="allowspecialq" value="off" /></td>
                    </tr>
                    <tr>
                        <td>Show Full XMB Version Info<br /><span>This will show the full version information of your XMB Board. Default: Off</span></td>
                        <td><input type="checkbox" name="showfullinfo" value="off" /></td>
                    </tr>
                    <tr class="category">
                        <td colspan="2">XMB E-Mail Settings</td>
                    </tr>
                    <tr>
                        <td colspan="2"><p>XMB by default uses sendmail to send email from the board. As some hosts don't allow direct use of sendmail, you may chose to configure XMB to use SMTP to send E-mail instead. Please choose your configuration below.</p></td>
                    </tr>
                    <tr>
                        <td>Mail Handler<br /><span>Chose your mail handler. If you wish to use the default sendmail, select "Default" and disregard the configuration options below. If you chose SMTP, please complete the options below. Default: "Default"</span></td>
                        <td><select name="MAILER_TYPE"><option value="default">Default</option><option value="socket_SMTP">socket SMTP</option></select></td>
                    </tr>
                    <tr>
                        <td>SMTP Username:</td>
                        <td><input type="text" name="MAILER_USER" value="username" /></td>
                    </tr>
                    <tr>
                        <td>SMTP Password:</td>
                        <td><input type="password" name="MAILER_PASS" value="password" /></td>
                    </tr>
                    <tr>
                        <td>SMTP Host:</td>
                        <td><input type="text" name="MAILER_HOST" value="mail.example.com" /></td>
                    </tr>
                    <tr>
                        <td>SMTP Port:</td>
                        <td><input type="text" name="MAILER_PORT" value="25" /></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="configure"><input type="submit" value="Configure" name="submit" /></td>
                    </tr>
                </table>
            </form>
            <form action="index.php?step=5" method="post">
                <p class="button"><input type="submit" value="Next Step" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>

<?php
            break;
        }
        break; // end case 4

    case 5: // Make the administrator set a username and password for the super admin user

        require_once(ROOT.'config.php');

        $config_array = array(
            'dbname' => 'DB/NAME',
            'dbuser' => 'DB/USER',
            'dbpw' => 'DB/PW',
            'dbhost' => 'DB_HOST',
            'database' => 'DB_TYPE',
            'tablepre' => 'TABLE/PRE',
            'full_url' => 'FULLURL',
            'ipcheck' => 'IPCHECK',
            'allow_spec_q' => 'SPECQ',
            'show_full_info' => 'SHOWFULLINFO',
            'comment_output' => 'COMMENTOUTPUT'
        );
        foreach($config_array as $key => $value) {
            if (${$key} === $value) {
                error('Incorrect Configuration', 'XMB noticed that your config.php file is not fully configured.<br />Please go back to the previous step and follow the instructions carefully.<br />Be sure to click the button labeled "Configure" before proceeding.', TRUE);
            }
        }

        $versionlong = '';
        require ROOT.'include/debug.inc.php';
        $array = parse_url($full_url);
        if (!isset($array['path'])) {
            $array['path'] = '/';
        }
        if (strpos($array['host'], '.') === FALSE || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $array['host'])) {
            $array['host'] = '';
        } elseif (substr($array['host'], 0, 4) === 'www.') {
            $array['host'] = substr($array['host'], 3);
        }
        debugURLsettings(($array['scheme'] == 'https'), $array['host'], $array['path']);
        unset($array);


        ?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li>Version Check</li>
                <li>License Agreement</li>
                <li>Configuration</li>
                <li class="current">Create Super Administrator Account</li>
                <li>Install</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>Create Super Administrator Account</h1>
            <p>Please fill out the Username, Password, and E-Mail account for the first Super Administrator account for your message board. This will be the account you use to first login to your board</p>
            <script type="text/javascript">
            <!--//--><![CDATA[//><!--
            function disableButton() {
                var newAttr = document.createAttribute("disabled");
                newAttr.nodeValue = "disabled";
                document.getElementById("submit1").setAttributeNode(newAttr);
                return true;
            }
            //--><!]]>
            </script>
            <form action="index.php?step=6" method="post" onsubmit="disableButton();">
                <table cellspacing="1px">
                    <tr>
                        <td>Username:</td>
                        <td><input type="text" name="frmUsername" size="32" /></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input type="password" name="frmPassword" size="32" /></td>
                    </tr>
                    <tr>
                        <td>Confirm Password:</td>
                        <td><input type="password" name="frmPasswordCfm" size="32" /></td>
                    </tr>
                    <tr>
                        <td>E-Mail Address:</td>
                        <td><input type="text" name="frmEmail" size="32" /></td>
                    </tr>
                </table>
                <p class="button"><input type="submit" value="Begin Installation &gt;" id="submit1" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
        break;

    case 6: // remaining parts
        ?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li>Version Check</li>
                <li>License Agreement</li>
                <li>Configuration</li>
                <li>Create Super Administrator Account</li>
                <li class="current">Install</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>Installing XMB</h1>
            <div class="install">
<?php
        // first, let's check if we have right version of PHP
        show_act('Checking PHP version');
        if (version_compare(phpversion(), PHP_MIN_VER, '<')) {
            show_result(X_INST_ERR);
            error('Version mismatch', 'XMB requires PHP version '.PHP_MIN_VER.' or higher to work properly.  Version '.phpversion().' is running.', true);
        }
        show_result(X_INST_OK);

        // let's check if all files we need actually exist.
        show_act('Checking Directory Structure');
        foreach($req['dirs'] as $dir) {
            if (!file_exists(ROOT.$dir)) {
                if ($dir == 'images') {
                    show_result(X_INST_WARN);
                    error('Missing Directory', 'XMB could not locate the <i>/images</i> directory. Although this directory, and its contents are not vital to the functioning of XMB, we do recommend you upload it, so you can enjoy the full look and feel of each theme.', false);
                    show_act('Checking (remaining) Directory Structure');
                } else {
                    show_result(X_INST_ERR);
                    error('Missing Directory', 'XMB could not locate the <i>'.$dir.'</i> directory. Please upload this directory for XMB to proceed with the installation.', true);
                }
            } else {
                continue;
            }
        }
        show_result(X_INST_OK);

        show_act('Checking Required Files');
        foreach($req['files'] as $file) {
            if (!file_exists(ROOT.$file)) {
                show_result(X_INST_ERR);
                error('Missing File', 'XMB could not locate the file <i>/'.$file.'</i>, this file is required for XMB to work properly. Please upload this file and restart installation.', true);
            }
        }
        show_result(X_INST_OK);

        // check db-connection.
        require_once(ROOT.'config.php');

        // double check all stuff here
        show_act('Checking Database Files');

        // Force upgrade to mysqli
        if ( 'mysql' === $database ) $database = 'mysqli';

        if (!file_exists(ROOT.'db/'.$database.'.php')) {
            show_result(X_INST_ERR);
            error('Database connection', 'XMB could not locate the <i>/db/'.$database.'.php</i> file, you have configured xmb to use this database-type. For it to work you will need to upload the file, or change the config.php file to reflect a different choice.', true);
        }
        show_result(X_INST_OK);

        show_act('Checking Database API');
        // let's check if the actual functionality exists...
        $err = false;
        switch($database) {
            case 'mysqli':
                if ( ! extension_loaded( 'mysqli' ) ) {
                    show_result(X_INST_ERR);
                    $err = true;
                }
                break;
            default:
                show_result(X_INST_ERR);
                error('Database Handler', 'Unknown handler provided', true);
                break;
        }

        if ($err === true) {
            error('Database Handler', 'XMB has determined that your php installation does not support the functions required to use <i>'.$database.'</i> to store all data.', true);
            unset($err);
        }
        show_result(X_INST_OK);

        // let's check the connection itself.
        show_act('Checking Database Connection Security');
        if ($dbuser == 'root') {
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
                $sqlver = mysql_get_server_info($link);
                mysql_close($link);
                show_act('Checking Database Version');
                if (version_compare($sqlver, MYSQL_MIN_VER, '<')) {
                    show_result(X_INST_ERR);
                    error('Version mismatch', 'XMB requires MySQL version '.MYSQL_MIN_VER.' or higher to work properly.  Version '.$sqlver.' is running.', true);
                } else {
                    show_result(X_INST_OK);
                }
                break;
            case 'mysqli':
                $link = new mysqli( $dbhost, $dbuser, $dbpw );
                if ( mysqli_connect_error() ) {
                    show_result( X_INST_ERR );
                    error( 'Database Connection', 'XMB could not connect to the specified database. The database returned "error '.mysqli_connect_error().': '.mysqli_connect_errno(), true );
                } else {
                    show_result( X_INST_OK );
                }
                $sqlver = $link->server_info;
                $link->close();
                show_act( 'Checking Database Version' );
                if ( version_compare( $sqlver, MYSQL_MIN_VER, '<' ) ) {
                    show_result( X_INST_ERR );
                    error( 'Version mismatch', 'XMB requires MySQL version '.MYSQL_MIN_VER.' or higher to work properly.  Version '.$sqlver.' is running.', true );
                } else {
                    show_result( X_INST_OK );
                }
                break;
            default:
                show_result(X_INST_SKIP);
                break;
        }

        // throw in all stuff then :)
        require './cinst.php';
?>
            </div>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="complete">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>Installation Complete</h1>
            <p>The installation process completed successfully, and your new XMB Powered Forum is now ready for you to use. Please click <a href="../index.php">here</a> to go to your forum.</p>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
        break;
    default:
        header('Location: index.php?step=1');
        exit;
}
?>
    <div id="footer">
        <div class="top"><span></span></div>
        <div class="center-content">
            <span><a href="https://www.xmbforum2.com/" onclick="window.open(this.href); return false;"><strong><abbr title="eXtreme Message Board">XMB</abbr>
            Forum Software</strong></a>&nbsp;&copy; <?php echo COPY_YEAR; ?> The XMB Group</span>
        </div>
        <div class="bottom"><span></span></div>
    </div>
</div>
</body>
</html>
