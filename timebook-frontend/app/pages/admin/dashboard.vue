<template>
    <div class="container">
        <div class="header">
            <h1 class="title">ダッシュボード</h1>
            <p v-if="teacher" class="welcome">ようこそ、{{ teacher.name }} 先生</p>
        </div>

        <!-- ローディング中 -->
        <div v-if="loading" class="loading">
            <div class="spinner"></div>
            <p>読み込み中...</p>
        </div>

        <!-- エラー表示 -->
        <div v-else-if="error" class="error">
            <p>{{ error }}</p>
        </div>

        <!-- ダッシュボード内容 -->
        <div v-else>
            <!-- 統計カード -->
            <div class="stats-grid">
                <!-- 今日の予約 -->
                <div class="stat-card stat-card-blue">
                    <div class="stat-icon">📅</div>
                    <div class="stat-content">
                        <p class="stat-label">今日の予約</p>
                        <p class="stat-value">{{ todayCount }}件</p>
                    </div>
                </div>

                <!-- 今月の予約 -->
                <div class="stat-card stat-card-green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <p class="stat-label">今月の予約</p>
                        <p class="stat-value">{{ stats?.this_month_reservations || 0 }}件</p>
                    </div>
                </div>

                <!-- 全体の予約 -->
                <div class="stat-card stat-card-purple">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <p class="stat-label">全体の予約</p>
                        <p class="stat-value">{{ stats?.total_reservations || 0 }}件</p>
                    </div>
                </div>
            </div>

            <!-- 今日の予約一覧 -->
            <div class="today-reservations">
                <h2 class="section-title">今日の予約</h2>
                
                <div v-if="todayReservations.length === 0" class="no-reservations">
                    <p>今日の予約はありません</p>
                </div>

                <div v-else class="reservation-list">
                    <div
                        v-for="reservation in todayReservations"
                        :key="reservation.id"
                        class="reservation-card"
                    >
                        <div class="reservation-info">
                            <div class="student-icon">👤</div>
                            <div class="student-details">
                                <p class="student-name">{{ reservation.student_name }}</p>
                                <p class="student-email">{{ reservation.student_email }}</p>
                            </div>
                        </div>
                        
                        <div class="lesson-time">
                            <p class="time">
                                {{ formatTime(reservation.lesson_start_time) }} - {{ formatTime(reservation.lesson_end_time) }}
                            </p>
                            <p class="duration">
                                {{ reservation.lesson_slot?.duration_minutes }}分間
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
definePageMeta({
    middleware: 'auth'
})
const { fetchWithAuth } = useAuth()

// データの状態管理
const loading = ref(true)
const error = ref('')
const teacher = ref<any>(null)
const stats = ref<any>(null)
const todayReservations = ref<any[]>([])
const todayCount = ref(0)

// ページ読み込み時にデータ取得
onMounted(async () => {
    await loadDashboardData()
})

// ダッシュボードデータを読み込む
const loadDashboardData = async () => {
    try {
        loading.value = true
        error.value = ''

        // 先生情報を取得
        const teacherData = localStorage.getItem('teacher')
        if (teacherData) {
            teacher.value = JSON.parse(teacherData)
        }

        // 統計情報を取得
        const statsResponse = await fetchWithAuth('http://localhost/api/dashboard/stats')
        if (statsResponse.success) {
            stats.value = statsResponse.data
        }

        // 今日の予約を取得
        const todayResponse = await fetchWithAuth('http://localhost/api/dashboard/today-reservations')
        if (todayResponse.success) {
            todayReservations.value = todayResponse.data.reservations
            todayCount.value = todayResponse.data.count
        }

    } catch (err: any) {
        console.error('ダッシュボードデータの取得エラー:', err)
        error.value = 'データの読み込みに失敗しました'
    } finally {
        loading.value = false
    }
}

// 時刻をフォーマット (HH:MM:SS → HH:MM)
const formatTime = (time: string) => {
    if (!time) return ''
    return time.substring(0, 5)
}
</script>

<style scoped>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.header {
    margin-bottom: 2rem;
}

.title {
    font-size: 2rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0.5rem;
}

.welcome {
    color: #718096;
    font-size: 1.1rem;
}

.loading {
    text-align: center;
    padding: 3rem;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.error {
    background-color: #fff5f5;
    color: #e53e3e;
    padding: 1rem;
    border-radius: 8px;
    border: 2px solid #feb2b2;
}

/* 統計カード */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
}

.stat-card-blue {
    border-left: 5px solid #667eea;
}

.stat-card-green {
    border-left: 5px solid #48bb78;
}

.stat-card-purple {
    border-left: 5px solid #9f7aea;
}

.stat-icon {
    font-size: 3rem;
}

.stat-content {
    flex: 1;
}

.stat-label {
    color: #718096;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
}

/* 今日の予約一覧 */
.today-reservations {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    padding: 1.5rem;
    border-bottom: 2px solid #e2e8f0;
    margin: 0;
}

.no-reservations {
    padding: 3rem;
    text-align: center;
    color: #a0aec0;
}

.reservation-list {
    display: flex;
    flex-direction: column;
}

.reservation-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.2s;
}

.reservation-card:last-child {
    border-bottom: none;
}

.reservation-card:hover {
    background-color: #f7fafc;
}

.reservation-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.student-icon {
    font-size: 2.5rem;
    background: linear-gradient(135deg, #667eea 0%, #64b5f6 100%);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.student-details {
    flex: 1;
}

.student-name {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.student-email {
    color: #a0aec0;
    font-size: 0.9rem;
}

.lesson-time {
    text-align: right;
}

.time {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.duration {
    color: #a0aec0;
    font-size: 0.9rem;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .reservation-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .lesson-time {
        text-align: left;
    }
}
</style>