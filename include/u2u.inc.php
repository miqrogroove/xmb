<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

class U2U
{
    private const U2U_FOLDER_COL_SIZE = 32;

    private array $farray = []; // Array of ints, message counts indexed by English internal folder name.
    private array $folders = []; // Array of strings, translated folder names.

    private int $messageCount = 0;
    
    private string $folder; // The current folder name, as supplied by the user.  Used in mass editing.
    private string $footer;
    private string $header;

    public function __construct(
        private Core $core,
        private DBStuff $db,
        private Email $email,
        private SQL $sql,
        private Template $template,
        private Translation $tran,
        private Validation $validate,
        private Variables $vars,
    ) {
        $folders = empty($this->vars->self['u2ufolders']) ? [] : explode(",", $this->vars->self['u2ufolders']);
        $folders = array_map('trim', $folders);
        sort($folders);
        $this->folders = array_merge([
            'Inbox' => $this->vars->lang['textu2uinbox'],
            'Outbox' => $this->vars->lang['textu2uoutbox'],
            'Drafts' => $this->vars->lang['textu2udrafts'],
            'Trash' => $this->vars->lang['textu2utrash'],
        ], $folders);

        $this->header = $template->process('u2u_header.php');
        $this->footer = $template->process('u2u_footer.php');
    }

    public function setFolder(string $folder)
    {
        if (! $this->folderExists($folder)) {
            header('HTTP/1.0 404 Not Found');
            $this->error($this->vars->lang['textnofolder']);
        }

        $this->folder = $folder;
    }

