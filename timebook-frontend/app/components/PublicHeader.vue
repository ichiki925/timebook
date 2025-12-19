<template>
    <header>
        <div class="container">
            <div class="logo">
                <NuxtLink to="/">
                    🎹 TimeBook
                </NuxtLink>
            </div>
            <nav>
                <NuxtLink to="/slots" class="nav-link">
                    📅 空き枠一覧
                </NuxtLink>
                <NuxtLink to="/reservations" class="nav-link">
                    📋 予約確認
                </NuxtLink>
                <NuxtLink to="/login" class="nav-link login-link">
                    🔐 講師ログイン
                </NuxtLink>
            </nav>
        </div>
    </header>
</template>

<script setup lang="ts">
// 生徒向けヘッダー - ログイン不要
</script>

<style scoped>
header {
    background: linear-gradient(135deg, #667eea 0%, #64b5f6 100%);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo a {
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
    text-decoration: none;
    transition: opacity 0.3s ease;
}

.logo a:hover {
    opacity: 0.8;
}

nav {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.nav-link {
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.2);
}

.login-link {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.login-link:hover {
    background: rgba(255, 255, 255, 0.25);
}
</style>