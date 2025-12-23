<template>
    <header>
        <div class="container">
            <!-- ロゴ部分 -->
            <div class="logo">
                <NuxtLink to="/admin/dashboard">
                    <MusicalNoteIcon class="logo-icon" />
                    <span>TimeBook</span>
                </NuxtLink>
            </div>

            <!-- ナビゲーション部分 -->
            <nav>
                <NuxtLink to="/admin/dashboard" class="nav-link">
                    <ChartBarIcon class="nav-icon" />
                    <span>ダッシュボード</span>
                </NuxtLink>
                <NuxtLink to="/admin/lesson-slots" class="nav-link">
                    <CalendarDaysIcon class="nav-icon" />
                    <span>レッスン枠管理</span>
                </NuxtLink>
                <NuxtLink to="/admin/reservations" class="nav-link">
                    <ClipboardDocumentListIcon class="nav-icon" />
                    <span>予約管理</span>
                </NuxtLink>

                <!-- ユーザー情報部分 -->
                <div class="user-info">
                    <span class="teacher-name">{{ teacherName }}</span>
                    <button @click="handleLogout" class="logout-button">
                        <ArrowRightStartOnRectangleIcon class="nav-icon" />
                        <span>ログアウト</span>
                    </button>
                </div>
            </nav>
        </div>
    </header>
</template>

<script setup lang="ts">
import {
  MusicalNoteIcon,           // 🎹 ロゴ
  ChartBarIcon,              // 📊 ダッシュボード
  CalendarDaysIcon,          // 📅 レッスン枠管理
  ClipboardDocumentListIcon, // 📋 予約管理
  ArrowRightStartOnRectangleIcon  // 🚪 ログアウト
} from '@heroicons/vue/24/outline'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

// ルーター機能を使えるようにする
const router = useRouter()

// 先生の名前を保存する変数
const teacherName = ref('')

// ページが表示されたときに実行される
onMounted(() => {
    // localStorageから先生の情報を取得
    const teacherData = localStorage.getItem('teacher')

    if (teacherData) {
        try {
            // JSON文字列をオブジェクトに変換
            const teacher = JSON.parse(teacherData)
            teacherName.value = teacher.name || '先生'
        } catch (error) {
            console.error('先生情報の取得に失敗しました:', error)
            teacherName.value = '先生'
        }
    }
})

// ログアウトボタンが押されたときの処理
const handleLogout = () => {
    if (confirm('ログアウトしますか?')) {
        // ローカルストレージをクリア
        localStorage.removeItem('auth_token')
        localStorage.removeItem('teacher')

        // ログインページへ移動
        router.push('/login')
    }
}
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
    max-width: 1400px;
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
    gap: 1.5rem;
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


/* ここから新しい部分！ */
.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-left: 1rem;
    border-left: 2px solid rgba(255, 255, 255, 0.3);
}

.teacher-name {
    color: white;
    font-weight: 600;
    font-size: 1rem;
}

.logout-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.logout-button:hover {
    background: rgba(255, 255, 255, 0.25);
}
</style>