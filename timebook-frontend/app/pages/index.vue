<template>
  <div class="home-container">
    <div class="hero">
      <h1>🎹 TimeBook へようこそ</h1>
      <p class="subtitle">レッスン予約管理システム</p>

      <div class="cta-buttons">
        <NuxtLink to="/slots" class="cta-button primary">
          📅 空き枠を見る
        </NuxtLink>
        <NuxtLink to="/reservations" class="cta-button secondary">
          📋 予約履歴を確認
        </NuxtLink>
      </div>
    </div>

    <div class="features">
      <div class="feature-card">
        <div class="feature-icon">📅</div>
        <h3>簡単予約</h3>
        <p>空いているレッスン枠から好きな時間を選んで予約</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">📋</div>
        <h3>予約管理</h3>
        <p>メールアドレスで予約履歴を確認・キャンセル</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">🎹</div>
        <h3>レッスン管理</h3>
        <p>先生は空き枠を簡単に管理できます</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.home-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.hero {
  text-align: center;
  padding: 4rem 2rem;
  background: #5dade2;
  color: white;
  border-radius: 16px;
  margin-bottom: 3rem;
  box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.hero h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.subtitle {
  font-size: 1.3rem;
  opacity: 0.9;
  margin-bottom: 2rem;
}

.cta-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.cta-button {
  padding: 1rem 2rem;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 600;
  font-size: 1.1rem;
  transition: all 0.3s;
  display: inline-block;
}

.cta-button.primary {
  background: white;
  color: #5dade2;
}

.cta-button.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.cta-button.secondary {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid white;
}

.cta-button.secondary:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

/* 機能紹介 */
.features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.feature-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  text-align: center;
  transition: transform 0.3s;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h3 {
  color: #5dade2;
  margin-bottom: 0.5rem;
  font-size: 1.5rem;
}

.feature-card p {
  color: #666;
  line-height: 1.6;
}

/* レスポンシブ */
@media (max-width: 768px) {
  .hero h1 {
    font-size: 2rem;
  }

  .subtitle {
    font-size: 1.1rem;
  }

  .cta-buttons {
    flex-direction: column;
    align-items: stretch;
  }

  .features {
    grid-template-columns: 1fr;
  }
}
</style>