<template>
    <header class="app-header">
        <div class="container">
            <div class="header-content">
                <!-- ロゴ/タイトル -->
                <div class="logo">
                    <NuxtLink to="/">
                        <span class="logo-icon">🎹</span>
                        <span class="logo-text">TimeBook</span>
                    </NuxtLink>
                </div>

                <!-- ナビゲーション -->
                <nav class="nav">
                    <NuxtLink to="/slots" class="nav-link">
                        📅 空き枠一覧
                    </NuxtLink>
                    <NuxtLink to="/reservations" class="nav-link">
                        📋 予約履歴
                    </NuxtLink>
                    <NuxtLink to="/login" class="nav-link">
                        🔐 ログイン
                    </NuxtLink>
                </nav>
            </div>
        </div>
    </header>
</template>

<style scoped>
.app-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
}

/* ロゴ */
.logo a {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: white;
  font-size: 1.5rem;
  font-weight: bold;
  transition: opacity 0.2s;
}

.logo a:hover {
  opacity: 0.8;
}

.logo-icon {
  font-size: 2rem;
}

.logo-text {
  font-size: 1.5rem;
}

/* ナビゲーション */
.nav {
  display: flex;
  gap: 1.5rem;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  transition: all 0.2s;
  font-weight: 500;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* アクティブなリンク */
.nav-link.router-link-active {
  background: rgba(255, 255, 255, 0.3);
  font-weight: 600;
}

/* レスポンシブ */
@media (max-width: 640px) {
  .header-content {
    flex-direction: column;
    gap: 1rem;
  }

  .nav {
    flex-direction: column;
    width: 100%;
    text-align: center;
  }

  .nav-link {
    display: block;
  }

  .logo-text {
    font-size: 1.2rem;
  }
}
</style>