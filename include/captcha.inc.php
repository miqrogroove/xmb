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

/***************************************************************/
/* PhpCaptcha - A visual and audio CAPTCHA generation library

  Software License Agreement (BSD License)

  Copyright (C) 2005-2006, Edward Eliot.
  All rights reserved.

  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions are met:

     * Redistributions of source code must retain the above copyright
       notice, this list of conditions and the following disclaimer.
     * Redistributions in binary form must reproduce the above copyright
       notice, this list of conditions and the following disclaimer in the
       documentation and/or other materials provided with the distribution.
     * Neither the name of Edward Eliot nor the names of its contributors
       may be used to endorse or promote products derived from this software
       without specific prior written permission of Edward Eliot.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS" AND ANY
  EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

  Last Updated:  18th April 2006                               */
/***************************************************************/

/************************ Documentation ************************/
/*

Documentation is available at http://www.ejeliot.com/pages/2

*/

class Captcha
{
    private $oImage;
    private array $aFonts;
    private int $iWidth;
    private int $iHeight;
    private int $iNumChars = 0;
    private int $iNumDots;
    private int $iNumLines;
    private int $iSpacing;
    private bool $bCharShadow;
    private array $aCharSet;
    private bool $bCaseInsensitive;
    private array $aBackgroundImages;
    private int $iMinFontSize;
    private int $iMaxFontSize;
    private bool $bUseColor;
    private string $sFileType;
    private string $sCode = '';
    // XMB Members
    public readonly bool $bCompatible;
    public bool $bPoison;

    function __construct(private Core $core, private Observer $observer, private Variables $vars)
    {
        // get parameters
        $this->SetWidth((int) $this->vars->settings['captcha_image_width']);
        $this->SetHeight((int) $this->vars->settings['captcha_image_height']);
        $this->SetNumChars((int) $this->vars->settings['captcha_code_length']);
        $this->SetNumDots((int) $this->vars->settings['captcha_image_dots']);
        $this->SetNumLines((int) $this->vars->settings['captcha_image_lines']);
        $this->DisplayShadow($this->vars->settings['captcha_code_shadow'] == 'on' ? true : false);
        $this->SetCharSet($this->vars->settings['captcha_code_charset']);
        $this->SetFonts($this->vars->settings['captcha_image_fonts']);
        $this->CaseInsensitive($this->vars->settings['captcha_code_casesensitive'] == 'on' ? false : true);
        $this->SetBackgroundImages($this->vars->settings['captcha_image_bg']);
        $this->SetMinFontSize((int) $this->vars->settings['captcha_image_minfont']);
        $this->SetMaxFontSize((int) $this->vars->settings['captcha_image_maxfont']);
        $this->UseColor($this->vars->settings['captcha_image_color'] == 'on' ? true : false);
        $this->SetFileType($this->vars->settings['captcha_image_type']);
        // Initialize XMB Members
        $this->bPoison = false;
        $this->CheckCompatibility();
    }

    function CalculateSpacing()
    {
        if ($this->iNumChars > 0) {
            $this->iSpacing = (int)($this->iWidth / $this->iNumChars);
        }
    }

    function SetWidth($iWidth)
    {
        $this->iWidth = $iWidth;
        if ($this->iWidth > 500) $this->iWidth = 500; // to prevent perfomance impact
        $this->CalculateSpacing();
    }

    function SetHeight($iHeight)
    {
        $this->iHeight = $iHeight;
        if ($this->iHeight > 200) $this->iHeight = 200; // to prevent performance impact
    }

    function SetNumChars($iNumChars)
    {
        $this->iNumChars = $iNumChars;
        $this->CalculateSpacing();
    }

    function SetNumLines($iNumLines)
    {
        $this->iNumLines = $iNumLines;
    }

    function DisplayShadow($bCharShadow)
    {
        $this->bCharShadow = $bCharShadow;
    }

    //function SetOwnerText($sOwnerText) {  // Not used in XMB
    //   $this->sOwnerText = $sOwnerText;
    //}

