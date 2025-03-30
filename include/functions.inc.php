<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
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

use InvalidArgumentException;

/**
 * High-level and general-purpose methods which depend on XMB's shared services.
 *
 * @since 1.10.00
 */
class Core
{
    private array $mailConnections = [];

    public function __construct(
        private Attach $attach,
        private BBCode $bbcode,
        private DBStuff $db,
        private Debug $debug,
        private Forums $forums,
        private SmileAndCensor $smile,
        private SQL $sql,
        private Template $template,
        private Token $token,
        private Translation $tran,
        private Variables $vars,
    ) {
        // Property promotion
    }

    /**
     * All-purpose function for retrieving and sanitizing user input.
     *
     * @since 1.9.8 SP3
     */
    public function postedVar(string $varname, string $word = '', bool $htmlencode = true, bool $dbescape = true, bool $quoteencode = false, string $sourcearray = 'p'): string
    {
        $retval = getPhpInput($varname, $sourcearray);

        return $this->sanitizeString($retval, $word, $htmlencode, $dbescape, $quoteencode);
    }

    public function postedArray(
        string $varname,
        string $valueType = 'string',
        string $keyType = 'int',
        string $word = '',
        bool $htmlencode = true,
        bool $dbescape = true,
        bool $quoteencode = false,
        string $source = 'p',
    ): array {
        $input = getRawInput($varname, $source);

        // Convert a single or comma delimited list to an array
        if (is_string($input)) {
            if (strpos($input, ',') !== false) {
                $input = explode(',', $input);
            } else {
                $input = [$input];
            }
        } elseif (is_null($input)) {
            $input = [];
        }
        
        $keys = array_keys($input);
        if ($keyType == 'int') {
            $keys = array_map('intval', $keys);
        } else {
            foreach ($keys as &$key) {
                $key = str_replace("\x00", '', $key);
                $key = $this->sanitizeString($key, $word, $htmlencode, $dbescape, $quoteencode);
            }
        }

        foreach($input as &$theObject) {
            switch($valueType) {
                case 'onoff':
                    if (strtolower($theObject) !== 'on') {
                        $theObject = 'off';
                    }
                    break;
                case 'yesno':
                    if (strtolower($theObject) !== 'yes') {
                        $theObject = 'no';
                    }
                    break;
                    break;
                case 'int':
                    $theObject = (int) $theObject;
                    break;
                case 'string':
                default:
                    if (is_string($theObject)) {
                        $theObject = str_replace("\x00", '', $theObject);
                        $theObject = $this->sanitizeString($theObject, $word, $htmlencode, $dbescape, $quoteencode);
                    } else {
                        $theObject = '';
                    }
                    break;
            }
        }

        return array_combine($keys, $input);
    }

    /**
     * Reuseable function for sanitizing user input.
     *
     * @since 1.10.00
     */
    private function sanitizeString(string $input, string $word = '', bool $htmlencode = true, bool $dbescape = true, bool $quoteencode = false): string
    {
        $retval = $input;

        if ($word != '') {
            $retval = str_ireplace($word, "_".$word, $retval);
        }

        if ($htmlencode) {
            if ($quoteencode) {
                $retval = htmlspecialchars($retval, ENT_QUOTES);
            } else {
                $retval = htmlspecialchars($retval, ENT_NOQUOTES);
            }
        }

        if ($dbescape) {
            $this->db->escape_fast($retval);
        }

        return $retval;
    }

    /**
     * Determine if the authenticated user is allowed to access this website.
     *
     * @since 1.9.12
     * @param array $member The member's database record.
     * @param string $serror The pre-auth session status originally generated by header.php.
     * @return string Specific error codes, otherwise 'good'.
     */
    public function loginAuthorization(array $member, string $serror): string
    {
        $guess_limit = 10;
        $admin_limit = 1000;
        $lockout_timer = 3600 * 2;
        
        if ($serror == 'ip' && $member['status'] != 'Super Administrator' && $member['status'] != 'Administrator') {
            return 'ip-banned';
        } else if ($member['status'] == 'Banned') {
            return 'member-banned';
        } else if ((int) $member['bad_login_count'] >= $guess_limit && time() < (int) $member['bad_login_date'] + $lockout_timer) {
            $this->auditBadLogin($member);
            if ($member['status'] != 'Super Administrator') {
                // This special code could help implement old session vs. new login policy, but the session system currently lacks a public logout method for use with auditing.
                return 'password-locked';
            } else if ((int) $member['bad_login_count'] >= $admin_limit) {
                return 'password-locked';
            } else {
                // Super Admin has partial immunity to mitigate denial of service.
                return 'good';
            }
        } else {
            return 'good';
        }
    }

    /**
     * Record a failed login attempt.
     *
     * @since 1.9.12
     * @param array $member The member's database record.
     */
    public function auditBadLogin(array $member)
    {
        $guess_limit = 10;
        $lockout_timer = 3600 * 2;
        $reset_timer = 86400;

        if (time() >= (int) $member['bad_login_date'] + $reset_timer) {
            // After 24 hours, reset.
            $this->sql->resetLoginCounter($member['username'], time());
        } elseif ((int) $member['bad_login_count'] >= $guess_limit && time() >= (int) $member['bad_login_date'] + $lockout_timer) {
            // User had more than 10 failures and was locked out.  After 2 hours, reset.
            $this->sql->resetLoginCounter($member['username'], time());
        } else {
            $count = $this->sql->raiseLoginCounter($member['username']);
            if ($count == $guess_limit) {
                // Email the Super Administrators about this.
                $lang2 = $this->tran->loadPhrases(['charset', 'security_subject', 'login_audit_mail']);

                $mailquery = $this->sql->getSuperEmails();
                foreach ($mailquery as $admin) {
                    $translate = $lang2[$admin['langfile']];
                    $adminemail = htmlspecialchars_decode($admin['email'], ENT_QUOTES);
                    $name = htmlspecialchars_decode($member['username'], ENT_QUOTES);
                    $body = "{$translate['login_audit_mail']}\n\n$name";
                    $this->xmb_mail($adminemail, $translate['security_subject'], $body, $translate['charset']);
                }
            }
        }
    }

    /**
     * Record a failed session hijack attempt.
     *
     * @since 1.9.12
     * @param array $member The member's database record.
     */
    public function auditBadSession(array $member)
    {
        $reset_timer = 86400;
        
        if (time() > (int) $member['bad_login_date'] + $reset_timer) {
            $this->sql->resetSessionCounter($member['username'], time());
        } else {
            $count = $this->sql->raiseSessionCounter($member['username']);
        }
    }

    /**
     * nav() - Create a navigation link and add it to the $navigation template property.
     *
     * Create a navigation link using $navigation global with a possible optional addition
     *
     * @since 1.9.1
     * @param    $add        (optional, false) additional navigation element if string or clear navigation if null.
     * @param    $raquo      (optional, true) prepends &raquo; to the string if true, doesn't if false. Defaults to true.
     */
    public function nav(?string $add = null, bool $raquo = true)
    {
        if (is_null($add)) {
            $this->template->navigation = '';
        } else {
            $this->template->navigation .= ($raquo ? ' &raquo; ' : ''). $add;
        }
    }

    /**
     * Get a template with the token filled in.
     *
     * DEPRECATED by XMB 1.10.00
     *
     * TODO: This method should be removed prior to beta testing.  The new Template system makes this awkward and unnecessary.
     *
     * @since 1.9.11.11
     * @param string $name   The template name.
     * @param string $action The action for which the token is valid.
     * @param string $id     The object for which the token is valid.
     * @param int    $ttl    Validity time in seconds.
     * @return string
     */
    function template_secure(string $name, string $action, string $id, int $ttl): string
    {
        trigger_error('Function template_secure() is deprecated in this version of XMB', E_USER_DEPRECATED);
        $token = $this->token->create($action, $id, $ttl);
        $placeholder = '<input type="hidden" name="token" value="" />';
        $replace = "<input type='hidden' name='token' value='$token' />";
        return str_replace($placeholder, $replace, $this->template->process($name));
    }

    /**
     * Assert token validity for a user request.
     *
     * @since 1.9.11.11
     * @param string $action The action for which the token is valid.
     * @param string $id     The object for which the token is valid.
     * @param bool   $error_header Display header template on errors?
     */
    function request_secure(string $action, string $id, bool $error_header = false)
    {
        $token = getPhpInput('token');

        if (! $this->token->consume($token, $action, $id)) {
            $this->error($this->vars->lang['bad_token'], $error_header);
        }
    }

    public function rawHTMLmessage(string $rawstring, string $allowhtml = 'no'): string
    {
        if ($allowhtml == 'yes') {
            return $this->smile->censor(htmlspecialchars_decode($rawstring, ENT_NOQUOTES));
        } else {
            return $this->smile->censor(decimalEntityDecode($rawstring));
        }
    }

    //Per the design of version 1.9.9, subjects are only allowed decimal entity references and no other HTML.
    public function rawHTMLsubject(string $rawstring): string
    {
        return $this->smile->censor(decimalEntityDecode($rawstring));
    }

