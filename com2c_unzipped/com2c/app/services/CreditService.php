<?php
class CreditService {
    public function hold(string $ownerType, int $ownerId, int $amount, ?int $sessionId = null): bool {
        // TODO: write credit_ledger hold
        return true;
    }

    public function deduct(string $ownerType, int $ownerId, int $amount, ?int $sessionId = null): bool {
        // TODO: write credit_ledger deduct
        return true;
    }

    public function release(string $ownerType, int $ownerId, int $amount, ?int $sessionId = null): bool {
        // TODO: write credit_ledger release
        return true;
    }
}
