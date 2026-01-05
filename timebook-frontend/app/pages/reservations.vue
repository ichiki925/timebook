<template>
    <div class="container">
        <h1 class="title">予約履歴</h1>

        <!-- ローディング -->
        <div v-if="loading" class="loading">
            <p>読み込み中...</p>
        </div>

        <!-- エラー -->
        <div v-else-if="error" class="error">
            <p>エラー: {{ error }}</p>
        </div>

        <!-- 予約一覧 -->
        <div v-else-if="reservations.length > 0" class="reservations-section">
            <p class="result-count">{{ reservations.length }}件の予約があります</p>

            <div class="reservations-list">
                <div
                    v-for="reservation in reservations"
                    :key="reservation.id"
                    class="reservation-card"
                >
                    <!-- 日付と時間 -->
                    <div class="reservation-header">
                        <h3>{{ formatDate(reservation.lesson_date) }}</h3>
                        <p class="time">
                            {{ reservation.start_time.substring(0, 5) }} -
                            {{ reservation.end_time.substring(0, 5) }}
                        </p>
                    </div>

                    <!-- 予約詳細 -->
                    <div class="reservation-details">
                        <div class="detail-row">
                            <span class="label">お名前:</span>
                            <span>{{ reservation.student_name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">メールアドレス:</span>
                            <span>{{ reservation.student_email }}</span>
                        </div>
                        <div v-if="reservation.notes" class="detail-row">
                            <span class="label">備考:</span>
                            <span>{{ reservation.notes }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">予約日時:</span>
                            <span>{{ formatDateTime(reservation.created_at) }}</span>
                        </div>
                    </div>
                    <!-- キャンセルボタン（confirmed状態の予約のみ表示） -->
                    <div class="card-actions">
                        <button
                            class="cancel-button"
                            @click="openCancelModal(reservation)"
                        >
                            この予約をキャンセル
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 予約がない場合 -->
        <div v-else class="no-reservations">
            <p>まだ予約がありません。</p>
            <NuxtLink to="/" class="link-button">レッスン枠を見る</NuxtLink>
        </div>

        <!-- キャンセル確認モーダル -->
        <div v-if="showCancelModal" class="modal-overlay" @click="closeCancelModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>予約のキャンセル</h2>
                    <button class="close-button" @click="closeCancelModal">×</button>
                </div>
                <div class="modal-body">
                    <div class="warning-box">
                        <p><strong>本当にこの予約をキャンセルしますか？</strong></p>
                    </div>

                    <div class="cancel-info">
                        <p><strong>{{ formatDate(selectedReservation.lesson_date) }}</strong></p>
                        <p>{{ selectedReservation.start_time.substring(0, 5) }} - {{ selectedReservation.end_time.substring(0, 5) }}</p>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="keep-button"
                            @click="closeCancelModal"
                        >
                            予約を保持
                        </button>
                        <button
                            type="button"
                            class="confirm-cancel-button"
                            @click="confirmCancel"
                            :disabled="cancelling"
                        >
                            {{ cancelling ? 'キャンセル中...' : 'キャンセルする' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- 成功メッセージ -->
        <div v-if="successMessage" class="success-message">
            {{ successMessage }}
        </div>

        <!-- キャンセルエラーメッセージ -->
        <div v-if="cancelError" class="error-message">
            {{ cancelError }}
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useStudentAuth } from '~/composables/useStudentAuth'
import { useStudentReservation } from '~/composables/useStudentReservation'

const { requireAuth } = useStudentAuth()
const { fetchMyReservations, cancelReservation } = useStudentReservation()
const reservations = ref([])
const loading = ref(false)
const error = ref(null)


// キャンセル関連の状態
const showCancelModal = ref(false)
const selectedReservation = ref(null)
const cancelling = ref(false)
const successMessage = ref('')
const cancelError = ref('')

// ページ読み込み時に実行
onMounted(async () => {
    // ログインチェック
    const isAuthenticated = await requireAuth()
    if (!isAuthenticated) return

    // 予約履歴を取得
    await loadReservations()
})

// 予約一覧を取得
async function loadReservations() {
    loading.value = true
    error.value = null

    const result = await fetchMyReservations()

    if (result.success) {
        reservations.value = result.reservations
    } else {
        error.value = result.error
    }

    loading.value = false
}

// キャンセルモーダルを開く
function openCancelModal(reservation) {
    selectedReservation.value = reservation
    showCancelModal.value = true
}

// キャンセルモーダルを閉じる
function closeCancelModal() {
    showCancelModal.value = false
    selectedReservation.value = null
}

// ⭐ キャンセルを確定
async function confirmCancel() {
    if (!selectedReservation.value) {
        return
    }

    cancelling.value = true
    successMessage.value = ''
    cancelError.value = ''

    const result = await cancelReservation(selectedReservation.value.id)

    if (result.success) {
        successMessage.value = result.message

        setTimeout(() => {
            successMessage.value = ''
        }, 5000)

        closeCancelModal()

        // 予約一覧を再取得
        await loadReservations()
    } else {
        cancelError.value = result.error

        setTimeout(() => {
            cancelError.value = ''
        }, 5000)
    }

    cancelling.value = false
}


// 日付をフォーマット
function formatDate(dateString) {
    const date = new Date(dateString)
    const year = date.getFullYear()
    const month = date.getMonth() + 1
    const day = date.getDate()

    const weekdays = ['日', '月', '火', '水', '木', '金', '土']
    const weekday = weekdays[date.getDay()]

    return `${year}年${month}月${day}日（${weekday}）`
}

// 日時をフォーマット
function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString)
    const year = date.getFullYear()
    const month = date.getMonth() + 1
    const day = date.getDate()
    const hours = date.getHours().toString().padStart(2, '0')
    const minutes = date.getMinutes().toString().padStart(2, '0')

    return `${year}年${month}月${day}日 ${hours}:${minutes}`
}

</script>

<style scoped>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.title {
    font-size: 1.1rem;
    font-weight: lighter;
    color: #1a202c;
    margin-bottom: 2rem;
}

.loading, .error, .no-reservations {
    text-align: center;
    padding: 3rem;
    font-size: 1.1rem;
}

.error {
    color: #e53e3e;
    background-color: #fff5f5;
    border-radius: 8px;
}

.no-reservations {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.link-button {
    display: inline-block;
    background: #5dade2;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
}

.link-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(93, 173, 226, 0.4);
}

.result-count {
    color: #4a5568;
    margin-bottom: 1.5rem;
    font-size: 1rem;
}

.reservations-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.reservation-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}

.reservation-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.reservation-header {
    margin-bottom: 1.5rem;
}

.reservation-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 0.5rem 0;
}

