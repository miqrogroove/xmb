<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2024, The XMB Group
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
 */

declare(strict_types=1);

namespace XMB;

/**
 * Provides most of the procedural logic formerly in header.php.
 *
 * @since 1.10.00
 */
class BootupLoader
{
    public function __construct(private Core $core, private DBStuff $db, private Template $template, private Variables $vars)
    {
        // Property promotion.
    }

    public function loadSettings()
    {
        // This is normally the first query on the connection, so do not panic unless query logging is enabled.
        $panic = $this->vars->debug && $this->vars->log_mysql_errors;
        $squery = $this->db->query("SELECT * FROM ".X_PREFIX."settings", $panic);

        // Assume XMB is not installed if first query fails.
        if (false === $squery) {
            header('HTTP/1.0 500 Internal Server Error');
            if (file_exists(ROOT.'install/')) {
                exit('XMB is not yet installed. Please do so at this time. Just <a href="./install/index.php">click here</a>.');
            }
            exit('Fatal Error: XMB is not installed. Please upload the /install/ directory to begin.');
        }
        if ($this->db->num_rows($squery) == 0) {
            header('HTTP/1.0 500 Internal Server Error');
            exit('Fatal Error: The XMB settings table is empty.');
        }
        // Check schema for upgrade compatibility back to 1.8 SP2.
        $row = $this->db->fetch_array($squery);
        if (isset($row['langfile'])) {
            // Schema version <= 4 has only one row.
            foreach ($row as $key => $val) {
                $this->vars->settings[$key] = $val;
            }
            if (! isset($this->vars->settings['schema_version'])) {
                $this->vars->settings['schema_version'] = '0';
            }
        } else {
            // Current schema uses a separate row for each setting.
            do {
                $this->vars->settings[$row['name']] = $row['value'];
            } while ($row = $this->db->fetch_array($squery));
        }
        $this->db->free_result($squery);
        unset($row);

        if ((int) $this->vars->settings['postperpage'] < 5) {
            $this->vars->settings['postperpage'] = '30';
        }

        if ((int) $this->vars->settings['topicperpage'] < 5) {
            $this->vars->settings['topicperpage'] = '30';
        }

        if ((int) $this->vars->settings['memberperpage'] < 5) {
            $this->vars->settings['memberperpage'] = '30';
        }

        if ((int) $this->vars->settings['smcols'] < 1) {
            $this->vars->settings['smcols'] = '4';
        }

        // The latest upgrade script advertises compatibility with v1.8 SP2.  These defaults might not exist yet.
        if (empty($this->vars->settings['onlinetodaycount']) || (int) $this->vars->settings['onlinetodaycount'] < 5) {
            $this->vars->settings['onlinetodaycount'] = '30';
        }

        if (
            empty($this->vars->settings['captcha_code_length'])
            || (int) $this->vars->settings['captcha_code_length'] < 3 
            || (int) $this->vars->settings['captcha_code_length'] >= X_NONCE_KEY_LEN
        ) {
            $this->vars->settings['captcha_code_length'] = '8';
        }

        if (empty($this->vars->settings['ip_banning'])) {
            $this->vars->settings['ip_banning'] == 'off';
        }

        if (empty($this->vars->settings['schema_version'])) {
            $this->vars->settings['schema_version'] == '0';
        }

        // Validate maxattachsize with PHP configuration.
        $inimax = phpShorthandValue('upload_max_filesize');
        if (empty($this->vars->settings['maxattachsize']) || $inimax < (int) $this->vars->settings['maxattachsize']) {
            $this->vars->settings['maxattachsize'] = $inimax;
        }
    }

    public function setHeaders()
    {
        // Set Global HTTP Headers
        $script = basename($_SERVER['SCRIPT_NAME']);
        if ($script != 'files.php' && $script != 'css.php') {
            header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }

        ini_set('user_agent', 'XMB-eXtreme-Message-Board/1.9; ' . $this->vars->full_url);

        $this->vars->oldtopics = getPhpInput('oldtopics', 'c');

        if ($script != 'viewthread.php' && ! empty($this->vars->oldtopics)) {
            $this->core->put_cookie('oldtopics', $this->vars->oldtopics, (time() + $this->vars::ONLINE_TIMER));
        }
    }

