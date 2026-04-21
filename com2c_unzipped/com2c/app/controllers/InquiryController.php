<?php
class InquiryController extends Controller
{
    public function index(): void
    {
        $db = DB::conn();
        $userId = 1;

        $stmt = $db->prepare("
            SELECT
                i.*,
                c.title AS card_title,
                c.name AS card_name
            FROM inquiries i
            INNER JOIN cards c ON i.card_id = c.id
            WHERE c.user_id = :user_id
            ORDER BY i.created_at DESC, i.id DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        $items = $stmt->fetchAll();

        $this->view('inquiries/index', [
            'items' => $items,
        ]);
    }

    public function show(): void
    {
        $db = DB::conn();
        $inquiryId = (int)($_GET['id'] ?? 0);

        if ($inquiryId <= 0) {
            http_response_code(400);
            echo '유효하지 않은 문의 ID입니다.';
            return;
        }

        $stmt = $db->prepare("
            SELECT i.*, c.title AS card_title, c.name AS card_name
            FROM inquiries i
            INNER JOIN cards c ON i.card_id = c.id
            WHERE i.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $inquiryId]);
        $inquiry = $stmt->fetch();

        if (!$inquiry) {
            http_response_code(404);
            echo '문의를 찾을 수 없습니다.';
            return;
        }

        $messages = $this->loadMessagesWithAttachments($inquiryId);

        $this->view('inquiries/show', [
            'inquiry' => $inquiry,
            'messages' => $messages,
        ]);
    }

