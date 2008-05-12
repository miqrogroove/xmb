<?php
/**
 * eXtreme Message Board
 * XMB 1.9.10
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
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

// class defaults
define('CAPTCHA_WIDTH', $SETTINGS['captcha_image_width']);
define('CAPTCHA_HEIGHT', $SETTINGS['captcha_image_width']);
define('CAPTCHA_NUM_CHARS', $SETTINGS['captcha_code_length']);
define('CAPTCHA_NUM_DOTS', $SETTINGS['captcha_image_dots']);
define('CAPTCHA_NUM_LINES', $SETTINGS['captcha_image_lines']);
define('CAPTCHA_CHAR_SHADOW', $SETTINGS['captcha_code_shadow'] == 'on' ? true : false);
define('CAPTCHA_CHAR_SET', $SETTINGS['captcha_code_charset']);
define('CAPTCHA_FONTS', $SETTINGS['captcha_image_fonts']);
define('CAPTCHA_CASE_INSENSITIVE', $SETTINGS['captcha_code_casesensitive'] == 'on' ? false : true);
define('CAPTCHA_BACKGROUND_IMAGES', $SETTINGS['captcha_image_bg']);
define('CAPTCHA_MIN_FONT_SIZE', $SETTINGS['captcha_image_minfont']);
define('CAPTCHA_MAX_FONT_SIZE', $SETTINGS['captcha_image_maxfont']);
define('CAPTCHA_USE_COLOR', $SETTINGS['captcha_image_color'] == 'on' ? true : false);
define('CAPTCHA_FILE_TYPE', $SETTINGS['captcha_image_type']);

if (!defined('IN_CODE')) {
    exit ("Not allowed to run this file directly.");
}

class Captcha {
    var $oImage;
    var $aFonts;
    var $iWidth;
    var $iHeight;
    var $iNumChars;
    var $iNumDots;
    var $iNumLines;
    var $iSpacing;
    var $bCharShadow;
    var $aCharSet;
    var $bCaseInsensitive;
    var $aBackgroundImages;
    var $iMinFontSize;
    var $iMaxFontSize;
    var $bUseColor;
    var $sFileType;
    var $sCode = '';
    var $bCompatible;

    function Captcha($iWidth = CAPTCHA_WIDTH, $iHeight = CAPTCHA_HEIGHT) {
        // get parameters
        $this->SetNumChars(CAPTCHA_NUM_CHARS);
        $this->SetNumDots(CAPTCHA_NUM_DOTS);
        $this->SetNumLines(CAPTCHA_NUM_LINES);
        $this->DisplayShadow(CAPTCHA_CHAR_SHADOW);
        $this->SetCharSet(CAPTCHA_CHAR_SET);
        $this->SetFonts(CAPTCHA_FONTS);
        $this->CaseInsensitive(CAPTCHA_CASE_INSENSITIVE);
        $this->SetBackgroundImages(CAPTCHA_BACKGROUND_IMAGES);
        $this->SetMinFontSize(CAPTCHA_MIN_FONT_SIZE);
        $this->SetMaxFontSize(CAPTCHA_MAX_FONT_SIZE);
        $this->UseColor(CAPTCHA_USE_COLOR);
        $this->SetFileType(CAPTCHA_FILE_TYPE);
        $this->SetWidth($iWidth);
        $this->SetHeight($iHeight);
        $this->CheckCompatibility();
    }

    function CalculateSpacing() {
        $this->iSpacing = (int)($this->iWidth / $this->iNumChars);
    }

    function SetWidth($iWidth) {
        $this->iWidth = $iWidth;
        if ($this->iWidth > 500) $this->iWidth = 500; // to prevent perfomance impact
        $this->CalculateSpacing();
    }

    function SetHeight($iHeight) {
        $this->iHeight = $iHeight;
        if ($this->iHeight > 200) $this->iHeight = 200; // to prevent performance impact
    }

    function SetNumChars($iNumChars) {
        $this->iNumChars = $iNumChars;
        $this->CalculateSpacing();
    }

    function SetNumDots($iNumDots) {
        $this->iNumDots = $iNumDots;
    }

    function SetNumLines($iNumLines) {
        $this->iNumLines = $iNumLines;
    }

    function DisplayShadow($bCharShadow) {
        $this->bCharShadow = $bCharShadow;
    }

    function SetCharSet($vCharSet) {
        // check for input type
        if (is_array($vCharSet)) {
            $this->aCharSet = $vCharSet;
        } else {
            if ($vCharSet != '') {
                // split items on commas
                $aCharSet = explode(',', $vCharSet);

                // initialise array
                $this->aCharSet = array();

                // loop through items
                foreach($aCharSet as $sCurrentItem) {
                    // a range should have 3 characters, otherwise is normal character
                    if (strlen($sCurrentItem) == 3) {
                        // split on range character
                        $aRange = explode('-', $sCurrentItem);

                        // check for valid range
                        if (count($aRange) == 2 && $aRange[0] < $aRange[1]) {
                            // create array of characters from range
                            $aRange = range($aRange[0], $aRange[1]);

                            // add to charset array
                            $this->aCharSet = array_merge($this->aCharSet, $aRange);
                        }
                    } else {
                        $this->aCharSet[] = $sCurrentItem;
                    }
                }
            }
        }
    }

    function SetFonts($vFonts) {
        // check for input type
        if (is_array($vFonts)) {
            $aFonts = $vFonts;
        } else {
            if ($vFonts != '') {
                // split items on commas
                $aFonts = explode(',', $vFonts);
            } else {
                $aFonts = array('fonts');
            }
        }

        // initialise array
        $this->aFonts = array();

        // loop through items
        foreach($aFonts as $sCurrentItem) {
            if (is_dir($sCurrentItem)) {
                $dir = opendir($sCurrentItem);
                while($file = readdir($dir)) {
                    if (false !== strpos($file, '.ttf')) {
                        $this->aFonts[] = $sCurrentItem . '/' . $file;
                    }
                }
            } else {
                if (is_file($sCurrentItem) && false !== strpos($sCurrentItem, '.ttf')) {
                    $this->aFonts[] = $sCurrentItem;
                } else if (is_file($sCurrentItem . '.ttf')) {
                    $this->aFonts[] = $sCurrentItem . '.ttf';
                } else if (is_file('./fonts/' . $sCurrentItem) && false !== strpos($sCurrentItem, '.ttf')) {
                    $this->aFonts[] = './fonts/' .$sCurrentItem;
                } else if (is_file('./fonts/' .$sCurrentItem . '.ttf')) {
                    $this->aFonts[] = './fonts/' . $sCurrentItem . '.ttf';
                }
            }
        }
    }

    function CaseInsensitive($bCaseInsensitive) {
        $this->bCaseInsensitive = $bCaseInsensitive;
    }

    function SetBackgroundImages($vBackgroundImages) {
        // check for input type
        if (is_array($vBackgroundImages)) {
            $aBackgroundImages = $vBackgroundImages;
        } else {
            if ($vBackgroundImages != '') {
                // split items on commas
                $aBackgroundImages = explode(',', $vBackgroundImages);
            } else {
                $aBackgroundImages = array();
            }
        }

        // initialise array
        $this->aBackgroundImages = array();

        // loop through items
        foreach($aBackgroundImages as $sCurrentItem) {
            if (is_dir($sCurrentItem)) {
                $dir = opendir($sCurrentItem);
                while($file = readdir($dir)) {
                    if (false !== strpos($file, '.png')) {
                        $this->aBackgroundImages[] = $sCurrentItem . '/' . $file;
                    } else if (false !== strpos($file, '.gif')) {
                        $this->aBackgroundImages[] = $sCurrentItem . '/' . $file;
                    } else if (false !== strpos($file, '.jpg')) {
                        $this->aBackgroundImages[] = $sCurrentItem . '/' . $file;
                    } else if (false !== strpos($file, '.jpeg')) {
                        $this->aBackgroundImages[] = $sCurrentItem . '/' . $file;
                    }
                }
            } else {
                if (is_file($sCurrentItem) && false !== strpos($sCurrentItem, '.png')) {
                    $this->aBackgroundImages[] = $sCurrentItem;
                } else if (is_file($sCurrentItem) && false !== strpos($sCurrentItem, '.gif')) {
                    $this->aBackgroundImages[] = $sCurrentItem;
                } else if (is_file($sCurrentItem) && false !== strpos($sCurrentItem, '.jpg')) {
                    $this->aBackgroundImages[] = $sCurrentItem;
                } else if (is_file($sCurrentItem) && false !== strpos($sCurrentItem, '.jpeg')) {
                    $this->aBackgroundImages[] = $sCurrentItem;
                }
            }
        }
    }

    function SetMinFontSize($iMinFontSize) {
        $this->iMinFontSize = $iMinFontSize;
    }

    function SetMaxFontSize($iMaxFontSize) {
        $this->iMaxFontSize = $iMaxFontSize;
    }

    function UseColor($bUseColor) {
        $this->bUseColor = $bUseColor;
    }

    function SetFileType($sFileType) {
        // check for valid file type
        if (in_array($sFileType, array('gif', 'png', 'jpeg'))) {
            $this->sFileType = $sFileType;
        } else {
            $this->sFileType = 'jpeg';
        }
    }

    function DrawDots() {
        for($i = 0; $i < $this->iNumDots; $i++) {
            // allocate color
            if ($this->bUseColor) {
                $iDotColor = imagecolorallocate($this->oImage, rand(100, 250), rand(100, 250), rand(100, 250));
            } else {
                $iRandColor = rand(100, 250);
                $iDotColor = imagecolorallocate($this->oImage, $iRandColor, $iRandColor, $iRandColor);
            }

            // draw dot
            imagesetpixel($this->oImage, rand(0, $this->iWidth), rand(0, $this->iHeight), $iDotColor);
        }
    }

    function DrawLines() {
        for($i = 0; $i < $this->iNumLines; $i++) {
            // allocate color
            if ($this->bUseColor) {
                $iLineColor = imagecolorallocate($this->oImage, rand(100, 250), rand(100, 250), rand(100, 250));
            } else {
                $iRandColor = rand(100, 250);
                $iLineColor = imagecolorallocate($this->oImage, $iRandColor, $iRandColor, $iRandColor);
            }

            // draw line
            imageline($this->oImage, rand(0, $this->iWidth), rand(0, $this->iHeight), rand(0, $this->iWidth), rand(0, $this->iHeight), $iLineColor);
        }
    }

function GenerateCode() {
        global $db, $onlinetime;
        $db->query('DELETE FROM '.X_PREFIX.'captchaimages WHERE dateline < '.(time() - 86400));
        // loop through and generate the code letter by letter
        for($i = 0; $i < $this->iNumChars; $i++) {
            if (count($this->aCharSet) >= $this->iNumChars) {
                // select random character and add to code string
                $this->sCode .= $this->aCharSet[array_rand($this->aCharSet)];
            } else {
                // select random character and add to code string
                $this->sCode .= chr(rand(65, 90));
            }
        }

        // save code in DB and return image hash.
        $time = $onlinetime;

        if ($this->bCaseInsensitive) {
            $imagehash = md5(strtoupper($this->sCode));
        } else {
            $imagehash = md5($this->sCode);
        }

        $db->query("INSERT INTO ".X_PREFIX."captchaimages (imagehash, imagestring, dateline) VALUES ('$imagehash', '$this->sCode', '$time')");
        return $imagehash;
    }

    function RetrieveCode($imghash) {
        global $db;
        // check imagehash
        $imghash = checkInput($imghash, '', '', "javascript", false);
        if ($imghash == 'test') {
            $imgCode = 'CaPtChA';
        } else {
            $query = $db->query("SELECT * FROM ".X_PREFIX."captchaimages WHERE imagehash='$imghash'");
            $captchaimage = $db->fetch_array($query);
            $db->free_result($query);
            $imgCode = $captchaimage['imagestring'];
        }

        // reset code
        $this->sCode = $imgCode;
        return $imgCode;
    }

    function DrawCharacters() {
        // loop through and write out selected number of characters
        for($i = 0; $i < strlen($this->sCode); $i++) {
            // select random font
            $sCurrentFont = $this->aFonts[array_rand($this->aFonts)];

            // select random color
            if ($this->bUseColor) {
               $iTextColor = imagecolorallocate($this->oImage, rand(0, 100), rand(0, 100), rand(0, 100));
               if ($this->bCharShadow) {
                   // shadow color
                   $iShadowColor = imagecolorallocate($this->oImage, rand(0, 100), rand(0, 100), rand(0, 100));
                }
            } else {
                $iRandColor = rand(0, 100);
                $iTextColor = imagecolorallocate($this->oImage, $iRandColor, $iRandColor, $iRandColor);
                if ($this->bCharShadow) {
                    // shadow color
                    $iRandColor = rand(0, 100);
                    $iShadowColor = imagecolorallocate($this->oImage, $iRandColor, $iRandColor, $iRandColor);
                }
            }

            // select random font size
            $iFontSize = rand($this->iMinFontSize, $this->iMaxFontSize);

            // select random angle
            $iAngle = rand(-30, 30);

            // get dimensions of character in selected font and text size
            $aCharDetails = imageftbbox($iFontSize, $iAngle, $sCurrentFont, $this->sCode[$i], array());

            // calculate character starting coordinates
            $iX = $this->iSpacing / 4 + $i * $this->iSpacing;
            $iCharHeight = $aCharDetails[2] - $aCharDetails[5];
            $iY = $this->iHeight / 2 + $iCharHeight / 4;

            // write text to image
            imagefttext($this->oImage, $iFontSize, $iAngle, $iX, $iY, $iTextColor, $sCurrentFont, $this->sCode[$i], array());
            if ($this->bCharShadow) {
                $iOffsetAngle = rand(-30, 30);
                $iRandOffsetX = rand(-5, 5);
                $iRandOffsetY = rand(-5, 5);
                imagefttext($this->oImage, $iFontSize, $iOffsetAngle, $iX + $iRandOffsetX, $iY + $iRandOffsetY, $iShadowColor, $sCurrentFont, $this->sCode[$i], array());
            }
        }
    }

    function WriteFile() {
        // tell browser that data is jpeg
        header("Content-type: image/$this->sFileType");
        switch($this->sFileType) {
            case 'gif':
                imagegif($this->oImage);
                break;
            case 'png':
                imagepng($this->oImage);
                break;
            default:
                imagejpeg($this->oImage);
        }
    }

    function Create($imghash) {
        global $THEME;
        // get background image if specified and copy to CAPTCHA
        if (!empty($this->aBackgroundImages)) {
            // create new image
            $this->oImage = imagecreatetruecolor($this->iWidth, $this->iHeight);

            // create background image
            $iRandImage = array_rand($this->aBackgroundImages);
            $vBackgroundImage = $this->aBackgroundImages[$iRandImage];
            $ext = substr($vBackgroundImage, -3);
            switch($ext) {
                case 'gif':
                    $oBackgroundImage = imagecreatefromgif($vBackgroundImage);
                    break;
                case 'png':
                    $oBackgroundImage = imagecreatefrompng($vBackgroundImage);
                    break;
                default:
                    $oBackgroundImage = imagecreatefromjpeg($vBackgroundImage);
            }

            // copy background image
            imagecopy($this->oImage, $oBackgroundImage, 0, 0, 0, 0, $this->iWidth, $this->iHeight);

            // free memory used to create background image
            imagedestroy($oBackgroundImage);
        } elseif ($this->bUseColor) {
            $this->oImage = imagecreatetruecolor($this->iWidth, $this->iHeight);
        } else {
            // create new image
            $this->oImage = imagecreate($this->iWidth, $this->iHeight);
        }

        // allocate alternative background color 2 to match theme.
        $bg_red = hexdec(substr($THEME['altbg2'], 1, 2));
        $bg_green = hexdec(substr($THEME['altbg2'], 3, 2));
        $bg_blue = hexdec(substr($THEME['altbg2'], 5, 2));
        $bgcolor = imagecolorallocate($this->oImage, $bg_red, $bg_green, $bg_blue);

        if ($this->bUseColor And empty($this->aBackgroundImages)) {
            imagefill($this->oImage, 0, 0, $bgcolor);
        }

        $this->DrawDots();
        $this->DrawLines();
        $this->RetrieveCode($imghash);
        $this->DrawCharacters();

        // write out image to file or browser
        $this->WriteFile();

        // free memory used in creating image
        imagedestroy($this->oImage);
        return true;
    }

    // call this method statically
    function ValidateCode($sUserCode, $imghash) {
        global $db;

        if ($imghash == 'test') {
            return false;
        }

        $this->RetrieveCode($imghash);
        if ($this->bCaseInsensitive) {
            $sUserCode = strtoupper($sUserCode);
            $this->sCode = strtoupper($this->sCode);
        }

        if ($sUserCode == $this->sCode) {
            // clear to prevent re-use
            $db->query("DELETE FROM ".X_PREFIX."captchaimages WHERE imagehash='$imghash'");
            return true;
        }
        return false;
    }

    function CheckCompatibility() {
        // check for required gd functions
        if ($this->bCompatible === false) {
            return false;
        } else if (!function_exists('imagecreate') || !function_exists("image$this->sFileType") || ($this->aBackgroundImages != '' && !function_exists('imagecreatetruecolor'))) {
            $this->bCompatible = false;
            return false;
        } else if (empty($this->aFonts)) {
            $this->bCompatible = false;
            return false;
        } else {
            $this->bCompatible = true;
            return true;
        }
    }
}
?>