    public function getFooter(): string
    {
        return $this->footer;
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * Display a themed notice.
     *
     * @since 1.9.1 Formerly u2u_msg()
     * @since 1.10.00
     * @param string $msg
     * @param string $redirect
     */
    public function msg(string $msg, string $redirect)
    {
        if (! empty($redirect)) {
            $this->core->redirect($redirect);
        }
        $this->template->u2uheader = $this->header;
        $this->template->u2ufooter = $this->footer;
        $this->template->msg = $msg;

        $this->template->process('u2u_msg.php', echo: true);
        exit;
    }

    /**
     * Send a U2U message to one or more recipients.
     *
     * @since 1.9.1 Formerly u2u_send_multi_recp()
     * @since 1.10.00
     * @param string $msgto The recipient(s) of this U2U message.
     * @param string $subject The U2U Subject
     * @param string $message The U2U message body
     * @return string HTML formatted error messages
     */
    private function send_multi_recp(string $msgto, string $subject, string $message): string
    {
        $errors = '';
        $recipients = array_unique(array_map('trim', explode(',', $msgto)));

        foreach ($recipients as $recp) {
            $errors .= $this->send_single($recp, $subject, $message);
        }

        return $errors;
    }

    /**
     * Sends a message from the current user to the specified username.
     *
     * Assumes the current user is already authenticated and not banned from U2U.
     *
     * @since 1.10.00
     * @param string $msgto Username, HTML encoded
     * @param string $subject Message subject line, HTML encoded
     * @param string $message Message body, HTML encoded
     * @return string Empty string on success, HTML formatted messages on failure.
     */
    public function send_single(string $msgto, string $subject, string $message): string
    {
        $errors = '';

        $rcpt = $this->sql->getMemberByName($msgto);
        if (! empty($rcpt)) {
            $ilist = array_map('trim', explode(',', $rcpt['ignoreu2u']));
            if (! in_array($this->vars->self['username'], $ilist) || X_ADMIN) {
                $this->sql->addU2U(
                    to: $rcpt['username'],
                    from: $this->vars->self['username'],
                    type: 'incoming',
                    owner: $rcpt['username'],
                    folder: 'Inbox',
                    subject: $subject,
                    message: $message,
                    isRead: 'no',
                    isSent: 'yes',
                    timestamp: $this->vars->onlinetime,
                );
                if ($this->vars->self['saveogu2u'] == 'yes') {
                    $this->sql->addU2U(
                        to: $rcpt['username'],
                        from: $this->vars->self['username'],
                        type: 'outgoing',
                        owner: $this->vars->self['username'],
                        folder: 'Outbox',
                        subject: $subject,
                        message: $message,
                        isRead: 'no',
                        isSent: 'yes',
                        timestamp: $this->vars->onlinetime,
                    );
                }

                if ($rcpt['emailonu2u'] == 'yes' && $rcpt['status'] != 'Banned') {
                    $lang2 = $this->tran->loadPhrases(['charset', 'textnewu2uemail', 'textnewu2ubody']);
                    $translate = $lang2[$rcpt['langfile']];
                    $u2uurl = $this->vars->full_url . 'u2u.php';
                    $rawusername = rawHTML($this->vars->self['username']);
                    $rawaddress = rawHTML($rcpt['email']);
                    $body = "$rawusername {$translate['textnewu2ubody']} \n$u2uurl";
                    $this->email->send($rawaddress, $translate['textnewu2uemail'], $body, $translate['charset']);
                }
            } else {
                $errors = '<br />'.$this->vars->lang['u2ublocked'];
            }
        } else {
            $errors = '<br />'.$this->vars->lang['badrcpt'];
        }

        return $errors;
    }

    /**
     * Create a U2U message to one or more recipients, including message editing and previewing.
     *
     * @since 1.9.1 Formerly u2u_send()
     * @since 1.10.00
     * @param int    $u2uid Generates a quoted message from the given message ID.
     * @param string $msgto The recipient(s) of this U2U message.
     * @param string $subject The U2U Subject
     * @param string $message The U2U message body
     * @return string The left-hand-pane view
     */
    public function send(int $u2uid, string $msgto, string $subject, string $message): string
    {
        // TODO: These action inputs would be better as DI params: forward, reply, save, preview, send.
        $forward = getPhpInput('forward', 'g') === 'yes';
        $reply = getPhpInput('reply', 'g') === 'yes';

        $username = $this->validate->postedVar(
            varname: 'username',
            dbescape: false,
            sourcearray: 'g',
        ); //username is the param from u2u links on profiles.

        if ($this->vars->self['ban'] == 'u2u' || $this->vars->self['ban'] == 'both') {
            $this->error($this->vars->lang['textbanfromu2u']);
        }

        if (! X_STAFF && $this->messageCount >= (int) $this->vars->settings['u2uquota'] && (int) $this->vars->settings['u2uquota'] > 0) {
            $this->error($this->vars->lang['u2ureachedquota']);
        }

        if (onSubmit('savesubmit')) {
            if (empty($subject)) {
                $subject = $this->vars->lang['textnosub'];
            }

            if (empty($message)) {
                $this->error($this->vars->lang['u2uempty']);
            }

            $this->sql->addU2U(
                to: '',
                from: '',
                type: 'draft',
                owner: $this->vars->self['username'],
                folder: 'Drafts',
                subject: $subject,
                message: $message,
                isRead: 'yes',
                isSent: 'no',
                timestamp: $this->vars->onlinetime,
            );

            $this->msg($this->vars->lang['imsavedmsg'], $this->vars->full_url . 'u2u.php?folder=Drafts');
        } elseif (onSubmit('sendsubmit')) {
            $errors = '';
            if (empty($subject)) {
                $subject = $this->vars->lang['textnosub'];
            }

            if (empty($message)) {
                $this->error($this->vars->lang['u2umsgempty']);
            }

            if ((int) $this->vars->self['post_date'] >= $this->vars->onlinetime - (int) $this->vars->settings['floodctrl']) {
                $this->error($this->vars->lang['floodprotect_u2u']);
            } else {
                $this->sql->setMemberPostDate((int) $this->vars->self['uid'], $this->vars->onlinetime);
            }

            if (strstr($msgto, ',') && X_STAFF) {
                $errors = $this->send_multi_recp($msgto, $subject, $message);
            } else {
                $errors = $this->send_single($msgto, $subject, $message);
            }

            if (empty($errors)) {
                $this->msg($this->vars->lang['imsentmsg'], $this->vars->full_url . 'u2u.php');
            } else {
                $this->msg(substr($errors, 6) , $this->vars->full_url . 'u2u.php');
            }
        }

        if ($u2uid > 0) {
            $query = $this->db->query("SELECT subject, msgfrom, message FROM " . $this->vars->tablepre . "u2u WHERE u2uid = '$u2uid' AND owner = '" . $this->vars->xmbuser . "'");
            $quote = $this->db->fetch_array($query);
            if ($quote) {
                if (noSubmit('previewsubmit')) {
                    $prefixes = array($this->vars->lang['textre'].' ', $this->vars->lang['textfwd'].' ');
                    $subject = str_replace($prefixes, '', $quote['subject']);
                    $message = $this->core->rawHTMLmessage($quote['message']);
                    if ($forward) {
                        $subject = $this->vars->lang['textfwd'].' '.$subject;
                        $message = '[quote][i]'.$this->vars->lang['origpostedby'].' '.$quote['msgfrom']."[/i]\n".$message.'[/quote]';
                    } elseif ($reply) {
                        $subject = $this->vars->lang['textre'].' '.$subject;
                        $message = '[quote]'.$message.'[/quote]';
                        $username = $quote['msgfrom'];
                    }
                }
            }
            $this->db->free_result($query);
        }

        if (onSubmit('previewsubmit')) {
            $subject = $this->core->rawHTMLsubject($subject);
            $this->template->u2usubject = $subject;
            $this->template->u2umessage = $this->core->postify($message);
            $this->template->u2upreview = $this->template->process('u2u_send_preview.php');
            $message = $this->core->rawHTMLmessage($message);
            $username = $msgto;
        } else {
            $this->template->u2upreview = '';
        }

        $this->template->message = $message;
        $this->template->subject = $subject;
        $this->template->u2uid = $u2uid;
        $this->template->username = $username;

        return $this->template->process('u2u_send.php');
    }

    /**
     * Generates the web format HTML of the specified message.
     *
     * @since 1.9.1 Formerly u2u_view()
     * @since 1.10.00
     * @param int $u2uid
     * @return string
     */
    function view(int $u2uid): string
    {
        $subTemplate = new Template($this->vars);
        $subTemplate->addRefs();
        $subTemplate->thewidth = $this->template->thewidth;
        $subTemplate->u2uid = $u2uid;

        if (! ($u2uid > 0)) {
            $this->error($this->vars->lang['textnonechosen'], $this->vars->full_url . 'u2u.php');
        }

        $query = $this->db->query("SELECT u.*, m.avatar FROM " . $this->vars->tablepre . "u2u AS u LEFT JOIN " . $this->vars->tablepre . "members AS m ON u.msgfrom = m.username WHERE u2uid = '$u2uid' AND owner = '" . $this->vars->xmbuser . "'");
        $u2u = $this->db->fetch_array($query);
        null_string($this->vars->self['avatar']);
        null_string($u2u['avatar']);

        if (! $u2u) {
            $this->error($this->vars->lang['u2uadmin_noperm']);
        }

        if ('on' == $this->vars->settings['images_https_only']) {
            if (strpos($this->vars->self['avatar'], ':') !== false && substr($this->vars->self['avatar'], 0, 6) !== 'https:') {
                $this->vars->self['avatar'] = '';
            }
            if (strpos($u2u['avatar'], ':') !== false && substr($u2u['avatar'], 0, 6) !== 'https:') {
                $u2u['avatar'] = '';
            }
        }

        $subTemplate->u2uavatar = '';
        if ($u2u['type'] == 'incoming') {
            $this->db->query("UPDATE " . $this->vars->tablepre . "u2u SET readstatus='yes' WHERE u2uid=$u2u[u2uid] OR (u2uid=$u2u[u2uid]+1 AND type='outgoing' AND msgto='" . $this->vars->xmbuser . "')");
            if ($this->vars->settings['avastatus'] != 'off' && $u2u['avatar'] !== '') {
                $subTemplate->u2uavatar = '<br /><img src="' . $u2u['avatar'] . '" />';
            }
        } elseif ($u2u['type'] == 'draft') {
            $this->db->query("UPDATE " . $this->vars->tablepre . "u2u SET readstatus = 'yes' WHERE u2uid = $u2u[u2uid]");
            if ($this->vars->settings['avastatus'] != 'off' && $this->vars->self['avatar'] !== '') {
                $subTemplate->u2uavatar = '<br /><img src="' . $this->vars->self['avatar'] . '" />';
            }
        } else {
            if ($this->vars->settings['avastatus'] != 'off' && $this->vars->self['avatar'] !== '') {
                $subTemplate->u2uavatar = '<br /><img src="' . $this->vars->self['avatar'] . '" />';
            }
        }

        $adjTime = $this->core->timeKludge((int) $u2u['dateline']);
        $u2udate = $this->core->printGmDate($adjTime);
        $u2utime = gmdate($this->vars->timecode, $adjTime);
        $subTemplate->u2udateline = "$u2udate " . $this->vars->lang['textat'] . " $u2utime";
        $subTemplate->u2usubject = $this->core->rawHTMLsubject($u2u['subject']);
        $subTemplate->u2umessage = $this->core->postify($u2u['message']);
        $subTemplate->u2ufolder = $u2u['folder'];
        $subTemplate->u2ufrom = '<a href="' . $this->vars->full_url . 'member.php?action=viewpro&amp;member=' . recodeOut($u2u['msgfrom']) . '" target="mainwindow">' . $u2u['msgfrom'] . '</a>';
        $subTemplate->u2uto = ($u2u['type'] == 'draft') ? $this->vars->lang['textu2unotsent'] : '<a href="' . $this->vars->full_url . 'member.php?action=viewpro&amp;member=' . recodeOut($u2u['msgto']) . '" target="mainwindow">' . $u2u['msgto'] . '</a>';
        $subTemplate->type = $u2u['type'];

        if ($u2u['type'] == 'draft') {
            $subTemplate->sendoptions = '<input type="radio" name="mod" value="send" /> ' . $this->vars->lang['textu2u'] . '<br />';
            $subTemplate->delchecked = ' checked="checked"';
        } elseif ($u2u['msgfrom'] !== $this->vars->self['username']) {
            $subTemplate->sendoptions = '<input type="radio" name="mod" value="reply" checked="checked" /> ' . $this->vars->lang['textreply'] . '<br /><input type="radio" name="mod" value="forward" /> ' . $this->vars->lang['textforward'] . '<br />';
            $subTemplate->delchecked = '';
        } else {
            $subTemplate->sendoptions = '';
            $subTemplate->delchecked = ' checked="checked"';
        }

        $mtofolder = [];
        $mtofolder[] = '<select name="tofolder">';
        $mtofolder[] = '<option value="">' . $this->vars->lang['textpickfolder'] . '</option>';
        foreach ($this->folders as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }
            $mtofolder[] = '<option value="'.$key.'">'.$value.'</option>';
        }
        $mtofolder[] = '</select>';
        $subTemplate->mtofolder = implode("\n", $mtofolder);

        $this->db->free_result($query);

        return $subTemplate->process('u2u_view.php');
    }

