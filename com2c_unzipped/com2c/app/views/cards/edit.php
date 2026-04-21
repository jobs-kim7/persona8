<div class="card stack">
  <div class="badge">Edit Card</div>
  <h1><?= e($card['title']) ?></h1>
  <p class="muted">기본 정보, AI 인사말, 링크 포트폴리오를 한 화면에서 수정합니다.</p>
</div>

<form method="post" action="/cards/update" class="stack">
  <input type="hidden" name="id" value="<?= e((string)$card['id']) ?>">

  <div class="grid-2">
    <div class="card stack">
      <h2>기본 정보</h2>

      <div>
        <label for="title">카드 제목 *</label>
        <input id="title" name="title" value="<?= e($card['title']) ?>">
      </div>

      <div>
        <label for="purpose">카드 목적</label>
        <select id="purpose" name="purpose">
          <?php
          $purposes = ['basic','networking','collab','portfolio','recruiting','soho'];
          foreach ($purposes as $p):
          ?>
            <option value="<?= e($p) ?>" <?= $card['purpose'] === $p ? 'selected' : '' ?>>
              <?= e($p) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="name">이름 *</label>
        <input id="name" name="name" value="<?= e($card['name']) ?>">
      </div>

      <div>
        <label for="one_liner">한 줄 소개</label>
        <input id="one_liner" name="one_liner" value="<?= e($card['one_liner'] ?? '') ?>">
      </div>

      <div>
        <label for="role_title">직함 / 역할</label>
        <input id="role_title" name="role_title" value="<?= e($card['role_title'] ?? '') ?>">
      </div>

      <div>
        <label for="company_name">회사 / 소속</label>
        <input id="company_name" name="company_name" value="<?= e($card['company_name'] ?? '') ?>">
      </div>

      <div>
        <label for="email">이메일</label>
        <input id="email" name="email" value="<?= e($card['email'] ?? '') ?>">
      </div>

      <div>
        <label for="phone">전화번호</label>
        <input id="phone" name="phone" value="<?= e($card['phone'] ?? '') ?>">
      </div>

      <div>
        <label for="bio">소개문</label>
        <textarea id="bio" name="bio" rows="5"><?= e($card['bio'] ?? '') ?></textarea>
      </div>

      <div>
        <label for="status">상태</label>
        <select id="status" name="status">
          <?php
          $statuses = ['draft','published','archived'];
          foreach ($statuses as $s):
          ?>
            <option value="<?= e($s) ?>" <?= $card['status'] === $s ? 'selected' : '' ?>>
              <?= e($s) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="card stack">
      <h2>AI 설정</h2>

      <div>
        <label for="ai_role">AI 역할</label>
        <select id="ai_role" name="ai_role">
          <?php
          $roles = ['guide','matchmaker','recruiting','soho_helper'];
          $currentRole = $ai['ai_role'] ?? 'guide';
          foreach ($roles as $r):
          ?>
            <option value="<?= e($r) ?>" <?= $currentRole === $r ? 'selected' : '' ?>>
              <?= e($r) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="tone">응답 톤</label>
        <select id="tone" name="tone">
          <?php
          $tones = ['business','warm','practical'];
          $currentTone = $ai['tone'] ?? 'business';
          foreach ($tones as $t):
          ?>
            <option value="<?= e($t) ?>" <?= $currentTone === $t ? 'selected' : '' ?>>
              <?= e($t) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="greeting_message">첫 인사말</label>
        <textarea id="greeting_message" name="greeting_message" rows="5"><?= e($ai['greeting_message'] ?? '') ?></textarea>
      </div>

      <?php
      $qa = [];
      if (!empty($ai['quick_actions'])) {
          $decodedQa = json_decode($ai['quick_actions'], true);
          if (is_array($decodedQa)) {
              $qa = array_values($decodedQa);
          }
      }
      ?>

      <div>
        <label>빠른 선택 1</label>
        <input name="quick_action_1" value="<?= e($qa[0] ?? '') ?>">
      </div>

      <div>
        <label>빠른 선택 2</label>
        <input name="quick_action_2" value="<?= e($qa[1] ?? '') ?>">
      </div>

      <div>
        <label>빠른 선택 3</label>
        <input name="quick_action_3" value="<?= e($qa[2] ?? '') ?>">
      </div>

      <div>
        <label>빠른 선택 4</label>
        <input name="quick_action_4" value="<?= e($qa[3] ?? '') ?>">
      </div>
    </div>
  </div>

  <div class="card stack">
    <h2>링크 포트폴리오 추가</h2>

    <div class="grid-2">
      <div>
        <label for="new_link_type">링크 유형</label>
        <select id="new_link_type" name="new_link_type">
          <option value="website">website</option>
          <option value="youtube">youtube</option>
          <option value="instagram">instagram</option>
          <option value="blog">blog</option>
          <option value="linkedin">linkedin</option>
          <option value="pdf">pdf</option>
          <option value="kakao">kakao</option>
          <option value="booking">booking</option>
          <option value="other">other</option>
        </select>
      </div>

      <div>
        <label for="new_link_title">링크 제목</label>
        <input id="new_link_title" name="new_link_title" placeholder="예: 유튜브 인터뷰">
      </div>
    </div>

    <div>
      <label for="new_link_url">링크 URL</label>
      <input id="new_link_url" name="new_link_url" placeholder="https://...">
    </div>

    <p class="muted">저장 버튼을 누르면 링크가 현재 카드에 추가됩니다.</p>
  </div>

  <div class="card stack">
    <div class="row">
      <button class="btn" type="submit">저장하기</button>
      <a class="btn secondary" href="/cards/public?id=<?= e((string)$card['id']) ?>">공개보기</a>
      <a class="btn secondary" href="/dashboard">대시보드</a>
    </div>
  </div>
</form>

<div class="card stack">
  <h2>현재 링크 목록</h2>

  <?php if (!empty($links)): ?>
    <?php foreach ($links as $link): ?>
      <div class="card">
        <div class="badge"><?= e($link['link_type']) ?></div>
        <h3 style="margin:10px 0 6px;"><?= e($link['title'] ?? $link['url']) ?></h3>
        <p class="muted" style="margin:0; word-break: break-all;"><?= e($link['url']) ?></p>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="muted">아직 등록된 링크가 없습니다.</p>
  <?php endif; ?>
</div>