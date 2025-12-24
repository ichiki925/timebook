<template>
    <div class="reservations-page">
        <div class="container">
            <!-- ページタイトル -->
            <h1 class="page-title">
                <ClipboardDocumentListIcon class="title-icon" />
                <span>予約管理</span>
            </h1>

            <!-- フィルター部分 -->
            <div class="filters">
                <div class="filter-group">
                    <label>開始日:</label>
                    <input type="date" v-model="filters.startDate" />
                </div>
                <div class="filter-group">
                    <label>終了日:</label>
                    <input type="date" v-model="filters.endDate" />
                </div>
                <div class="filter-group">
                    <label>ステータス:</label>
                    <select v-model="filters.status">
                        <option value="">すべて</option>
                        <option value="confirmed">確定のみ</option>
                        <option value="cancelled">キャンセルのみ</option>
                    </select>
                </div>
                <button @click="fetchReservations" class="filter-button">
                    <MagnifyingGlassIcon class="button-icon" />
                    <span>絞り込み</span>
                </button>
            </div>

            <!-- ローディング表示 -->
            <div v-if="loading" class="loading">
                読み込み中...
            </div>

            <!-- 予約一覧テーブル -->
            <div v-else-if="reservations.length > 0" class="table-container">
                <table class="reservations-table">
                    <thead>
                        <tr>
                            <th>日時</th>
                            <th>生徒名</th>
                            <th>コース</th>
                            <th>ステータス</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="reservation in reservations" :key="reservation.id">
                            <td>{{ formatDateTime(reservation) }}</td>
                            <td>{{ reservation.student_name }}</td>
                            <td>{{ reservation.course_type }}</td>
                            <td>
                                <span 
                                    class="status-badge" 
                                    :class="reservation.status"
                                >
                                    {{ getStatusText(reservation.status) }}
                                </span>
                            </td>
                            <td>
                                <button 
                                    v-if="reservation.status === 'confirmed'"
                                    @click="cancelReservation(reservation.id)"
                                    class="cancel-button"
                                >
                                    キャンセル
                                </button>
                                <button 
                                    v-else
                                    @click="restoreReservation(reservation.id)"
                                    class="restore-button"
                                >
                                    復活
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 予約がない場合 -->
            <div v-else class="no-data">
                予約がありません
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {
    ClipboardDocumentListIcon,
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'
definePageMeta({
    middleware: 'auth'
})

import { ref, onMounted } from 'vue'

const { fetchWithAuth } = useAuth()

// データの定義
const reservations = ref<any[]>([])  // 予約のリスト
const loading = ref(false)     // ローディング状態

// フィルターの定義
const filters = ref({
    startDate: '',
    endDate: '',
    status: ''  // '', 'confirmed', 'cancelled'
})

// ページが表示されたときに実行
onMounted(() => {
    // 今日から30日後までをデフォルトで設定
    const today = new Date()
    const after30Days = new Date()
    after30Days.setDate(today.getDate() + 30)

    filters.value.startDate = formatDate(today)
    filters.value.endDate = formatDate(after30Days)

    // データを取得
    fetchReservations()
})

// 予約データを取得する関数
const fetchReservations = async () => {
    loading.value = true

    try {
        // クエリパラメータを作成
        const params = new URLSearchParams()
        if (filters.value.startDate) {
            params.append('start_date', filters.value.startDate)
        }
        if (filters.value.endDate) {
            params.append('end_date', filters.value.endDate)
        }
        if (filters.value.status) {
            params.append('status', filters.value.status)
        }

        const url = `http://localhost/api/reservations?${params.toString()}`
        const response = await fetchWithAuth(url)

        if (response.ok) {
            const data = await response.json()
            reservations.value = data.data || []
        } else {
            console.error('予約の取得に失敗しました')
            reservations.value = []
        }
    } catch (error) {
        console.error('エラー:', error)
        reservations.value = []
    } finally {
        loading.value = false
    }
}

// 予約をキャンセルする関数
const cancelReservation = async (id: number) => {
    if (!confirm('この予約をキャンセルしますか？')) {
        return
    }

    try {
        const response = await fetchWithAuth(`http://localhost/api/reservations/${id}`, {
            method: 'PUT',
            body: JSON.stringify({
                status: 'cancelled'
            })
        })

        if (response.ok) {
            alert('キャンセルしました')
            // データを再取得
            fetchReservations()
        } else {
            alert('キャンセルに失敗しました')
        }
    } catch (error) {
        console.error('エラー:', error)
        alert('エラーが発生しました')
    }
}

// 予約を復活させる関数
const restoreReservation = async (id: number) => {
    if (!confirm('この予約を復活させますか？')) {
        return
    }

    try {
        const response = await fetchWithAuth(`http://localhost/api/reservations/${id}`, {
            method: 'PUT',
            body: JSON.stringify({
                status: 'confirmed'
            })
        })

        if (response.ok) {
            alert('復活しました')
            // データを再取得
            fetchReservations()
        } else {
            alert('復活に失敗しました')
        }
    } catch (error) {
        console.error('エラー:', error)
        alert('エラーが発生しました')
    }
}

// 日付と時刻を整形して表示する関数
const formatDateTime = (reservation: any) => {
    const slot = reservation.lesson_slot
    if (!slot) return '-'

    const date = slot.date  // "2025-12-25"
    const startTime = slot.start_time.split('T')[1]?.substring(0, 5) || slot.start_time.substring(0, 5)
    const endTime = slot.end_time.split('T')[1]?.substring(0, 5) || slot.end_time.substring(0, 5)

    return `${date} ${startTime}-${endTime}`
}

// ステータスのテキストを返す関数
const getStatusText = (status: string) => {
    return status === 'confirmed' ? '確定' : 'キャンセル'
}

// 日付をYYYY-MM-DD形式にフォーマットする関数
const formatDate = (date: Date) => {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}
</script>

<style scoped>
.reservations-page {
    min-height: 100vh;
    background: #f0f8ff;
    padding: 2rem 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.page-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #2d3748;
    font-size: 2rem;
    margin-bottom: 2rem;
}

.title-icon {
    width: 32px;
    height: 32px;
}

/* フィルター部分 */
.filters {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #333;
}

.filter-group input,
.filter-group select {
    padding: 0.5rem;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #5dade2;
}

.filter-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.5rem;
    background: #5dade2;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
}

