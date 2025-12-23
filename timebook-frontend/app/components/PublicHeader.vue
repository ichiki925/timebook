<template>
    <header>
        <div class="container">
            <div class="logo">
                <NuxtLink to="/">
                    <MusicalNoteIcon class="logo-icon" />
                    <span>TimeBook</span>
                </NuxtLink>
            </div>
            <nav>
                <NuxtLink to="/slots" class="nav-link">
                    <CalendarDaysIcon class="nav-icon" />
                    <span>空き枠一覧</span>
                </NuxtLink>
                <NuxtLink to="/reservations" class="nav-link">
                    <ClipboardDocumentListIcon class="nav-icon" />
                    <span>予約確認</span>
                </NuxtLink>
                <NuxtLink to="/login" class="nav-link login-link">
                    <LockClosedIcon class="nav-icon" />
                    <span>講師ログイン</span>
                </NuxtLink>
            </nav>
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
header {
    background: #5dade2;
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
    text-decoration: none;
    transition: opacity 0.3s ease;
}

.logo a:hover {
    opacity: 0.8;
}

.logo-icon {
    width: 28px;
    height: 28px;
}

nav {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
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

.nav-icon {
    width: 20px;
    height: 20px;
}

.login-link {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.login-link:hover {
    background: rgba(255, 255, 255, 0.25);
}
</style>