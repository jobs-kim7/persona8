<div class="card stack">
  <div class="badge">내 문의</div>
  <h1><?= e($inquiry['visitor_name'] ?? '문의자') ?>님의 문의</h1>
  <p class="muted">현재 상태: <?= e($inquiry['status'] ?? 'new') ?></p>

  <?php if (isset($_GET['welcome']) && $_GET['welcome'] === '1'): ?>
    <div class="card stack">
      <div class="badge">안내</div>
      <p class="muted">문의가 정상 접수되었습니다. 아래 AI 안내와 현재 상태를 확인한 뒤, 필요하면 사진이나 참고자료를 바로 추가해 주세요.</p>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['saved']) && $_GET['saved'] === '1'): ?>
    <div class="card stack">
      <div class="badge">저장 완료</div>
      <p class="muted">추가 메시지 또는 첨부자료가 저장되었습니다.</p>
    </div>
  <?php endif; ?>
</div>

<?php
  $roleLabelMap = [
    'visitor' => '나',
    'owner' => '운영자',
    'ai' => 'AI',
    'system' => '상태',
  ];
?>

<div class="card stack">
  <h2>대화 타임라인</h2>

  <?php if (!empty($messages)): ?>
    <div class="timeline">
      <?php foreach ($messages as $message): ?>
        <?php
          $sender = $message['sender_type'] ?? 'system';
          $roleLabel = $roleLabelMap[$sender] ?? '메시지';
        ?>
        <div class="timeline-message <?= e($sender) ?>">
          <div class="timeline-meta">
            <span class="timeline-role"><?= e($roleLabel) ?></span>
            <?php if (!empty($message['created_at'])): ?>
              <span class="timeline-time"><?= e($message['created_at']) ?></span>
            <?php endif; ?>
          </div>

          <?php if (!empty($message['message_text'])): ?>
            <p class="timeline-text"><?= e($message['message_text']) ?></p>
          <?php endif; ?>

          <?php if (!empty($message['attachments'])): ?>
            <div class="row" style="gap:10px; margin-top:10px; flex-wrap:wrap;">
              <?php foreach ($message['attachments'] as $attachment): ?>
                <a href="<?= e($attachment['file_path']) ?>" target="_blank" rel="noopener noreferrer">
                  <img src="<?= e($attachment['file_path']) ?>" alt="<?= e($attachment['file_name'] ?? '첨부 이미지') ?>" style="width:100px; height:100px; object-fit:cover; border-radius:12px; border:1px solid #e5e7eb;">
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="timeline-message system">
      <p class="timeline-text">표시할 대화가 없습니다.</p>
    </div>
  <?php endif; ?>
</div>

<div class="card stack">
  <h2>추가 자료 보내기</h2>
  <p class="muted">AI 안내를 보고 보완이 필요하면 메시지나 사진을 추가로 보낼 수 있습니다.</p>

  <form method="post" action="/my/inquiries/reply" enctype="multipart/form-data" class="stack">
    <input type="hidden" name="inquiry_id" value="<?= e((string)$inquiry['id']) ?>">
    <input type="hidden" name="email" value="<?= e((string)$email) ?>">

    <div>
      <label for="message_text">추가 메시지</label>
      <textarea id="message_text" name="message_text" rows="5" placeholder="추가 설명이나 보완 내용을 입력해주세요."></textarea>
    </div>

    <div>
      <label for="my_attachment">사진/자료 첨부 (1개)</label>
      <input id="my_attachment" name="my_attachment" type="file" accept=".jpg,.jpeg,.png,.webp,.gif">
    </div>

    <div class="row">
      <button class="btn" type="submit">추가 자료 보내기</button>
      <a class="btn secondary" href="/my/inquiries?email=<?= e((string)$email) ?>">내 문의 목록</a>
    </div>
  </form>
</div>