    public function prepareSession(): array
    {
        $serror = '';
        $action = getPhpInput('action', 'g');
        $script = basename($_SERVER['SCRIPT_NAME']);

        // Check if the client is ip-banned
        if ($this->vars->settings['ip_banning'] === 'on') {
            $ips = explode(".", $onlineip);
            if (count($ips) === 4) {
                $query = $db->query("SELECT id FROM ".X_PREFIX."banned WHERE ((ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')) AND NOT (ip1='-1' AND ip2='-1' AND ip3='-1' AND ip4='-1')");
                $result = $db->num_rows($query);
                $db->free_result($query);
                if ($result > 0) {
                    // Block all non-admins
                    $serror = 'ip';
                }
                unset($result);
            }
            unset($ips);
        }

        // Check other access restrictions
        if ('' === $serror) {
            if ((int) $this->vars->settings['schema_version'] < 5) {
                // During upgrade of session system, no features are available.
                $serror = 'bstatus';
            } elseif (($action == 'login' || $action == 'lostpw') && $script == 'misc.php') {
                // Allow login
            } elseif ($script == 'css.php' || $script == 'lost.php') {
                // Allow stylesheets and password resets
            } elseif ($this->vars->settings['bbstatus'] == 'off') {
                // Block all non-admins
                $serror = 'bstatus';
            } elseif ($this->vars->settings['regstatus'] == 'on' && ($action == 'reg' || $action == 'captchaimage') && ($script == 'misc.php' || $script == 'member.php')) {
                // Allow registration
            } elseif ($this->vars->settings['regviewonly'] == 'on') {
                // Block all guests
                $serror = 'guest';
            } else {
                // Allow everything else
            }
        }

        // Authenticate session or login credentials.
        $force_inv = false;
        if ((int) $this->vars->settings['schema_version'] < 5) {
            $mode = 'disabled';
        } else if (defined('XMB_UPGRADE') && isset($_POST['xmbpw'])) {
            $mode = 'login';
        } else if ($action == 'login' && onSubmit('loginsubmit') && $script == 'misc.php') {
            $mode = 'login';
            $force_inv = (formInt('hide') == 1);
        } else if ($action == 'logout' && $script == 'misc.php') {
            $mode = 'logout';
        } else {
            $mode = 'resume';
        }

        return [
            'force_inv' => $force_inv,
            'mode' => $mode,
            'serror' => $serror,
        ];
    }
    
    public function setCharset()
    {
        // Specify all charset variables as early as possible.
        $action = getPhpInput('action', 'g');
        $download = getInt('download');

        if ($action != 'attachment' && !($action == 'templates' && $download != 0) && !($action == 'themes' && $download != 0)) {
            header('Content-type: text/html;charset=' . $this->vars->lang['charset']);
        }
        if (function_exists('mb_list_encodings')) {
            // The list of charsets common to mb_string and htmlspecialchars is extremely restrictive.
            switch (strtoupper($this->vars->lang['charset'])) {
            case 'UTF-8':
                $newcharset = 'UTF-8';
                break;
            case 'WINDOWS-1251':
                $newcharset = 'Windows-1251';
                break;
            default:
                $newcharset = 'ISO-8859-1';
                break;
            }
            if (! in_array($newcharset, mb_list_encodings())) {
                $newcharset = 'ISO-8859-1';
            }
        } else {
            $newcharset = 'ISO-8859-1';
        }
        ini_set('default_charset', $newcharset);
    }
    
    public function setBaseElement()
    {
        // Create a base element so that links aren't broken if scripts are accessed using unexpected paths.
        // XMB expects all links to be relative to $full_url + script name + query string.
        $querystring = strstr($this->vars->url, '?');
        if ($querystring === false) {
            $querystring = '';
        }
        $querystring = preg_replace('/[^\x20-\x7e]/', '', $querystring);
        if ($this->vars->url == $this->vars->cookiepath) {
            $this->template->baseelement = '<base href="' . $this->vars->full_url . '" />' . "\n";
        } else {
            $this->template->baseelement = '<base href="' . $this->vars->full_url . basename($_SERVER['SCRIPT_NAME']) . attrOut($querystring) . '" />' . "\n";
        }
    }
    