    /**
     * Display printable format, or send-to-email, for the specified message.
     *
     * @since 1.9.1 Formerly u2u_print()
     * @since 1.10.00
     * @param int $u2uid
     */
    public function printOrEmail(int $u2uid, bool $eMail = false)
    {
        if (! ($u2uid > 0)) {
            $this->error($this->vars->lang['textnonechosen'], $this->vars->full_url . 'u2u.php');
        }

        $query = $this->db->query("SELECT * FROM " . $this->vars->tablepre . "u2u WHERE u2uid = '$u2uid' AND owner = '" . $this->vars->xmbuser . "'");
        $u2u = $this->db->fetch_array($query);
        $this->db->free_result($query);

        if (empty($u2u)) {
            $this->error($this->vars->lang['u2uadmin_noperm']);
        }

        $adjTime = $this->core->timeKludge((int) $u2u['dateline']);
        $u2udate = $this->core->printGmDate($adjTime);
        $u2utime = gmdate($this->vars->timecode, $adjTime);
        $this->template->u2udateline = $u2udate . ' ' . $this->vars->lang['textat'] . ' ' . $u2utime;
        $this->template->u2usubject = $this->core->rawHTMLsubject($u2u['subject']);
        $this->template->u2umessage = $this->core->postify($u2u['message']);
        $this->template->u2ufolder = $u2u['folder'];
        $this->template->u2ufrom = $u2u['msgfrom'];
        $this->template->u2uto = ($u2u['type'] == 'draft') ? $this->vars->lang['textu2unotsent'] : $u2u['msgto'];

        if ($eMail) {
            // Make an HTML-formatted email containing the U2U body.
            $css = $this->template->process('css.php');
            if (file_exists(ROOT . $this->vars->theme['imgdir'] . '/theme.css')) {
                $extra = file_get_contents(ROOT . $this->vars->theme['imgdir'] . '/theme.css');
                if (false !== $extra) {
                    $css .= $extra;
                }
            }
            $this->template->css = "<style type='text/css'>\n$css\n</style>";
            $this->template->mailHeader = $this->template->process('email_html_header.php');
            $this->template->mailFooter = $this->template->process('email_html_footer.php');
            $title = $this->vars->lang['textu2utoemail'] . ' ' . $this->core->rawTextSubject($u2u['subject']);
            $body = $this->template->process('u2u_email.php');
            $rawemail = rawHTML($this->vars->self['email']);
            $result = $this->email->send($rawemail, $title, $body, $this->vars->lang['charset'], html: true);
            $this->msg($this->vars->lang['textu2utoemailsent'], $this->vars->full_url . "u2u.php?action=view&amp;u2uid=$u2uid");
        } else {
            $this->template->process('u2u_printable.php', echo: true);
            exit;
        }
    }

