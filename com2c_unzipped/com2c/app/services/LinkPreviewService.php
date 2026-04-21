<?php
class LinkPreviewService {
    public function fetch(string $url): array {
        // TODO: fetch og:title / og:image
        return [
            'title' => $url,
            'description' => '',
            'thumbnail_url' => '',
        ];
    }
}
