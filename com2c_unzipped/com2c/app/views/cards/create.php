<div class="card stack">
  <div class="badge">New Card</div>
  <h1>새 카드 만들기</h1>
  <p class="muted">일단 가장 중요한 기본 정보만 저장해서 흐름을 먼저 살립니다.</p>
</div>

<div class="card stack">
  <form method="post" action="/cards/store" class="stack">
    <div>
      <label for="title">카드 제목 *</label>
      <input id="title" name="title" value="<?= e(old('title')) ?>" placeholder="예: 기본 소개용">
    </div>

    <div>
      <label for="purpose">카드 목적</label>
      <select id="purpose" name="purpose">
        <option value="basic">basic</option>
        <option value="networking">networking</option>
        <option value="collab">collab</option>
        <option value="portfolio">portfolio</option>
        <option value="recruiting">recruiting</option>
        <option value="soho">soho</option>
      </select>
    </div>

    <div>
      <label for="name">이름 *</label>
      <input id="name" name="name" value="<?= e(old('name')) ?>" placeholder="예: 잡스">
    </div>

    <div>
      <label for="one_liner">한 줄 소개</label>
      <input id="one_liner" name="one_liner" value="<?= e(old('one_liner')) ?>" placeholder="예: 에너지·지역·문화를 연결하는 프로젝트 빌더">
    </div>

    <div>
      <label for="role_title">직함 / 역할</label>
      <input id="role_title" name="role_title" value="<?= e(old('role_title')) ?>" placeholder="예: Founder">
    </div>

    <div>
      <label for="company_name">회사 / 소속</label>
      <input id="company_name" name="company_name" value="<?= e(old('company_name')) ?>" placeholder="예: com2c">
    </div>

    <div>
      <label for="email">이메일</label>
      <input id="email" name="email" value="<?= e(old('email')) ?>" placeholder="예: hello@com2c.me">
    </div>

    <div>
      <label for="phone">전화번호</label>
      <input id="phone" name="phone" value="<?= e(old('phone')) ?>" placeholder="예: 010-0000-0000">
    </div>

    <div>
      <label for="bio">소개문</label>
      <textarea id="bio" name="bio" rows="5" placeholder="간단한 자기소개 또는 카드 설명을 적어주세요."><?= e(old('bio')) ?></textarea>
    </div>

    <div>
      <label for="status">상태</label>
      <select id="status" name="status">
        <option value="draft">draft</option>
        <option value="published">published</option>
        <option value="archived">archived</option>
      </select>
    </div>

    <div class="row">
      <button class="btn" type="submit">카드 저장</button>
      <a class="btn secondary" href="/dashboard">취소</a>
    </div>
  </form>
</div>