.reservation-header .time {
    font-size: 1.25rem;
    color: #4a5568;
    margin: 0;
}

.reservation-details {
    border-top: 1px solid #e2e8f0;
    padding-top: 1rem;
}

.detail-row {
    display: flex;
    padding: 0.5rem 0;
}

.detail-row .label {
    font-weight: 600;
    color: #4a5568;
    min-width: 150px;
}

/* キャンセルボタン */
.card-actions {
    border-top: 1px solid #e2e8f0;
    padding-top: 1rem;
    display: flex;
    justify-content: flex-end;
}

.card-actions .cancel-button {
    background-color: #fff;
    color: #e53e3e;
    border: 2px solid #e53e3e;
    border-radius: 8px;
    padding: 0.5rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.card-actions .cancel-button:hover {
    background-color: #e53e3e;
    color: white;
}

/* ⭐ モーダル */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    animation: fadeIn 0.2s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s;
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
    font-size: 1.1rem;
    font-weight: lighter;
    color: #2d3748;
    margin: 0;
}

.close-button {
    background: none;
    border: none;
    font-size: 2rem;
    color: #a0aec0;
    cursor: pointer;
    padding: 0;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}

.close-button:hover {
    background-color: #f7fafc;
    color: #4a5568;
}

.modal-body {
    padding: 1.5rem;
}

.warning-box {
    background-color: #fff5f5;
    border: 2px solid #feb2b2;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    text-align: center;
}

.warning-box p {
    margin: 0;
    color: #c53030;
}

.cancel-info {
    background: #5dade2;
    color: white;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    text-align: center;
}

.cancel-info p {
    margin: 0.25rem 0;
}

.modal-footer {
    display: flex;
    gap: 1rem;
}

.keep-button,
.confirm-cancel-button {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.keep-button {
    background-color: #e2e8f0;
    color: #4a5568;
}

.keep-button:hover {
    background-color: #cbd5e0;
}

.confirm-cancel-button {
    background-color: #e53e3e;
    color: white;
}

.confirm-cancel-button:hover {
    background-color: #c53030;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(229, 62, 62, 0.4);
}

.confirm-cancel-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.success-message {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #48bb78;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.3s ease-out;
    z-index: 1001;
}

.error-message {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #f56565;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.3s ease-out;
    z-index: 1001;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* 既存のスタイルの最後に追加 */

/* スマホ対応（640px以下） */
@media (max-width: 640px) {
    .container {
        padding: 1rem;
    }

    .title {
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .reservation-header h3 {
        font-size: 1.25rem;
    }

    .reservation-header .time {
        font-size: 1rem;
    }

    .detail-row {
        flex-direction: column;
        gap: 0.25rem;
    }

    .detail-row .label {
        min-width: auto;
        font-size: 0.875rem;
    }

    .keep-button,
    .confirm-cancel-button {
        font-size: 0.875rem;
        padding: 0.65rem 1rem;
    }
}
</style>
