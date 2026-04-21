<div class="card stack">
  <div class="badge">공개 카드</div>
  <h1><?= e($card['name']) ?></h1>

  <?php if (!empty($card['one_liner'])): ?>
    <p><?= e($card['one_liner']) ?></p>
  <?php endif; ?>

  <div class="row">
    <a class="btn" href="#inquiry-form">문의 남기기</a>
    <?php if (!empty($portfolioItems) || !empty($links)): ?>
      <a class="btn secondary" href="#portfolio">포트폴리오 보기</a>
    <?php endif; ?>
  </div>
</div>

<?php if (isset($_GET['submitted']) && $_GET['submitted'] === '1'): ?>
  <div class="card stack">
    <div class="badge">전달 완료</div>
    <h2>문의가 전달되었습니다</h2>
    <p class="muted">먼저 문의를 안전하게 접수했습니다. 이제 내 문의 화면에서 AI 답변을 보고 사진이나 참고자료를 바로 추가할 수 있습니다.</p>
    <?php if (!empty($_GET['email']) && !empty($_GET['inquiry_id'])): ?>
      <div class="row">
        <a class="btn" href="/my/inquiries/show?id=<?= e((string)$_GET['inquiry_id']) ?>&email=<?= e((string)$_GET['email']) ?>">내 문의로 이동</a>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if (!empty($card['role_title']) || !empty($card['company_name']) || !empty($card['bio'])): ?>
  <div class="card stack">
    <h2>소개</h2>

    <?php if (!empty($card['role_title']) || !empty($card['company_name'])): ?>
      <p class="muted">
        <?php if (!empty($card['role_title'])): ?>
          <?= e($card['role_title']) ?>
        <?php endif; ?>
        <?php if (!empty($card['role_title']) && !empty($card['company_name'])): ?>
          ·
        <?php endif; ?>
        <?php if (!empty($card['company_name'])): ?>
          <?= e($card['company_name']) ?>
        <?php endif; ?>
      </p>
    <?php endif; ?>

    <?php if (!empty($card['bio'])): ?>
      <p><?= nl2br(e($card['bio'])) ?></p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="card stack">
  <h2>AI 인사말</h2>

  <p class="muted">
    <?= e($ai['greeting_message'] ?? '안녕하세요. 어떤 목적으로 오셨는지 알려주시면 가장 적절한 연결 방식을 안내해드릴게요.') ?>
  </p>

  <?php if (!empty($quickActions)): ?>
    <div class="row">
      <?php foreach ($quickActions as $action): ?>
        <button class="btn secondary" type="button"><?= e($action) ?></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<div class="card stack" id="inquiry-form">
  <h2>문의 남기기</h2>
  <p class="muted">먼저 문의를 접수하면, 바로 내 문의 화면으로 이동해 AI 안내를 보고 사진이나 참고자료를 이어서 추가할 수 있습니다.</p>

  <form method="post" action="/inquiries/store" class="stack">
    <input type="hidden" name="card_id" value="<?= e((string)$card['id']) ?>">

    <div>
      <label for="visitor_name">이름 *</label>
      <input id="visitor_name" name="visitor_name" placeholder="이름을 입력해주세요">
    </div>

    <div>
      <label for="visitor_email">이메일 *</label>
      <input id="visitor_email" name="visitor_email" placeholder="답변 받을 이메일">
    </div>

    <div>
      <label for="inquiry_type">문의 유형</label>
      <select id="inquiry_type" name="inquiry_type">
        <option value="general">일반 문의</option>
        <option value="collab">협업 제안</option>
        <option value="consulting">상담 요청</option>
        <option value="recruiting">채용 제안</option>
        <option value="booking">예약 문의</option>
      </select>
    </div>

    <div>
      <label for="message">문의 내용 *</label>
      <textarea id="message" name="message" rows="6" placeholder="문의 내용을 입력해주세요"></textarea>
    </div>

    <div class="row">
      <button class="btn" type="submit">문의 시작하기</button>
    </div>
  </form>
</div>

<?php if (!empty($portfolioItems) || !empty($links)): ?>
  <div class="card stack" id="portfolio">
    <h2>포트폴리오</h2>

    <?php if (!empty($portfolioItems)): ?>
      <?php foreach ($portfolioItems as $item): ?>
        <div class="card">
          <div class="badge"><?= e($item['item_type']) ?></div>
          <h3 style="margin:10px 0 6px;"><?= e($item['title']) ?></h3>

          <?php if (!empty($item['summary'])): ?>
            <p class="muted"><?= e($item['summary']) ?></p>
          <?php endif; ?>

          <?php if (($item['item_type'] ?? '') === 'link' && !empty($item['target_url'])): ?>
            <p style="word-break: break-all;">
              <a href="<?= e($item['target_url']) ?>" target="_blank" rel="noopener noreferrer">
                <?= e($item['target_url']) ?>
              </a>
            </p>
          <?php endif; ?>

          <?php if (($item['item_type'] ?? '') === 'image' && !empty($item['image_url'])): ?>
            <p>
              <a href="<?= e($item['image_url']) ?>" target="_blank" rel="noopener noreferrer">
                <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['title']) ?>" style="max-width:220px; border-radius:14px; border:1px solid #e5e7eb;">
              </a>
            </p>
          <?php endif; ?>

          <?php if (($item['item_type'] ?? '') === 'pdf' && !empty($item['file_url'])): ?>
            <p>
              <a href="<?= e($item['file_url']) ?>" target="_blank" rel="noopener noreferrer">
                PDF 보기
              </a>
            </p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($links)): ?>
      <?php foreach ($links as $link): ?>
        <div class="card">
          <div class="badge"><?= e($link['link_type']) ?></div>
          <h3 style="margin:10px 0 6px;"><?= e($link['title'] ?? $link['url']) ?></h3>

          <?php if (!empty($link['description'])): ?>
            <p class="muted"><?= e($link['description']) ?></p>
          <?php endif; ?>

          <p style="word-break: break-all;">
            <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer">
              <?= e($link['url']) ?>
            </a>
          </p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if (!empty($card['email']) || !empty($card['phone'])): ?>
  <div class="card stack">
    <h2>연락처</h2>

    <?php if (!empty($card['email'])): ?>
      <p>이메일: <a href="mailto:<?= e($card['email']) ?>"><?= e($card['email']) ?></a></p>
    <?php endif; ?>

    <?php if (!empty($card['phone'])): ?>
      <p>전화번호: <a href="tel:<?= e($card['phone']) ?>"><?= e($card['phone']) ?></a></p>
    <?php endif; ?>
  </div>
<?php endif; ?>
