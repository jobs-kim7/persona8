<div class="card stack">
  <div class="badge">Dashboard</div>
  <h1>내 카드</h1>
  <p class="muted">최근 카드와 전체 카드를 확인할 수 있습니다.</p>
</div>

<?php if ($recentCard): ?>
  <div class="card stack">
    <div class="badge">Recent Card</div>
    <h2><?= e($recentCard['title']) ?></h2>
    <p class="muted">
      <?= e($recentCard['name']) ?>
      <?php if (!empty($recentCard['one_liner'])): ?>
        · <?= e($recentCard['one_liner']) ?>
      <?php endif; ?>
    </p>
    <p class="muted">Updated: <?= e($recentCard['updated_at']) ?></p>

    <div class="row">
      <a class="btn" href="/cards/edit?id=<?= e((string)$recentCard['id']) ?>">업데이트</a>
      <a class="btn secondary" href="/cards/public?id=<?= e((string)$recentCard['id']) ?>">공개보기</a>
    </div>
  </div>
<?php else: ?>
  <div class="card stack">
    <h2>아직 카드가 없습니다</h2>
    <p class="muted">첫 카드를 만들어보세요.</p>
    <div>
      <a class="btn" href="/cards/new">새 카드 만들기</a>
    </div>
  </div>
<?php endif; ?>

<div class="card stack">
  <div class="row" style="justify-content: space-between; align-items: center;">
    <div>
      <strong>AI Credits:</strong> <?= e((string)$credit) ?>
    </div>
    <div>
      <a class="btn" href="/cards/new">새 카드 만들기</a>
    </div>
  </div>
</div>

<div class="card stack">
  <h2>카드 목록</h2>

  <?php if (!empty($cards)): ?>
    <?php foreach ($cards as $card): ?>
      <div class="card">
        <div class="row" style="justify-content: space-between; align-items: center;">
          <div>
            <div class="badge"><?= e($card['purpose']) ?></div>
            <h3 style="margin: 10px 0 6px;"><?= e($card['title']) ?></h3>
            <p class="muted" style="margin: 0 0 6px;">
              <?= e($card['name']) ?>
              <?php if (!empty($card['one_liner'])): ?>
                · <?= e($card['one_liner']) ?>
              <?php endif; ?>
            </p>
            <p class="muted" style="margin: 0;">slug: <?= e($card['slug']) ?> / status: <?= e($card['status']) ?></p>
          </div>

          <div class="row">
            <a class="btn secondary" href="/cards/edit?id=<?= e((string)$card['id']) ?>">수정</a>
            <a class="btn" href="/cards/public?id=<?= e((string)$card['id']) ?>">보기</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="muted">등록된 카드가 없습니다.</p>
  <?php endif; ?>
</div>