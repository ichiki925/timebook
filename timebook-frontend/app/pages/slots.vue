<template>
    <div class="container">
        <h1 class="title">利用可能なレッスン枠</h1>

        <div v-if="loading" class="loading">
            <p>読み込み中...</p>
        </div>

        <div v-else-if="error" class="error">
            <p>エラー: {{ error }}</p>
        </div>

        <div v-else class="slots-container">
            <div v-if="slots.length === 0" class="no-slots">
                <p>現在、予約可能なレッスン枠はありません。</p>
            </div>

            <div v-else>
                <!-- 日付グループごとにループ -->
                <div
                    v-for="(group, date) in groupedSlots"
                    :key="date"
                    class="date-group"
                >
                    <!-- 日付の見出し -->
                    <h2 class="date-header">
                        {{ formatDateHeader(date) }}
                    </h2>

                    <!-- その日のレッスン枠 -->
                    <div class="slots-grid">
                        <div
                            v-for="slot in group"
                            :key="slot.id"
                            class="slot-card"
                        >
                            <div class="slot-info">
                                <div class="slot-time">
                                    {{ slot.start_time.substring(0, 5) }} - {{ slot.end_time.substring(0, 5) }}
                                </div>
                                <div class="slot-duration">
                                    {{ slot.duration }}分
                                </div>
                            </div>

                            <button
                                class="reserve-button"
                                @click="openModal(slot)"
                            >
                                予約する
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>予約情報の入力</h2>
                    <button class="close-button" @click="closeModal">×</button>
                </div>
                <div class="modal-body">
                    <!-- 選択したレッスン枠の情報 -->
                    <div class="selected-slot-info">
                        <p><strong>{{ formatDate(selectedSlot.date) }}</strong></p>
                        <p>{{ selectedSlot.start_time.substring(0, 5) }} - {{ selectedSlot.end_time.substring(0, 5) }}（{{ selectedSlot.duration }}分）</p>
                    </div>
                    <form @submit.prevent="submitReservation">
                        <div class="form-group">
                            <label for="name">お名前 <span class="required">*</span></label>
                            <input 
                                id="name"
                                v-model="formData.name"
                                type="text"
                                placeholder="山田 太郎"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">メールアドレス <span class="required">*</span></label>
                            <input 
                                id="email"
                                v-model="formData.email"
                                type="email"
                                placeholder="example@email.com"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="phone">電話番号 <span class="required">*</span></label>
                            <input 
                                id="phone"
                                v-model="formData.phone"
                                type="tel"
                                placeholder="090-1234-5678"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="notes">備考</label>
                            <textarea 
                                id="notes"
                                v-model="formData.notes"
                                rows="3"
                                placeholder="ご要望などがあればご記入ください"
                            ></textarea>
                        </div>

                        <div class="modal-footer">
                            <button 
                                type="button" 
                                class="cancel-button"
                                @click="closeModal"
                            >
                                キャンセル
                            </button>
                            <button 
                                type="submit" 
                                class="submit-button"
                                :disabled="submitting"
                            >
                                {{ submitting ? '予約中...' : '予約を確定する' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- 成功メッセージ -->
        <div v-if="successMessage" class="success-message">
            {{ successMessage }}
        </div>

        <!-- エラーメッセージ -->
        <div v-if="reservationError" class="error-message">
            {{ reservationError }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const slots = ref([])
const loading = ref(true)
const error = ref(null)
const successMessage = ref('')
const reservationError = ref('')

// モーダル関連の状態
const showModal = ref(false)
const selectedSlot = ref(null)
const submitting = ref(false)

// フォームデータ
const formData = ref({
    name: '',
    email: '',
    phone: '',
    notes: ''
})

// 新しく追加：日付でグループ化するcomputed
const groupedSlots = computed(() => {
    const groups = {}

    // slotsを日付ごとに分類
    slots.value.forEach(slot => {
        const date = slot.date

        // その日付のグループがまだなければ作成
        if (!groups[date]) {
            groups[date] = []
        }

        // その日付のグループに追加
        groups[date].push(slot)
    })

    return groups
})


// 日付をフォーマットする関数
function formatDateHeader(dateString) {
    const date = new Date(dateString)
    const month = date.getMonth() + 1
    const day = date.getDate()

    const weekdays = ['日', '月', '火', '水', '木', '金', '土']
    const weekday = weekdays[date.getDay()]

    return `${month}月${day}日（${weekday}）`
}

// 日付をフォーマットする関数（予約成功メッセージ用）
function formatDate(dateString) {
    const date = new Date(dateString)
    const year = date.getFullYear()
    const month = date.getMonth() + 1
    const day = date.getDate()

    const weekdays = ['日', '月', '火', '水', '木', '金', '土']
    const weekday = weekdays[date.getDay()]

    return `${year}年${month}月${day}日（${weekday}）`
}

// モーダルを開く
function openModal(slot) {
    selectedSlot.value = slot
    showModal.value = true

    // フォームをリセット
    formData.value = {
        name: '',
        email: '',
        phone: '',
        notes: ''
    }
}

// モーダルを閉じる
function closeModal() {
    showModal.value = false
    selectedSlot.value = null
}

// 予約を送信
async function submitReservation() {
    successMessage.value = ''
    reservationError.value = ''
    submitting.value = true

    try {
        const response = await fetch('http://localhost/api/reservations/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                lesson_slot_id: selectedSlot.value.id,
                student_name: formData.value.name,
                student_email: formData.value.email,
                student_phone: formData.value.phone,
                course_type: selectedSlot.value.duration,
                notes: formData.value.notes
            })
        })

        const data = await response.json()

        if (response.ok) {
            successMessage.value = `予約が完了しました！${formatDate(selectedSlot.value.date)} ${selectedSlot.value.start_time.substring(0, 5)} - ${selectedSlot.value.end_time.substring(0, 5)}`

            setTimeout(() => {
                successMessage.value = ''
            }, 5000)

            closeModal()
            fetchSlots()
        } else {
            reservationError.value = data.message || '予約に失敗しました'

            setTimeout(() => {
                reservationError.value = ''
            }, 5000)
        }

    } catch (err) {
        reservationError.value = '通信エラーが発生しました: ' + err.message

        setTimeout(() => {
            reservationError.value = ''
        }, 5000)
    } finally {
        submitting.value = false
    }
}

