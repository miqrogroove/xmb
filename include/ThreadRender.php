<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00
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

/**
 * Thread rendering logic that might otherwise be duplicated from the viewthread controller.
 *
 * @since 1.10.00
 */
class ThreadRender
{
    public function __construct(private Core $core, private Ranks $ranks, private SQL $sql, private Variables $vars)
    {
        // Property promotion
    }

    /**
     * Generate HTML for the votable options in a poll.
     *
     * @param int $voteID The record number from the vote_desc table.
     * @param bool $quarantine
     * @param string $allowsmilies Value from forums table.
     * @param string $allowbbcode Value from forums table.
     * @return string
     */
    public function pollOptionsVotable(int $voteID, bool $quarantine, string $allowsmilies, string $allowbbcode): string
    {
        $template = new Template($this->vars);
        $template->addRefs();

        $options = $this->sql->getVoteOptions($voteID, $quarantine);

        $html = '';
        foreach ($options as $option) {
            $template->id = (int) $option['vote_option_id'];
            $template->name = $this->core->postify(
                message: $option['vote_option_text'],
                allowsmilies: $allowsmilies,
                allowbbcode: $allowbbcode,
                allowimgcode: 'no',
                ismood: 'yes',
            );
            $html .= $template->process('viewthread_poll_options.php');
        }
        return $html;
    }

    /**
     * Generate HTML for member's online status.
     *
     * @param int $lastvisit Value from members table.
     * @param ?string $invisible Value from members table.
     * @return string
     */
    private function onlineNow(int $lastvisit, ?string $invisible): string
    {
        if ($this->vars->onlinetime - $lastvisit <= $this->vars::ONLINE_TIMER) {
            if ('1' === $invisible) {
                if (! X_ADMIN) {
                    $html = $this->vars->lang['memberisoff'];
                } else {
                    $html = $this->vars->lang['memberison'] . ' (' . $this->vars->lang['hidden'] . ')';
                }
            } else {
                $html = $this->vars->lang['memberison'];
            }
        } else {
            $html = $this->vars->lang['memberisoff'];
        }
        return $html;
    }

    /**
     * Generate HTML for the date of the post.
     *
     * @param int $timestamp Value from posts.dateline
     * @return string
     */
    private function postDate(int $timestamp): string
    {
        $adjStamp = $this->core->timeKludge($timestamp);
        $date = $this->core->printGmDate($adjStamp);
        $time = gmdate($this->vars->timecode, $adjStamp);

        return $this->vars->lang['textposton'] . " $date " . $this->vars->lang['textat'] . " $time";
    }

    /**
     * Generate HTML for the post icon.
     *
     * @param string $icon Value from posts.icon
     * @return string
     */
    private function postIcon(string $icon): string
    {
        $relPath = $this->vars->theme['smdir'] . '/' . $icon;

        if ($icon != '' && file_exists(ROOT . $relPath)) {
            $src = $this->vars->full_url . $relPath;
            $alt = $icon;
        } else {
            $src = $this->vars->full_url . $this->vars->theme['imgdir'] . '/default_icon.gif';
            $alt = '';
        }

        return "<img src='$src' alt='$alt' border='0' />";
    }