    /**
     * Delete the specified message.
     *
     * @since 1.9.1 Formerly u2u_delete()
     * @since 1.10.00
     * @param int $u2uid
     */
    public function delete(int $u2uid)
    {
        if (! ($u2uid > 0)) {
            $this->error($this->vars->lang['textnonechosen'], $this->vars->full_url . 'u2u.php');
        }

        $this->modDelete([$u2uid]);
    }

    /**
     * Mass delete the specified messages.
     *
     * @since 1.9.1 Formerly u2u_mod_delete()
     * @since 1.10.00
     * @param array $u2u_select Array of int u2uid values.
     */
    public function modDelete(array $u2u_select)
    {
        // Sanitize input and validate it against the submitted folder name.
        $u2u_select = array_map('intval', $u2u_select);
        if ($this->folder !== $this->sql->getU2UFolder($u2u_select)) $this->error($this->vars->lang['textnofolder']);
        $in = implode(',', $u2u_select);

        if ($this->folder == "Trash") {
            $this->db->query("DELETE FROM " . $this->vars->tablepre . "u2u WHERE u2uid IN ($in) AND owner = '" . $this->vars->xmbuser . "'");
        } else {
            $this->db->query("UPDATE " . $this->vars->tablepre . "u2u SET folder = 'Trash' WHERE u2uid IN ($in) AND owner = '" . $this->vars->xmbuser . "'");
        }

        $this->msg($this->vars->lang['imdeletedmsg'], $this->vars->full_url . 'u2u.php?folder=' . recodeOut($this->folder));
    }