    function SetCharSet($vCharSet)
    {
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

    function CaseInsensitive($bCaseInsensitive)
    {
        $this->bCaseInsensitive = $bCaseInsensitive;
    }

    //function SetBackgroundImages($vBackgroundImages) {  // Replaced, See Below
    //   $this->vBackgroundImages = $vBackgroundImages;
    //}

    function SetMinFontSize($iMinFontSize)
    {
        $this->iMinFontSize = $iMinFontSize;
    }

    function SetMaxFontSize($iMaxFontSize)
    {
        $this->iMaxFontSize = $iMaxFontSize;
    }

    function UseColor($bUseColor)
    {
        $this->bUseColor = $bUseColor;
    }

    function SetFileType($sFileType)
    {
        // check for valid file type
        if (in_array($sFileType, array('gif', 'png', 'jpeg'))) {
            $this->sFileType = $sFileType;
        } else {
            $this->sFileType = 'jpeg';
        }
    }

    private function DrawLines($bg_lum, $colors)
    {
        //XMB chooses a lightness range that will always be similar to the background color.
        if ($bg_lum < 128) {
            $rmin = 0;
            $rmax = 145;
        } else {
            $rmin = 110;
            $rmax = 255;
        }

        for($i = 0; $i < $this->iNumLines; $i++) {
            // allocate color
            if ($this->bUseColor) {  // XMB forces true color mode to prevent palette overflow.
                $iLineColor = imagecolorallocate($this->oImage, rand($rmin, $rmax), rand($rmin, $rmax), rand($rmin, $rmax));
            } else {
                $iRandColor = rand($rmin, $rmax);
                if (empty($this->aBackgroundImages)) {  // XMB pre-allocates all greyscales to prevent palette overflow.
                    $iLineColor = $colors[$iRandColor];
                } else {
                    $iLineColor = imagecolorallocate($this->oImage, $iRandColor, $iRandColor, $iRandColor);
                }
            }

            // draw line
            imageline($this->oImage, rand(0, $this->iWidth), rand(0, $this->iHeight), rand(0, $this->iWidth), rand(0, $this->iHeight), $iLineColor);
        }
    }

    //function DrawOwnerText() {  // Not used in XMB
    //   // allocate owner text colour
    //   $iBlack = imagecolorallocate($this->oImage, 0, 0, 0);
    //   // get height of selected font
    //   $iOwnerTextHeight = imagefontheight(2);
    //   // calculate overall height
    //   $iLineHeight = $this->iHeight - $iOwnerTextHeight - 4;
    //
    //   // draw line above text to separate from CAPTCHA
    //   imageline($this->oImage, 0, $iLineHeight, $this->iWidth, $iLineHeight, $iBlack);
    //
    //   // write owner text
    //   imagestring($this->oImage, 2, 3, $this->iHeight - $iOwnerTextHeight - 3, $this->sOwnerText, $iBlack);
    //
    //   // reduce available height for drawing CAPTCHA
    //   $this->iHeight = $this->iHeight - $iOwnerTextHeight - 5;
    //}

    public function GenerateCode()
    {
        // reset code
        $this->sCode = '';

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

        return $this->core->nonce_create($this->sCode);
    }

    private function DrawCharacters($bg_lum, $colors)
    {
        // XMB chooses a lightness range that will never conflict with the background color.
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
                if (empty($this->aBackgroundImages)) {  // XMB pre-allocates all greyscales to prevent palette overflow.
                    $iTextColor = $colors[$iRandColor];
                } else {
                    $iTextColor = imagecolorallocate($this->oImage, $iRandColor, $iRandColor, $iRandColor);
                }
                if ($this->bCharShadow) {
                    // shadow color
                    $iRandColor = rand($rmin, $rmax);
                    if (empty($this->aBackgroundImages)) {  // XMB pre-allocates all greyscales to prevent palette overflow.
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
            $iX = intval($this->iSpacing / 4) + $i * $this->iSpacing;
            $iCharHeight = $aCharDetails[2] - $aCharDetails[5];
            $iY = intval($this->iHeight / 2 + $iCharHeight / 4);

            // write text to image
            imagefttext($this->oImage, $iFontSize, $iAngle, $iX, $iY, $iTextColor, $sCurrentFont, $this->sCode[$i]);
            
            if ($this->bCharShadow) {
                $iOffsetAngle = rand(-30, 30);
                
                $iRandOffsetX = rand(-5, 5);
                $iRandOffsetY = rand(-5, 5);
                
                imagefttext($this->oImage, $iFontSize, $iOffsetAngle, $iX + $iRandOffsetX, $iY + $iRandOffsetY, $iShadowColor, $sCurrentFont, $this->sCode[$i]);
            }
        }
    }

    private function WriteFile()
    {
        // Explicitly re-run XMB's output stream check, and do not rely on the DEBUG constant.
        $this->observer->assertEmptyOutputStream('misc.php (?) before the call to Captcha::WriteFile()', use_debug: false);

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

    public function Create($imghash)
    {
        $this->bPoison = true;

        // XMB calculates the color components of alternative background color 2 to match theme.
        $bg_red = hexdec(substr($this->vars->theme['altbg2'], 1, 2));
        $bg_green = hexdec(substr($this->vars->theme['altbg2'], 3, 2));
        $bg_blue = hexdec(substr($this->vars->theme['altbg2'], 5, 2));
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
            // XMB forces true color mode to prevent palette overflow.
            $this->oImage = imagecreatetruecolor($this->iWidth, $this->iHeight);
            $bgcolor = imagecolorallocate($this->oImage, $bg_red, $bg_green, $bg_blue);
            imagefill($this->oImage, 0, 0, $bgcolor);
        } else {
            // XMB pre-allocates all greyscales to prevent palette overflow.
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


    /* end Edward Eliot code */
    /***************************************************************/
    // All remaining functions are maintained for use with XMB.


    public function ValidateCode($sUserCode, $imghash)
    {
        if ($this->bPoison) {
            return false;
        }

        $this->bPoison = true;

        if (strlen($sUserCode) != $this->iNumChars || $imghash == 'test') {
            return false;
        }

        if ($this->bCaseInsensitive) {
            $sUserCode = strtoupper($sUserCode);
        }

        return $this->core->nonce_use($sUserCode, $imghash);
    }

    private function SetNumDots($iNumDots)
    {
        $this->iNumDots = $iNumDots;
    }

    private function SetFonts($vFonts)
    {
        // override any pre-defined file path
        putenv('GDFONTPATH=' . realpath(ROOT));

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
        $this->aFonts = [];

        // loop through items
        foreach($aFonts as $sCurrentItem) {
            if (is_dir(ROOT . $sCurrentItem)) {
                $dir = opendir(ROOT . $sCurrentItem);
                while($file = readdir($dir)) {
                    if (false !== strpos($file, '.ttf')) {
                        $this->aFonts[] = ROOT . $sCurrentItem . '/' . $file;
                    }
                }
            } else {
                if (is_file(ROOT . $sCurrentItem) && false !== strpos($sCurrentItem, '.ttf')) {
                    $this->aFonts[] = $sCurrentItem;
                } else if (is_file(ROOT . $sCurrentItem . '.ttf')) {
                    $this->aFonts[] = ROOT . $sCurrentItem . '.ttf';
                } else if (is_file(ROOT . 'fonts/' . $sCurrentItem) && false !== strpos($sCurrentItem, '.ttf')) {
                    $this->aFonts[] = ROOT . 'fonts/' . $sCurrentItem;
                } else if (is_file(ROOT . 'fonts/' . $sCurrentItem . '.ttf')) {
                    $this->aFonts[] = ROOT . 'fonts/' . $sCurrentItem . '.ttf';
                }
            }
        }
    }

    private function SetBackgroundImages($vBackgroundImages)
    {
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
            if (is_dir(ROOT . $sCurrentItem)) {
                $dir = opendir(ROOT . $sCurrentItem);
                while($file = readdir($dir)) {
                    if (false !== strpos($file, '.png')) {
                        $this->aBackgroundImages[] = ROOT . $sCurrentItem . '/' . $file;
                    } else if (false !== strpos($file, '.gif')) {
                        $this->aBackgroundImages[] = ROOT . $sCurrentItem . '/' . $file;
                    } else if (false !== strpos($file, '.jpg')) {
                        $this->aBackgroundImages[] = ROOT . $sCurrentItem . '/' . $file;
                    } else if (false !== strpos($file, '.jpeg')) {
                        $this->aBackgroundImages[] = ROOT . $sCurrentItem . '/' . $file;
                    }
                }
            } elseif (is_file(ROOT . $sCurrentItem)) {
                if (false !== strpos($sCurrentItem, '.png')) {
                    $this->aBackgroundImages[] = ROOT . $sCurrentItem;
                } elseif (false !== strpos($sCurrentItem, '.gif')) {
                    $this->aBackgroundImages[] = ROOT . $sCurrentItem;
                } elseif (false !== strpos($sCurrentItem, '.jpg')) {
                    $this->aBackgroundImages[] = ROOT . $sCurrentItem;
                } elseif (false !== strpos($sCurrentItem, '.jpeg')) {
                    $this->aBackgroundImages[] = ROOT . $sCurrentItem;
                }
            }
        }
    }

    private function DrawDots($colors)
    {
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

    private function RetrieveCode($imghash)
    {
        if ($imghash == 'test') {
            $this->bPoison = true;
            $this->sCode = 'CaPtChA';
        } else {
            $this->sCode = $this->core->nonce_peek($imghash, $this->iNumChars);
        }
    }

    private function CheckCompatibility()
    {
        // check for required gd functions
        if (!function_exists('imagecreate')
            || !function_exists("image$this->sFileType")
            || !function_exists('imagecreatetruecolor')
            || !function_exists('imageftbbox')
            || !function_exists('imagefttext')
            || empty($this->aFonts)
        ) {
            $this->bCompatible = false;
            $this->bPoison = true;
            return false;
        } else {
            $this->bCompatible = true;
            return true;
        }
    }
}
