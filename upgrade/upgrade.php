<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2009, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * http://www.ientry.com
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/

// Script Parameters
define('XMB_V', '1.9.11');
define('XMB_UPGRADE_FILE', 'XMB_1_9_11.xmb');
define('XMB_SCHEMA_VER', 3);
define('MYSQL_MIN_VER', '4.1.7');
define('PHP_MIN_VER', '4.3.0');
$req['dirs'] = array('db', 'fonts', 'images', 'include', 'js', 'lang');
$req['files'] = array(
    'buddy.php',
    'config.php',
    'cp.php',
    'cp2.php',
    'db/mysql.php',
    'editprofile.php',
    'faq.php',
    'files.php',
    'forumdisplay.php',
    'header.php',
    'include/admin.inc.php',
    'include/attach.inc.php',
    'include/attach-admin.inc.php',
    'include/buddy.inc.php',
    'include/captcha.inc.php',
    'include/debug.inc.php',
    'include/functions.inc.php',
    'include/global.inc.php',
    'include/online.inc.php',
    'include/smtp.inc.php',
    'include/spelling.inc.php',
    'include/translation.inc.php',
    'include/u2u.inc.php',
    'include/validate.inc.php',
    'include/validate-email.inc.php',
    'index.php',
    'lang/English.lang.php',
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
    'upgrade/upgrade.lib.php',
    'upgrade/'.XMB_UPGRADE_FILE,
    'viewthread.php',
    'vtmisc.php'
);

function print_header() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>XMB Upgrade Utility</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <link href="install.css" rel="stylesheet" type="text/css" media="screen, projection" />
</head>
<body>
<div id="main">
    <div id="header">
        <img src="images/logo.png" alt="XMB" title="XMB" />
    </div>
    <?php
}

function print_footer() {
?>
    <div id="footer">
        <div class="top"><span></span></div>
        <div class="center-content">
            <span>The XMB Group &copy; 2008<br />
            Sponsored by iEntry</span>
        </div>
        <div class="bottom"><span></span></div>
    </div>
</div>
</body>
</html>
<?php
}

