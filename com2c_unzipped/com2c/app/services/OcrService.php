<?php
class OcrService {
    public function parseBusinessCard(string $path): array {
        // TODO: integrate OCR or external extraction
        return [
            'name' => '',
            'company_name' => '',
            'email' => '',
            'phone' => '',
        ];
    }
}
