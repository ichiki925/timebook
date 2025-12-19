<template>
    <div class="container">
        <div class="header">
            <h1 class="title">レッスン枠管理</h1>
            <button class="create-button" @click="showCreateModal = true">
                ➕ 新規作成
            </button>
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

        <!-- レッスン枠一覧 -->
        <div v-else>
            <div v-if="groupedSlots.length === 0" class="no-slots">
                <p>レッスン枠がありません</p>
                <p class="hint">「新規作成」ボタンから追加してください</p>
            </div>

            <div v-else class="slots-container">
                <!-- 日付グループごとに表示 -->
                <div
                    v-for="group in groupedSlots"
                    :key="group.date"
                    class="date-group"
                >
                    <h2 class="date-header">{{ formatDate(group.date) }}</h2>

                    <div class="slots-table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="col-time">時間</th>
                                    <th class="col-duration">レッスン時間</th>
                                    <th class="col-status">予約状況</th>
                                    <th class="col-actions">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="slot in group.slots" :key="slot.id">
                                    <td class="time">
                                        {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}
                                    </td>
                                    <td>{{ slot.duration }}分</td>
                                    <td>
                                        <span v-if="slot.has_reservation" class="status reserved">予約済み</span>
                                        <span v-else class="status available">空き</span>
                                    </td>
                                    <td class="actions">
                                        <div class="actions-inner">
                                            <button
                                                v-if="!slot.has_reservation"
                                                class="edit-button"
                                                @click="openEditModal(slot)"
                                            >
                                                ✏️ 編集
                                            </button>
                                            <button
                                                v-if="!slot.has_reservation"
                                                class="delete-button"
                                                @click="confirmDelete(slot)"
                                            >
                                                🗑️ 削除
                                            </button>
                                            <span v-else class="disabled-hint">予約済みのため操作不可</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 新規作成モーダル -->
        <div v-if="showCreateModal" class="modal-overlay" @click="closeCreateModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>レッスン枠を作成</h2>
                    <button class="close-button" @click="closeCreateModal">×</button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="createSlot">
                        <div class="form-group">
                            <label>日付 <span class="required">*</span></label>
                            <input
                                v-model="createForm.date"
                                type="date"
                                :min="today"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label>開始時刻 <span class="required">*</span></label>
                            <input
                                v-model="createForm.start_time"
                                type="time"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label>レッスン時間 <span class="required">*</span></label>
                            <select v-model="createForm.duration" required>
                                <option value="">選択してください</option>
                                <option value="30">30分</option>
                                <option value="60">60分</option>
                            </select>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="cancel-button" @click="closeCreateModal">
                                キャンセル
                            </button>
                            <button type="submit" class="submit-button" :disabled="submitting">
                                {{ submitting ? '作成中...' : '作成する' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 編集モーダル -->
        <div v-if="showEditModal" class="modal-overlay" @click="closeEditModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>レッスン枠を編集</h2>
                    <button class="close-button" @click="closeEditModal">×</button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="updateSlot">
                        <div class="form-group">
                            <label>日付 <span class="required">*</span></label>
                            <input
                                v-model="editForm.date"
                                type="date"
                                :min="today"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label>開始時刻 <span class="required">*</span></label>
                            <input
                                v-model="editForm.start_time"
                                type="time"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label>レッスン時間 <span class="required">*</span></label>
                            <select v-model="editForm.duration" required>
                                <option value="30">30分</option>
                                <option value="60">60分</option>
                            </select>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="cancel-button" @click="closeEditModal">
                                キャンセル
                            </button>
                            <button type="submit" class="submit-button" :disabled="submitting">
                                {{ submitting ? '更新中...' : '更新する' }}
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
        <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
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
const slots = ref<any[]>([])
const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// モーダル状態
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingSlot = ref<any>(null)

// フォームデータ
const today = computed(() => {
    const now = new Date()
    return now.toISOString().split('T')[0]
})

const createForm = ref({
    date: today.value,
    start_time: '10:00',
    duration: '60'
})

const editForm = ref({
    date: '',
    start_time: '',
    duration: ''
})

// 日付でグループ化
const groupedSlots = computed(() => {
    const groups: any = {}

    slots.value.forEach(slot => {
        const date = slot.date
        if (!groups[date]) {
            groups[date] = []
        }
        groups[date].push(slot)
    })

    return Object.keys(groups)
        .sort()
        .map(date => ({
            date,
            slots: groups[date]
        }))
})

// ページ読み込み時にデータ取得
onMounted(async () => {
    await loadSlots()
})

// レッスン枠一覧を読み込む
const loadSlots = async () => {
    try {
        loading.value = true
        error.value = ''

        // 今日から30日後までの枠を取得
        const startDate = new Date()
        const endDate = new Date()
        endDate.setDate(endDate.getDate() + 30)

        const response = await fetchWithAuth(
            `http://localhost/api/lesson-slots?start_date=${startDate.toISOString().split('T')[0]}&end_date=${endDate.toISOString().split('T')[0]}`
        )

        if (response.success) {
            // グループ化されたデータをフラットな配列に変換
            const flatSlots: any[] = []
            Object.keys(response.data).forEach(date => {
                response.data[date].forEach((slot: any) => {
                    flatSlots.push({
                        ...slot,
                        date
                    })
                })
            })
            slots.value = flatSlots
        }
    } catch (err: any) {
        console.error('レッスン枠の取得エラー:', err)
        error.value = 'データの読み込みに失敗しました'
    } finally {
        loading.value = false
    }
}

// レッスン枠を作成
const createSlot = async () => {
    try {
        submitting.value = true
        errorMessage.value = ''

        const response = await fetchWithAuth('http://localhost/api/lesson-slots', {
            method: 'POST',
            body: JSON.stringify(createForm.value)
        })

        if (response.success) {
            successMessage.value = 'レッスン枠を作成しました'
            setTimeout(() => { successMessage.value = '' }, 3000)

            closeCreateModal()
            await loadSlots()
        } else {
            errorMessage.value = response.message || 'レッスン枠の作成に失敗しました'
            setTimeout(() => { errorMessage.value = '' }, 5000)
        }
    } catch (err: any) {
        console.error('作成エラー:', err)
        errorMessage.value = '通信エラーが発生しました'
        setTimeout(() => { errorMessage.value = '' }, 5000)
    } finally {
        submitting.value = false
    }
}

// 編集モーダルを開く
const openEditModal = (slot: any) => {
    editingSlot.value = slot
    editForm.value = {
        date: slot.date,
        start_time: slot.start_time.substring(0, 5),
        duration: String(slot.duration)
    }
    showEditModal.value = true
}

// レッスン枠を更新
const updateSlot = async () => {
    try {
        submitting.value = true
        errorMessage.value = ''

        const response = await fetchWithAuth(`http://localhost/api/lesson-slots/${editingSlot.value.id}`, {
            method: 'PUT',
            body: JSON.stringify(editForm.value)
        })

        if (response.success) {
            successMessage.value = 'レッスン枠を更新しました'
            setTimeout(() => { successMessage.value = '' }, 3000)

            closeEditModal()
            await loadSlots()
        } else {
            errorMessage.value = response.message || 'レッスン枠の更新に失敗しました'
            setTimeout(() => { errorMessage.value = '' }, 5000)
        }
    } catch (err: any) {
        console.error('更新エラー:', err)
        errorMessage.value = '通信エラーが発生しました'
        setTimeout(() => { errorMessage.value = '' }, 5000)
    } finally {
        submitting.value = false
    }
}

// 削除確認
const confirmDelete = async (slot: any) => {
    if (!confirm(`${slot.date} ${slot.start_time.substring(0, 5)}のレッスン枠を削除しますか？`)) {
        return
    }
    await deleteSlot(slot.id)
}

// レッスン枠を削除
const deleteSlot = async (id: number) => {
    try {
        errorMessage.value = ''

        const response = await fetchWithAuth(`http://localhost/api/lesson-slots/${id}`, {
            method: 'DELETE'
        })

        if (response.success) {
            successMessage.value = 'レッスン枠を削除しました'
            setTimeout(() => { successMessage.value = '' }, 3000)

            await loadSlots()
        } else {
            errorMessage.value = response.message || 'レッスン枠の削除に失敗しました'
            setTimeout(() => { errorMessage.value = '' }, 5000)
        }
    } catch (err: any) {
        console.error('削除エラー:', err)
        errorMessage.value = '通信エラーが発生しました'
        setTimeout(() => { errorMessage.value = '' }, 5000)
    }
}

// モーダルを閉じる
const closeCreateModal = () => {
    showCreateModal.value = false
    createForm.value = {
        date: today.value,
        start_time: '10:00',
        duration: '60'
    }
}

const closeEditModal = () => {
    showEditModal.value = false
    editingSlot.value = null
}

// 日付をフォーマット
const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    const month = date.getMonth() + 1
    const day = date.getDate()
    const weekdays = ['日', '月', '火', '水', '木', '金', '土']
    const weekday = weekdays[date.getDay()]
    return `${month}月${day}日（${weekday}）`
}

const formatTime = (timeString: string) => {
    if (!timeString) return ''
    // "2025-12-18T14:00:00.000000Z" から "14:00" を抽出
    return timeString.split('T')[1]?.substring(0, 5) || timeString.substring(0, 5)
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
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.title {
    font-size: 2rem;
    font-weight: 700;
    color: #1a202c;
}

.create-button {
    background: linear-gradient(135deg, #667eea 0%, #64b5f6 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.create-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
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

.no-slots {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.no-slots p {
    color: #a0aec0;
    font-size: 1.1rem;
}

.hint {
    margin-top: 0.5rem;
    font-size: 0.9rem;
}

.slots-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.date-group {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.date-header {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2d3748;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #64b5f6 100%);
    color: white;
    margin: 0;
}

.slots-table {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background-color: #f7fafc;
}

th {
    text-align: left;
    padding: 1rem 1.5rem;
    font-weight: 600;
    color: #4a5568;
    border-bottom: 2px solid #e2e8f0;
}

th.col-time {
    width: 25%;
}

th.col-duration {
    width: 12%;
}

th.col-status {
    width: 18%;
}

th.col-actions {
    width: 40%;
}

td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover {
    background-color: #f7fafc;
}

.time {
    font-weight: 600;
    color: #2d3748;
}

.status {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status.available {
    background-color: #c6f6d5;
    color: #22543d;
}

.status.reserved {
    background-color: #fed7d7;
    color: #742a2a;
}

.actions-inner {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.edit-button,
.delete-button {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.edit-button {
    background-color: #bee3f8;
    color: #2c5282;
}

.edit-button:hover {
    background-color: #90cdf4;
}

.delete-button {
    background-color: #fed7d7;
    color: #c53030;
}

.delete-button:hover {
    background-color: #feb2b2;
}

.disabled-hint {
    color: #a0aec0;
    font-size: 0.85rem;
    display: inline-block;
    line-height: 1.4;
}

/* モーダル */
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
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
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
    background: linear-gradient(135deg, #667eea 0%, #64b5f6 100%);
    color: white;
}

.submit-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
}

.submit-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.success-message,
.error-message {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.3s ease-out;
    z-index: 1000;
}

.success-message {
    background-color: #48bb78;
    color: white;
}

.error-message {
    background-color: #f56565;
    color: white;
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