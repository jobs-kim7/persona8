<div class="card stack">
  <div class="badge">문의함</div>
  <h1>받은 문의</h1>
  <p class="muted">공개 카드에서 들어온 문의를 확인합니다.</p>
</div>

<?php
$fitLabelMap = [
    'high' => '높음',
    'medium' => '중간',
    'low' => '낮음',
    'unknown' => '미확정',
];

$typeLabelMap = [
    'general' => '일반 문의',
    'collab' => '협업 제안',
    'consulting' => '상담 요청',
    'recruiting' => '채용 제안',
    'booking' => '예약 문의',
];
?>

<?php if (!empty($items)): ?>
  <?php foreach ($items as $item): ?>
    <div class="card stack">
      <div class="row" style="justify-content: space-between; align-items: center;">
        <div>
          <div class="badge"><?= e($typeLabelMap[$item['inquiry_type']] ?? $item['inquiry_type']) ?></div>
          <h2 style="margin:10px 0 6px;"><?= e($item['visitor_name'] ?? '이름 없음') ?></h2>
          <p class="muted" style="margin:0;">
            카드: <?= e($item['card_title'] ?? '') ?>
            <?php if (!empty($item['card_name'])): ?>
              · <?= e($item['card_name']) ?>
            <?php endif; ?>
          </p>
        </div>

        <div class="stack" style="text-align:right;">
          <div class="badge">적합도: <?= e($fitLabelMap[$item['fit_level']] ?? $item['fit_level']) ?></div>
          <div class="badge"><?= e($item['status']) ?></div>
        </div>
      </div>

      <?php if (!empty($item['visitor_email'])): ?>
        <p><strong>이메일:</strong> <?= e($item['visitor_email']) ?></p>
      <?php endif; ?>

      <?php if (!empty($item['raw_message'])): ?>
        <div>
          <strong>원본 문의</strong>
          <p style="margin-top:8px; white-space:pre-line;"><?= e($item['raw_message']) ?></p>
        </div>
      <?php endif; ?>

      <div>
        <strong>AI 요약</strong>
        <p style="margin-top:8px; white-space:pre-line;"><?= e($item['summary_text']) ?></p>
      </div>

      <?php if (!empty($item['ai_recommendation'])): ?>
        <div>
          <strong>AI 제안</strong>
          <p style="margin-top:8px;"><?= e($item['ai_recommendation']) ?></p>
        </div>
      <?php endif; ?>

      <p class="muted">등록일: <?= e($item['created_at']) ?></p>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="card stack">
    <h2>아직 문의가 없습니다</h2>
    <p class="muted">공개 카드에서 문의를 남기면 여기에 표시됩니다.</p>
  </div>
<?php endif; ?>