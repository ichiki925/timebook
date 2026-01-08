export const useStudentAuth = () => {
    const config = useRuntimeConfig()
    // 生徒会員の認証状態をグローバルに管理
    const user = useState<any>('student', () => null)
    const token = useState<string | null>('studentToken', () => null)

    /**
     * 初期化: localStorageからトークンとユーザー情報を読み込み
     */
    const initialize = () => {
        if (process.client) {
            const savedToken = localStorage.getItem('studentAuthToken')
            const savedUser = localStorage.getItem('studentUser')

            if (savedToken && savedUser) {
                token.value = savedToken
                user.value = JSON.parse(savedUser)
            }
        }
    }

    /**
     * 会員登録
     */
    const register = async (name: string, email: string, password: string, passwordConfirmation: string) => {
        try {
            const response = await fetch(`${config.public.apiBaseUrl}/student/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name,
                    email,
                    password,
                    password_confirmation: passwordConfirmation,
                }),
            })

            const data = await response.json()

            if (response.ok) {
                return { success: true, message: data.message }
            } else {
                // バリデーションエラーの処理
                const errors = data.errors || { general: [data.message] }
                return { success: false, errors }
            }
        } catch (error) {
            return {
                success: false,
                errors: { general: ['通信エラーが発生しました'] },
            }
        }
    }

    /**
     * ログイン
     */
    const login = async (email: string, password: string) => {
        try {
            const response = await fetch(`${config.public.apiBaseUrl}/student/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password }),
            })

            const data = await response.json()

            if (response.ok) {
                // トークンとユーザー情報を保存
                token.value = data.token
                user.value = data.user

                // localStorageに保存
                if (process.client) {
                localStorage.setItem('studentAuthToken', data.token)
                localStorage.setItem('studentUser', JSON.stringify(data.user))
                }

                return { success: true, user: data.user }
            } else {
                return {
                    success: false,
                    errors: { general: [data.message || 'ログインに失敗しました'] },
                }
            }
        } catch (error) {
            return {
                success: false,
                errors: { general: ['通信エラーが発生しました'] },
            }
        }
    }

    /**
     * ログアウト
     */
    const logout = async () => {
        try {
            // サーバー側のトークンを削除
            if (token.value) {
                await fetch(`${config.public.apiBaseUrl}/student/logout`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Authorization: `Bearer ${token.value}`,
                    },
                })
            }
        } catch (error) {
            console.error('ログアウトエラー:', error)
        } finally {
            // クライアント側のトークンとユーザー情報を削除
            token.value = null
            user.value = null

            if (process.client) {
                localStorage.removeItem('studentAuthToken')
                localStorage.removeItem('studentUser')
            }

        // ログインページにリダイレクト
        await navigateTo('/login')
        }
    }

    /**
     * ユーザー情報を取得（トークンの検証も兼ねる）
     */
    const fetchUser = async () => {
        if (!token.value) return false

        try {
            const response = await fetch(`${config.public.apiBaseUrl}/student/user`, {
                headers: {
                    Authorization: `Bearer ${token.value}`,
                },
            })

            if (response.ok) {
                const data = await response.json()
                user.value = data.user

                // localStorageも更新
                if (process.client) {
                    localStorage.setItem('studentUser', JSON.stringify(data.user))
                }

                return true
            } else {
                // トークンが無効な場合
                token.value = null
                user.value = null

                if (process.client) {
                    localStorage.removeItem('studentAuthToken')
                    localStorage.removeItem('studentUser')
                }

                return false
            }
        } catch (error) {
            console.error('ユーザー情報取得エラー:', error)
            return false
        }
    }

    /**
     * ログインチェック（ページ保護用）
     */
    const requireAuth = async () => {
        initialize()

        if (!token.value) {
            await navigateTo('/login')
            return false
        }

        // トークンの有効性を確認
        const isValid = await fetchUser()
        if (!isValid) {
            await navigateTo('/login')
            return false
        }

        return true
    }

    /**
     * 認証付きAPIリクエスト
     */
    const fetchWithAuth = async (url: string, options: RequestInit = {}) => {
        // トークンがない場合、localStorageから取得を試みる
        let authToken = token.value
        if (!authToken && process.client) {
            authToken = localStorage.getItem('studentAuthToken')
            if (authToken) {
                token.value = authToken
            }
        }

        if (!authToken) {
            throw new Error('認証トークンがありません')
        }

        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                Authorization: `Bearer ${authToken}`,
                ...options.headers,
            },
        })

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        return await response.json()
    }

    // ログイン状態をチェック
    const isAuthenticated = computed(() => !!token.value)

    return {
        user,
        token,
        initialize,
        register,
        login,
        logout,
        fetchUser,
        requireAuth,
        fetchWithAuth,
        isAuthenticated,
    }
}
