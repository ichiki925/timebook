export const useAuth = () => {
    const teacher = useState<any>('teacher', () => null)
    const token = useState<string | null>('token', () => null)

    // ログイン処理
    const login = async (email: string, password: string) => {
        try {
            const response = await fetch('http://localhost/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password }),
            })

            const data = await response.json()

            if (data.success) {
                teacher.value = data.data.teacher
                token.value = data.data.token

                // トークンをlocalStorageに保存
                if (process.client) {
                    localStorage.setItem('auth_token', data.data.token)
                    localStorage.setItem('teacher', JSON.stringify(data.data.teacher))
                }

                return { success: true }
            } else {
                return { success: false, message: data.message }
            }
        } catch (error) {
            console.error('Login error:', error)
            return { success: false, message: 'ログインに失敗しました' }
        }
    }

    // ログアウト処理
    const logout = async () => {
        try {
            if (token.value) {
                await fetch('http://localhost/api/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token.value}`,
                    },
                })
            }
        } catch (error) {
            console.error('Logout error:', error)
        } finally {
            teacher.value = null
            token.value = null

            if (process.client) {
                localStorage.removeItem('auth_token')
                localStorage.removeItem('teacher')
            }
        }
    }

    // ログイン状態を復元
    const restoreAuth = () => {
        if (process.client) {
            const savedToken = localStorage.getItem('auth_token')
            const savedTeacher = localStorage.getItem('teacher')

            if (savedToken && savedTeacher) {
                token.value = savedToken
                teacher.value = JSON.parse(savedTeacher)
            }
        }
    }

    // ログイン状態をチェック
    const isAuthenticated = computed(() => !!token.value)

    return {
        teacher,
        token,
        login,
        logout,
        restoreAuth,
        isAuthenticated,
    }
}