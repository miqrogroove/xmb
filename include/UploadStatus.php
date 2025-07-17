<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-1
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

enum UploadStatus
{
    case Success;         // Formerly any int >= 0
    case BadStoragePath;  // Formerly X_BAD_STORAGE_PATH
    case CountExceeded;   // Formerly X_ATTACH_COUNT_EXCEEDED
    case DimsExceeded;    // Formerly X_IMAGE_DIMS_EXCEEDED
    case EmptyUpload;     // Formerly X_EMPTY_UPLOAD
    case GenericError;    // Formerly X_GENERIC_ATTACH_ERROR
    case InvalidFilename; // Formerly X_INVALID_FILENAME
    case InvalidURL;      // Formerly X_INVALID_REMOTE_LINK
    case NotAnImage;      // Formerly X_NOT_AN_IMAGE
    case NoTempFile;      // Formerly X_NO_TEMP_FILE
    case SizeExceeded;    // Formerly X_ATTACH_SIZE_EXCEEDED
}