    /**
     * Edit the folder of a message to the specified value.
     *
     * @since 1.9.1 Formerly u2u_move()
     * @since 1.10.00
     * @param int $u2uid
     * @param string $tofolder The destination folder of the move.
     */
    public function move(int $u2uid, string $tofolder)
    {
        if (! ($u2uid > 0)) {
            $this->error($this->vars->lang['textnonechosen'], $this->vars->full_url . 'u2u.php');
        }

        // This value is related to template u2u_view.php
        $type = getPhpInput('type');

        if (empty($tofolder)) {
            $this->error($this->vars->lang['textnofolder'], $this->vars->full_url . "u2u.php?action=view&amp;u2uid=$u2uid");
        }

        if (! (in_array($tofolder, $this->folders) || $tofolder == 'Inbox' || $tofolder == 'Outbox' || $tofolder == 'Drafts') || ($tofolder == 'Inbox' && ($type == 'draft' || $type == 'outgoing')) || ($tofolder == 'Outbox' && ($type == 'incoming' || $type == 'draft')) || ($tofolder == 'Drafts' && ($type == 'incoming' || $type == 'outgoing'))) {
            $this->error($this->vars->lang['textcantmove'], $this->vars->full_url . "u2u.php?action=view&amp;u2uid=$u2uid");
        }

        $this->db->escape_fast($tofolder);
        $this->db->query("UPDATE " . $this->vars->tablepre . "u2u SET folder = '$tofolder' WHERE u2uid = '$u2uid' AND owner = '" . $this->vars->xmbuser . "'");

        $this->msg($this->vars->lang['textmovesucc'], $this->vars->full_url . 'u2u.php?folder=' . recodeOut($this->folder));
    }

    /**
     * Mass edit the folder of many messages to the specified value.
     *
     * @since 1.9.1 Formerly u2u_mod_move()
     * @since 1.10.00
     * @param string $tofolder The destination folder of the move.
     * @param array $u2u_select Array of int u2uid values.
     */
    public function modMove(string $tofolder, array $u2u_select)
    {
        // Sanitize input and validate it against the submitted folder name.
        $u2u_select = array_map('intval', $u2u_select);
        if ($this->folder !== $this->sql->getU2UFolder($u2u_select)) $this->error($this->vars->lang['textnofolder']);

        $in = '';
        foreach ($u2u_select as $value) {
            if ($value <= 0) continue;

            // These values are related to template u2u_row.php
            $type = getPhpInput("type$value");
            if ((in_array($tofolder, $this->folders) || $tofolder == 'Inbox' || $tofolder == 'Outbox' || $tofolder == 'Drafts') && !($tofolder == 'Inbox' && ($type == 'draft' || $type == 'outgoing')) && !($tofolder == 'Outbox' && ($type == 'incoming' || $type == 'draft')) && !($tofolder == 'Drafts' && ($type == 'incoming' || $type == 'outgoing'))) {
                $in .= (empty($in)) ? "$value" : ",$value";
            }
        }

        if (empty($in)) {
            $this->error($this->vars->lang['textcantmove'], $this->vars->full_url . 'u2u.php?folder=' . recodeOut($this->folder));
        }

        $this->db->escape_fast($tofolder);
        $this->db->query("UPDATE " . $this->vars->tablepre . "u2u SET folder = '$tofolder' WHERE u2uid IN ($in) AND owner = '" . $this->vars->xmbuser . "'");

        $this->msg($this->vars->lang['textmovesucc'], $this->vars->full_url . 'u2u.php?folder=' . recodeOut($this->folder));
    }

    /**
     * Edit the readstatus of a message to 'no'.
     *
     * @since 1.9.1 Formerly u2u_markUnread()
     * @since 1.10.00
     * @param int $u2uid
     * @param string $type
     */
    public function markUnread(int $u2uid, string $type)
    {
        if (! ($u2uid > 0)) {
            $this->error($this->vars->lang['textnonechosen'], $this->vars->full_url . "u2u.php");
        }

        if (empty($this->folder)) {
            $this->error($this->vars->lang['textnofolder'], $this->vars->full_url . "u2u.php?action=view&amp;u2uid=$u2uid");
        }

        if ($type == 'outgoing') {
            $this->error($this->vars->lang['textnomur'], $this->vars->full_url . 'u2u.php?folder=' . recodeOut($this->folder));
        }

        $this->db->query("UPDATE " . $this->vars->tablepre . "u2u SET readstatus = 'no' WHERE u2uid=$u2uid AND owner = '" . $this->vars->xmbuser . "'");

        $this->msg($this->vars->lang['textmarkedunread'], $this->vars->full_url . 'u2u.php?folder=' . recodeOut($this->folder));
    }

