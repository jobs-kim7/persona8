<?php
class SponsorService {
    public function lockSponsorForSession(array $cardSponsorSettings): ?int {
        // TODO: self / partner / mixed sponsor logic
        return $cardSponsorSettings['default_sponsor_id'] ?? null;
    }
}
