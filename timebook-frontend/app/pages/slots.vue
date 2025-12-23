<template>
    <div class="container">
        <h1 class="title">レッスン予約カレンダー</h1>

        <div v-if="loading" class="loading">
            <p>読み込み中...</p>
        </div>

        <div v-else-if="error" class="error">
            <p>エラー: {{ error }}</p>
        </div>

        <div v-else class="calendar-container">
            <!-- 週の移動ボタン -->
            <div class="week-navigation">
                <button @click="previousWeek" class="nav-button">← 前の週</button>
                <div class="week-info">
                    {{ formatWeekRange() }}
                </div>
                <button @click="nextWeek" class="nav-button">次の週 →</button>
            </div>

            <!-- カレンダー本体 -->
            <div class="calendar">
                <!-- ヘッダー（曜日） -->
                <div class="calendar-header">
                    <div class="time-column-header"></div>
                    <div
                        v-for="(date, index) in weekDates"
                        :key="index"
                        class="day-header"
                    >
                        <div class="day-name">{{ formatDayName(date) }}</div>
                        <div class="day-date">{{ formatDayDate(date) }}</div>
                    </div>
                </div>

                <!-- 時間軸 + レッスン枠 -->
                <div class="calendar-body">
                    <div
                        v-for="(timeSlot, index) in timeSlots"
                        :key="timeSlot.label"
                        class="time-row"
                    >
                        <!-- 時間ラベル -->
                        <div class="time-label">
                            {{ index > 0 && timeSlot.minute === 0 ? timeSlot.label : '' }}
                        </div>

                        <!-- 各曜日のセル -->
                        <div
                            v-for="(date, dateIndex) in weekDates"
                            :key="dateIndex"
                            class="time-cell"
                        >
                            <!-- レッスン枠があれば表示 -->
                            <div
                                v-if="getSlotForCell(date, timeSlot)"
                                class="lesson-slot"
                                :style="{
                                    height: getSlotHeight(getSlotForCell(date, timeSlot).duration) + 'px'
                                }"
                                @click="openModal(getSlotForCell(date, timeSlot))"
                            >
                                <div class="slot-time-display">
                                    {{ getSlotForCell(date, timeSlot).start_time.substring(0, 5) }} - 
                                    {{ getSlotForCell(date, timeSlot).end_time.substring(0, 5) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 予約モーダル（既存のまま） -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>予約情報の入力</h2>
                    <button class="close-button" @click="closeModal">×</button>
                </div>
                <div class="modal-body">
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
const currentWeekStart = ref(new Date())

// 週の開始日を月曜日に設定する関数
function getMonday(date) {
    const d = new Date(date)
    const day = d.getDay()
    const diff = d.getDate() - day + (day === 0 ? -6 : 1) // 日曜日の場合は-6、それ以外は1を足す
    return new Date(d.setDate(diff))
}

// 週の日付を取得（月曜〜日曜）
const weekDates = computed(() => {
    const monday = getMonday(currentWeekStart.value)
    const dates = []

    for (let i = 0; i < 7; i++) {
        const date = new Date(monday)
        date.setDate(monday.getDate() + i)
        dates.push(date)
    }

    return dates
})

// 前の週に移動
function previousWeek() {
    const newDate = new Date(currentWeekStart.value)
    newDate.setDate(newDate.getDate() - 7)
    currentWeekStart.value = newDate
}

// 次の週に移動
function nextWeek() {
    const newDate = new Date(currentWeekStart.value)
    newDate.setDate(newDate.getDate() + 7)
    currentWeekStart.value = newDate
}

// 時間軸の生成（10:00 〜 20:00、30分刻み）
const timeSlots = computed(() => {
    const slots = []
    const startHour = 9  // 開始時刻
    const endHour = 21    // 終了時刻

    for (let hour = startHour; hour <= endHour; hour++) {
        // 00分
        slots.push({
            hour: hour,
            minute: 0,
            label: `${String(hour).padStart(2, '0')}:00`
        })

        // 30分（最後の時間は30分を追加しない）
        if (hour < endHour) {
            slots.push({
                hour: hour,
                minute: 30,
                label: `${String(hour).padStart(2, '0')}:30`
            })
        }
    }

    return slots
})

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

// 週の範囲を表示する関数
function formatWeekRange() {
    const start = weekDates.value[0]
    const end = weekDates.value[6]

    return `${start.getMonth() + 1}月${start.getDate()}日 - ${end.getMonth() + 1}月${end.getDate()}日`
}

// 曜日名を取得
function formatDayName(date) {
    const weekdays = ['日', '月', '火', '水', '木', '金', '土']
    return weekdays[date.getDay()]
}

// 日付を取得
function formatDayDate(date) {
    return `${date.getMonth() + 1}/${date.getDate()}`
}

// 日付を YYYY-MM-DD 形式に変換
function formatDateYYYYMMDD(date) {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

// 特定の日付・時間のレッスン枠を取得
function getSlotForCell(date, timeSlot) {
    const dateString = formatDateYYYYMMDD(date)

    // その日のレッスン枠を探す
    return slots.value.find(slot => {
        // APIの日付形式（2025-12-22T00:00:00.000000Z）を YYYY-MM-DD に変換
        const slotDate = slot.date.substring(0, 10)  // ← この行を追加

        if (slotDate !== dateString) return false  // ← slot.date を slotDate に変更

        // 開始時刻を比較
        const slotStartHour = parseInt(slot.start_time.substring(0, 2))
        const slotStartMinute = parseInt(slot.start_time.substring(3, 5))

        return slotStartHour === timeSlot.hour && slotStartMinute === timeSlot.minute
    })
}

// レッスン枠の高さを計算（duration に応じて）
function getSlotHeight(duration) {
    // 30分 = 1マス（60px）
    // 60分 = 2マス（120px）
    return (duration / 30) * 60
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
    max-width: 1400px;
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

/* 週の移動ボタン */
.week-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.nav-button {
    background: #5dade2;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.nav-button:hover {
    background: #4a9fd5;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(93, 173, 226, 0.3);
}

.week-info {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
}

/* カレンダー本体 */
.calendar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

/* ヘッダー（曜日） */
.calendar-header {
    display: grid;
    grid-template-columns: 80px repeat(7, 1fr);
    background: #f7fafc;
    border-bottom: 2px solid #e2e8f0;
}

.day-header {
    padding: 1rem;
    text-align: center;
    border-left: 1px solid #e2e8f0;
}

.day-name {
    font-size: 0.875rem;
    color: #718096;
    margin-bottom: 0.25rem;
}

.day-date {
    font-size: 1.125rem;
    font-weight: 600;
    color: #2d3748;
}

/* カレンダー本体 */
.calendar-body {
    max-height: 600px;
    overflow-y: auto;
}

.time-row {
    display: grid;
    grid-template-columns: 80px repeat(7, 1fr);
    min-height: 60px;
}

.time-label {
    padding: 0.5rem;
    text-align: right;
    font-size: 0.875rem;
    color: #718096;
    background: #f7fafc;
    border-right: 1px solid #e2e8f0;
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    padding-top: 0;
    transform: translateY(-0.5em);
}

.time-cell {
    border-bottom: 1px solid #e2e8f0;
    border-left: 1px solid #e2e8f0;
    position: relative;
    min-height: 60px;
}

/* モーダルのスタイル（既存のまま） */
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

/* レッスン枠のスタイル */
.lesson-slot {
    position: absolute;
    top: 0;
    left: 4px;
    right: 4px;
    background: linear-gradient(135deg, #5dade2 0%, #48a9d8 100%);
    border-radius: 8px;
    padding: 0.5rem;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(93, 173, 226, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.lesson-slot:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(93, 173, 226, 0.4);
    background: linear-gradient(135deg, #4a9fd5 0%, #3d91c4 100%);
}

.slot-time-display {
    font-size: 0.875rem;
    font-weight: 600;
    text-align: center;
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