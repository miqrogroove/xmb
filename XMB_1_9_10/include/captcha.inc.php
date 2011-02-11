<?php
/**
 * eXtreme Message Board
 * XMB 1.9.10 Karl // 11 February 2011 Security Patch
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
    var $bPoison;

    function Captcha($iWidth = CAPTCHA_WIDTH, $iHeight = CAPTCHA_HEIGHT) {
        $this->bPoison = FALSE;

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

    function DrawDots($colors) {
        $rmin = 0;
        $rmax = 255;

        for($i = 0; $i < $this->iNumDots; $i++) {
            // allocate color
            if ($this->bUseColor) {
                $iDotColor = imagecolorallocate($this->oImage, rand($rmin, $rmax), rand($rmin, $rmax), rand($rmin, $rmax));
            } else {
                $iRandColor = rand($rmin, $rmax);
                if (empty($this->aBackgroundImages)) {
                    $iDotColor = $colors[$iRandColor];
                } else {
                    $iDotColor = imagecolorallocate($this->oImage, $iRandColor, $iRandColor, $iRandColor);
                }
            }

            // draw dot
            imagesetpixel($this->oImage, rand(0, $this->iWidth), rand(0, $this->iHeight), $iDotColor);
        }
    }

    function DrawLines($bg_lum, $colors) {
        // Choose a luminosity range that will always be similar to the background color.
        if ($bg_lum < 128) {
            $rmin = 0;
            $rmax = 145;
        } else {
            $rmin = 110;
            $rmax = 255;
        }

        for($i = 0; $i < $this->iNumLines; $i++) {
            // allocate color
            if ($this->bUseColor) {
                $iLineColor = imagecolorallocate($this->oImage, rand($rmin, $rmax), rand($rmin, $rmax), rand($rmin, $rmax));
            } else {
                $iRandColor = rand($rmin, $rmax);
                if (empty($this->aBackgroundImages)) {
                    $iLineColor = $colors[$iRandColor];
                } else {
                    $iLineColor = imagecolorallocate($this->oImage, $iRandColor, $iRandColor, $iRandColor);
                }
            }

            // draw line
            imageline($this->oImage, rand(0, $this->iWidth), rand(0, $this->iHeight), rand(0, $this->iWidth), rand(0, $this->iHeight), $iLineColor);
        }
    }

    function GenerateCode() {
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

        if ($this->bCaseInsensitive) {
            $this->sCode = strtoupper($this->sCode);
        }

        // XMB saves code in DB and returns hashed code.
        $this->bPoison = TRUE;

        return nonce_create($this->sCode);
    }

    function RetrieveCode($imghash) {
        if ($imghash == 'test') {
            $this->bPoison = TRUE;
            $this->sCode = 'CaPtChA';
        } else {
            $this->sCode = nonce_peek($imghash, $this->iNumChars);
        }
    }

    function DrawCharacters($bg_lum, $colors) {
        // Choose a luminosity range that will never conflict with the background color.
        if ($bg_lum > 127) {
            $rmin = 0;
            $rmax = 110;
        } else {
            $rmin = 145;
            $rmax = 255;
        }

        // loop through and write out selected number of characters
        for($i = 0; $i < strlen($this->sCode); $i++) {
            // select random font
            $sCurrentFont = $this->aFonts[array_rand($this->aFonts)];

            // select random color
            if ($this->bUseColor) {
               $iTextColor = imagecolorallocate($this->oImage, rand($rmin, $rmax), rand($rmin, $rmax), rand($rmin, $rmax));
               if ($this->bCharShadow) {
                   // shadow color
                   $iShadowColor = imagecolorallocate($this->oImage, rand($rmin, $rmax), rand($rmin, $rmax), rand($rmin, $rmax));
                }
            } else {
                $iRandColor = rand($rmin, $rmax);
                if (empty($this->aBackgroundImages)) {
                    $iTextColor = $colors[$iRandColor];
                } else {
                    $iTextColor = imagecolorallocate($this->oImage, $iRandColor, $iRandColor, $iRandColor);
                }
                if ($this->bCharShadow) {
                    // shadow color
                    $iRandColor = rand($rmin, $rmax);
                    if (empty($this->aBackgroundImages)) {
                        $iShadowColor = $colors[$iRandColor];
                    } else {
                        $iShadowColor = imagecolorallocate($this->oImage, $iRandColor, $iRandColor, $iRandColor);
                    }
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
                imagegif ($this->oImage);
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

        $this->bPoison = TRUE;

        // calculate color components of alternative background color 2 to match theme.
        $bg_red = hexdec(substr($THEME['altbg2'], 1, 2));
        $bg_green = hexdec(substr($THEME['altbg2'], 3, 2));
        $bg_blue = hexdec(substr($THEME['altbg2'], 5, 2));
        $bg_lum = (max($bg_red, $bg_green, $bg_blue) + min($bg_red, $bg_green, $bg_blue)) >> 1;
        $colors = array();

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
                    $oBackgroundImage = imagecreatefromgif ($vBackgroundImage);
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
        } else if ($this->bUseColor) {
            $this->oImage = imagecreatetruecolor($this->iWidth, $this->iHeight);
            $bgcolor = imagecolorallocate($this->oImage, $bg_red, $bg_green, $bg_blue);
            imagefill($this->oImage, 0, 0, $bgcolor);
        } else {
            // create new image
            $this->oImage = imagecreate($this->iWidth, $this->iHeight);
            imagecolorallocate($this->oImage, $bg_red, $bg_green, $bg_blue);
            for($i = 1; $i <= 255; $i++) {
                $colors[$i] = imagecolorallocate($this->oImage, $i, $i, $i);
            }
            $colors[0] = $colors[1];
        }

        $this->DrawLines($bg_lum, $colors);
        $this->DrawDots($colors);
        $this->RetrieveCode($imghash);
        $this->DrawCharacters($bg_lum, $colors);

        // write out image to file or browser
        $this->WriteFile();

        // free memory used in creating image
        imagedestroy($this->oImage);
        return true;
    }

    // call this method statically
    function ValidateCode($sUserCode, $imghash) {
        if ($this->bPoison) {
            return FALSE;
        }

        $this->bPoison = TRUE;

        if (strlen($sUserCode) != $this->iNumChars or $imghash == 'test') {
            return FALSE;
        }

        if ($this->bCaseInsensitive) {
            $sUserCode = strtoupper($sUserCode);
        }

        return nonce_use($sUserCode, $imghash);
    }

    function CheckCompatibility() {
        // check for required gd functions
        if ($this->bCompatible === false) {
            $this->bPoison = TRUE;
            return false;
        } else if (!function_exists('imagecreate') || !function_exists("image$this->sFileType") || ($this->aBackgroundImages != '' && !function_exists('imagecreatetruecolor'))) {
            $this->bCompatible = false;
            $this->bPoison = TRUE;
            return false;
        } else if (empty($this->aFonts)) {
            $this->bCompatible = false;
            $this->bPoison = TRUE;
            return false;
        } else {
            $this->bCompatible = true;
            return true;
        }
    }
}
?>