    /**
     * Mass edit the readstatus of many messages to 'no'.
     *
     * @since 1.9.1 Formerly u2u_mod_markUnread()
     * @since 1.10.00
     * @param array $u2u_select Array of int u2uid values.
     */
    public function modMarkUnread(array $u2u_select)
    {
        // Sanitize input and validate it against the submitted folder name.
        $u2u_select = array_map('intval', $u2u_select);
        if ($this->folder !== $this->sql->getU2UFolder($u2u_select)) $this->error($this->vars->lang['textnofolder']);

        if (empty($u2u_select)) {
            $this->error($this->vars->lang['textnonechosen'], $this->vars->full_url . 'u2u.php?folder=' . recodeOut($this->folder));
        }

        $in = '';
        foreach ($u2u_select as $value) {
            if ($value <= 0) continue;

            // These values are related to template u2u_row.php
            if (getPhpInput("type$value") != 'outgoing') {
                $value = intval($value);
                $in .= (empty($in)) ? "$value" : ",$value";
            }
        }

        if (empty($in)) {
            $this->error($this->vars->lang['textnonechosen'], $this->vars->full_url . 'u2u.php?folder=' . recodeOut($this->folder));
        }

        $this->db->query("UPDATE " . $this->vars->tablepre . "u2u SET readstatus = 'no' WHERE u2uid IN ($in) AND owner = '" . $this->vars->xmbuser . "'");

        $this->msg($this->vars->lang['textmarkedunread'], $this->vars->full_url . 'u2u.php?folder=' . recodeOut($this->folder));
    }

    /**
     * Save changes to the user's list of custom folder names.
     *
     * @since 1.9.1 Formerly u2u_folderSubmit()
     * @since 1.10.00
     */
    function folderSubmit(string $u2ufolders)
    {
        $error = '';

        // Trim all folder names, remove all duplicates, use case-insensitivity due to absence of explicit column collation.
        $newfolders = explode(',', $u2ufolders);
        $testarray = ['inbox', 'outbox', 'drafts', 'trash'];
        foreach ($newfolders as $key => $value) {
            $value = trim($value);
            if (strlen($value) > $this::U2U_FOLDER_COL_SIZE) {
                $value = substr($value, 0, $this::U2U_FOLDER_COL_SIZE);
            }
            $ci_value = strtolower($value);
            if (strpos($ci_value, '&lt;') !== false || strpos($ci_value, '&gt;') !== false) {
                // Angle braces are problematic because we use these folder names in URL query strings.
                $value = '';
            }
            if (empty($value) || in_array($ci_value, $testarray)) {
                unset($newfolders[$key]);
            } else {
                $newfolders[$key] = $value;
                $testarray[] = $ci_value;
            }
        }

        // Prevent deleting non-empty custom folders
        foreach ($this->folders as $value) {
            if (! empty($this->farray[$value]) && ! in_array($value, $newfolders) && ! in_array($value, ['Inbox', 'Outbox', 'Drafts', 'Trash'])) {
                $newfolders[] = $value;
                $error .= (empty($error)) ? '<br />' . $this->vars->lang['foldersupdateerror'] . ' ' . $value : ', ' . $value;
            }
        }

        $u2ufolders = implode(', ', $newfolders);
        $this->db->escape_fast($u2ufolders);
        $this->db->query("UPDATE " . $this->vars->tablepre . "members SET u2ufolders = '$u2ufolders' WHERE username = '" . $this->vars->xmbuser . "'");

        $this->msg($this->vars->lang['foldersupdate'] . $error, $this->vars->full_url . 'u2u.php?folder=Inbox');
    }

    /**
     * Displays the ignore list from the form submission variables, and
     * attempts to update the ignore list if the Ignore Submit button has been pressed
     *
     * @since 1.9.1 Formerly u2u_ignore()
     * @since 1.10.00
     * @return string The left pane to render if no post data.
     */
    public function ignore(): string
    {
        $leftpane = '';
        if (onSubmit('ignoresubmit')) {
            $ignorelist = $this->validate->postedVar('ignorelist');
            $this->vars->self['ignoreu2u'] = $ignorelist;
            $this->db->query("UPDATE " . $this->vars->tablepre . "members SET ignoreu2u = '" . $this->vars->self['ignoreu2u'] . "' WHERE username = '" . $this->vars->xmbuser . "'");
            $this->msg($this->vars->lang['ignoreupdate'], $this->vars->full_url . 'u2u.php?action=ignore');
        } else {
            $this->template->hIgnoreu2u = $this->vars->self['ignoreu2u'];
            $leftpane = $this->template->process('u2u_ignore.php');
        }

        return $leftpane;
    }

