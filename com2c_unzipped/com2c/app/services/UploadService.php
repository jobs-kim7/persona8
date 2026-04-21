<?php
class UploadService {
    public function save(array $file, string $dir): string {
        // TODO: sanitize, move uploaded file
        return '/uploads/' . basename($file['name'] ?? 'placeholder.png');
    }
}
