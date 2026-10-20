<?php
/**
 * Poster upload helper (Practical 12).
 * Checks: upload error code, size, real MIME type (finfo), real image (getimagesize), extension
 * whitelist.  The file is stored under a random name - the client's file name is never used.
 */
const POSTER_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
const POSTER_DIR   = __DIR__ . '/../Practical-12/uploads/';

/** @return array{0:?string,1:?string} [stored file name, error message] */
function save_poster(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [null, null];   // optional
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) return [null, 'The poster is larger than 2 MB.'];
    if ($file['error'] !== UPLOAD_ERR_OK) return [null, 'The upload failed (error ' . (int)$file['error'] . '). Please try again.'];
    if ($file['size'] > POSTER_MAX_BYTES) return [null, 'The poster is larger than 2 MB.'];
    if (!is_uploaded_file($file['tmp_name'])) return [null, 'Invalid upload.'];

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(POSTER_TYPES[$mime])) return [null, 'Only JPG, PNG or WEBP images are allowed.'];
    if (@getimagesize($file['tmp_name']) === false) return [null, 'The file is not a valid image.'];

    $name = bin2hex(random_bytes(12)) . '.' . POSTER_TYPES[$mime];     // random name, extension from real MIME
    if (!move_uploaded_file($file['tmp_name'], POSTER_DIR . $name)) return [null, 'The poster could not be stored.'];
    return [$name, null];
}

function delete_poster(?string $name): void
{
    if ($name && preg_match('/^[a-f0-9]{24}\.(jpg|png|webp)$/', $name)) {     // only files we created
        @unlink(POSTER_DIR . $name);
    }
}

/** URL of a poster (seeded events have none). */
function poster_url(?string $name): ?string
{
    return ($name && preg_match('/^[A-Za-z0-9._-]+\.(jpg|png|webp)$/', $name)) ? '../Practical-12/uploads/' . $name : null;
}