    /**
     * Prepare the common viewthread_post.php template variables that are based on the posts table record.
     *
     * @param array $post Results from the posts query, including related members records.
     * @param Template $template The template instance to populate.
     */
    public function preparePost(array $post, Template $template)
    {
        $full_url = $this->vars->full_url;
        
        // A 2nd template helps keep the sub-template variables separate.
        $prepTemplate = new Template($this->vars);
        $prepTemplate->addRefs();

        $template->fid = $post['fid'];
        $template->icon = $this->postIcon($post['icon']);
        $template->linktitle = $this->core->rawHTMLsubject($post['subject']);
        $template->onlinenow = $this->onlineNow((int) $post['lastvisit'], $post['invisible']);
        $template->pid = $post['pid'];
        $template->poston = $this->postDate((int) $post['dateline']);
        $template->subject = wordwrap($template->linktitle, 150, '<br />', true) . '<br />';
        $template->tid = $post['tid'];

        if (X_ADMIN) {
            $template->ip = $template->process('viewthread_post_ip.php');
        } else {
            $template->ip = '';
        }

        // Is reporting enabled for the viewing user?
        if (X_MEMBER && $post['author'] != $this->vars->xmbuser && $this->vars->settings['reportpost'] == 'on') {
            // Is the viewing user quarantined?
            if ('on' == $this->vars->settings['quarantine_new_users'] && (0 == (int) $this->vars->self['postnum'] || 'yes' == $this->vars->self['waiting_for_mod']) && ! X_STAFF) {
                $template->reportlink = '';
            } else {
                $template->reportlink = $template->process('viewthread_post_report.php');
            }
        } else {
            $template->reportlink = '';
        }

        $template->edit = $template->process('viewthread_post_edit.php');
        $template->repquote = $template->process('viewthread_post_repquote.php');


        if ($post['author'] != 'Anonymous' && $post['username'] && ('off' == $this->vars->settings['hide_banned'] || $post['status'] != 'Banned')) {
            $prepTemplate->encodename = recodeOut($post['author']);
            $prepTemplate->profileURL = $full_url . 'member.php?action=viewpro&amp;member=' . $prepTemplate->encodename;
            $template->profilelink = "<a href='" . $prepTemplate->profileURL . "'>{$post['author']}</a>";
            $template->profile = $prepTemplate->process('viewthread_post_profile.php');

            if (X_GUEST && $this->vars->settings['captcha_status'] == 'on' && $this->vars->settings['captcha_search_status'] == 'on') {
                $template->search = '';
            } else {
                $template->search = $prepTemplate->process('viewthread_post_search.php');
            }

            $rank = $this->ranks->find($post['status'], (int) $post['postnum']);

            $template->showtitle = ($post['customstatus'] != '') ? rawHTML($post['customstatus']) . '<br />' : rawHTML($rank['title']) . '<br />';
            $template->stars = str_repeat("<img src='" . $full_url . $this->vars->theme['imgdir'] . "/star.gif' alt='*' border='0' />", (int) $rank['stars']) . '<br />';

            $prepTemplate->url = format_member_site($post['site']);
            if ($prepTemplate->url == '') {
                $template->site = '';
            } else {
                $template->site = $prepTemplate->process('viewthread_post_site.php');
            }

            if (X_GUEST) {
                $template->u2u = '';
            } else {
                $template->u2u = $prepTemplate->process('viewthread_post_u2u.php');
            }

            // $rankAvatar is the avatar configured in rank settings.  $avatar is the user's avatar, pulled from the posts-join-members query.
            if ($rank['avatarrank'] != '') {
                $template->rankAvatar = "<img src='{$rank['avatarrank']}' alt='" . $this->vars->lang['altavatar'] . "' border='0' /><br />";
            } else {
                $template->rankAvatar = '';
            }

            $template->avatar = '';
            $avatarURL = $post['avatar'] ?? '';
            $avatarURL = str_replace("script:", "sc ript:", $avatarURL);
            if ($rank['allowavatars'] == 'no' || $avatarURL == '') {
                // skip
            } elseif ('on' == $this->vars->settings['images_https_only'] && strpos($avatarURL, ':') !== false && substr($avatarURL, 0, 6) !== 'https:') {
                // skip
            } elseif ($this->vars->settings['avastatus'] == 'on' || $this->vars->settings['avastatus'] == 'list') {
                $template->avatar = "<img src='$avatarURL' alt='" . $this->vars->lang['altavatar'] . "' border='0' />";
            }

            if ($post['mood'] == '') {
                $template->mood = '';
            } else {
                $template->mood = '<strong>' . $this->vars->lang['mood'] . '</strong> ' . $this->core->postify($post['mood'], allowimgcode: 'no', ismood: 'yes');
            }

            if ($post['location'] != '') {
                $template->location = '<br />' . $this->vars->lang['textlocation'] . ' ' . $this->core->rawHTMLsubject($post['location']);
            } else {
                $template->location = '';
            }

            $template->tharegdate = $this->core->printGmDate($this->core->timeKludge((int) $post['regdate']));

            // Some of the post record fields may be unused but they are made available anyway.
            $template->author = $post['author'];
            $template->postnum = $post['postnum'];
            $template->usesig = $post['usesig'];
        } else {
            $template->author = ($post['author'] == 'Anonymous') ? $this->vars->lang['textanonymous'] : $post['author'];
            $template->avatar = '';
            $template->location = '';
            $template->mood = '';
            $template->postnum = $this->vars->lang['not_applicable_abbr'];
            $template->profile = '';
            $template->profilelink = $template->author;
            $template->rankAvatar = '';
            $template->search = '';
            $template->showtitle = $this->vars->lang['textunregistered'] . '<br />';
            $template->site = '';
            $template->stars = '';
            $template->tharegdate = $this->vars->lang['not_applicable_abbr'];
            $template->u2u = '';
            $template->usesig = 'no';
        }
    }

    /**
     * Prepare the post body with attachment links, BBCode processing, and signature.
     *
     * @param array $post Results from the posts query, including related members records.
     * @param Template $template The template instance to populate.
     */
    public function preparePostBody(array $post, array $forum, array $attachments, bool $quarantine, Template $template)
    {
        if ($forum['attachstatus'] == 'on') {
            $files = [];
            foreach ($attachments as $attach) {
                if ($attach['pid'] === $post['pid']) {
                    $files[] = $attach;
                }
            }
            if (count($files) > 0) {
                $post['message'] = $this->core->bbcodeFileTags($post['message'], $files, (int) $post['pid'], ($forum['allowbbcode'] == 'yes' && $post['bbcodeoff'] == 'no'), $quarantine);
            }
        }

        $template->message = $this->core->postify(
            message: $post['message'],
            smileyoff: $post['smileyoff'],
            bbcodeoff: $post['bbcodeoff'],
            allowsmilies: $forum['allowsmilies'],
            allowbbcode: $forum['allowbbcode'],
            allowimgcode: $forum['allowimgcode'],
        );

        if ($post['usesig'] == 'yes' && ! empty($post['sig'])) {
            $template->sig = $this->core->postify(
                message: $post['sig'],
                allowsmilies: $forum['allowsmilies'],
                allowbbcode: $this->vars->settings['sigbbcode'],
                allowimgcode: $forum['allowimgcode'],
            );
            $template->message .= $template->process('viewthread_post_sig.php');
        }
    }
}