.button-icon {
    width: 20px;
    height: 20px;
}

.filter-button:hover {
    background: #4a9eca;
}

/* ローディング */
.loading {
    background: white;
    padding: 3rem;
    border-radius: 12px;
    text-align: center;
    font-size: 1.2rem;
    color: #5dade2;
}

/* テーブル */
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.reservations-table {
    width: 100%;
    border-collapse: collapse;
}

.reservations-table thead {
    background: #5dade2;
}

.reservations-table th {
    padding: 1rem;
    text-align: left;
    color: white;
    font-weight: 600;
}

.reservations-table tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.3s ease;
}

.reservations-table tbody tr:hover {
    background: #f9f9f9;
}

.reservations-table td {
    padding: 1rem;
    color: #333;
    vertical-align: middle;
}

/* ステータスバッジ */
.status-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

.status-badge.confirmed {
    background: #d4edda;
    color: #155724;
}

.status-badge.cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* ボタン */
.cancel-button,
.restore-button {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cancel-button {
    background: #f56565;
    color: white;
}

.cancel-button:hover {
    background: #e53e3e;
}

.restore-button {
    background: #48bb78;
    color: white;
}

.restore-button:hover {
    background: #38a169;
}

/* 予約がない場合 */
.no-data {
    background: white;
    padding: 3rem;
    border-radius: 12px;
    text-align: center;
    color: #999;
    font-size: 1.1rem;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .filters {
        flex-direction: column;
    }

    .filter-group {
        width: 100%;
    }

    .filter-button {
        width: 100%;
    }

    .table-container {
        overflow-x: auto;
    }

    .reservations-table {
        min-width: 600px;
    }
}
</style>