function error($head, $msg, $die=true) {
    echo "\n";
    echo '<h1 class="progressErr">'.$head.'</h1>';
    echo '<span class="progressWarn">'.$msg.'</span><br />';
    echo "\n";
    ob_flush();
    if ($die) {
        echo '
            </div>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="footer">
        <div class="top"><span></span></div>
        <div class="center-content">
            <span>The XMB Group &copy; 2008<br />
            Sponsored by iEntry</span>
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
    ob_flush();
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
?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li class="current">Welcome</li>
                <li>Version Check</li>
                <li>License Agreement</li>
                <li>Upgrade</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>XMB Upgrade Utility</h1>
            <p>Welcome to the XMB Upgrade Utility. The next steps will guide you through the process to update your XMB board to the latest stable version. Please ensure you have made a backup of your current XMB Database and files. Once you have completed your backup, upload all the <?php echo XMB_V; ?> files uploaded to your forum directory, and re-configure the config.php file. Once you have completed these steps, you may proceed with the upgrade process.</p>
            <h1>Please Note</h1>
            <p>This upgrade may temporarily use up to two times your current database size (though only in extreme circumstances)</p>
            <form action="./upgrade.php?step=2" method="post">
                <p class="button"><input type="submit" value="Begin Upgrade &gt;" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
        print_footer();
        break;
    case 2: //version check
        print_header();
?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li class="current">Version Check</li>
                <li>License Agreement</li>
                <li>Upgrade</li>
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
                <li>Upgrade to Version: XMB <?php echo XMB_V;?></li>
                <li>Latest Available Version: <img src="http://www.xmbforum.com/phpbin/xmbvc/vc.php?bg=f0f0f0&amp;fg=000000" alt="XMB Version Cant Be Found" style="position: relative; top: 8px;" /></li>
            </ul>
            <form action="./upgrade.php?step=3" method="post">
                <p class="button"><input type="submit" value="Upgrade to XMB <?php echo XMB_V;?> &gt;" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
        print_footer();
        break;
    case 3: // agreement
        print_header();
?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li>Version Check</li>
                <li class="current">License Agreement</li>
                <li>Upgrade</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>XMB <?php echo XMB_V;?> License Agreement</h1>
            <p>Please read over the agreement below, and if you agree to it select the button at the very bottom. By selecting the button, you agree to the terms below.</p>
            <textarea style="width: 90%" rows="30"  name="agreement" style= "font-family: Verdana; font-size: 8pt; margin-left: 4%;" readonly="readonly">
XMB <?php echo XMB_V;?>  License (Updated November 2007)
www.xmbforum.com
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
the GNU General Public License from time to time.  Such new versions will
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
SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE POSSIBILITY OF
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
            <form action="./upgrade.php?step=4" method="post">
                <p class="button"><input type="submit" value="I Agree To These Terms &gt;" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
        print_footer();
        break;
    case 4:
        define('COMMENTOUTPUT', false);
        define('IPREG', false);
        define('IPCHECK', false);
        define('SPECQ', false);
        define('SHOWFULLINFO', false);
        define('MAXATTACHSIZE', 256000);
        define('IN_CODE', true);

        require ROOT.'config.php';

        if (!defined('DEBUG')) {
            define('DEBUG', FALSE);
        }
        if (headers_sent()) {
            if (DEBUG) {
                headers_sent($filepath, $linenum);
                exit("XMB Upgrade Step 4 Error: Failed to start due to file corruption.  Please inspect $filepath at line number $linenum.");
            } else {
                exit("XMB Upgrade Step 4 failed to start.  Set DEBUG to TRUE in config.php to see file system details.");
            }
        }

        print_header();
?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li>Version Check</li>
                <li>License Agreement</li>
                <li class="current">Upgrade</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>Performing Upgrade</h1>
            <div class="install">
<?php
        while(ob_get_level() > 0) {
            ob_end_flush();
        }
        ob_implicit_flush(1);

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
                show_result(X_INST_ERR);
                error('Incorrect Configuration', 'XMB noticed that your config.php has not been fully configured.<br />The $'.$key.' has not been configured correctly.<br /><br />Please configure config.php before continuing.<br />Refresh the browser after uploading the new config.php (when asked if you want to resubmit POST data, click the \'OK\'-button).', true);
            }
        }
        
        require '../include/debug.inc.php';
        $array = parse_url($full_url);
        if (!isset($array['path'])) {
            $array['path'] = '/';
        }
        debugURLsettings(($array['scheme'] == 'https'), $array['host'], $array['path']);
        unset($array);

        define('X_PREFIX', $tablepre);
        require ROOT."db/$database.php";

        // Increase the time limit for long running queries to five minutes. This should be enough, but if you need
        // more, make it more.
        show_act('Extending script timeout to 300 seconds.');
        if (!ini_get('safe_mode')) {
            @set_time_limit(300);
            show_result(X_INST_OK);
        } else {
            show_result(X_INST_WARN);
            error('Safe-mode detected', 'XMB has detected that PHP is running in safe-mode. This could lead to errors during the upgrade process. It is advised that you turn safe-mode off in php.ini.', false);
        }

        show_act('Checking PHP version');
        $current = phpversion();
        $current = array_map('intval', explode('.', $current));
        $min = array_map('intval', explode('.', PHP_MIN_VER));
        if ($current[0] < $min[0] || ($current[0] == $min[0] && ($current[1] < $min[1] || ($current[1] == $min[1] && $current[2] < $min[2])))) {
            show_result(X_INST_ERR);
            error('Version mismatch', 'XMB requires PHP version '.PHP_MIN_VER.' or higher to work properly.  Version '.phpversion().' is running.', true);
        }
        show_result(X_INST_OK);

        // let's check if all files we need actually exist.
        show_act('Checking Directory Structure');
        foreach($req['dirs'] as $dir) {
            if (!file_exists(ROOT.$dir)) {
                if ($dir == './images') {
                    show_result(X_INST_WARN);
                    error('Missing Directory', 'XMB could not locate the <i>./images</i> directory. Although this directory and its contents are not vital to the functioning of XMB, we do recommend you upload it, so you can enjoy the full look and feel of each theme.', false);
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
            if (!file_exists(ROOT.$file)) {
                show_result(X_INST_ERR);
                error('Missing File', 'XMB could not locate the file <i>/'.$file.'</i>, this file is required for XMB to work properly. Please upload this file and restart installation.', true);
            }
        }
        show_result(X_INST_OK);

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
        if ($dbname == 'DB_NAME' || $dbuser == 'DB_USER' || $dbpw == 'DB_PW') {
            show_result(X_INST_WARN);
            error('Incorrect Configuration', 'Most likely, config.php has not been correctly configured for database name, user or password. Please check these details.', false);
        }

        $mysqlver = 0;

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
                mysql_close();
                show_act('Checking Database Version');
                $current = array_map('intval', explode('.', $sqlver));
                $min = array_map('intval', explode('.', MYSQL_MIN_VER));
                if ($current[0] < $min[0] || ($current[0] == $min[0] && ($current[1] < $min[1] || ($current[1] == $min[1] && $current[2] < $min[2])))) {
                    show_result(X_INST_ERR);
                    error('Version mismatch', 'XMB requires MySQL version '.MYSQL_MIN_VER.' or higher to work properly.  Version '.$sqlver.' is running.', true);
                } else {
                    show_result(X_INST_OK);
                }
                break;
            default:
                show_result(X_INST_SKIP);
                break;
        }

        // do the work! :)
        show_act('Starting upgrade.');
        show_result(X_INST_OK);
        error('Lengthy operation Notice', 'This may take a while, please do not disturb.', false);

        $db = new dbstuff;
        $db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

        require './upgrade.lib.php';

        show_act('Opening XMB Upgrade Template');
        $u = new Upgrade($db, XMB_UPGRADE_FILE, $tablepre);
        if ($u) {
            $tbl = $u->getTablesByTablepre($u->tablepre);
            if (count($tbl) == 0) {
                show_result(X_INST_ERR);
                error('Could not locate a valid installation of XMB. Please check if config.php is correctly configured', true);
            } else {
                // load them now into the cache so we can work on (and check against) them
                foreach($tbl as $t) {
                    $u->loadTable($t);
                }
            }
        } else {
            show_result(X_INST_ERR);
            error('Could not open XMB templates.', true);
        }
        show_result(X_INST_OK);

        // clear logs so we don't have discrepencies between various formats.
        // When upgrading they shouldn't be necessery anymore anyway, thus saving space
        // and making the board more efficient
        show_act('Clearing logs');
        $u->dropTableFromCache('logs', true); // also drops the actual mysql table
        show_result(X_INST_OK);

        show_act('Fixing necessary indices');
        $u->fixIndex();
        show_result(X_INST_OK);

        $r = $db->query("SELECT u2uid FROM `".$u->tablepre. "u2u`");
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

        show_act("Removing u2uadmin spy tool");
        if (file_exists(ROOT . 'u2uadmin.php') && !@unlink(ROOT . 'u2uadmin.php')) {
            show_result(X_INST_SKIP);
            error('Permission Error', 'The u2uadmin spy tool ("u2uadmin.php") has been found on the server, but could not be removed. Please remove it as soon as possible.', false);
        } else {
            show_result(X_INST_OK);
        }

        show_act("Check and remove sid");
        $u->removeSid();
        show_result(X_INST_OK);

        show_act('Collecting data for data-restructuring');
        $u->fixBirthdays(0);
        $u->fixForumPerms(0);
        show_result(X_INST_OK);

        $tablesCreated = array();

        show_act('Creating missing tables');
        $tbl = $u->getTablesByTablepre($u->tablepre);
        $t = $u->getMissingTables();

        foreach($t['-'] as $k=>$table) {
            $query = $u->createTableQueryByTablename($table);
            $db->query($query);
            $tablesCreated[] = $table;
        }
        show_result(X_INST_OK);

        show_act('Creating temporary fields...');
        $u->createTempFields();
        show_result(X_INST_OK);

        show_act('Changing table schemas to '.XMB_V);
        foreach($tbl as $t) {
            if (in_array($t, $tablesCreated)) {
                // no need to change a freshly (re-)made table
                continue;
            }
            $t = substr($t, strlen($u->tablepre));
            $d[$t] = $u->makeDiff($t);
        }

        $diff = $u->makeIntelligentDiff($d);
        foreach($diff as $table=>$type) {
            $qs = $u->createQueryFromDiff($diff[$table], $table);
            foreach($qs as $k=>$q) {
                $db->query($q.';');
            }
        }
        show_result(X_INST_OK);

        show_act('Restructure data (may take a long time)');
        foreach($tbl as $t) {
            $t = substr($t, strlen($u->tablepre));
            $d[$t] = $u->makeLocationDiff($t);
        }

        foreach($d as $tbl=>$diff) {
            if ($diff === null) {
                continue;
            }

            $fromTable = $u->tablepre . $tbl;
            $tmpTable = $u->tablepre . $tbl . '_tmp';

            $db->query("DROP TABLE IF EXISTS `$tmpTable`");
            $db->query("RENAME TABLE `$fromTable` TO `$tmpTable`");
            $db->query("DROP TABLE IF EXISTS `$fromTable`");
            $db->query($u->createTableQueryByTablename($tbl));
            $q = $u->createLocationChangeQuery($fromTable, $tmpTable, $diff);
            $db->query($q);
            $db->query("DROP TABLE `$tmpTable`");
        }
        show_result(X_INST_OK);

        show_act('Fixing birthday values');
        $u->fixBirthdays(1);
        show_result(X_INST_OK);

        show_act('Fixing forum post permissions');
        $u->fixForumPerms(1);
        show_result(X_INST_OK);

        show_act('Fixing missing posts per page values');
        $u->fixPPP($mysqlver);
        show_result(X_INST_OK);

        show_act('Updating outgoing U2U status');
        $db->query("UPDATE `".$u->tablepre."members` SET saveogu2u='yes'");
        show_result(X_INST_OK);

        show_act('Upgrading polls to new system (may take a long time)');
        $u->fixPolls();
        show_result(X_INST_OK);

        show_act('Dropping temporary fields...');
        $u->dropTempFields();
        show_result(X_INST_OK);
        
        //Explicitly reset the schema version number
        $db->query("UPDATE ".$u->tablepre."settings SET schema_version = ".XMB_SCHEMA_VER);

        show_act("Updating themes to ".XMB_V." themes");

        $u->deleteThemeByName(''); // delete blank themes

        $newTheme = $u->findThemeIDByName('XMB Davis');
        if (!$newTheme) {
            $db->query("INSERT INTO ".$tablepre."themes (`name`,      `bgcolor`, `altbg1`,  `altbg2`,  `link`,    `bordercolor`, `header`,  `headertext`, `top`,       `catcolor`,   `tabletext`, `text`,    `borderwidth`, `tablewidth`, `tablespace`, `font`,                              `fontsize`, `boardimg`, `imgdir`,       `smdir`,          `cattext`) "
                                               ."VALUES ('XMB Davis', 'bg.gif',  '#FFFFFF', '#f4f7f8', '#24404b', '#86a9b6',     '#d3dfe4', '#24404b',    'topbg.gif', 'catbar.gif', '#000000',   '#000000', '1px',         '97%',        '5px',        'Tahoma, Arial, Helvetica, Verdana', '11px',     'logo.gif', 'images/davis', 'images/smilies', '#163c4b');");
            $newTheme = $db->insert_id();
        }

        // Update default theme.
        $db->query("UPDATE `".$u->tablepre."settings` SET theme=$newTheme");

        show_result(X_INST_OK);

        show_act('Inserting '.XMB_V.' templates');

        $templates = explode("|#*XMB TEMPLATE FILE*#|", file_get_contents(ROOT.'templates.xmb'));

        $db->query("TRUNCATE TABLE `".$u->tablepre."templates`");

        $values = array();
        foreach($templates as $val) {
            $template = explode("|#*XMB TEMPLATE*#|", $val);
            $template[1] = isset($template[1]) ? addslashes(ltrim($template[1])) : '';
            $values[] = "('".$db->escape_var($template[0])."', '".$db->escape_var($template[1])."')";
        }
        unset($templates);
        if (count($values) > 0) {
            $values = implode(', ', $values);
            $db->query("INSERT INTO ".$u->tablepre."templates (name, template) VALUES $values");
        }
        unset($values);
        $db->query("DELETE FROM `".$u->tablepre."templates` WHERE name=''");

        show_result(X_INST_OK);

        show_act('Fixing special ranks');

        $db->query("DELETE FROM `".$u->tablepre."ranks` WHERE title in ('Moderator', 'Super Moderator', 'Administrator', 'Super Administrator')");

        $db->query("INSERT INTO ".$u->tablepre."ranks (`title`, `posts`, `stars`, `allowavatars`, `avatarrank`) VALUES ('Moderator', -1, 6, 'yes', '');");
        $db->query("INSERT INTO ".$u->tablepre."ranks (`title`, `posts`, `stars`, `allowavatars`, `avatarrank`) VALUES ('Super Moderator', -1, 7, 'yes', '');");
        $db->query("INSERT INTO ".$u->tablepre."ranks (`title`, `posts`, `stars`, `allowavatars`, `avatarrank`) VALUES ('Administrator', -1, 8, 'yes', '');");
        $db->query("INSERT INTO ".$u->tablepre."ranks (`title`, `posts`, `stars`, `allowavatars`, `avatarrank`) VALUES ('Super Administrator', -1, 9, 'yes', '');");

        show_result(X_INST_OK);

        
        show_act('Initializing the translation system');

        require_once(ROOT.'include/translation.inc.php');
        $upload = file_get_contents(ROOT.'lang/English.lang.php');

        show_result(X_INST_OK);
        show_act('Installing the '.XMB_V.' English Translation');

        if (installNewTranslation($upload)) {
            show_result(X_INST_OK);
        } else {
            show_result(X_INST_ERR);
        }
        unset($upload);


        show_act('Removing install and upgrade files.');
        $problem = false;

        $req['dirs'] = array('install', 'upgrade');
        $req['files'] = array('emailfriend.php', 'upgrade.php', 'upgrade.lib.php', 'install.css', 'XMB_1_9_2.xmb', 'createFile.php', 'readme.txt', 'cplogfile.php', 'splash.gif', 'agreement.html', 'cinst.php', 'install.html', 'main_start.gif', 'versioncheck.html');

        foreach($req['dirs'] as $dir) {
            rmFromDir(ROOT.$dir);
            clearstatcache();
            if (file_exists(ROOT.$dir)) {
                if (!$problem) {
                    $problem = true;
                } else {
                    show_act('   Removing directory `'.$dir.'`');
                }
                show_result(X_INST_WARN);
                error('Remove Directory Error', 'XMB could not remove the <i>'.$dir.'</i> directory. Please delete this directory before using your board.', false);
            }
        }

        foreach($req['files'] as $file) {
            @unlink(ROOT.$file);
            clearstatcache();
            if (file_exists(ROOT.$file)) {
                if (!$problem) {
                    $problem = true;
                } else {
                    show_act('Removing file `'.$file.'`');
                }
                show_result(X_INST_WARN);
                error('Remove File Error', 'XMB could not remove <i>'.$file.'</i> file. Please delete this file before using your board.', false);
            }
        }

        if (!$problem) {
            show_result(X_INST_OK);
        }
        ?>
            </div>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="complete">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>Upgrade Process Complete</h1>
            <p>The upgrade process has completed successfully. Your upgraded XMB board is now ready for use. Please click <a href="../index.php">here</a> to go to your forum.</p>
        </div>
        <div class="bottom"><span></span></div>
    </div>
        <?php
        print_footer();
        break;
    default:
        header('location: upgrade.php?step=1');
        break;
}

function rmFromDir($path) {
    if (is_dir($path)) {
        $stream = opendir($path);
        while(($file = readdir($stream)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            rmFromDir($path.'/'.$file);
        }
        @rmdir($path);
    } else if (is_file($path)) {
        @unlink($path);
    }
}
?>