async function fetchSlots() {
    try {
        loading.value = true
        const response = await fetch('http://localhost/api/reservations/available-slots?teacher_id=1')
        const data = await response.json()
        // 各slotにreservingフラグを追加
        slots.value = data.data.map(slot => ({
            ...slot,
            reserving: false
        }))
    } catch (err) {
        error.value = err.message
    } finally {
        loading.value = false
    }
}

fetchSlots()
</script>

<style scoped>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.title {
    font-size: 2rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 2rem;
}

.loading, .error {
    text-align: center;
    padding: 3rem;
    font-size: 1.1rem;
}

.error {
    color: #e53e3e;
    background-color: #fff5f5;
    border-radius: 8px;
}

.no-slots {
    text-align: center;
    padding: 3rem;
    color: #718096;
}

.date-group {
    margin-bottom: 3rem;
}

.date-header {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 3px solid #5dade2;
}

.slots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.slot-card {
    background: #5dade2;
    border-radius: 12px;
    padding: 1.5rem;
    color: white;
    box-shadow: 0 4px 6px rgba(93, 173, 226, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.slot-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 12px rgba(93, 173, 226, 0.3);
}

.slot-info {
    flex: 1;
}

.slot-time {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.slot-duration {
    font-size: 0.9rem;
    opacity: 0.9;
}

.reserve-button {
    background-color: white;
    color: #5dade2;
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
}

.reserve-button:hover {
    background-color: #f7fafc;
    transform: scale(1.02);
}

.reserve-button:active {
    transform: scale(0.98);
}

/* ⭐ モーダルのスタイル */
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
    max-height: 90vh;
    overflow-y: auto;
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
    font-size: 1.5rem;
    font-weight: 700;
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

.selected-slot-info {
    background: #5dade2;
    color: white;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    text-align: center;
}

.selected-slot-info p {
    margin: 0.25rem 0;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.required {
    color: #e53e3e;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #5dade2;
}

.modal-footer {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.cancel-button,
.submit-button {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.cancel-button {
    background-color: #e2e8f0;
    color: #4a5568;
}

.cancel-button:hover {
    background-color: #cbd5e0;
}

.submit-button {
    background: #5dade2;
    color: white;
}

.submit-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(93, 173, 226, 0.4);
}

.submit-button:disabled {
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
    z-index: 1000;
    white-space: pre-line;
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
    z-index: 1000;
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
</style>