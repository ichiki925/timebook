import { useStudentAuth } from './useStudentAuth'

export const useStudentReservation = () => {
    const { token } = useStudentAuth()
    const loading = ref(false)
    const error = ref<string | null>(null)

    /**
     * 会員の予約履歴を取得
     */
    const fetchMyReservations = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await fetch('http://localhost/api/student/reservations', {
                headers: {
                Authorization: `Bearer ${token.value}`,
                },
            })

            const data = await response.json()

            if (response.ok) {
                return { success: true, reservations: data.reservations }
            } else {
                error.value = data.message || '予約履歴の取得に失敗しました'
                return { success: false, error: error.value }
            }
        } catch (err) {
            error.value = '通信エラーが発生しました'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    /**
     * 予約を作成
     */
    const createReservation = async (lessonSlotId: number, notes: string = '') => {
        loading.value = true
        error.value = null

        try {
            const response = await fetch('http://localhost/api/student/reservations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${token.value}`,
                },
                body: JSON.stringify({
                    lesson_slot_id: lessonSlotId,
                    notes,
                }),
            })

            const data = await response.json()

            if (response.ok) {
                return { success: true, reservation: data.reservation, message: data.message }
            } else {
                error.value = data.message || '予約の作成に失敗しました'
                return { success: false, error: error.value }
            }
        } catch (err) {
            error.value = '通信エラーが発生しました'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    /**
     * 予約詳細を取得
     */
    const fetchReservation = async (reservationId: number) => {
        loading.value = true
        error.value = null

        try {
            const response = await fetch(
                `http://localhost/api/student/reservations/${reservationId}`,
                {
                    headers: {
                        Authorization: `Bearer ${token.value}`,
                    },
                }
            )

            const data = await response.json()

            if (response.ok) {
                return { success: true, reservation: data.reservation }
            } else {
                error.value = data.message || '予約詳細の取得に失敗しました'
                return { success: false, error: error.value }
            }
        } catch (err) {
            error.value = '通信エラーが発生しました'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    /**
     * 予約をキャンセル
     */
    const cancelReservation = async (reservationId: number) => {
        loading.value = true
        error.value = null

        try {
            const response = await fetch(
                `http://localhost/api/student/reservations/${reservationId}`,
                {
                    method: 'DELETE',
                    headers: {
                        Authorization: `Bearer ${token.value}`,
                    },
                }
            )

            const data = await response.json()

            if (response.ok) {
                return { success: true, message: data.message }
            } else {
                error.value = data.message || '予約のキャンセルに失敗しました'
                return { success: false, error: error.value }
            }
        } catch (err) {
            error.value = '通信エラーが発生しました'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    return {
        loading,
        error,
        fetchMyReservations,
        createReservation,
        fetchReservation,
        cancelReservation,
    }
}