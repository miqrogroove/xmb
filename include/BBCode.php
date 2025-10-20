<?php

/**
 * eXtreme Message Board
 * XMB 1.10.01
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

class BBCode
{
    private array $colors = [
        'aliceblue',
        'antiquewhite',
        'aqua',
        'aquamarine',
        'azure',
        'beige',
        'bisque',
        'black',
        'blanchedalmond',
        'blue',
        'blueviolet',
        'brown',
        'burlywood',
        'cadetblue',
        'chartreuse',
        'chocolate',
        'coral',
        'cornflowerblue',
        'cornsilk',
        'crimson',
        'cyan',
        'darkblue',
        'darkcyan',
        'darkgoldenrod',
        'darkgray',
        'darkgreen',
        'darkgrey',
        'darkkhaki',
        'darkmagenta',
        'darkolivegreen',
        'darkorange',
        'darkorchid',
        'darkred',
        'darksalmon',
        'darkseagreen',
        'darkslateblue',
        'darkslategray',
        'darkslategrey',
        'darkturquoise',
        'darkviolet',
        'deeppink',
        'deepskyblue',
        'dimgray',
        'dimgrey',
        'dodgerblue',
        'firebrick',
        'floralwhite',
        'forestgreen',
        'fuchsia',
        'gainsboro',
        'ghostwhite',
        'gold',
        'goldenrod',
        'gray',
        'green',
        'greenyellow',
        'grey',
        'honeydew',
        'hotpink',
        'indianred',
        'indigo',
        'ivory',
        'khaki',
        'lavender',
        'lavenderblush',
        'lawngreen',
        'lemonchiffon',
        'lightblue',
        'lightcoral',
        'lightcyan',
        'lightgoldenrodyellow',
        'lightgray',
        'lightgreen',
        'lightgrey',
        'lightpink',
        'lightsalmon',
        'lightseagreen',
        'lightskyblue',
        'lightslategray',
        'lightslategrey',
        'lightsteelblue',
        'lightyellow',
        'lime',
        'limegreen',
        'linen',
        'magenta',
        'maroon',
        'mediumaquamarine',
        'mediumblue',
        'mediumorchid',
        'mediumpurple',
        'mediumseagreen',
        'mediumslateblue',
        'mediumspringgreen',
        'mediumturquoise',
        'mediumvioletred',
        'midnightblue',
        'mintcream',
        'mistyrose',
        'moccasin',
        'navajowhite',
        'navy',
        'oldlace',
        'olive',
        'olivedrab',
        'orange',
        'orangered',
        'orchid',
        'palegoldenrod',
        'palegreen',
        'paleturquoise',
        'palevioletred',
        'papayawhip',
        'peachpuff',
        'peru',
        'pink',
        'plum',
        'powderblue',
        'purple',
        'red',
        'rosybrown',
        'royalblue',
        'saddlebrown',
        'salmon',
        'sandybrown',
        'seagreen',
        'seashell',
        'sienna',
        'silver',
        'skyblue',
        'slateblue',
        'slategray',
        'slategrey',
        'snow',
        'springgreen',
        'steelblue',
        'tan',
        'teal',
        'thistle',
        'tomato',
        'turquoise',
        'violet',
        'wheat',
        'white',
        'whitesmoke',
        'yellow',
        'yellowgreen',
    ];

    public function __construct(private ThemeManager $theme, private Variables $vars)
    {
        // Property promotion
    }

    /**
     * BBCode processor
     *
     * Does not properly handle the contents of any "code" block bbcode.  See bbcodeCode() before using this.
     *
     * @since 1.9.8 SP3 Formerly "bbcode()".  Prior to that it was integrated with postify().
     * @since 1.10.00
     * @param string $message Variable required and modified.
     * @param bool $allowimgcode When false, img and youtube bbcodes will not be processed.
     * @param bool $allowurlcode When false, url bbcodes and raw URLs will not be converted to HTML links.
     */
    public function process(string &$message, bool $allowimgcode, bool $allowurlcode)
    {
        //Balance simple tags.
        $begin = [
            0 => '[b]',
            1 => '[i]',
            2 => '[u]',
            3 => '[marquee]',
            4 => '[blink]',
            5 => '[strike]',
            6 => '[quote]',
            8 => '[list]',
            9 => '[list=1]',
            10 => '[list=a]',
            11 => '[list=A]',
        ];

        $end = [
            0 => '[/b]',
            1 => '[/i]',
            2 => '[/u]',
            3 => '[/marquee]',
            4 => '[/blink]',
            5 => '[/strike]',
            6 => '[/quote]',
            8 => '[/list]',
            9 => '[/list=1]',
            10 => '[/list=a]',
            11 => '[/list=A]',
        ];

        foreach ($begin as $key => $value) {
            $check = substr_count($message, $value) - substr_count($message, $end[$key]);
            if ($check > 0) {
                $message .= str_repeat($end[$key], $check);
            } else if ($check < 0) {
                $message = str_repeat($value, abs($check)).$message;
            }
        }

        // Balance regex tags.
        $regex = [
            'align' => "@\\[align=(left|center|right|justify)\\]@i",
            'font' => "@\\[font=([a-z\\- 0-9]+)\\]@i",
            'rquote' => "@\\[rquote=(\\d+)&(?:amp;)?tid=(\\d+)&(?:amp;)?author=([^\\[\\]<>]+)\\]@s",
            'size' => "@\\[size=([+-]?[0-9]{1,2})\\]@",
            'color' => [
                'named' => "@\\[color=([a-z]{3,20})\\]@i",
                'hex' => "@\\[color=#([\\da-f]{3,6})\\]@i",
                'rgb' => "@\\[color=rgb\\(([\\s]*[\\d]{1,3}%?[\\s]*,[\\s]*[\\d]{1,3}%?[\\s]*,[\\s]*[\\d]{1,3}%?[\\s]*)\\)\\]@i",
                'hsl' => "@\\[color=hsl\\(([\\s]*[\\d]{1,3}[\\s]*,[\\s]*[\\d]{1,3}%[\\s]*,[\\s]*[\\d]{1,3}%[\\s]*)\\)\\]@i",
            ],
        ];

        $this->balanceTags($message, $regex);

        // Replace simple tags.
        $find = [
            0 => '[b]',
            1 => '[/b]',
            2 => '[i]',
            3 => '[/i]',
            4 => '[u]',
            5 => '[/u]',
            6 => '[marquee]',
            7 => '[/marquee]',
            8 => '[blink]',
            9 => '[/blink]',
            10 => '[strike]',
            11 => '[/strike]',
            12 => '[quote]',
            13 => '[/quote]',
            14 => '[code]',
            15 => '[/code]',
            16 => '[list]',
            17 => '[/list]',
            18 => '[list=1]',
            19 => '[list=a]',
            20 => '[list=A]',
            21 => '[/list=1]',
            22 => '[/list=a]',
            23 => '[/list=A]',
            24 => '[*]',
            25 => '[/color]',
            26 => '[/font]',
            27 => '[/size]',
            28 => '[/align]',
            29 => '[/rquote]'
        ];

        $replace = [
            0 => '<strong>',
            1 => '</strong>',
            2 => '<em>',
            3 => '</em>',
            4 => '<u>',
            5 => '</u>',
            6 => '<div class="marquee"><div class="marquee2">',
            7 => '</div></div>',
            8 => '<span class="blink">',
            9 => '</span>',
            10 => '<strike>',
            11 => '</strike>',
            12 => ' <!-- nobr --><table align="center" class="quote" cellspacing="0" cellpadding="0"><tr><td class="quote">'.$this->vars->lang['textquote'].'</td></tr><tr><td class="quotemessage"><!-- /nobr -->',
            13 => ' </td></tr></table>',
            14 => ' <!-- nobr --><table align="center" class="code" cellspacing="0" cellpadding="0"><tr><td class="code">'.$this->vars->lang['textcode'].'</td></tr><tr><td class="codemessage"><code>',
            15 => '</code></td></tr></table><!-- /nobr -->',
            16 => '<ul type="square">',
            17 => '</ul>',
            18 => '<ol type="1">',
            19 => '<ol type="A">',
            20 => '<ol type="A">',
            21 => '</ol>',
            22 => '</ol>',
            23 => '</ol>',
            24 => '<li />',
            25 => '</span>',
            26 => '</span>',
            27 => '</span>',
            28 => '</div>',
            29 => ' </td></tr></table>'
        ];

        $message = str_replace($find, $replace, $message);

        // Replace regex tags.
        $patterns = [];
        $replacements = [];

        $patterns[] = $regex['rquote'];
        $replacements[] = ' <!-- nobr --><table align="center" class="quote" cellspacing="0" cellpadding="0"><tr><td class="quote">'.$this->vars->lang['textquote'].' <a href="viewthread.php?tid=$2&amp;goto=search&amp;pid=$1" rel="nofollow">'.$this->vars->lang['origpostedby'].' $3 &nbsp;<img src="'.$this->vars->theme['imgdir'].'/lastpost.gif" border="0" alt="" style="vertical-align: middle;" /></a></td></tr><tr><td class="quotemessage"><!-- /nobr -->';
        $patterns[] = $regex['color']['hex'];
        $replacements[] = '<span style="color: #$1;">';
        $patterns[] = $regex['color']['rgb'];
        $replacements[] = '<span style="color: rgb($1);">';
        $patterns[] = $regex['color']['hsl'];
        $replacements[] = '<span style="color: hsl($1);">';
        $patterns[] = $regex['font'];
        $replacements[] = '<span style="font-family: $1;">';
        $patterns[] = $regex['align'];
        $replacements[] = '<div style="text-align: $1;">';

        $patterns[] = "@\\[pid=(\\d+)&amp;tid=(\\d+)](.*?)\\[/pid]@si";
        $replacements[] = '<!-- nobr --><a href="viewthread.php?tid=$2&amp;goto=search&amp;pid=$1"><strong><!-- /nobr -->$3</strong> &nbsp;<img src="'.$this->vars->theme['imgdir'].'/lastpost.gif" border="0" alt="" style="vertical-align: middle;" /></a>';

        if ($allowimgcode) {
            $patterns[] = '@\[youtube\](?:[^\[\]<>]*(?:v\=|/))?([a-z0-9_-]++)[^\[\]<>]*\[/youtube\]@i';
            $replacements[] = '<!-- nobr --><iframe class="video" src="https://www.youtube.com/embed/\1" allowfullscreen></iframe><!-- /nobr -->';
        }

        $message = preg_replace($patterns, $replacements, $message);

        $message = preg_replace_callback($regex['size'], [$this, 'sizeTags'], $message);

        $message = preg_replace_callback($regex['color']['named'], [$this, 'colorNameTags'], $message);

        if ($allowimgcode) {
            $https_only = 'on' == $this->vars->settings['images_https_only'];
            $base_pattern = get_img_regexp($https_only);

            $patterns = [
                '/\[img\]' . $base_pattern . '\[\/img\]/i',
                '/\[img=([0-9]*?){1}x([0-9]*?)\]' . $base_pattern . '\[\/img\]/i',
            ];
            $message = preg_replace_callback($patterns, [$this, 'imgs'], $message);
        }

        if ($allowurlcode) {
            /*
              This block positioned last so that bare URLs may appear adjacent to BBCodes without matching on square braces.
              Regexp explanation: match strings surrounded by whitespace or () or ><.  Do not include the surrounding chars.
                Group 1 will be identical to the full match so that the callback function can be reused for [url] codes.
            */
            $regexp = '(?<=^|\s|>|\()'
                    . '('
                    . '(?:(?:http|ftp)s?://|www)'
                    . '[-a-z0-9.]+\.[a-z]{2,4}'
                    . '[^\s()"\'<>\[\]]*'
                    . ')'
                    . '(?=$|\s|<|\))';
            $message = preg_replace_callback("#$regexp#i", [$this, 'longURLs'], $message);

            //[url]https://www.example.com/[/url]
            //[url]www.example.com[/url]
            $message = preg_replace_callback("#\[url\]([^\"'<>]+?)\[/url\]#i", [$this, 'longURLs'], $message);

            //[url=https://www.example.com/]Lorem Ipsum[/url]
            //[url=www.example.com]Lorem Ipsum[/url]
            $message = preg_replace_callback("#\[url=([^\"'<>\[\]]+)\](.*?)\[/url\]#i", [$this, 'longURLs'], $message);
        }

        $patterns = [
            "#\\[email\\]([^\"'<>]+?)\\[/email\\]#i",
            "#\\[email=([^\"'<>\\[\\]]+)\\](.+?)\\[/email\\]#i",
        ];
        $message = preg_replace_callback($patterns, [$this, 'emails'], $message);

        return true;
    }

    /**
     * Full parsing of [code] tags.
     *
     * @since 1.9.11.12 Formerly "bbcodeCode()"
     * @since 1.10.00
     * @param string $message
     * @return array Odd number indexes contain the code block contents.
     */
    public function parseCodeBlocks($message)
    {
        $counter = 0;
        $offset = 0;
        $done = false;
        $messagearray = [];
        while (! $done) {
            $pos = strpos($message, '[code]', $offset);
            if (false === $pos) {
                $messagearray[$counter] = substr($message, $offset);
                $messagearray[$counter] = str_replace('[/code]', '&#091;/code]', $messagearray[$counter]);
                if ($counter > 1) {
                    $messagearray[$counter] = '[/code]'.$messagearray[$counter];
                }
                $done = true;
            } else {
                $pos += strlen('[code]');
                $messagearray[$counter] = substr($message, $offset, $pos - $offset);
                $messagearray[$counter] = str_replace('[/code]', '&#091;/code]', $messagearray[$counter]);
                if ($counter > 1) {
                    $messagearray[$counter] = '[/code]'.$messagearray[$counter];
                }
                $counter++;
                $offset = $pos;
                $pos = strpos($message, '[/code]', $offset);
                if (false === $pos) {
                    $messagearray[$counter] = substr($message, $offset);
                    $counter++;
                    $messagearray[$counter] = '[/code]';
                    $done = true;
                } else {
                    $messagearray[$counter] = substr($message, $offset, $pos - $offset);
                    $counter++;
                    $offset = $pos + strlen('[/code]');
                }
            }
        }
        return $messagearray;
    }

    /**
     * Guarantees each BBCode has an equal number of open and close tags.
     *
     * @since 1.9.11.12 Formerly bbcodeBalanceTags()
     * @since 1.10.00
     * @param string $message Read/Write Variable
     * @param array $regex Indexed by code name
     */
    private function balanceTags(&$message, $regex)
    {
        foreach ($regex as $code => $pattern) {
            if (is_array($pattern)) {
                $open = 0;
                foreach ($pattern as $subpattern) {
                    $open += preg_match_all($subpattern, $message, $matches);
                }
            } else {
                $open = preg_match_all($pattern, $message, $matches);
            }
            $close = substr_count($message, "[/$code]");
            $open -= $close;
            if ($open > 0) {
                $message .= str_repeat("[/$code]", $open);
            } elseif ($open < 0) {
                $message = preg_replace("@\\[/$code]@", "&#091;/$code]", $message, -$open);
            }
        }
    }

    /**
     * Handles the [url] BBCode.
     *
     * This helper function is algorithmically required in order to fully support
     * unencoded square braces in BBCode URLs.  Encoding of the RFC 1738 Unsafe
     * character set thus remains optional at the BBCode and HTML layers.
     *
     * Credit for the value used in $scheme_whitelist goes to the WordPress project.
     *
     * @since 1.9.11.12 Formerly bbcodeLongURLs()
     * @since 1.10.00
     * @param array $url Expects $url[0] to be the raw BBCode, $url[1] to be the URL only, and optionally $url[2] to be the display text.
     * @return string The HTML replacement for $url[0] if the code was valid, else the code is unchaged.
     */
    private function longURLs(array $url): string
    {
        $url_max_display_len = 60;
        $scheme_whitelist = array('http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn');

        $colon = strpos($url[1], ':');
        if (false !== $colon) {
            $scheme = substr($url[1], 0, $colon);
            if (in_array($scheme, $scheme_whitelist)) {
                $href = $url[1];
            } else {
                return $url[0];
            }
        } else {
            $href = 'http://'.$url[1];
        }
        if (! empty($url[2])) {
            $text = $url[2];
        } elseif (strlen($url[1]) <= $url_max_display_len) {
            $text = $url[1];
        } else {
            $text = substr($url[1], 0, $url_max_display_len).'...';
        }

        $href = $this->bbcode_out($href);

        return "<!-- nobr --><a href='$href' onclick='window.open(this.href); return false;'><!-- /nobr -->$text</a>";
    }

    /**
     * Creates a styled span relative to the theme's font size.
     *
     * @since 1.9.11 Formerly bbcodeSizeTags()
     * @since 1.10.00
     */
    private function sizeTags(array $matches): string
    {
        $relative = (int) $matches[1];
        $o = $this->theme->fontSize($relative);

        $html = "<span style='font-size: $o;'>";

        return $html;
    }

    /**
     * Handles the [email] BBCode.
     *
     * @since 1.9.12.03 Formerly bbcode_emails()
     * @since 1.10.00
     * @param array $matches Expects $matches[0] to be the raw BBCode, $matches[1] to be the URL only, and optionally $matches[2] to be the display text.
     * @return string The HTML replacement for $matches[0].
     */
    private function emails(array $matches): string
    {
        $text = $matches[2] ?? $matches[1];
        $address = $this->bbcode_out($matches[1]);

        return "<a href='mailto:$address'>$text</a>";
    }

    /**
     * Handles the [img] BBCode.
     *
     * @since 1.9.12.03 Formerly bbcode_imgs()
     * @since 1.10.00
     * @param array $matches Expects different elements depending on the pattern.
     * @return string The HTML replacement for $matches[0].
     */
    private function imgs(array $matches): string
    {
        if (count($matches) < 5) {
            $width = 0;
            $height = 0;
            $scheme = $matches[1];
            $path = $matches[2];
            $query = $matches[3] ?? '';
        } else {
            $width = (int) $matches[1];
            $height = (int) $matches[2];
            $scheme = $matches[3];
            $path = $matches[4];
            $query = $matches[5] ?? '';
        }

        if ($width < 1 || $height < 1) {
            $size = '';
        } else {
            $size = "width='$width' height='$height'";
        }

        $address = $this->bbcode_out("$scheme://$path$query");

        return "<!-- nobr --><img $size src='$address' alt='' border='0' /><!-- /nobr -->";
    }

    /**
     * Output filter for BBCodes
     *
     * @since 1.9.12.03
     */
    private function bbcode_out(string $message): string
    {
        $retval = $message;
        $retval = htmlspecialchars($retval, double_encode: false);
        $retval = str_replace(array('[', ']'), array('&#91;', '&#93;'), $retval);
        return $retval;
    }

    /**
     * Handles the [color] BBCode for named colors.
     *
     * @since 1.10.00
     * @param array $matches Expects $matches[0] to be the raw BBCode, and $matches[1] to be the named color only.
     * @return string The HTML replacement for $matches[0].
     */
    private function colorNameTags(array $matches): string
    {
        if (in_array(strtolower($matches[1]), $this->colors)) {
            $color = $matches[1];
        } else {
            $color = $this->vars->theme['text'];
        }

        return "<span style='color: $color;'>";
    }
}