    public function setVisit()
    {
        // Read last visit cookies
        $xmblva = getInt('xmblva', 'c'); // Previous request timestamp.
        $xmblvb = getInt('xmblvb', 'c'); // Ending timestamp of previous session.
        $onlinetime = $this->vars->onlinetime;

        if ($xmblvb > 0) {
            $thetime = $xmblvb;     // lvb will expire in 600 seconds, so if it's there, we're still in a session and persisting the value from the last visit.
        } else if ($xmblva > 0) {
            $thetime = $xmblva;     // Not currently logged in, so let's get the time from the last visit and save it
        } else {
            $thetime = $onlinetime; // no cookie at all, so this is your first visit
        }

        // login/logout links
        if (X_MEMBER) {
            if (X_ADMIN) {
                $url = $this->vars->full_url . 'admin/';
                $cplink = " - <a href='$url'>" . $this->vars->lang['textcp'] . '</a>';
            } else {
                $cplink = '';
            }
            $url = $this->vars->full_url . 'misc.php?action=logout';
            $loginout = "<a href='$url'>" . $this->vars->lang['textlogout'] . '</a>';

            $url = $this->vars->full_url . 'memcp.php';
            $memcp = "<a href='$url'>" . $this->vars->lang['textusercp'] . '</a>';

            $url = $this->vars->full_url . 'u2u.php';
            $u2ulink = '<a href="' . $url . '" onclick="Popup(this.href, \'Window\', 700, 450); return false;">' . $this->vars->lang['banu2u'] . '</a> - ';

            $url = $this->vars->full_url . 'member.php?action=viewpro&amp;member=' . recodeOut($this->vars->xmbuser);
            $profile = "<a href='$url'>" . $this->vars->xmbuser . '</a>';

            $this->template->notify = $this->vars->lang['loggedin'] . " {$profile}<br />[{$loginout} - {$u2ulink}{$memcp}{$cplink}]";

            // Update lastvisit in the header shown
            if ((int) $this->vars->self['lastvisit'] < $thetime || (
                (int) $this->vars->self['lastvisit'] > $thetime + $this->vars::ONLINE_TIMER && (int) $this->vars->self['lastvisit'] < $onlinetime - $this->vars::ONLINE_TIMER
            )) {
                $thetime = (int) $this->vars->self['lastvisit'];
            }
            $lastlocal = $this->core->timeKludge($thetime);
            $lastdate = gmdate($this->vars->dateformat, $lastlocal);
            $lasttime = gmdate($this->vars->timecode, $lastlocal);
            $this->template->lastvisittext = $this->vars->lang['lastactive'] . ' ' . $lastdate . ' ' . $this->vars->lang['textat'] . ' ' . $lasttime;
        } else {
            // Checks for the possibility to register
            if ($this->vars->settings['regstatus'] == 'on') {
                $url = $this->vars->full_url . 'member.php?action=reg';
                $this->template->reglink = "- <a href='$url'>" . $this->vars->lang['textregister'] . '</a>';
            } else {
                $this->template->reglink = '';
            }
            $url = $this->vars->full_url . 'misc.php?action=login';
            $loginout = "<a href='$url'>" . $this->vars->lang['textlogin'] . '</a>';
            $this->template->notify = $this->vars->lang['notloggedin'] . ' [' . $loginout . ' ' . $this->template->reglink . ']';
            $this->template->lastvisittext = '';
        }

        // Update last visit cookies
        $this->core->put_cookie('xmblva', (string) $onlinetime, ($onlinetime + (86400*365))); // lva == now
        $this->core->put_cookie('xmblvb', (string) $thetime, ($onlinetime + $this->vars::ONLINE_TIMER)); // lvb == last visit
        $this->vars->lastvisit = $thetime; // Used by forumdisplay
    }

    public function checkU2U(SQL $sql)
    {
        // check for new u2u's
        $newu2unum = $sql->countU2UInbox($this->vars->self['username']);
        $newu2umsg = '';
        if ($newu2unum > 0) {
            $newu2umsg = "<a href='u2u.php' onclick=\"Popup(this.href, 'Window', 700, 450); return false;\">" . $this->vars->lang['newu2u1'] . ' ' . $newu2unum . ' ' . $this->vars->lang['newu2u2'] . '</a>';
            // Popup Alert
            if ('2' === $this->vars->self['u2ualert'] || ('1' === $this->vars->self['u2ualert'] && 'index.php' === basename($_SERVER['SCRIPT_NAME']))) {
                $newu2umsg .= '<script language="JavaScript" type="text/javascript">function u2uAlert() { ';
                if ($newu2unum == 1) {
                    $newu2umsg .= 'u2uAlertMsg = "' . $this->vars->lang['newu2u1'] . ' ' . $newu2unum . $this->vars->lang['u2ualert5'] . '"; ';
                } else {
                    $newu2umsg .= 'u2uAlertMsg = "' . $this->vars->lang['newu2u1'] . ' ' . $newu2unum . $this->vars->lang['u2ualert6'] . '"; ';
                }
                $newu2umsg .= "if (confirm(u2uAlertMsg)) { Popup('u2u.php', 'testWindow', 700, 450); } } setTimeout('u2uAlert();', 10);</script>";
            }
        }
        $this->template->newu2umsg = $newu2umsg;
    }
    
