<?php
class CardController extends Controller
{
    public function create(): void
    {
        $this->view('cards/create');
    }

    public function store(): void
    {
        $db = DB::conn();
        $userId = 1;

        $title = trim($_POST['title'] ?? '');
        $purpose = trim($_POST['purpose'] ?? 'basic');
        $name = trim($_POST['name'] ?? '');
        $oneLiner = trim($_POST['one_liner'] ?? '');
        $roleTitle = trim($_POST['role_title'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $status = trim($_POST['status'] ?? 'draft');

        if ($title === '' || $name === '') {
            http_response_code(422);
            echo 'title 과 name 은 필수입니다.';
            return;
        }

        $allowedPurposes = ['basic', 'networking', 'collab', 'portfolio', 'recruiting', 'soho'];
        if (!in_array($purpose, $allowedPurposes, true)) {
            $purpose = 'basic';
        }

        $allowedStatuses = ['draft', 'published', 'archived'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'draft';
        }

        $baseSlug = $this->slugify($title !== '' ? $title : $name);
        $slug = $this->generateUniqueSlug($db, $userId, $baseSlug);

        $stmt = $db->prepare("
            INSERT INTO cards (
                user_id, slug, title, purpose, name, one_liner, role_title,
                company_name, email, phone, bio, status, created_at, updated_at
            ) VALUES (
                :user_id, :slug, :title, :purpose, :name, :one_liner, :role_title,
                :company_name, :email, :phone, :bio, :status, NOW(), NOW()
            )
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':slug' => $slug,
            ':title' => $title,
            ':purpose' => $purpose,
            ':name' => $name,
            ':one_liner' => $oneLiner !== '' ? $oneLiner : null,
            ':role_title' => $roleTitle !== '' ? $roleTitle : null,
            ':company_name' => $companyName !== '' ? $companyName : null,
            ':email' => $email !== '' ? $email : null,
            ':phone' => $phone !== '' ? $phone : null,
            ':bio' => $bio !== '' ? $bio : null,
            ':status' => $status,
        ]);

        $cardId = (int)$db->lastInsertId();

        $aiStmt = $db->prepare("
            INSERT INTO card_ai_settings (
                card_id, ai_role, greeting_message, quick_actions, tone,
                revisit_greeting_enabled, abnormal_access_fallback, model_name,
                created_at, updated_at
            ) VALUES (
                :card_id, 'guide', :greeting_message, :quick_actions, 'business',
                1, 'email_only', 'gpt-4o-mini', NOW(), NOW()
            )
        ");

        $aiStmt->execute([
            ':card_id' => $cardId,
            ':greeting_message' => '안녕하세요. 어떤 목적으로 오셨는지 알려주시면 가장 적절한 연결 방식을 안내해드릴게요.',
            ':quick_actions' => json_encode(['협업 문의', '상담 요청', '채용 제안', '포트폴리오 보기'], JSON_UNESCAPED_UNICODE),
        ]);

        $this->redirect('/cards/edit?id=' . $cardId);
    }

    public function edit(): void
    {
        $db = DB::conn();

        $cardId = (int)($_GET['id'] ?? 0);
        if ($cardId <= 0) {
            http_response_code(400);
            echo '유효하지 않은 카드 ID입니다.';
            return;
        }

        $stmt = $db->prepare("SELECT * FROM cards WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $cardId]);
        $card = $stmt->fetch();

        if (!$card) {
            http_response_code(404);
            echo '카드를 찾을 수 없습니다.';
            return;
        }

        $aiStmt = $db->prepare("SELECT * FROM card_ai_settings WHERE card_id = :card_id LIMIT 1");
        $aiStmt->execute([':card_id' => $cardId]);
        $ai = $aiStmt->fetch();

        $linksStmt = $db->prepare("
            SELECT *
            FROM card_links
            WHERE card_id = :card_id
            ORDER BY sort_order ASC, id ASC
        ");
        $linksStmt->execute([':card_id' => $cardId]);
        $links = $linksStmt->fetchAll();

        $this->view('cards/edit', [
            'card' => $card,
            'ai' => $ai,
            'links' => $links,
        ]);
    }

    public function update(): void
    {
        $db = DB::conn();

        $cardId = (int)($_POST['id'] ?? 0);
        if ($cardId <= 0) {
            http_response_code(400);
            echo '유효하지 않은 카드 ID입니다.';
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $oneLiner = trim($_POST['one_liner'] ?? '');

        if ($title === '' || $name === '') {
            http_response_code(422);
            echo 'title 과 name 은 필수입니다.';
            return;
        }

        $purpose = trim($_POST['purpose'] ?? 'basic');
        $roleTitle = trim($_POST['role_title'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $status = trim($_POST['status'] ?? 'draft');

        $allowedPurposes = ['basic', 'networking', 'collab', 'portfolio', 'recruiting', 'soho'];
        if (!in_array($purpose, $allowedPurposes, true)) {
            $purpose = 'basic';
        }

        $allowedStatuses = ['draft', 'published', 'archived'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'draft';
        }

        // 1. cards 업데이트
        $stmt = $db->prepare("
            UPDATE cards
            SET
                title = :title,
                purpose = :purpose,
                name = :name,
                one_liner = :one_liner,
                role_title = :role_title,
                company_name = :company_name,
                email = :email,
                phone = :phone,
                bio = :bio,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $cardId,
            ':title' => $title,
            ':purpose' => $purpose,
            ':name' => $name,
            ':one_liner' => $oneLiner !== '' ? $oneLiner : null,
            ':role_title' => $roleTitle !== '' ? $roleTitle : null,
            ':company_name' => $companyName !== '' ? $companyName : null,
            ':email' => $email !== '' ? $email : null,
            ':phone' => $phone !== '' ? $phone : null,
            ':bio' => $bio !== '' ? $bio : null,
            ':status' => $status,
        ]);

        // 2. AI 설정값 정리
        $greetingMessage = trim($_POST['greeting_message'] ?? '');
        $aiRole = trim($_POST['ai_role'] ?? 'guide');
        $tone = trim($_POST['tone'] ?? 'business');

        $allowedRoles = ['guide', 'matchmaker', 'recruiting', 'soho_helper'];
        if (!in_array($aiRole, $allowedRoles, true)) {
            $aiRole = 'guide';
        }

        $allowedTones = ['business', 'warm', 'practical'];
        if (!in_array($tone, $allowedTones, true)) {
            $tone = 'business';
        }

        $quickActions = [
            trim($_POST['quick_action_1'] ?? ''),
            trim($_POST['quick_action_2'] ?? ''),
            trim($_POST['quick_action_3'] ?? ''),
            trim($_POST['quick_action_4'] ?? ''),
        ];
        $quickActions = array_values(array_filter($quickActions, fn($v) => $v !== ''));
        $quickActionsJson = json_encode($quickActions, JSON_UNESCAPED_UNICODE);

        // 3. card_ai_settings 존재 여부 확인
        $aiCheck = $db->prepare("SELECT id FROM card_ai_settings WHERE card_id = :card_id LIMIT 1");
        $aiCheck->execute([':card_id' => $cardId]);
        $aiRow = $aiCheck->fetch();

        if ($aiRow) {
            $aiUpdate = $db->prepare("
                UPDATE card_ai_settings
                SET
                    ai_role = :ai_role,
                    greeting_message = :greeting_message,
                    quick_actions = :quick_actions,
                    tone = :tone,
                    updated_at = NOW()
                WHERE card_id = :card_id
            ");

            $aiUpdate->execute([
                ':card_id' => $cardId,
                ':ai_role' => $aiRole,
                ':greeting_message' => ($greetingMessage !== '' ? $greetingMessage : null),
                ':quick_actions' => $quickActionsJson,
                ':tone' => $tone,
            ]);
        } else {
            $aiInsert = $db->prepare("
                INSERT INTO card_ai_settings (
                    card_id,
                    ai_role,
                    greeting_message,
                    quick_actions,
                    tone,
                    revisit_greeting_enabled,
                    abnormal_access_fallback,
                    model_name,
                    created_at,
                    updated_at
                ) VALUES (
                    :card_id,
                    :ai_role,
                    :greeting_message,
                    :quick_actions,
                    :tone,
                    1,
                    'email_only',
                    'gpt-4o-mini',
                    NOW(),
                    NOW()
                )
            ");

            $aiInsert->execute([
                ':card_id' => $cardId,
                ':ai_role' => $aiRole,
                ':greeting_message' => ($greetingMessage !== '' ? $greetingMessage : null),
                ':quick_actions' => $quickActionsJson,
                ':tone' => $tone,
            ]);
        }

        // 4. 새 링크 추가
        $newLinkType = trim($_POST['new_link_type'] ?? '');
        $newLinkUrl = trim($_POST['new_link_url'] ?? '');
        $newLinkTitle = trim($_POST['new_link_title'] ?? '');

        if ($newLinkUrl !== '') {
            $allowedLinkTypes = ['website','youtube','instagram','blog','linkedin','pdf','kakao','booking','other'];
            if (!in_array($newLinkType, $allowedLinkTypes, true)) {
                $newLinkType = 'website';
            }

            $sortStmt = $db->prepare("
                SELECT COALESCE(MAX(sort_order), 0) AS max_sort
                FROM card_links
                WHERE card_id = :card_id
            ");
            $sortStmt->execute([':card_id' => $cardId]);
            $maxSortRow = $sortStmt->fetch();
            $maxSort = (int)($maxSortRow['max_sort'] ?? 0);

            $linkInsert = $db->prepare("
                INSERT INTO card_links (
                    card_id,
                    link_type,
                    url,
                    title,
                    description,
                    thumbnail_url,
                    sort_order,
                    is_featured,
                    is_visible,
                    created_at,
                    updated_at
                ) VALUES (
                    :card_id,
                    :link_type,
                    :url,
                    :title,
                    NULL,
                    NULL,
                    :sort_order,
                    0,
                    1,
                    NOW(),
                    NOW()
                )
            ");

            $linkInsert->execute([
                ':card_id' => $cardId,
                ':link_type' => $newLinkType,
                ':url' => $newLinkUrl,
                ':title' => $newLinkTitle !== '' ? $newLinkTitle : $newLinkUrl,
                ':sort_order' => $maxSort + 1,
            ]);
        }

        $this->redirect('/cards/edit?id=' . $cardId);
    }

    public function publicShow(): void
    {
        $db = DB::conn();

        $cardId = (int)($_GET['id'] ?? 0);
        if ($cardId <= 0) {
            http_response_code(400);
            echo '유효하지 않은 카드 ID입니다.';
            return;
        }

        $stmt = $db->prepare("SELECT * FROM cards WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $cardId]);
        $card = $stmt->fetch();

        if (!$card) {
            http_response_code(404);
            echo '카드를 찾을 수 없습니다.';
            return;
        }

        $aiStmt = $db->prepare("SELECT * FROM card_ai_settings WHERE card_id = :card_id LIMIT 1");
        $aiStmt->execute([':card_id' => $cardId]);
        $ai = $aiStmt->fetch();

        $linksStmt = $db->prepare("
            SELECT *
            FROM card_links
            WHERE card_id = :card_id
            ORDER BY sort_order ASC, id ASC
        ");
        $linksStmt->execute([':card_id' => $cardId]);
        $links = $linksStmt->fetchAll();

        $quickActions = [];
        if (!empty($ai['quick_actions'])) {
            $decoded = json_decode($ai['quick_actions'], true);
            if (is_array($decoded)) {
                $quickActions = $decoded;
            }
        }

        $this->view('cards/public', [
            'card' => $card,
            'ai' => $ai,
            'links' => $links,
            'quickActions' => $quickActions,
        ], 'public_card');
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9가-힣]+/u', '-', $text);
        $text = trim($text, '-');

        if ($text === '') {
            $text = 'card';
        }

        return mb_substr($text, 0, 80);
    }

    private function generateUniqueSlug(PDO $db, int $userId, string $baseSlug): string
    {
        $slug = $baseSlug;
        $i = 1;

        while (true) {
            $stmt = $db->prepare("SELECT id FROM cards WHERE user_id = :user_id AND slug = :slug LIMIT 1");
            $stmt->execute([
                ':user_id' => $userId,
                ':slug' => $slug,
            ]);

            $existing = $stmt->fetch();
            if (!$existing) {
                return $slug;
            }

            $i++;
            $slug = $baseSlug . '-' . $i;
        }
    }
}