    /**
     * Perform BBCode, Smilie, and Word Wrapping for a single post body.
     *
     * @since 1.0
     * @param string $message For PHP 8.1 compatibility, null input is no longer allowed.
     * @param string $smileyoff
     * @param string $bbcodeoff
     * @param string $allowsmilies
     * @param string $allowhtml Obsolete.  Must be 'no'.
     * @param string $allowbbcode
     * @param string $allowimgcode
     * @param bool $ignorespaces Obsolete.
     * @param string $ismood
     * @param string $wrap
     */
    function postify(
        string $message,
        string $smileyoff = 'no',
        string $bbcodeoff = 'no',
        string $allowsmilies = 'yes',
        string $allowhtml = 'no',
        string $allowbbcode = 'yes',
        string $allowimgcode = 'yes',
        bool $ignorespaces = false,
        string $ismood = "no",
        string $wrap = "yes",
    ): string {
        if ('no' !== $allowhtml) {
            throw new LogicException('The allowhtml parameter only accepts a value of "no" in this version of XMB.');
        }

        $bballow = ($allowbbcode == 'yes' || $allowbbcode == 'on') ? (($bbcodeoff != 'off' && $bbcodeoff != 'yes') ? true : false) : false;
        $smiliesallow = ($allowsmilies == 'yes' || $allowsmilies == 'on') ? (($smileyoff != 'off' && $smileyoff != 'yes') ? true : false) : false;
        $allowurlcode = ($ismood != 'yes');
        $allowimgcode = ($allowimgcode != 'no' && $allowimgcode != 'off');

        if ($bballow) {
            if ($ismood == 'yes') {
                $message = str_replace(array('[rquote=', '[quote]', '[/quote]', '[code]', '[/code]', '[list]', '[/list]', '[list=1]', '[list=a]', '[list=A]', '[/list=1]', '[/list=a]', '[/list=A]', '[*]'), '_', $message);
            }

            //Remove the code block contents from $message.
            $messagearray = $this->bbcode->parseCodeBlocks($message);
            $message = array();
            for($i = 0; $i < count($messagearray); $i += 2) {
                $message[$i] = $messagearray[$i];
            }
            $message = implode("<!-- code -->", $message);

            // Do BBCode
            $message = $this->rawHTMLmessage($message, $allowhtml);
            $this->bbcode->process($message, $allowimgcode, $allowurlcode);
            if ($smiliesallow) {
                $smileURL = $this->vars->full_url . $this->vars->theme['smdir'] . '/';
                $this->smile->smile($message, $smileURL);
            }
            $message = nl2br($message);

            // Replace the code block contents in $message.
            if (count($messagearray) > 1) {
                $message = explode("<!-- code -->", $message);
                for($i = 0; $i < count($message) - 1; $i++) {
                    $message[$i] .= $this->smile->censor($messagearray[$i*2+1]);
                }
                $message = implode("", $message);
            }

            if ('yes' == $wrap) {
                $this->xmb_wordwrap($message);
            } else {
                $message = str_replace(array('<!-- nobr -->', '<!-- /nobr -->'), array('', ''), $message);
            }
        } else {
            $message = $this->rawHTMLmessage($message, $allowhtml);
            if ($smiliesallow) {
                $smileURL = $this->vars->full_url . $this->vars->theme['smdir'] . '/';
                $this->smile->smile($message, $smileURL);
            }
            $message = nl2br($message);
            if ('yes' == $wrap) {
                $this->xmb_wordwrap($message);
            }
        }

        $message = preg_replace('#(script|about|applet|activex|chrome):#is',"\\1 &#058;",$message);

        return $message;
    }

    /**
     * Wraps long lines but avoids certain elements.
     *
     * @since 1.9.11.12
     * @param string $input Read/Write Variable
     */
    private function xmb_wordwrap(&$input)
    {
        $br = trim(nl2br("\n"));
        $messagearray = preg_split("#<!-- nobr -->|<!-- /nobr -->#", $input);
        for($i = 0; $i < sizeof($messagearray); $i++) {
            if ($i % 2 == 0) {
                $messagearray[$i] = explode($br, $messagearray[$i]);
                foreach($messagearray[$i] as $key => $val) {
                    $messagearray[$i][$key] = wordwrap($val, 150, "\n", TRUE);
                }
                $messagearray[$i] = implode($br, $messagearray[$i]);
            } // else inside nobr block
        }
        $input = implode('', $messagearray);
    }

