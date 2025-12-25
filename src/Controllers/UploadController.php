<?php

declare(strict_types=1);

namespace WikiApp\Controllers;

use WikiApp\Lib\Session;
use WikiApp\Lib\Utils;

class UploadController
{
    /**
     * Handle image upload.
     */
    public function upload(): void
    {
        header('Content-Type: application/json');

        // Check if user is logged in
        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }

        // Check if file was uploaded
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = isset($_FILES['image']) ? $this->getUploadError($_FILES['image']['error']) : 'No file uploaded';
            echo json_encode(['success' => false, 'error' => $error]);
            return;
        }

        $file = $_FILES['image'];

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: JPEG, PNG, GIF, WebP, SVG']);
            return;
        }

        // Validate file size (max 10MB)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 10MB']);
            return;
        }

        // Generate unique filename
        $extension = $this->getExtensionFromMime($mimeType);
        $filename = uniqid('img_') . '_' . time() . '.' . $extension;

        // Create year/month subdirectory
        $subdir = date('Y/m');
        $uploadDir = __DIR__ . '/../../uploads/' . $subdir;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadPath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $url = Utils::url('/uploads/' . $subdir . '/' . $filename);
            echo json_encode(['success' => true, 'url' => $url]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save file']);
        }
    }

    /**
     * Get human-readable upload error message.
     */
    private function getUploadError(int $errorCode): string
    {
        return match($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by extension',
            default => 'Unknown upload error',
        };
    }

    /**
     * Get file extension from MIME type.
     */
    private function getExtensionFromMime(string $mimeType): string
    {
        return match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => 'bin',
        };
    }
}
