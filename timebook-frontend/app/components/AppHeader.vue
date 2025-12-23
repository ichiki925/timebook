<template>
    <header class="app-header">
        <div class="container">
            <div class="header-content">
                <!-- ロゴ/タイトル -->
                <div class="logo">
                    <NuxtLink to="/">
                        <MusicalNoteIcon class="logo-icon" />
                        <span class="logo-text">TimeBook</span>
                    </NuxtLink>
                </div>

                <!-- ナビゲーション -->
                <nav class="nav">
                    <NuxtLink to="/slots" class="nav-link">
                        <CalendarDaysIcon class="nav-icon" />
                        <span>空き枠一覧</span>
                    </NuxtLink>
                    <NuxtLink to="/reservations" class="nav-link">
                        <ClipboardDocumentListIcon class="nav-icon" />
                        <span>予約履歴</span>
                    </NuxtLink>
                    <NuxtLink to="/login" class="nav-link">
                        <LockClosedIcon class="nav-icon" />
                        <span>ログイン</span>
                    </NuxtLink>
                </nav>
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
import {
  MusicalNoteIcon,
  CalendarDaysIcon,
  ClipboardDocumentListIcon,
  LockClosedIcon
} from '@heroicons/vue/24/outline'
</script>

<style scoped>
.app-header {
  background: #5dade2;
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
  width: 32px;
  height: 32px;
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
  display: flex;
  align-items: center;
  gap: 0.5rem;
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

.nav-icon {
  width: 20px;
  height: 20px;
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
    display: flex;
    justify-content: center;
  }

  .logo-text {
    font-size: 1.2rem;
  }
}
</style>