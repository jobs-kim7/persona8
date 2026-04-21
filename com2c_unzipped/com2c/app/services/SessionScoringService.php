<?php
class SessionScoringService {
    public function isValidConversation(array $messages, bool $abnormalFlag = false): bool {
        if ($abnormalFlag) {
            return false;
        }
        $meaningful = 0;
        $assistant = 0;
        foreach ($messages as $message) {
            if (($message['sender_type'] ?? '') === 'visitor' && !empty($message['is_meaningful_input'])) {
                $meaningful++;
            }
            if (($message['sender_type'] ?? '') === 'assistant') {
                $assistant++;
            }
        }
        return $meaningful >= 1 && $assistant >= 2;
    }
}