    /**
     * Processes tags like [file]1234[/file]
     *
     * Caller should query the attachments table.
     *
     * @since 1.9.11
     * @param string $message The post body.
     * @param array $files Contains the result rows from an attachment query.
     * @param int $pid Pass zero when in newthread or reply preview.
     * @param bool $bBBcodeOnForThisPost
     * @param bool $quarantine Are these files in a private table for review?
     * @return string The modified post body.
     */
    public function bbcodeFileTags(string $message, array $files, int $pid, bool $bBBcodeOnForThisPost, bool $quarantine = false): string
    {
        $count = 0;
        $separator = '';
        $htmlencode = true;
        $template = new \XMB\Template($this->vars);
        $template->addRefs();
        foreach ($files as $attach) {
            $post = [];
            $post['filename'] = attrOut($attach['filename']);
            $post['filetype'] = attrOut($attach['filetype']);
            $post['fileurl'] = $this->attach->getURL((int) $attach['aid'], $pid, $attach['filename'], $htmlencode, $quarantine);
            $template->attachsize = $this->attach->getSizeFormatted($attach['filesize']);

            $post['filedims'] = '';
            $output = '';
            $prefix = '';
            $extension = strtolower(get_extension($post['filename']));
            $img_extensions = ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'wbmp', 'wbm', 'bmp'];
            if ($this->vars->settings['attachimgpost'] == 'on' && in_array($extension, $img_extensions)) {
                if ((int) $attach['thumbid'] > 0) {
                    $post['thumburl'] = $this->attach->getURL((int) $attach['thumbid'], $pid, $attach['thumbname'], $htmlencode, $quarantine);
                    $result = explode('x', $attach['thumbsize']);
                    $post['filedims'] = 'width="' . $result[0] . 'px" height="' . $result[1] . 'px"';
                    $template->post = $post;
                    $output = $template->process('viewthread_post_attachmentthumb.php');
                } else {
                    if ($attach['img_size'] != '') {
                        $result = explode('x', $attach['img_size']);
                        $post['filedims'] = 'width="'.$result[0].'px" height="'.$result[1].'px"';
                    }
                    $template->post = $post;
                    $output = $template->process('viewthread_post_attachmentimage.php');
                }
                $separator = '';
            } else {
                $template->downloadcount = (int) $attach['downloads'];
                $template->post = $post;
                $output = $template->process('viewthread_post_attachment.php');
                if ($separator == '') {
                    $prefix = "<br /><br />";
                }
                $separator = "<br /><br />";
            }
            $output = '<!-- nobr -->'.trim(str_replace(array("\n","\r"), array('',''), $output)).'<!-- /nobr -->'; // Avoid nl2br, trailing space, wordwrap.
            if ($count == 0) {
                $prefix = "<br /><br />";
            }
            $matches = 0;
            if ($bBBcodeOnForThisPost) {
                $find = "[file]{$attach['aid']}[/file]";
                $pos = strpos($message, $find);
                if ($pos !== FALSE) {
                    $matches = 1;
                    $message = substr($message, 0, $pos) . $output . substr($message, $pos + strlen($find));
                }
            }
            if ($matches == 0) {
                $message .= $prefix.$output.$separator; // Do we need some sort of a separator template here?
                $count++;
            }
        }
        return $message;
    }

    /**
     * Converts simple tags like [pid]1234[/pid] to storeable tags like [pid=1234&tid=567]Example Thread[/pid]
     *
     * @since 1.9.11
     * @param string $message Read/Write Variable.  Returns the post text with converted tags.
     */
    function postLinkBBcode(string &$message) {
        $items = [];
        $pattern = "@\\[pid](\\d+)\\[/pid]@si";
        preg_match_all($pattern, $message, $results, PREG_SET_ORDER);
        if (count($results) == 0) {
            return;
        }
        foreach ($results as $result) {
            $items[] = $result[1];
        }

        $pids = implode(', ', $items);
        $query = $this->db->query("SELECT p.pid, p.tid, p.subject, t.subject AS tsubject, t.fid FROM " . $this->vars->tablepre . "posts AS p LEFT JOIN " . $this->vars->tablepre . "threads AS t USING (tid) WHERE pid IN ($pids)");
        while ($row = $this->db->fetch_array($query)) {
            $perms = $this->checkForumPermissions($this->forums->getForum((int) $row['fid']));
            if ($perms[$this->vars::PERMS_VIEW] && $perms[$this->vars::PERMS_PASSWORD]) {
                if ($row['subject'] != '') {
                    $subject = stripslashes($row['subject']);
                } else {
                    $subject = stripslashes($row['tsubject']);
                }
                $pattern = "[pid]{$row['pid']}[/pid]";
                $replacement = "[pid={$row['pid']}&amp;tid={$row['tid']}]{$subject}[/pid]";
                $message = str_replace($pattern, $replacement, $message);
            }
        }
    }

    /**
     * Check whether the specified moderator is privileged according a specific forum's list of moderators.
     *
     * @since 1.0
     * @param string $username The username for the moderator of the post.
     * @param string $mods The forums.moderator value of the forum being moderated.
     * @param bool $override Whether to just return 'Moderator', for example by passing a boolean user level.
     * @return string Either 'Moderator' or an empty string.
     */
    function modcheck(string $username, string $mods, bool $override = X_SMOD): string
    {
        $retval = '';
        if ($override) {
            $retval = 'Moderator';
        } else if (X_MOD) {
            $username = strtoupper($username);
            $mods = explode(',', $mods);
            foreach($mods as $key=>$moderator) {
                if (strtoupper(trim($moderator)) === $username) {
                    $retval = 'Moderator';
                    break;
                }
            }
        }

        return $retval;
    }

    /**
     * Check whether the specified moderator is privileged according to a specific post author's status.
     *
     * @since 1.9.10
     * @param string $username The username for the moderator of the post.
     * @param string $mods The forums.moderator value of the forum being moderated.
     * @param string $origstatus The members.status value for the author of the post.
     * @return string Either 'Moderator' or an empty string.
     */
    function modcheckPost($username, $mods, $origstatus)
    {
        global $SETTINGS;
        $retval = $this->modcheck($username, $mods);

        if ($retval != '' && $SETTINGS['allowrankedit'] != 'off') {
            switch($origstatus) {
                case 'Super Administrator':
                    if (!X_SADMIN) {
                        $retval = '';
                    }
                    break;
                case 'Administrator':
                    if (!X_ADMIN) {
                        $retval = '';
                    }
                    break;
                case 'Super Moderator':
                    if (!X_SMOD) {
                        $retval = '';
                    }
                    break;
                //If member does not have X_MOD then modcheck() returned an empty string.  No reason to continue testing.
            }
        }

        return $retval;
    }

    /**
     * As of version 1.9.11, function forum() is not responsible for any permissions checking.
     * Caller should use permittedForums() or getStructuredForums() instead of querying for the parameters.
     *
     * @since 1.0
     */
    function forum($forum, $templateName, $index_subforums)
    {
        $lang = &$this->vars->lang;
        
        $template = new \XMB\Template($this->vars);
        $template->addRefs();

        $forum['name'] = fnameOut($forum['name']);
        null_string($forum['description']);
        $forum['description'] = html_entity_decode($forum['description']);
        $template->forum = $forum;

        if (! empty($forum['lastpost'])) {
            $lastpost = explode('|', $forum['lastpost']);
            $dalast = $lastpost[0];

            // Translate "Anonymous" author.
            $lastpostname = trim($lastpost[1]);
            if ('Anonymous' == $lastpostname) {
                $lastpostname = $lang['textanonymous'];
            }

            $lastPid = isset($lastpost[2]) ? $lastpost[2] : 0;

            $lastpostdate = gmdate($this->vars->dateformat, $this->timeKludge((int) $lastpost[0]));
            $lastposttime = gmdate($this->vars->timecode, $this->timeKludge((int) $lastpost[0]));
            $template->lastpost = "$lastpostdate {$lang['textat']} $lastposttime<br />{$lang['textby']} $lastpostname";
            $template->lastpostrow = $template->process($templateName.'_lastpost.php');
        } else {
            $dalast = 0;
            $lastPid = 0;
            $template->lastpostrow = $template->process($templateName.'_nolastpost.php');
        }

        $oT = strpos($this->vars->oldtopics, "|$lastPid|");
        if ($this->vars->lastvisit < $dalast && $oT === false) {
            $folder = '<img src="'.$this->vars->theme['imgdir'].'/red_folder.gif" alt="'.$lang['altredfolder'].'" border="0" />';
        } else {
            $folder = '<img src="'.$this->vars->theme['imgdir'].'/folder.gif" alt="'.$lang['altfolder'].'" border="0" />';
        }

        if ($dalast == '') {
            $folder = '<img src="'.$this->vars->theme['imgdir'].'/folder.gif" alt="'.$lang['altfolder'].'" border="0" />';
        }
        $template->folder = $folder;

        if (! empty($forum['moderator'])) {
            $list = [];
            $moderators = explode(', ', $forum['moderator']);
            foreach ($moderators as $moderator) {
                $list[] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($moderator).'">'.$moderator.'</a>';
            }
            $moderators = implode(', ', $list);
            $forum['moderator'] = "{$lang['textmodby']} $moderators";
            if ('' !== $forum['description']) {
                $forum['moderator'] = '<br />' . $forum['moderator'];
            }
            $template->forum = $forum;
        }

        $subforums = [];
        if (count($index_subforums) > 0) {
            for($i=0; $i < count($index_subforums); $i++) {
                $sub = $index_subforums[$i];
                if ($sub['fup'] === $forum['fid']) {
                    $subforums[] = '<a href="forumdisplay.php?fid='.intval($sub['fid']).'">'.fnameOut($sub['name']).'</a>';
                }
            }
        }

        if (!empty($subforums)) {
            $subforums = implode(', ', $subforums);
            $subforums = "{$lang['textsubforums']} <span class='plainlinks'>$subforums</span>";
            if ('' !== $forum['description'] || '' != $forum['moderator']) {
                $subforums = '<br />' . $subforums;
            }
        } else {
            $subforums = '';
        }
        $template->subforums = $subforums;

        return $template->process($templateName . '.php');
    }

    /**
     * Handles most of the I/O tasks to create a collection of numbered pages
     * from an ordered collection of items.
     *
     * Caller must echo the returned html directly or in a template variable.
     *
     * @since 1.9.11
     * @param int $num Total number of items in the collection.
     * @param int $perpage Number of items to display on each page.
     * @param string $baseurl Relative URL of the first page in the collection.
     * @param mixed $canonical Optional. Specify FALSE if the $baseurl param is not a canonical URL. Specify a Relative URL string to override $baseurl.
     * @return array Associative indexes: 'html' the link bar string, 'start' the LIMIT int used in queries.
     */
    public function multipage(int $num, int $perpage, string $baseurl, bool $canonical = true): array
    {
        // Initialize
        $return = [];
        $page = getInt('page');
        $max_page = $this->quickpage(intval($num), intval($perpage));
        if ($canonical === true) $canonical = &$baseurl;

        // Calculate the LIMIT start number for queries
        if ($page > 1 && $page <= $max_page) {
            $return['start'] = ($page-1) * $perpage;
            if ($canonical !== false) $this->setCanonicalLink($canonical . ((strpos($baseurl, '?') !== false) ? '&amp;' : '?') . 'page=' . $page);
        } elseif ($page == 0 && !isset($_GET['page'])) {
            $return['start'] = 0;
            $page = 1;
            if ($canonical !== false) $this->setCanonicalLink($canonical);
        } elseif ($page == 1) {
            $newurl = preg_replace('/[^\x20-\x7e]/', '', $this->vars->url);
            $newurl = str_replace('&page=1', '', $newurl);
            $newurl = substr($this->vars->full_url, 0, -strlen($this->vars->cookiepath)).$newurl;
            header('HTTP/1.0 301 Moved Permanently');
            header("Location: $newurl");
            exit;
        } else {
            header('HTTP/1.0 404 Not Found');
            $this->error($this->vars->lang['generic_missing']);
        }

        // Generate the multipage link bar.
        $return['html'] = $this->multi($page, $max_page, $baseurl);

        return $return;
    }

    /**
     * Generates an HTML page-selection bar for any collection of numbered pages.
     *
     * The link to each page in the collection will have the "page" variable added
     * to its query string, except for page number one.
     *
     * @since 1.5.0
     * @param int $page Current page number, must be >= 1.
     * @param int $lastpage Total number of pages in the collection.
     * @param string $mpurl Read-Only Variable. Relative URL of the first page in the collection.
     * @param bool $isself FALSE indicates the page bar will be displayed on a page that is not part of the collection.
     * @return string HTML links. Empty string if the $lastpage parameter was <= 1 or $page was invalid.
     */
    function multi(int $page, int $lastpage, string &$mpurl, bool $isself = true): string
    {
        $multipage = $this->vars->lang['textpages'];

        if ($page >= 1 && $lastpage > 1 && $page <= $lastpage) {
            if ($page >= $lastpage - 3) {
                $to = $lastpage;
            } else {
                $to = $page + 3;
            }

            if ($page <= 4) {
                $from = 1;
            } else {
                $from = $page - 3;
            }

            $to--;
            $from++;

            $string = (strpos($mpurl, '?') !== false) ? '&amp;' : '?';

            // Link to first page
            $multipage .= "\n";
            if ($page != 1 || !$isself) {
                $extra = '';
                if ($isself) {
                    if (2 == $page) {
                        $extra = ' rel="prev start"';
                    } else {
                        $extra = ' rel="start"';
                    }
                }
                $multipage .= '&nbsp;<u><a href="'.$mpurl.'"'.$extra.'>1</a></u>';
                if ($from > 2) {
                    $multipage .= "\n&nbsp;..";
                }
            } else {
                $multipage .= '&nbsp;<strong>1</strong>';
            }

            // Link to current page and up to 2 prev and 2 next pages.
            $multipage .= "\n";
            for($i = $from; $i <= $to; $i++) {
                if ($i != $page) {
                    $extra = '';
                    if ($isself) {
                        if ($i == $page - 1) {
                            $extra = ' rel="prev"';
                        } else if ($i == $page + 1) {
                            $extra = ' rel="next"';
                        }
                        if ($page == 1) {
                            $extra .= ' rev="start"';
                        }
                    }
                    $multipage .= '&nbsp;<u><a href="'.$mpurl.$string.'page='.$i.'"'.$extra.'>'.$i.'</a></u>';
                } else {
                    $multipage .= '&nbsp;<strong>'.$i.'</strong>';
                }
                $multipage .= "\n";
            }

            // Link to last page
            if ($lastpage != $page) {
                if (($lastpage - 1) > $to) {
                    $multipage .= "&nbsp;..\n";
                }
                $extra = '';
                if ($isself) {
                    if ($page == $lastpage - 1) {
                        $extra = ' rel="next"';
                    }
                    if ($page == 1) {
                        $extra .= ' rev="start"';
                    }
                }
                $multipage .= '&nbsp;<u><a href="'.$mpurl.$string.'page='.$lastpage.'"'.$extra.'>'.$lastpage.'</a></u>';
            } else {
                $multipage .= '&nbsp;<strong>'.$lastpage.'</strong>';
            }
        } else {
            $multipage = '';
        }

        return $multipage;
    }

    /**
     * Determine how many pages exist in a paged set of records.
     *
     * @param int $things Total record count.
     * @param int $thingsperpage How many records per page.
     * @return int Total pages.
     */
    public function quickpage(int $things, int $thingsperpage): int
    {
        return ((($things > 0) && ($thingsperpage > 0) && ($things > $thingsperpage)) ? ceil($things / $thingsperpage) : 1);
    }

    /**
     * Generates the HTML to display a smilie picker.
     *
     * @since 1.5.0
     */
    public function smilieinsert(string $type = 'normal'): string
    {
        $counter = 0;
        $smilie = [];
        $sms = [];
        $smcols = (int) $this->vars->settings['smcols'];
        $smtotal = (int) $this->vars->settings['smtotal'];
        if ($type == 'quick') {
            $smcols = 4;
            $smtotal = 16;
        } elseif ($type == 'full') {
            $smtotal = 0;
        }
        $enabled = $this->smile->isAnySmilieInstalled() && $this->vars->settings['smileyinsert'] == 'on' && $smcols > 0;

        if (! $enabled) return '';
        
        $template = new \XMB\Template($this->vars);
        $template->addRefs();

        foreach ($this->smile->smilieCache() as $smilie['code'] => $smilie['url']) {
            $template->smilie = $smilie;
            $sms[] = $template->process('functions_smilieinsert_smilie.php');
            if ($smtotal > 0) {
                if (++$counter >= $smtotal) break;
            }
        }

        $smilies = '<tr>';
        for ($i = 0; $i < count($sms); $i++) {
            $smilies .= $sms[$i];
            if (($i + 1) % $smcols == 0) {
                $smilies .= '</tr>';
                if (($i + 1) < count($sms)) {
                    $smilies .= '<tr>';
                }
            }
        }

        if (count($sms) % $smcols > 0) {
            $left = $smcols - (count($sms) % $smcols);
            for ($i = 0; $i < $left; $i++) {
                $smilies .= '<td />';
            }
            $smilies .= '</tr>';
        }
        
        $template->smilies = $smilies;

        return $template->process('functions_smilieinsert.php');
    }

    /**
     * @since 1.5
     * @param int $fid
     * @param int $oldThreadCount Optional.  Specify the last-seen value of forums.threads to help avoid update races.
     */
    public function updateforumcount(int $fid, ?int $oldThreadCount = null)
    {
        $db = $this->db;

        $counts = $this->sql->getForumCounts($fid);

        $lastpost = $this->sql->findLastpostByForum($fid);

        $this->sql->setForumCounts($fid, $lastpost, $counts['posts'], $counts['threads'], $oldThreadCount);
    }

    /**
     * @since 1.5
     * @param int $tid
     */
    public function updatethreadcount(int $tid, ?int $oldReplyCount = null)
    {
        $db = $this->db;
        $quarantine = false;

        $replycount = $this->sql->countPosts($quarantine, $tid);

        if ($replycount === 0) return; // Sanity check: There are no posts in this thraed.

        $replycount--;
        $query = $db->query("SELECT dateline, author, pid FROM " . $this->vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline DESC, pid DESC LIMIT 1");
        $lp = $db->fetch_array($query);
        $db->free_result($query);
        $query = $db->query("SELECT date, username FROM " . $this->vars->tablepre . "logs WHERE tid = $tid AND action = 'bump' ORDER BY date DESC LIMIT 1");
        if ($db->num_rows($query) == 1) {
            $lb = $db->fetch_array($query);
            $lp['dateline'] = $lb['date'];
            $lp['author'] = $lb['username'];
        }
        $db->free_result($query);
        $lastpost = $lp['dateline'].'|'.$lp['author'].'|'.$lp['pid'];
        $db->escape_fast($lastpost);

        $where = "WHERE tid = $tid";

        if (! is_null($oldReplyCount)) $where .= " AND replies = $oldReplyCount";

        $db->query("UPDATE " . $this->vars->tablepre . "threads SET replies = $replycount, lastpost = '$lastpost' $where");
    }

    /**
     * Generates sub-templates in the $footerstuff array and returns it.
     *
     * @since 1.8.0
     */
    function end_time(): array
    {
        $template = new \XMB\Template($this->vars);
        $template->addRefs();
        
        $footerstuff = [];

        $mtime2 = explode(' ', microtime());
        $endtime = $mtime2[1] + $mtime2[0];

        $totaltime = ($endtime - $this->vars->starttime);

        $footer_options = explode('-', $this->vars->settings['footer_options']);

        if (X_ADMIN && in_array('serverload', $footer_options)) {
            $template->load = $this->ServerLoad();
            if (!empty($template->load)) {
                $footerstuff['load'] = $template->process('footer_load.php');
            } else {
                $footerstuff['load'] = '';
            }
        } else {
            $footerstuff['load'] = '';
        }

        if (in_array('queries', $footer_options)) {
            $template->querynum = $this->db->getQueryCount();
            $footerstuff['querynum'] = $template->process('footer_querynum.php');
        } else {
            $footerstuff['querynum'] = '';
        }

        if (in_array('phpsql', $footer_options)) {
            $template->db_duration = number_format(($this->db->getDuration() / $totaltime) * 100, 1);
            $template->php_duration = number_format((1 - ($this->db->getDuration() / $totaltime)) * 100, 1);
            $footerstuff['phpsql'] = $template->process('footer_phpsql.php');
        } else {
            $footerstuff['phpsql'] = '';
        }

        if (in_array('loadtimes', $footer_options) && X_ADMIN) {
            $template->totaltime = number_format($totaltime, 7);
            $footerstuff['totaltime'] = $template->process('footer_totaltime.php');
        } else {
            $footerstuff['totaltime'] = '';
        }

        if (X_SADMIN && $this->vars->debug) {
            $footerstuff['querydump'] = $this->debug->printAllQueries();
        } else {
            $footerstuff['querydump'] = '';
        }
        
        return $footerstuff;
    }

    /**
     * @since 1.9.1
     */
    function redirect(string $path, int $timeout = 2)
    {
        if (strpos(urldecode($path), "\n") !== false || strpos(urldecode($path), "\r") !== false) {
            throw new InvalidArgumentException('Tried to redirect to potentially insecure url.');
        }

        if (headers_sent()) {
            $template = new \XMB\Template($this->vars);
            $template->path = addslashes($path);
            $template->timeout = $timeout * 1000;
            $template->process('functions_redirect.php', echo: true);
        } else {
            if ($timeout == 0) {
                header('HTTP/1.0 302 Found');
                header("Location: $path");
                exit;
            } else {
                header("Refresh: $timeout; URL=$path");
            }
        }
    }

    /**
     * @since 1.9.1
     */
    function ServerLoad()
    {
        if ($stats = @exec('uptime')) {
            $parts = explode(',', $stats);
            $count = count($parts);
            $first = explode(' ', $parts[$count-3]);
            $c = count($first);
            $first = $first[$c-1];
            return array($first, $parts[$count-2], $parts[$count-1]);
        } else {
            return array();
        }
    }

    /**
     * Display a themed error message.
     *
     * @since 1.9.1
     */
    function error(
        string $msg,
        bool $showheader = true,
        string $prepend = '',
        string $append = '',
        ?string $redirect = null,
        bool $die = true,
        bool $return_as_string = false,
        bool $showfooter = true,
        bool $isError = true,
    ): string {
        $template = $this->template;

        $template->message = $msg;

        if ($isError) {
            $name = 'error';
        } else {
            $name = 'message';
        }

        if ($showheader) {
            $this->nav($this->vars->lang[$name]);
        }

        if (is_string($redirect)) {
            $this->redirect($redirect, timeout: 3);
        }

        if ($showheader) {
            $template->header = $template->process('header.php');
        } else {
            $template->header = '';
        }

        $error = $template->process($name . '.php');

        if ($showfooter) {
            $template->footerstuff = $this->end_time();
            $footer = $template->process('footer.php');
        } else {
            $footer = '';
        }

        if ($return_as_string) {
            $return = $prepend . $error . $append . $footer;
        } else {
            echo $prepend, $error, $append, $footer;
            $return = '';
        }

        if ($die) {
            exit();
        }

        return $return;
    }

    /**
     * Displays a themed message.
     *
     * This helper method is now an alias of error($msg, isError: false).
     *
     * @since 1.9.8
     */
    function message(
        string $msg,
        bool $showheader = true,
        string $prepend = '',
        string $append = '',
        ?string $redirect = null,
        bool $die = true,
        bool $return_as_string = false,
        bool $showfooter = true
    ): string {
        return $this->error($msg, $showheader, $prepend, $append, $redirect, $die, $return_as_string, $showfooter, isError: false);
    }

    /**
     * Returns a themed error message.
     *
     * @since 1.9.10
     */
    function softerror($msg): string {
        return $this->error(
            $msg,
            showheader: false,
            append: '<br />',
            die: false,
            return_as_string: true,
            showfooter: false,
        );
    }

    /**
     * XMB's Cookie helper.
     *
     * @since 1.9.1
     */
    function put_cookie(string $name, string $value = '', int $expire = 0, ?string $path = null, ?string $domain = null, bool $secure = false)
    {
        // Make sure the output stream is still empty.  Otherwise, someone called this function at the wrong time.
        if (headers_sent()) {
            trigger_error('Attempted use of put_cookie() after headers already sent.', E_USER_WARNING);
            return;
        }

        // Default arguments were poorly chosen, so let's try to fill them in now.
        if (is_null($path)) $path = $this->vars->cookiepath;
        if (is_null($domain)) $domain = $this->vars->cookiedomain;
        if (! $secure) $secure = $this->vars->cookiesecure;
        $httponly = true;
        $samesite = 'Lax';

        $options = [
            'expires' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ];
        setcookie($name, $value, $options);
    }

    /**
     * Record a moderator or admin action for auditing.
     *
     * @since 1.9.1
     * @param string $user The plain text version of the username.
     * @param string $action The script or query used.
     * @param int $fid The forum ID used.
     * @param int $tid The thread ID used.
     * @param int $timestamp The time of the log entry.
     */
    function audit(string $user, string $action, int $fid = 0, int $tid = 0)
    {
        $action = cdataOut($action);

        $this->sql->addLog($user, $action, $fid, $tid, $this->vars->onlinetime);

        return true;
    }

    /**
     * Send a mail message.
     *
     * Works just like php's mail() function, but allows sending trough alternative mailers as well.
     *
     * @since 1.9.2
     * @return bool Success
     */
    private function altMail(string $to, string $subject, string $message, string $additional_headers = '', string $additional_parameters = ''): bool
    {
        $mailer = &$this->vars->mailer;

        $message = str_replace(["\r\n", "\r", "\n"], ["\n", "\n", "\r\n"], $message);
        $subject = str_replace(["\r", "\n"], ['', ''], $subject);

        if ($mailer['type'] == 'socket_SMTP') {
            require_once(XMB_ROOT . 'include/smtp.inc.php');

            if (! isset($this->mailConnections['socket_SMTP'])) {
                if ($this->vars->debug) {
                    $mail = new socket_SMTP(true, XMB_ROOT . 'smtp-log.txt');
                } else {
                    $mail = new socket_SMTP;
                }
                $this->mailConnections['socket_SMTP'] = $mail;
                if (! $mail->connect($mailer['host'], $mailer['port'], $mailer['username'], $mailer['password'])) {
                    return false;
                }
                register_shutdown_function(array($mail, 'disconnect'));
            } else {
                $mail = $this->mailConnections['socket_SMTP'];
                if (false === $mail->connection) {
                    return false;
                }
            }

            $subjectInHeader = false;
            $toInHeader = false;
            $additional_headers = explode("\r\n", $additional_headers);
            foreach ($additional_headers as $k => $h) {
                if (strpos(trim($h), 'ubject:') === 1) {
                    $additional_headers[$k] = "Subject: $subject\r\n";
                    $subjectInHeader = true;
                    continue;
                }

                if (strpos(trim(strtolower($h)), 'to:') === 0) {
                    $toInHeader = true;
                }
            }

            if (! $subjectInHeader) {
                $additional_headers[] = "Subject: $subject";
            }

            if (! $toInHeader) {
                $additional_headers[] = "To: $to";
            }

            $additional_headers = implode("\r\n", $additional_headers);

            return $mail->sendMessage($this->vars->settings['adminemail'], $to, $message, $additional_headers);
        } else {
            if (ini_get('safe_mode') == "1") {
                $return = mail($to, $subject, $message, $additional_headers);
            } else {
                $return = mail($to, $subject, $message, $additional_headers, $additional_parameters);
            }
            if (! $return) {
                $msg = 'XMB failed to send an e-mail because the PHP mail() function returned FALSE!  This might be caused by using an invalid address in XMB\'s Administrator E-Mail setting.';
                trigger_error($msg, E_USER_WARNING);
            }
            return $return;
        }
    }

    /**
     * Takes a system timestamp and uses the weird XMB logic to convert it to a 'local' timestamp.
     *
     * Although this was somewhat standardized in older versions, the code had been duplicated for every display in the system.
     *
     * @since 1.10.00
     */
    public function timeKludge(int $timestamp): int
    {
        $userHours = (float) $this->vars->timeoffset;

        $userOffset = (int) ($userHours * 3600);

        return $this->standardTime($timestamp) + $userOffset;
    }

    /**
     * Takes a system timestamp and uses the weird XMB logic to convert it to a UTC(?) timestamp.
     *
     * Although this was somewhat standardized in older versions, the code had been duplicated for every display in the system.
     *
     * @since 1.10.00
     */
    public function standardTime(int $timestamp): int
    {
        $extraHours = (float) $this->vars->settings['addtime'];

        $extraOffset = (int) ($extraHours * 3600);

        return $timestamp + $extraOffset;
    }

    /**
     * @since 1.9.8 SP2
     *
     * This function is recursive.  Why?
     */
    public function printGmDate($timestamp=null, $altFormat=null, $altOffset=0)
    {
        global $dateformat, $SETTINGS, $timeoffset;

        if ($timestamp === null) {
            $timestamp = time();
        }

        if ($altFormat === null) {
            $altFormat = $dateformat;
        }

        $f = false;
        if ((($pos = strpos($altFormat, 'F')) !== false && $f = true) || ($pos2 = strpos($altFormat, 'M')) !== false) {
            $startStr = substr($altFormat, 0, $pos);
            $endStr = substr($altFormat, $pos+1);
            $month = gmdate('m', intval($timestamp + ($timeoffset*3600)+(($altOffset+$SETTINGS['addtime'])*3600)));
            $textM = $this->month2text($month);
            return $this->printGmDate($timestamp, $startStr, $altOffset) . substr($textM,0, ($f ? strlen($textM) : 3)) . $this->printGmDate($timestamp, $endStr, $altOffset);
        } else {
            return gmdate($altFormat, intval($timestamp + ($timeoffset * 3600) + (($altOffset+$SETTINGS['addtime']) * 3600)));
        }
    }

    /**
     * @since 1.9.8
     */
    private function month2text(int $num): string
    {
        if ($num < 1 || $num > 12) {
            $num = 1;
        }

        $months = [
            1 => 'textjan',
            2 => 'textfeb',
            3 => 'textmar',
            4 => 'textapr',
            5 => 'textmay',
            6 => 'textjun',
            7 => 'textjul',
            8 => 'textaug',
            9 => 'textsep',
            10 => 'textoct',
            11 => 'textnov',
            12 => 'textdec',
        ];

        return $this->vars->lang[$months[$num]];
    }

    /**
     * Creates a multi-dimensional array of forums.
     *
     * The array uses the following associative subscripts:
     *  0:forums.type
     *  1:forums.fup (always '0' for groups)
     *  2:forums.fid
     *  3:forums.*
     * Usage example:
     *  $forums = getStructuredForums();
     *  echo fnameOut($forums['forum']['9']['14']['name']);
     *
     * @since 1.9.11
     * @param bool $usePerms If TRUE then not all forums are returned, only visible forums.
     * @return array
     */
    function getStructuredForums(bool $usePerms = false): array
    {
        if ($usePerms) {
            $forums = $this->permittedForums('forum');
        } else {
            $forums = $this->forums->forumCache();
        }

        // This function guarantees the following subscripts exist, regardless of forum count.
        $structured['group'] = [
            '0' => [],
        ];
        $structured['forum'] = [
            '0' => [],
        ];
        $structured['sub'] = [];

        foreach ($forums as $forum) {
            $structured[$forum['type']][$forum['fup']][$forum['fid']] = $forum;
        }

        return $structured;
    }

    /**
     * Creates an array of permitted forum records containing FIDs permitted for the current user.
     *
     * @since 1.9.11
     * @param string $mode Whether to check for 'forum' listing permissions or 'thread' listing permissions.
     * @param bool $check_parents Indicates whether each forum's permissions depend on the parent forum also being permitted.
     * @param string $user_status Optional masquerade value passed to checkForumPermissions().
     * @return array
     */
    public function permittedForums(string $mode = 'thread', bool $check_parents = true, ?string $user_status = null): array
    {
        $permitted = [];
        $fids = [
            'group' => [],
            'forum' => [],
            'sub' => [],
        ];

        $forumcache = $this->forums->forumCache();

        foreach ($forumcache as $forum) {
            $perms = $this->checkforumpermissions($forum, $user_status);
            if ($mode == 'thread') {
                // Forum groups are technically permitted. They need to be included in parent status checking later, but don't need to be included in the output.
                if ($forum['type'] == 'group' || ($perms[$this->vars::PERMS_VIEW] && $perms[$this->vars::PERMS_PASSWORD])) {
                    if ($forum['type'] !== 'group') {
                        $permitted[] = $forum;
                    }
                    $fids[$forum['type']][] = $forum['fid'];
                }
            } elseif ($mode == 'forum') {
                if ($this->vars->settings['hideprivate'] == 'off' || $forum['type'] == 'group' || $perms[$this->vars::PERMS_VIEW]) {
                    $permitted[] = $forum;
                    $fids[$forum['type']][] = $forum['fid'];
                }
            }
        }

        if ($check_parents) {
            // Rebuild the $fids['forum'] array using the enabled group list.
            $filtered = [];
            $fids['forum'] = [];
            foreach ($permitted as $forum) {
                if ($forum['type'] == 'group') {
                    $filtered[] = $forum;
                } elseif ($forum['type'] == 'forum') {
                    if (intval($forum['fup']) == 0 || array_search($forum['fup'], $fids['group']) !== false) {
                        $filtered[] = $forum;
                        $fids['forum'][] = $forum['fid'];
                    }
                }
            }

            // Finish building the $filtered list by adding sub-forums with permitted parents.
            foreach ($permitted as $forum) {
                if ($forum['type'] == 'sub') {
                    if (intval($forum['fup']) == 0 || array_search($forum['fup'], $fids['forum']) !== false) {
                        $filtered[] = $forum;
                    }
                }
            }

            $permitted = $filtered;
        }

        return $permitted;
    }

    /**
     * Creates an array of permitted forum FIDs for the current user when viewing thread info.
     *
     * This method eliminates the need for any arguments and coerces all array values to int.
     *
     * @since 1.10.00
     * @return array
     */
    public function permittedFIDsForThreadView(): array
    {
        $forums = $this->permittedForums();
        $fids = array_column($forums, 'fid');
        return array_map('intval', $fids);
    }

    /**
     * Simulates needed SQL results using the forum cache.
     *
     * @since 1.9.11
     * @param array $forums Read-Only Variable. Must be a return value from the function getStructuredForums()
     * @param array $cat
     * @param bool  $catsonly
     * @return array Two-dimensional array of forums (arrays of strings) sorted by the group's displayorder, then the forum's displayorder.
     */
    public function getIndexForums(array $forums, array $cat, bool $catsonly): array {
        $sorted = [];

        if (isset($cat['fid'])) {
            // Group forums.
            if (isset($forums['forum'][$cat['fid']])) {
                foreach($forums['forum'][$cat['fid']] as $forum) {
                    $forum['cat_fid'] = $cat['fid'];
                    $forum['cat_name'] = $cat['name'];
                    $sorted[] = $forum;
                }
            }
        } elseif ($catsonly) {
            // Groups instead of forums.
            foreach($forums['group']['0'] as $group) {
                $group['cat_fid'] = $group['fid'];
                $group['cat_name'] = $group['name'];
                $sorted[] = $group;
            }
        } else {
            // Ungrouped forums.
            foreach($forums['forum']['0'] as $forum) {
                $forum['cat_fid'] = '0';
                $forum['cat_name'] = '';
                $sorted[] = $forum;
            }
            // Grouped forums.
            foreach($forums['group']['0'] as $group) {
                if (isset($forums['forum'][$group['fid']])) {
                    foreach($forums['forum'][$group['fid']] as $forum) {
                        $forum['cat_fid'] = $group['fid'];
                        $forum['cat_name'] = $group['name'];
                        $sorted[] = $forum;
                    }
                }
            }
        }
        return $sorted;
    }

    /**
     * Generates the forum select HTML for things like search input.
     *
     * @since 1.9.8
     * @param string $selectname The HTML name attribute value.
     * @param bool $multiple Should the element render as a multi-select control?
     * @param bool $allowall Should an "All" forums choice be offered?
     * @param int $currentfid Context-sensitive forum ID.
     */
    public function forumList(string $selectname='srchfid', bool $multiple = false, bool $allowall = true, int $currentfid = 0)
    {
        $lang = &$this->vars->lang;

        // Initialize $forumselect
        $forumselect = [];
        if (!$multiple) {
            $forumselect[] = '<select name="'.$selectname.'">';
        } else {
            $forumselect[] = '<select name="'.$selectname.'[]" size="10" multiple="multiple">';
        }

        if ($allowall) {
            if ($currentfid == 0) {
                $forumselect[] = '<option value="all" selected="selected">'.$lang['textallforumsandsubs'].'</option>';
            } else {
                $forumselect[] = '<option value="all">'.$lang['textallforumsandsubs'].'</option>';
            }
        } else if (!$allowall && !$multiple) {
            $forumselect[] = '<option value="" disabled="disabled" selected="selected">'.$lang['textforum'].'</option>';
        }

        // Populate $forumselect
        $permitted = $this->getStructuredForums(usePerms: true);

        foreach($permitted['forum']['0'] as $forum) {
            $forumselect[] = '<option value="'.intval($forum['fid']).'"'.($forum['fid'] == $currentfid ? ' selected="selected"' : '').'> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
            if (isset($permitted['sub'][$forum['fid']])) {
                foreach($permitted['sub'][$forum['fid']] as $sub) {
                    $forumselect[] = '<option value="'.intval($sub['fid']).'"'.($sub['fid'] == $currentfid ? ' selected="selected"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
                }
            }
        }

        $forumselect[] = '<option value="0" disabled="disabled">&nbsp;</option>';
        foreach($permitted['group']['0'] as $group) {
            if (isset($permitted['forum'][$group['fid']]) && count($permitted['forum'][$group['fid']]) > 0) {
                $forumselect[] = '<option value="'.intval($group['fid']).'" disabled="disabled">'.fnameOut($group['name']).'</option>';
                foreach($permitted['forum'][$group['fid']] as $forum) {
                    $forumselect[] = '<option value="'.intval($forum['fid']).'"'.($forum['fid'] == $currentfid ? ' selected="selected"' : '').'> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
                    if (isset($permitted['sub'][$forum['fid']])) {
                        foreach($permitted['sub'][$forum['fid']] as $sub) {
                            $forumselect[] = '<option value="'.intval($sub['fid']).'"'.($sub['fid'] == $currentfid ? ' selected="selected"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
                        }
                    }
                }
            }
            $forumselect[] = '<option value="" disabled="disabled">&nbsp;</option>';
        }
        $forumselect[] = '</select>';
        return implode("\n", $forumselect);
    }

    /**
     * Generates the forum quick jump HTML.
     *
     * @since 1.9.8
     */
    public function forumJump(): string
    {
        $full_url = $this->vars->full_url;
        
        // Initialize $forumselect
        $forumselect = [];
        $checkid = max(getInt('fid', 'r'), getInt('gid', 'r'));

        $forumselect[] = "<select onchange=\"if (this.options[this.selectedIndex].value) {window.location=(''+this.options[this.selectedIndex].value)}\">";
        $forumselect[] = '<option value="">' . $this->vars->lang['forumjumpselect'] . '</option>';

        // Populate $forumselect
        $permitted = $this->getStructuredForums(usePerms: true);

        if (0 == count($permitted['group']['0']) && 0 == count($permitted['forum']['0'])) {
            return '';
        }

        foreach($permitted['forum']['0'] as $forum) {
            $dropselc1 = ($checkid == $forum['fid']) ? $this->vars::selHTML : '';
            $forumselect[] = '<option value="' . $full_url . 'forumdisplay.php?fid='.intval($forum['fid']).'" '.$dropselc1.'> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
            if (isset($permitted['sub'][$forum['fid']])) {
                foreach($permitted['sub'][$forum['fid']] as $sub) {
                    $dropselc2 = ($checkid == $sub['fid']) ? $this->vars::selHTML : '';
                    $forumselect[] = '<option value="' . $full_url . 'forumdisplay.php?fid='.intval($sub['fid']).'" '.$dropselc2.'>&nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
                }
            }
        }

        foreach($permitted['group']['0'] as $group) {
            if (isset($permitted['forum'][$group['fid']])) {
                $dropselc3 = ($checkid == $group['fid']) ? $this->vars::selHTML : '';
                $forumselect[] = '<option value=""></option>';
                $forumselect[] = '<option value="' . $full_url . 'index.php?gid='.intval($group['fid']).'" '.$dropselc3.'>'.fnameOut($group['name']).'</option>';
                foreach($permitted['forum'][$group['fid']] as $forum) {
                    $dropselc4 = ($checkid == $forum['fid']) ? $this->vars::selHTML : '';
                    $forumselect[] = '<option value="' . $full_url . 'forumdisplay.php?fid='.intval($forum['fid']).'" '.$dropselc4.'> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
                    if (isset($permitted['sub'][$forum['fid']])) {
                        foreach($permitted['sub'][$forum['fid']] as $sub) {
                            $dropselc5 = ($checkid == $sub['fid']) ? $this->vars::selHTML : '';
                            $forumselect[] = '<option value="' . $full_url . 'forumdisplay.php?fid='.intval($sub['fid']).'" '.$dropselc5.'>&nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
                        }
                    }
                }
            }
        }
        $forumselect[] = '</select>';
        return implode("\n", $forumselect);
    }

    /**
     * Creates a set of boolean permissions for a specific forum.
     *
     * Normal Usage Example
     *  $fid = 1;
     *  $forum = getForum($fid);
     *  $perms = checkForumPermissions($forum);
     *  if ($perms[X_PERMS_VIEW]) { //$self is allowed to view $forum }
     * Masquerade Example
     *  $result = $db->query('SELECT * FROM '.X_PREFIX.'members WHERE uid=1');
     *  $user = $db->fetch_array($result);
     *  $perms = checkForumPermissions($forum, $user['status']);
     *  if ($perms[X_PERMS_VIEW]) { //$user is allowed to view $forum }
     * Masquerade Example 2
     *  $perms = checkForumPermissions($forum, 'Moderator');
     *  if ($perms[X_PERMS_VIEW]) { //Moderators are allowed to view $forum }
     *
     * @since 1.9.10
     * @param array $forum One query row from the forums table, preferably provided by getForum().
     * @param string $user_status_in Optional. Masquerade as this user status, e.g. 'Guest'
     * @return array Of bools, indexed by X_PERMS_* constants.
     */
    function checkForumPermissions($forum, ?string $user_status_in = null)
    {
        if (is_string($user_status_in)) {
            $user_status = $this->vars->status_enum[$user_status_in];
        } else {
            $user_status = $this->vars->status_enum[$this->vars->self['status']];
        }

        // 1. Initialize $ret with zero permissions
        $ret = array_fill(0, $this->vars::PERMS_COUNT, false);
        $ret[$this->vars::PERMS_POLL] = false;
        $ret[$this->vars::PERMS_THREAD] = false;
        $ret[$this->vars::PERMS_REPLY] = false;
        $ret[$this->vars::PERMS_VIEW] = false;
        $ret[$this->vars::PERMS_USERLIST] = false;
        $ret[$this->vars::PERMS_PASSWORD] = false;

        // 2. Check Forum Postperm
        $pp = explode(',', $forum['postperm']);
        foreach($pp as $key=>$val) {
            if ((intval($val) & $user_status) != 0) {
                $ret[$key] = true;
            }
        }

        // 3. Check Forum Userlist
        if (is_null($user_status_in)) {
            $userlist = $forum['userlist'];

            if ($this->modcheck($this->vars->self['username'], $forum['moderator'], false) == "Moderator") {
                $ret[$this->vars::PERMS_USERLIST] = true;
                $ret[$this->vars::PERMS_VIEW] = true;
            } elseif (!X_GUEST) {
                $users = explode(',', $userlist);
                foreach($users as $user) {
                    if (strtolower(trim($user)) === strtolower($this->vars->self['username'])) {
                        $ret[$this->vars::PERMS_USERLIST] = true;
                        $ret[$this->vars::PERMS_VIEW] = true;
                        break;
                    }
                }
            }
        }

        // 4. Check COPPA Flag
        $coppa = $this->coppa_check();

        // 5. Set Effective Permissions
        $ret[$this->vars::PERMS_POLL]   = $ret[$this->vars::PERMS_RAWPOLL]   && $coppa;
        $ret[$this->vars::PERMS_THREAD] = $ret[$this->vars::PERMS_RAWTHREAD] && $coppa;
        $ret[$this->vars::PERMS_REPLY]  = $ret[$this->vars::PERMS_RAWREPLY]  && $coppa;
        $ret[$this->vars::PERMS_VIEW]   = $ret[$this->vars::PERMS_RAWVIEW] || $ret[$this->vars::PERMS_USERLIST];

        // 6. Check Forum Password
        $pwinput = getPhpInput('fidpw' . $forum['fid'], 'c');
        if ($forum['password'] == '' || $pwinput === $forum['password']) {
            $ret[$this->vars::PERMS_PASSWORD] = true;
        }

        return $ret;
    }

    /**
     * Enables you to do complex comparisons without string parsing.
     *
     * Normal Usage Example
     *  $fid = 1;
     *  $forum = getForum($fid);
     *  $viewperms = getOneForumPerm($forum, X_PERMS_RAWVIEW);
     *  if ($viewperms >= $status_enum['Member']) { //Some non-staff status has perms to view $forum }
     *  if ($viewperms == $status_enum['Guest']) { //$forum is guest-only }
     *  if ($viewperms == $status_enum['Member'] - 1) { //$forum is staff-only }
     *
     * @since 1.9.11
     * @param array $forum
     * @param int $bitfield Enumerated by X_PERMS_RAW* constants.  Other X_PERMS_* values will not work!
     * @return int
     */
    function getOneForumPerm(array $forum, int $bitfield): int
    {
        $pp = explode(',', $forum['postperm']);
        return (int) $pp[$bitfield];
    }

    /**
     * Displays a forum-specific password prompt, and accepts password input.
     *
     * Should be called when checkForumPermissions() shows X_PERMS_PASSWORD == false and the user is trying to access the forum.
     *
     * @since 1.9 Formerly known as pwverify().
     * @since 1.9.9
     */
    function handlePasswordDialog(int $fid)
    {
        $pwinput = getPhpInput('pw');
        $forum = $this->forums->getForum($fid);

        if (strlen($pwinput) != 0 && $forum !== null) {
            if ($pwinput === $forum['password']) {
                $this->put_cookie('fidpw' . $fid, $forum['password'], time() + (86400*30));
                $newurl = preg_replace('/[^\x20-\x7e]/', '', $this->vars->url);
                if (substr($newurl, 0, 1) === '/') {
                    // This is normal.
                    $newurl = $this->vars->full_url . substr($newurl, strlen($this->vars->cookiepath));
                } elseif (substr($newurl, 0, strlen($this->vars->full_url)) !== $this->vars->full_url) {
                    // This is unexpected and should be audited.  If the URI doesn't start with a slash or a full URL then something is misconfigured on the client or the server.
                    trigger_error('Forum password posted with unexpected URI: ' . $this->vars->url, E_USER_WARNING);
                    $newurl = $this->vars->full_url;
                }
                $this->redirect($newurl, timeout: 0);
                exit();
            } else {
                $message = $this->vars->lang['invalidforumpw'];
            }
        } else {
            $message = $this->vars->lang['forumpwinfo'];
        }

        $template = new \XMB\Template($this->vars);
        $template->addRefs();

        $template->label = str_replace('$forum', $forum['name'], $this->vars->lang['textpasswordForum']);
        $pwform = $template->process('forumdisplay_password.php');
        $this->message($message, append: $pwform);
    }

    /**
     * Creates a templated link to the forum search page.
     *
     * @since 1.9.11
     * @param int $fid Optional. Current FID number used to create a context-sensitive search.
     * @return string The search link, or empty string if search is disabled.
     */
    public function makeSearchLink(int $fid = 0): string
    {
        if ($this->vars->settings['searchstatus'] == 'on') {
            $template = new \XMB\Template($this->vars);
            $template->addRefs();
            if ($fid == 0) {
                $query = '';
            } else {
                $query = "?fid=$fid";
            }
            $template->searchURL = $this->vars->full_url . 'search.php' . $query;
            $template->imageURL = $this->vars->full_url . $this->vars->theme['imgdir'] . '/top_search.gif';
            return $template->process('functions_search_link.php');
        } else {
            return '';
        }
    }

    /**
     * Sets an SEO variable used in the header template to indicate the proper current relative URI.
     *
     * @since 1.9.11
     * @param string $relURI Path to the current page, relative to the base href (see header.php).
     */
    public function setCanonicalLink($relURI)
    {
        $testurl = $this->vars->cookiepath;
        if ($relURI != './') {
            $testurl .= str_replace('&amp;', '&', $relURI);
        }
        if ($this->vars->url !== $testurl) {
            $relURI = $this->vars->full_url . $relURI;
            $this->template->canonical_link = "<link rel='canonical' href='$relURI' />\n";
        }
    }

    /**
     * Simple SMTP message From header formation.
     *
     * @since 1.9.11.08
     * @param string $fromname Will be converted to an SMTP quoted string.
     * @param string $fromaddress Must be a fully validated e-mail address.
     * @return string
     */
    private function smtpHeaderFrom(string $fromname, string $fromaddress): string
    {
        $fromname = preg_replace('@([^\\t !\\x23-\\x5b\\x5d-\\x7e])@', '\\\\$1', $fromname);
        return 'From: "'.$fromname.'" <'.$fromaddress.'>';
    }

    /**
     * Generate a nonce.
     *
     * The XMB schema is currently limited to a 12-byte key length, and as such
     * does not offer user uniqueness beyond simple randomization.
     *
     * \XMB\Token\create() replaces this function for all purposes other than anonymous captcha.
     *
     * @since 1.9.11.11
     * @param string $key The known value, such as what the nonce may be used for.
     * @return string
     */
    public function nonce_create(string $key): string
    {
        global $self;
        
        $db = $this->db;

        $key = substr($key, 0, $this->vars::NONCE_KEY_LEN);
        $db->escape_fast($key);
        $nonce = bin2hex(random_bytes(16));
        $time = time();
        $db->query("INSERT INTO " . $this->vars->tablepre . "captchaimages (imagehash, imagestring, dateline) VALUES ('$nonce', '$key', '$time')");

        return $nonce;
    }

    /**
     * Reveal the nonce/key pair to the user, as in CAPTCHA.
     *
     * @since 1.9.11.11
     * @param  string $nonce The user input.
     * @param  int    $key_length The known length of the key.
     * @return string The key value.
     */
    public function nonce_peek(string $nonce, int $key_length): string
    {
        $db = $this->db;

        $key_length = (int) $key_length;
        if ($key_length >= $this->vars::NONCE_KEY_LEN) return '';  //Since the schema is so constrained, keep all the 12-byte keys secure.

        $db->escape_fast($nonce);
        $time = time() - $this->vars::NONCE_MAX_AGE;
        $result = $db->query(
            "SELECT imagestring
             FROM " . $this->vars->tablepre . "captchaimages
             WHERE imagehash = '$nonce' AND dateline >= $time AND LENGTH(imagestring) = $key_length"
        );
        if ($db->num_rows($result) === 1) {
            return $db->result($result);
        }
        return '';
    }

    /**
     * Test a nonce.
     *
     * @since 1.9.11.11
     * @param string $key The same value used in nonce_create().
     * @param string $nonce The user input.
     * @param int    $expire Optional. Number of seconds for which any nonce having the same $key will be valid.
     * @return bool True only if the user provided a unique nonce for the key/nonce pair.
     */
    public function nonce_use(string $key, string $nonce, int $expire = 0): bool
    {
        $db = $this->db;

        $key = substr($key, 0, $this->vars::NONCE_KEY_LEN);
        $db->escape_fast($key);
        $db->escape_fast($nonce);
        $time = time() - $this->vars::NONCE_MAX_AGE;
        $sql_expire = "dateline < $time";
        if ($expire > 0 && $expire < $this->vars::NONCE_MAX_AGE) {
            $time = time() - $expire;
            $sql_expire .= " OR imagestring='$key' AND dateline < $time";
        }
        $db->query("DELETE FROM " . $this->vars->tablepre . "captchaimages WHERE $sql_expire");
        $db->query("DELETE FROM " . $this->vars->tablepre . "captchaimages WHERE imagehash = '$nonce' AND imagestring = '$key'");

        return ($db->affected_rows() === 1);
    }

    /**
     * Send email with default headers.
     *
     * @since 1.9.11.15
     * @param string $to      Pass through to altMail()
     * @param string $subject Pass through to altMail()
     * @param string $message Pass through to altMail()
     * @param string $charset The character set used in $message param.
     * @param bool   $html    Optional. Set to true if the $message param is HTML formatted.
     * @return bool
     */
    public function xmb_mail(string $to, string $subject, string $message, string $charset, bool $html = false): bool
    {
        global $self, $bbname, $cookiedomain;

        if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32') {  // Official XMB hack for PHP bug #45305 a.k.a. #28038
            ini_set('sendmail_from', $this->vars->settings['adminemail']);
        }

        $rawbbname = htmlspecialchars_decode($this->vars->settings['bbname'], ENT_NOQUOTES);
        $rawusername = htmlspecialchars_decode($this->vars->self['username'] ?? '', ENT_QUOTES);

        if ($html) {
            $content_type = 'text/html';
        } else {
            $content_type = 'text/plain';
        }

        $headers = [];
        $headers[] = $this->smtpHeaderFrom($rawbbname, $this->vars->settings['adminemail']);
        $headers[] = 'X-Mailer: PHP';
        $headers[] = 'X-AntiAbuse: Board servername - ' . $this->vars->cookiedomain;
        if ($rawusername != '') {
            $headers[] = "X-AntiAbuse: Username - $rawusername";
        }
        $headers[] = "Content-Type: $content_type; charset=$charset";
        $headers = implode("\r\n", $headers);

        $params = '-f ' . $this->vars->settings['adminemail'];

        return $this->altMail($to, $subject, $message, $headers, $params);
    }

    /**
     * Generates the HTML for the timezone dropdown list.
     *
     * Caller needs to pre-load the timezone_control template for efficiency.
     *
     * @since 1.9.12
     * @param string $offset Must be in the MySQL Decimal format with 2 places after the decimal.
     * @return string HTML
     */
    function timezone_control(string $offset): string
    {
        $template = new \XMB\Template($this->vars);
        $template->addRefs();

        $total = 37;
        
        $sel = [];
        for ($i = 1; $i <= $total; $i++) {
            $sel[$i] = '';
        }
        
        $offset = number_format((float) $offset, 2);

        switch($offset) {
        case '-12.00':
            $sel[1] = $this->vars::selHTML;
            break;
        case '-11.00':
            $sel[2] = $this->vars::selHTML;
            break;
        case '-10.00':
            $sel[3] = $this->vars::selHTML;
            break;
        case '-9.50':
            $sel[37] = $this->vars::selHTML;
            break;
        case '-9.00':
            $sel[4] = $this->vars::selHTML;
            break;
        case '-8.00':
            $sel[5] = $this->vars::selHTML;
            break;
        case '-7.00':
            $sel[6] = $this->vars::selHTML;
            break;
        case '-6.00':
            $sel[7] = $this->vars::selHTML;
            break;
        case '-5.00':
            $sel[8] = $this->vars::selHTML;
            break;
        case '-4.00':
            $sel[9] = $this->vars::selHTML;
            break;
        case '-3.50':
            $sel[10] = $this->vars::selHTML;
            break;
        case '-3.00':
            $sel[11] = $this->vars::selHTML;
            break;
        case '-2.00':
            $sel[12] = $this->vars::selHTML;
            break;
        case '-1.00':
            $sel[13] = $this->vars::selHTML;
            break;
        case '1.00':
            $sel[15] = $this->vars::selHTML;
            break;
        case '2.00':
            $sel[16] = $this->vars::selHTML;
            break;
        case '3.00':
            $sel[17] = $this->vars::selHTML;
            break;
        case '3.50':
            $sel[18] = $this->vars::selHTML;
            break;
        case '4.00':
            $sel[19] = $this->vars::selHTML;
            break;
        case '4.50':
            $sel[20] = $this->vars::selHTML;
            break;
        case '5.00':
            $sel[21] = $this->vars::selHTML;
            break;
        case '5.50':
            $sel[22] = $this->vars::selHTML;
            break;
        case '5.75':
            $sel[23] = $this->vars::selHTML;
            break;
        case '6.00':
            $sel[24] = $this->vars::selHTML;
            break;
        case '6.50':
            $sel[25] = $this->vars::selHTML;
            break;
        case '7.00':
            $sel[26] = $this->vars::selHTML;
            break;
        case '8.00':
            $sel[27] = $this->vars::selHTML;
            break;
        case '9.00':
            $sel[28] = $this->vars::selHTML;
            break;
        case '9.50':
            $sel[29] = $this->vars::selHTML;
            break;
        case '10.00':
            $sel[30] = $this->vars::selHTML;
            break;
        case '10.50':
            $sel[36] = $this->vars::selHTML;
            break;
        case '11.00':
            $sel[31] = $this->vars::selHTML;
            break;
        case '12.00':
            $sel[32] = $this->vars::selHTML;
            break;
        case '12.75':
            $sel[35] = $this->vars::selHTML;
            break;
        case '13.00':
            $sel[33] = $this->vars::selHTML;
            break;
        case '14.00':
            $sel[34] = $this->vars::selHTML;
            break;
        case '0.00':
        default:
            $sel[14] = $this->vars::selHTML;
        }

        $template->sel = $sel;

        return $template->process('timezone_control.php');
    }

    /**
     * Generates HTML for a user status (role) select element.
     *
     * @since 1.10.00
     * @param string $statusField The name attribute for the select element.
     * @param string $currentStatus The previously saved value of members.status.
     * @return string
     */
    public function userStatusControl(string $statusField, string $currentStatus): string
    {
        $template = new \XMB\Template($this->vars);
        $template->addRefs();

        $template->statusField = $statusField;

        // From editprofile.php
        $template->sadminselect = '';
        $template->adminselect = '';
        $template->smodselect = '';
        $template->modselect = '';
        $template->memselect = '';
        $template->banselect = '';
        switch ($currentStatus) {
            case 'Super Administrator':
                $template->sadminselect = $this->vars::selHTML;
                break;
            case 'Administrator':
                $template->adminselect = $this->vars::selHTML;
                break;
            case 'Super Moderator':
                $template->smodselect = $this->vars::selHTML;
                break;
            case 'Moderator':
                $template->modselect = $this->vars::selHTML;
                break;
            case 'Member':
                $template->memselect = $this->vars::selHTML;
                break;
            case 'Banned':
                $template->banselect = $this->vars::selHTML;
                break;
            default:
                $template->memselect = $this->vars::selHTML;
        }
        
        return $template->process('admin_members_status_field.php');
    }

    /**
     * Checks if guest recently tried to register and disclosed age < 13
     *
     * @since 1.9.12
     * @return bool When false the website must not collect any information from the guest.
     */
    public function coppa_check(): bool 
    {
        $privacy = getPhpInput('privacy', 'c');
        return 'xmb' != $privacy;
    }
    
    /**
     * Checks permissions for pages restricted to administrators.
     *
     * @since 1.10.00
     */
    public function assertAdminOnly()
    {
        if (X_GUEST) {
            $this->redirect($this->vars->full_url . 'misc.php?action=login', timeout: 0);
        } elseif (! X_ADMIN) {
            header('HTTP/1.0 403 Forbidden');
            $this->message($this->vars->lang['u2uadmin_noperm']);
        }
    }

    /**
     * Checks a new password against requirements.
     *
     * @since 1.10.00
     * @param string $passInputName
     * @param string $passConfirmName
     * @return string The raw text of the new password.
     */
    public function assertPasswordPolicy(string $passInputName, string $passConfirmName): string
    {
        $password1 = getRawString($passInputName);
        $password2 = getRawString($passConfirmName);

        if ('' == $password1) {
            $this->error($this->vars->lang['textnopassword']);
        }
        if (strlen($password1) < $this->vars::PASS_MIN_LENGTH) {
            $core->error($this->vars->lang['pwtooshort']);
        }
        if (strlen($password1) > $this->vars::PASS_MAX_LENGTH) {
            $core->error($this->vars->lang['pwtoolong']);
        }
        if ($password1 !== $password2) {
            $this->error($this->vars->lang['pwnomatch']);
        }

        return $password1;
    }

    /**
     * Process and return the post_attachmentbox template.
     *
     * @since 1.10.00
     * @param int $currentAttachCount The number of files already attached to the post.
     * @return The attachmentbox HTML.
     */
    public function makeAttachmentBox(int $currentAttachCount): string
    {
        $template = new \XMB\Template($this->vars);
        $template->addRefs();

        $maxuploads = (int) $this->vars->settings['filesperpost'] - $currentAttachCount;
        if ($maxuploads <= 0) return '';
        $max_dos_limit = (int) ini_get('max_file_uploads');
        if ($max_dos_limit > 0) $maxuploads = min($maxuploads, $max_dos_limit);
        $template->maxuploads = $maxuploads;

        $template->maxsize = $this->attach->getSizeFormatted(min(phpShorthandValue('upload_max_filesize'), (int) $this->vars->settings['maxattachsize']));

        $max_dos_size = phpShorthandValue('post_max_size');
        $max_xmb_size = (int) $this->vars->settings['filesperpost'] * (int) $this->vars->settings['maxattachsize'];
        $maxtotal = (0 == $max_dos_size) ? $max_xmb_size : min($max_dos_size, $max_xmb_size);
        $template->maxtotal = $this->attach->getSizeFormatted($maxtotal);
        
        return $template->process('post_attachmentbox.php');
    }

    /**
     * Validate a new username
     *
     * @since 1.9.1 Formerly known as admin::check_restricted()
     * @since 1.10.00
     * @param string $input Username to check
     * @return bool Validity of the username.
     */
    public function usernameValidation(string $input): bool
    {
        // Check individual characters
        $nonprinting = '\\x00-\\x1F\\x7F';  // Universal chars that are invalid.
        $specials = '\\]\'<>\\\\|"[,@';  // Other universal chars disallowed by XMB: []'"<>\|,@
        $sequences = '|  ';  // Phrases disallowed, each separated by '|'
        $icharset = strtoupper($this->vars->charset);
        if (substr($icharset, 0, 8) == 'ISO-8859') {
            if ($icharset == 'ISO-8859-11') {
                $nonprinting .= '-\\x9F\\xDB-\\xDE\\xFC-\\xFF';  //More chars invalid for the Thai set.
            } else {
                $nonprinting .= '-\\x9F\\xAD';  //More chars invalid for all ISO 8859 sets except Part 11 (Thai).
            }
        } elseif (substr($icharset, 0, 11) == 'WINDOWS-125') {
            $nonprinting .= '\\xAD';  //More chars invalid for all Windows code pages.
        }

        if ($input !== preg_replace("#[{$nonprinting}{$specials}]{$sequences}#", '', $input)) {
            return false;
        }

        // Check admin-specified phrases
        $restrictions = $this->sql->getRestrictions();
        foreach ($restrictions as $restriction) {
            if ('0' === $restriction['case_sensitivity']) {
                $t_username = strtolower($input);
                $restriction['name'] = strtolower($restriction['name']);
            } else {
                $t_username = $input;
            }

            if ('1' === $restriction['partial']) {
                if (strpos($t_username, $restriction['name']) !== false) {
                    return false;
                }
            } else {
                if ($t_username === $restriction['name']) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get rid of potentially orphaned objects for a quarantined member.
     *
     * @since 1.9.12
     * @param string $username The HTML-escaped username, as in members.username
     */
    public function moderate_cleanup($username) {
        if ('Anonymous' == $username) return;

        $member = $this->sql->getMemberByName($username);
        if (empty($member)) throw new InvalidArgumentException('Username invalid or not found');

        $uid = $member['uid'];
        $this->db->query("DELETE FROM " . $this->vars->tablepre . "hold_attachments WHERE uid = $uid AND pid = 0");
    }
}
