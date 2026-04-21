<?php
class ChatController extends Controller {
    public function send(): void {
        // TODO: start session, lock sponsor, call AI, store messages
        echo json_encode(['ok' => true]);
    }
}