    /**
     * Generates the main HTML.
     *
     * @since 1.9.1 Formerly u2u_display()
     * @since 1.10.00
     * @return string
     */
    public function display(): string
    {
        $folder = $this->db->escape($this->folder);

        if (empty($folder)) {
            $folder = "Inbox";
        }

        $subTemplate = new Template($this->vars);
        $subTemplate->addRefs();
        $subTemplate->folderrecode = recodeOut($this->folder);
        $subTemplate->thewidth = $this->template->thewidth;
        $subTemplate->u2usin = '';
        $subTemplate->u2usout = '';
        $subTemplate->u2usdraft = '';

        switch ($folder) {
            case 'Inbox':
                $query = $this->db->query("SELECT u.u2uid, u.msgto, u.msgfrom, u.type, u.folder, u.subject, u.dateline, u.readstatus, m.username, m.invisible, m.lastvisit FROM " . $this->vars->tablepre . "u2u u LEFT JOIN " . $this->vars->tablepre . "members m ON u.msgfrom=m.username WHERE u.folder='$folder' AND u.owner='" . $this->vars->xmbuser . "' ORDER BY dateline DESC");
                break;
            case 'Outbox':
            case 'Drafts':
                $query = $this->db->query("SELECT u.u2uid, u.msgto, u.msgfrom, u.type, u.folder, u.subject, u.dateline, u.readstatus, m.username, m.invisible, m.lastvisit FROM " . $this->vars->tablepre . "u2u u LEFT JOIN " . $this->vars->tablepre . "members m ON u.msgto=m.username WHERE u.folder='$folder' AND u.owner='" . $this->vars->xmbuser . "' ORDER BY dateline DESC");
                break;
            default:
                $query = $this->db->query(
                    "SELECT u.u2uid, u.msgto, u.msgfrom, u.type, u.folder, u.subject, u.dateline, u.readstatus, m.username, m.invisible, m.lastvisit FROM " . $this->vars->tablepre . "u2u u LEFT JOIN " . $this->vars->tablepre . "members m ON u.msgfrom=m.username WHERE u.folder='$folder' AND u.owner='" . $this->vars->xmbuser . "' AND u.type='incoming' "
                  . "UNION ALL "
                  . "SELECT u.u2uid, u.msgto, u.msgfrom, u.type, u.folder, u.subject, u.dateline, u.readstatus, m.username, m.invisible, m.lastvisit FROM " . $this->vars->tablepre . "u2u u LEFT JOIN " . $this->vars->tablepre . "members m ON u.msgto=m.username WHERE u.folder='$folder' AND u.owner='" . $this->vars->xmbuser . "' AND u.type IN ('outgoing','draft') "
                  . "ORDER BY dateline DESC"
                );
                break;
        }

        while ($u2u = $this->db->fetch_array($query)) {
            if ($u2u['readstatus'] == 'yes') {
                $subTemplate->u2ureadstatus = $this->vars->lang['textread'];
            } else {
                $subTemplate->u2ureadstatus = '<strong>' . $this->vars->lang['textunread'] . '</strong>';
            }

            if (empty($u2u['subject'])) {
                $u2u['subject'] = '&laquo;' . $this->vars->lang['textnosub'] . '&raquo;';
            }

            $subTemplate->u2usubject = $this->core->rawHTMLsubject($u2u['subject']);

            if ($u2u['type'] == 'incoming' || $u2u['type'] == 'outgoing') {

                if ($this->vars->onlinetime - (int) $u2u['lastvisit'] <= $this->vars::ONLINE_TIMER) {
                    if ('1' === $u2u['invisible']) {
                        if (! X_ADMIN) {
                            $online = $this->vars->lang['textoffline'];
                        } else {
                            $online = $this->vars->lang['hidden'];
                        }
                    } else {
                        $online = $this->vars->lang['textonline'];
                    }
                } else {
                    $online = $this->vars->lang['textoffline'];
                }

                if ($u2u['type'] == 'incoming') {
                    $u2uname = $u2u['msgfrom'];
                } else {
                    $u2uname = $u2u['msgto'];
                }

                $subTemplate->u2usent = "<a href='" . $this->vars->full_url . 'member.php?action=viewpro&amp;member=' . recodeOut($u2uname) . "' target='_blank'>$u2uname</a> ($online)";
            } elseif ($u2u['type'] == 'draft') {
                $subTemplate->u2usent = $this->vars->lang['textu2unotsent'];
            }

            $adjTime = $this->core->timeKludge((int) $u2u['dateline']);
            $u2udate = $this->core->printGmDate($adjTime);
            $u2utime = gmdate($this->vars->timecode, $adjTime);
            $subTemplate->u2udateline = "$u2udate " . $this->vars->lang['textat'] . " $u2utime";

            $subTemplate->type = $u2u['type'];
            $subTemplate->u2uid = $u2u['u2uid'];

            switch ($u2u['type']) {
                case 'outgoing':
                    $subTemplate->u2usout .= $subTemplate->process('u2u_row.php');
                    break;
                case 'draft':
                    $subTemplate->u2usdraft .= $subTemplate->process('u2u_row.php');
                    break;
                case 'incoming':
                default:
                    $subTemplate->u2usin .= $subTemplate->process('u2u_row.php');
                    break;
            }
        }
        $this->db->free_result($query);

        if (empty($subTemplate->u2usin)) {
            $subTemplate->u2usin = $subTemplate->process('u2u_row_none.php');
        }

        if (empty($subTemplate->u2usout)) {
            $subTemplate->u2usout = $subTemplate->process('u2u_row_none.php');
        }

        if (empty($subTemplate->u2usdraft)) {
            $subTemplate->u2usdraft = $subTemplate->process('u2u_row_none.php');
        }

        switch ($folder) {
            case 'Outbox':
                $subTemplate->u2ulist = $subTemplate->process('u2u_outbox.php');
                break;
            case 'Drafts':
                $subTemplate->u2ulist = $subTemplate->process('u2u_drafts.php');
                break;
            case 'Inbox':
                $subTemplate->u2ulist = $subTemplate->process('u2u_inbox.php');
                break;
            default:
                $subTemplate->u2ulist = (
                    $subTemplate->process('u2u_inbox.php') . '<br />' .
                    $subTemplate->process('u2u_outbox.php') . '<br />' .
                    $subTemplate->process('u2u_drafts.php') . '<br />'
                );
        }

        $mtofolder = [];
        $mtofolder[] = '<select name="tofolder">';
        $mtofolder[] = '<option value="">' . $this->vars->lang['textpickfolder'] . '</option>';
        foreach ($this->folders as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }
            if ($key === $this->folder) continue;
            $mtofolder[] = "<option value='$key'>$value</option>";
        }
        $mtofolder[] = '</select>';
        $subTemplate->mtofolder = implode("\n", $mtofolder);
        $subTemplate->folder = $folder;

