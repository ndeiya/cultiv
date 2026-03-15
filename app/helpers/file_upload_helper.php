<?php
/**
 * File Upload Helper
 * Handles safe file uploads with validation.
 */

/**
 * Upload an image file with validation.
 *
 * @param array  $file         The $_FILES['field'] array
 * @param string $destination  Subdirectory under UPLOAD_PATH (e.g., 'reports', 'profiles')
 * @return array               ['success' => bool, 'filename' => string|null, 'error' => string|null]
 */
function upload_image(array $file, string $destination = ''): array
{
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds the server upload limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form upload limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        ];

        return [
            'success'  => false,
            'filename' => null,
            'error'    => $errorMessages[$file['error']] ?? 'Unknown upload error.',
        ];
    }

    // Validate file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $maxMB = MAX_UPLOAD_SIZE / (1024 * 1024);
        return [
            'success'  => false,
            'filename' => null,
            'error'    => "File size exceeds the maximum allowed ({$maxMB} MB).",
        ];
    }

    // Validate MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
        return [
            'success'  => false,
            'filename' => null,
            'error'    => 'Invalid file type. Allowed: JPEG, PNG, WebP, GIF.',
        ];
    }

    // Build the destination directory
    $uploadDir = UPLOAD_PATH;
    if ($destination) {
        $uploadDir .= '/' . trim($destination, '/');
    }

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate a unique filename
    $extension = match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'jpg',
    };

    $filename = uniqid('img_', true) . '.' . $extension;
    $fullPath = $uploadDir . '/' . $filename;

    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
        return [
            'success'  => false,
            'filename' => null,
            'error'    => 'Failed to save the uploaded file.',
        ];
    }

    // Attempt to compress the image for performance
    compress_image($fullPath, $mimeType);

    // Return the relative path from UPLOAD_PATH
    $relativePath = ($destination ? $destination . '/' : '') . $filename;

    return [
        'success'  => true,
        'filename' => $relativePath,
        'error'    => null,
    ];
}

/**
 * Compress an image file using GD.
 */
function compress_image(string $path, string $mimeType): void
{
    if (!extension_loaded('gd')) {
        return;
    }

    try {
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($path);
                if ($image) {
                    imagejpeg($image, $path, 80); // 80% quality
                    imagedestroy($image);
                }
                break;

            case 'image/png':
                $image = imagecreatefrompng($path);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                    imagepng($image, $path, 6); // Level 6 compression
                    imagedestroy($image);
                }
                break;
                
            case 'image/webp':
                $image = imagecreatefromwebp($path);
                if ($image) {
                    imagewebp($image, $path, 80);
                    imagedestroy($image);
                }
                break;
        }
    } catch (Throwable $e) {
        // Log error or ignore if compression fails
        error_log("Image compression failed for {$path}: " . $e->getMessage());
    }
}