    public function createNavbarLinks()
    {
        $links = [];
        $full_url = $this->vars->full_url;
        $imgdir = $full_url . $this->vars->theme['imgdir'];

        // Search-link
        $searchlink = $this->core->makeSearchLink();

        // Faq-link
        if ($this->vars->settings['faqstatus'] == 'on') {
            $links[] = '<img src="' . $imgdir . '/top_faq.gif" alt="" border="0" /> <a href="' . $full_url . 'faq.php"><font class="navtd">' . $this->vars->lang['textfaq'] . '</font></a>';
        }

        // Memberlist-link
        if ($this->vars->settings['memliststatus'] == 'on') {
            $links[] = '<img src="' . $imgdir . '/top_memberslist.gif" alt="" border="0" /> <a href="' . $full_url . 'misc.php?action=list"><font class="navtd">' . $this->vars->lang['textmemberlist'] . '</font></a>';
        }

        // Today's posts-link
        if ($this->vars->settings['todaysposts'] == 'on') {
            $links[] = '<img src="' . $imgdir . '/top_todaysposts.gif" alt="" border="0" /> <a href="' . $full_url . 'today.php"><font class="navtd">' . $this->vars->lang['navtodaysposts'] . '</font></a>';
        }

        // Stats-link
        if ($this->vars->settings['stats'] == 'on') {
            $links[] = '<img src="' . $imgdir . '/top_stats.gif" alt="" border="0" /> <a href="' . $full_url . 'stats.php"><font class="navtd">' . $this->vars->lang['navstats'] . '</font></a>';
        }

        // 'Forum Rules'-link
        if ($this->vars->settings['bbrules'] == 'on') {
            $links[] = '<img src="' . $imgdir . '/top_bbrules.gif" alt="" border="0" /> <a href="' . $full_url . 'faq.php?page=forumrules"><font class="navtd">' . $this->vars->lang['textbbrules'] . '</font></a>';
        }

        $this->template->links = implode(" &nbsp;\n", $links);
    }

    public function makePlugLinks()
    {
        // Show all plugins
        $pluglinks = [];
        foreach($this->vars->plugname as $plugnum => $item) {
            if (!empty($this->vars->plugurl[$plugnum]) && !empty($item)) {
                if (trim($this->vars->plugimg[$plugnum]) != '') {
                    $img = '&nbsp;<img src="'.$this->vars->plugimg[$plugnum].'" border="0" alt="'.$this->vars->plugname[$plugnum].'" />&nbsp;';
                } else {
                    $img = '';
                }

                if ($this->vars->plugadmin[$plugnum] != true || X_ADMIN) {
                    $pluglinks[] = $img.'<a href="'.$this->vars->plugurl[$plugnum].'"><font class="navtd">'.$this->vars->plugname[$plugnum].'</font></a>&nbsp;';
                }
            }
        }
        
        $this->template->pluglink = implode('&nbsp;', $pluglinks);
    }

    public function makeQuickJump()
    {
        // create forum jump
        if ($this->vars->settings['quickjump_status'] == 'on') {
            $this->template->quickjump = $this->core->forumJump();
        }
    }
    
    public function startCompression()
    {
        $action = getPhpInput('action', 'g');
        if (
            $this->vars->settings['gzipcompress'] == 'on'
            && $action != 'captchaimage'
            && basename($_SERVER['SCRIPT_NAME']) != 'files.php'
            && ! $this->vars->debug
        ) {
            if (($res = @ini_get('zlib.output_compression')) > 0) {
                // leave it
            } else if ($res === false) {
                // ini_get not supported. So let's just leave it
            } else {
                if (function_exists('gzopen')) {
                    $r = @ini_set('zlib.output_compression', 4096);
                    $r2 = @ini_set('zlib.output_compression_level', '3');
                    if (false === $r || false === $r2) {
                        ob_start('ob_gzhandler');
                    }
                } else {
                    ob_start('ob_gzhandler');
                }
            }
        }
    }
}