        return $subTemplate->process('u2u_main.php');
    }

    /**
     * Gathers stats about the user's U2U folders.
     *
     * @since 1.9.1 Formerly u2u_folderList()
     * @since 1.10.00
     * @return int The number of u2u's in total the user has
     */
    public function folderList(): int
    {
        $query = $this->db->query("SELECT folder, count(u2uid) as count FROM " . $this->vars->tablepre . "u2u WHERE owner = '" . $this->vars->xmbuser . "' GROUP BY folder ORDER BY folder ASC");
        while ($flist = $this->db->fetch_array($query)) {
            $this->farray[$flist['folder']] = (int) $flist['count'];
            $this->messageCount += (int) $flist['count'];
        }
        $this->db->free_result($query);
    
        $subTemplate = new Template($this->vars);
        $subTemplate->addRefs();

        // The folderlist is used only in template u2u.php so save that to the shared template service. 
        $this->template->folderlist = '';
        foreach ($this->folders as $link => $value) {
            if (is_numeric($link)) {
                $link = $value;
            }

            if ($link === $this->folder) {
                $subTemplate->value = "<strong>$value</strong>";
            }

            $count = (empty($this->farray[$link])) ? 0 : $this->farray[$link];
            $subTemplate->emptytrash = '';
            if ($link == 'Trash') {
                if ($count != 0) {
                    $subTemplate->emptytrash = ' (<a href="' . $this->vars->full_url . 'u2u.php?action=emptytrash">' . $this->vars->lang['textemptytrash'] . '</a>)';
                }
            }
            $subTemplate->link = recodeOut($link);
            $subTemplate->count = $count;
            $subTemplate->value = $value;
            $this->template->folderlist .= $subTemplate->process('u2u_folderlink.php');
        }

        return $this->messageCount;
    }

    /**
     * Folder existence checker
     *
     * @since 1.10.00
     */
    public function folderExists(string $folder): bool
    {
        $ci_value = strtolower($folder);
        $testarray = ['inbox', 'outbox', 'drafts', 'trash'];

        if (in_array($ci_value, $testarray)) return true;

        foreach ($this->folders as $name) {
            if ($ci_value === strtolower($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Error output wrapper for the U2U window.
     *
     * @since 1.10.00
     */
    public function error(string $msg, ?string $redirect = null)
    {
        $this->core->error(
            msg: $msg,
            showheader: false,
            prepend: $this->header,
            append: $this->footer,
            redirect: $redirect,
            showfooter: false,
        );
    }
}