    public function store(): void
    {
        $db = DB::conn();

        $cardId = (int)($_POST['card_id'] ?? 0);
        $visitorName = trim($_POST['visitor_name'] ?? '');
        $visitorEmail = trim($_POST['visitor_email'] ?? '');
        $inquiryType = trim($_POST['inquiry_type'] ?? 'general');
        $message = trim($_POST['message'] ?? '');

        if ($cardId <= 0) {
            http_response_code(400);
            echo '유효하지 않은 카드입니다.';
            return;
        }

        if ($visitorName === '' || $message === '') {
            http_response_code(422);
            echo '이름과 문의 내용은 필수입니다.';
            return;
        }

        $cardStmt = $db->prepare("SELECT id, title, name FROM cards WHERE id = :id LIMIT 1");
        $cardStmt->execute([':id' => $cardId]);
        $card = $cardStmt->fetch();

        if (!$card) {
            http_response_code(404);
            echo '카드를 찾을 수 없습니다.';
            return;
        }

        $allowedTypes = ['collab', 'consulting', 'recruiting', 'booking', 'general'];
        if (!in_array($inquiryType, $allowedTypes, true)) {
            $inquiryType = 'general';
        }

        $summaryService = new InquirySummaryService();
        $summaryResult = $summaryService->summarize([
            'visitor_name' => $visitorName,
            'visitor_email' => $visitorEmail,
            'inquiry_type' => $inquiryType,
            'message' => $message,
        ]);

        $stmt = $db->prepare("
            INSERT INTO inquiries (
                session_id,
                card_id,
                inquiry_type,
                visitor_name,
                visitor_email,
                raw_message,
                summary_text,
                fit_level,
                ai_recommendation,
                status,
                created_at,
                updated_at
            ) VALUES (
                :session_id,
                :card_id,
                :inquiry_type,
                :visitor_name,
                :visitor_email,
                :raw_message,
                :summary_text,
                :fit_level,
                :ai_recommendation,
                'new',
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            ':session_id' => 0,
            ':card_id' => $cardId,
            ':inquiry_type' => $inquiryType,
            ':visitor_name' => $visitorName,
            ':visitor_email' => ($visitorEmail !== '' ? $visitorEmail : null),
            ':raw_message' => $message,
            ':summary_text' => $summaryResult['summary_text'],
            ':fit_level' => $summaryResult['fit_level'],
            ':ai_recommendation' => $summaryResult['ai_recommendation'],
        ]);

        $inquiryId = (int)$db->lastInsertId();

        // 문의자 첫 메시지
        $msgStmt = $db->prepare("
            INSERT INTO inquiry_messages (
                inquiry_id,
                sender_type,
                visibility,
                message_text,
                is_internal,
                created_at,
                updated_at
            ) VALUES (
                :inquiry_id,
                'visitor',
                'public',
                :message_text,
                0,
                NOW(),
                NOW()
            )
        ");
        $msgStmt->execute([
            ':inquiry_id' => $inquiryId,
            ':message_text' => $message,
        ]);

        // AI 요약 / 제안은 운영자 전용
        if (!empty($summaryResult['summary_text'])) {
            $aiSummaryStmt = $db->prepare("
                INSERT INTO inquiry_messages (
                    inquiry_id,
                    sender_type,
                    visibility,
                    message_text,
                    is_internal,
                    created_at,
                    updated_at
                ) VALUES (
                    :inquiry_id,
                    'ai',
                    'owner_only',
                    :message_text,
                    1,
                    NOW(),
                    NOW()
                )
            ");
            $aiSummaryStmt->execute([
                ':inquiry_id' => $inquiryId,
                ':message_text' => $summaryResult['summary_text'],
            ]);
        }

        if (!empty($summaryResult['ai_recommendation'])) {
            $aiRecoStmt = $db->prepare("
                INSERT INTO inquiry_messages (
                    inquiry_id,
                    sender_type,
                    visibility,
                    message_text,
                    is_internal,
                    created_at,
                    updated_at
                ) VALUES (
                    :inquiry_id,
                    'ai',
                    'owner_only',
                    :message_text,
                    1,
                    NOW(),
                    NOW()
                )
            ");
            $aiRecoStmt->execute([
                ':inquiry_id' => $inquiryId,
                ':message_text' => '[AI 제안] ' . $summaryResult['ai_recommendation'],
            ]);
        }

        // 시스템 안내 메시지 (문의자도 보게)
        $systemStmt = $db->prepare("
            INSERT INTO inquiry_messages (
                inquiry_id,
                sender_type,
                visibility,
                message_text,
                is_internal,
                created_at,
                updated_at
            ) VALUES (
                :inquiry_id,
                'system',
                'system',
                :message_text,
                0,
                NOW(),
                NOW()
            )
        ");
        $systemStmt->execute([
            ':inquiry_id' => $inquiryId,
            ':message_text' => '문의가 접수되었습니다. 필요하면 아래에서 사진이나 자료를 바로 추가할 수 있습니다.',
        ]);

        // 공개카드 첫 문의는 첨부 없이 먼저 접수 → 즉시 내 문의 상세로 이동
        $redirectEmail = $visitorEmail !== '' ? urlencode($visitorEmail) : '';
        $this->redirect('/my/inquiries/show?id=' . $inquiryId . '&email=' . $redirectEmail . '&welcome=1');
    }

    public function reply(): void
    {
        $db = DB::conn();

        $inquiryId = (int)($_POST['inquiry_id'] ?? 0);
        $visibility = trim($_POST['visibility'] ?? 'public');
        $messageText = trim($_POST['message_text'] ?? '');

        if ($inquiryId <= 0) {
            http_response_code(400);
            echo '유효하지 않은 문의 ID입니다.';
            return;
        }

        if ($messageText === '' && empty($_FILES['reply_attachments'])) {
            http_response_code(422);
            echo '답변 내용이나 첨부파일 중 하나는 필요합니다.';
            return;
        }

        $allowedVisibility = ['public', 'owner_only'];
        if (!in_array($visibility, $allowedVisibility, true)) {
            $visibility = 'public';
        }

        $stmt = $db->prepare("
            INSERT INTO inquiry_messages (
                inquiry_id,
                sender_type,
                visibility,
                message_text,
                is_internal,
                created_at,
                updated_at
            ) VALUES (
                :inquiry_id,
                'owner',
                :visibility,
                :message_text,
                :is_internal,
                NOW(),
                NOW()
            )
        ");
        $stmt->execute([
            ':inquiry_id' => $inquiryId,
            ':visibility' => $visibility,
            ':message_text' => $messageText,
            ':is_internal' => $visibility === 'owner_only' ? 1 : 0,
        ]);

        $messageId = (int)$db->lastInsertId();
        $this->saveReplyAttachments($messageId, $_FILES['reply_attachments'] ?? null);

        $systemStmt = $db->prepare("
            INSERT INTO inquiry_messages (
                inquiry_id,
                sender_type,
                visibility,
                message_text,
                is_internal,
                created_at,
                updated_at
            ) VALUES (
                :inquiry_id,
                'system',
                'system',
                :message_text,
                0,
                NOW(),
                NOW()
            )
        ");
        $systemStmt->execute([
            ':inquiry_id' => $inquiryId,
            ':message_text' => '운영자가 답변을 남겼습니다.',
        ]);

        $this->redirect('/inquiries/show?id=' . $inquiryId . '&saved=1');
    }

    public function myIndex(): void
    {
        $db = DB::conn();
        $email = trim($_GET['email'] ?? '');

        $items = [];

        if ($email !== '') {
            $stmt = $db->prepare("
                SELECT i.*, c.title AS card_title
                FROM inquiries i
                INNER JOIN cards c ON i.card_id = c.id
                WHERE i.visitor_email = :email
                ORDER BY i.created_at DESC, i.id DESC
            ");
            $stmt->execute([':email' => $email]);
            $items = $stmt->fetchAll();
        }

        $this->view('my/inquiries/index', [
            'email' => $email,
            'items' => $items,
        ]);
    }

    public function myShow(): void
    {
        $db = DB::conn();
        $inquiryId = (int)($_GET['id'] ?? 0);
        $email = trim($_GET['email'] ?? '');

        if ($inquiryId <= 0 || $email === '') {
            http_response_code(400);
            echo '유효하지 않은 접근입니다.';
            return;
        }

        $stmt = $db->prepare("
            SELECT *
            FROM inquiries
            WHERE id = :id AND visitor_email = :email
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $inquiryId,
            ':email' => $email,
        ]);
        $inquiry = $stmt->fetch();

        if (!$inquiry) {
            http_response_code(404);
            echo '문의 정보를 찾을 수 없습니다.';
            return;
        }

        $messagesStmt = $db->prepare("
            SELECT *
            FROM inquiry_messages
            WHERE inquiry_id = :inquiry_id
              AND visibility IN ('public', 'system', 'visitor_only')
            ORDER BY created_at ASC, id ASC
        ");
        $messagesStmt->execute([':inquiry_id' => $inquiryId]);
        $messages = $messagesStmt->fetchAll();

        foreach ($messages as &$message) {
            $attachStmt = $db->prepare("
                SELECT *
                FROM inquiry_message_attachments
                WHERE inquiry_message_id = :message_id
                ORDER BY id ASC
            ");
            $attachStmt->execute([':message_id' => $message['id']]);
            $message['attachments'] = $attachStmt->fetchAll();
        }
        unset($message);

        $this->view('my/inquiries/show', [
            'email' => $email,
            'inquiry' => $inquiry,
            'messages' => $messages,
        ]);
    }

    public function myReply(): void
    {
        $db = DB::conn();

        $inquiryId = (int)($_POST['inquiry_id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $messageText = trim($_POST['message_text'] ?? '');

        if ($inquiryId <= 0 || $email === '') {
            http_response_code(400);
            echo '유효하지 않은 접근입니다.';
            return;
        }

        if ($messageText === '' && empty($_FILES['my_attachment'])) {
            http_response_code(422);
            echo '추가 메시지나 첨부파일 중 하나는 필요합니다.';
            return;
        }

        $stmt = $db->prepare("
            SELECT id
            FROM inquiries
            WHERE id = :id AND visitor_email = :email
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $inquiryId,
            ':email' => $email,
        ]);
        $inquiry = $stmt->fetch();

        if (!$inquiry) {
            http_response_code(404);
            echo '문의 정보를 찾을 수 없습니다.';
            return;
        }

        $msgStmt = $db->prepare("
            INSERT INTO inquiry_messages (
                inquiry_id,
                sender_type,
                visibility,
                message_text,
                is_internal,
                created_at,
                updated_at
            ) VALUES (
                :inquiry_id,
                'visitor',
                'public',
                :message_text,
                0,
                NOW(),
                NOW()
            )
        ");
        $msgStmt->execute([
            ':inquiry_id' => $inquiryId,
            ':message_text' => $messageText,
        ]);

        $messageId = (int)$db->lastInsertId();

        // 문의자 추가 자료 업로드: 단일 파일 1개
        if (!empty($_FILES['my_attachment']) && ($_FILES['my_attachment']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = $this->handleMessageAttachmentUpload($messageId, $_FILES['my_attachment']);
            if ($upload['ok']) {
                $attachStmt = $db->prepare("
                    INSERT INTO inquiry_message_attachments (
                        inquiry_message_id,
                        file_name,
                        file_path,
                        mime_type,
                        file_size,
                        created_at
                    ) VALUES (
                        :inquiry_message_id,
                        :file_name,
                        :file_path,
                        :mime_type,
                        :file_size,
                        NOW()
                    )
                ");
                $attachStmt->execute([
                    ':inquiry_message_id' => $messageId,
                    ':file_name' => $upload['file_name'],
                    ':file_path' => $upload['file_path'],
                    ':mime_type' => $upload['mime_type'],
                    ':file_size' => $upload['file_size'],
                ]);
            }
        }

        $systemStmt = $db->prepare("
            INSERT INTO inquiry_messages (
                inquiry_id,
                sender_type,
                visibility,
                message_text,
                is_internal,
                created_at,
                updated_at
            ) VALUES (
                :inquiry_id,
                'system',
                'system',
                :message_text,
                0,
                NOW(),
                NOW()
            )
        ");
        $systemStmt->execute([
            ':inquiry_id' => $inquiryId,
            ':message_text' => '문의자가 추가 자료를 보냈습니다.',
        ]);

        $this->redirect('/my/inquiries/show?id=' . $inquiryId . '&email=' . urlencode($email) . '&saved=1');
    }

    private function saveReplyAttachments(int $messageId, ?array $files): void
    {
        if (empty($files)) {
            return;
        }

        $db = DB::conn();
        $normalized = $this->normalizeUploadedFiles($files);

        if (count($normalized) > 2) {
            http_response_code(422);
            echo '운영자 답변 첨부는 최대 2개까지 가능합니다.';
            exit;
        }

        foreach ($normalized as $file) {
            $upload = $this->handleMessageAttachmentUpload($messageId, $file);
            if ($upload['ok']) {
                $attachStmt = $db->prepare("
                    INSERT INTO inquiry_message_attachments (
                        inquiry_message_id,
                        file_name,
                        file_path,
                        mime_type,
                        file_size,
                        created_at
                    ) VALUES (
                        :inquiry_message_id,
                        :file_name,
                        :file_path,
                        :mime_type,
                        :file_size,
                        NOW()
                    )
                ");
                $attachStmt->execute([
                    ':inquiry_message_id' => $messageId,
                    ':file_name' => $upload['file_name'],
                    ':file_path' => $upload['file_path'],
                    ':mime_type' => $upload['mime_type'],
                    ':file_size' => $upload['file_size'],
                ]);
            }
        }
    }

    private function loadMessagesWithAttachments(int $inquiryId): array
    {
        $db = DB::conn();

        $messagesStmt = $db->prepare("
            SELECT *
            FROM inquiry_messages
            WHERE inquiry_id = :inquiry_id
            ORDER BY created_at ASC, id ASC
        ");
        $messagesStmt->execute([':inquiry_id' => $inquiryId]);
        $messages = $messagesStmt->fetchAll();

        foreach ($messages as &$message) {
            $attachStmt = $db->prepare("
                SELECT *
                FROM inquiry_message_attachments
                WHERE inquiry_message_id = :message_id
                ORDER BY id ASC
            ");
            $attachStmt->execute([':message_id' => $message['id']]);
            $message['attachments'] = $attachStmt->fetchAll();
        }
        unset($message);

        return $messages;
    }

    private function normalizeUploadedFiles(array $files): array
    {
        $normalized = [];

        if (!is_array($files['name'] ?? null)) {
            if (($files['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $normalized[] = $files;
            }
            return $normalized;
        }

        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $normalized[] = [
                'name' => $files['name'][$i] ?? '',
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$i] ?? 0,
            ];
        }

        return $normalized;
    }

    private function handleMessageAttachmentUpload(int $messageId, array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => '파일 업로드에 실패했습니다.'];
        }

        $originalName = $file['name'] ?? 'file';
        $tmpPath = $file['tmp_name'] ?? '';
        $fileSize = (int)($file['size'] ?? 0);

        if (!is_uploaded_file($tmpPath)) {
            return ['ok' => false, 'message' => '유효하지 않은 업로드 파일입니다.'];
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed, true)) {
            return ['ok' => false, 'message' => '이미지 파일만 업로드할 수 있습니다.'];
        }

        if ($fileSize > 8 * 1024 * 1024) {
            return ['ok' => false, 'message' => '이미지 파일은 8MB 이하만 가능합니다.'];
        }

        $targetDir = BASE_PATH . '/uploads/inquiries/messages/' . $messageId;

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                return ['ok' => false, 'message' => '업로드 폴더를 생성할 수 없습니다.'];
            }
        }

        @chmod(BASE_PATH . '/uploads', 0755);
        @chmod(BASE_PATH . '/uploads/inquiries', 0755);
        @chmod(BASE_PATH . '/uploads/inquiries/messages', 0755);
        @chmod($targetDir, 0755);

        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME));
        if ($safeBase === '') {
            $safeBase = 'attachment';
        }

        $newFileName = $safeBase . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $targetDir . '/' . $newFileName;

        if (!move_uploaded_file($tmpPath, $targetPath)) {
            return ['ok' => false, 'message' => '파일 저장에 실패했습니다.'];
        }

        @chmod($targetPath, 0644);

        return [
            'ok' => true,
            'file_name' => $originalName,
            'file_path' => '/uploads/inquiries/messages/' . $messageId . '/' . $newFileName,
            'mime_type' => $file['type'] ?? null,
            'file_size' => $fileSize,
        ];
    }
}
