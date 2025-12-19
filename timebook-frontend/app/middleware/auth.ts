// middleware/auth.ts
// 管理者ページへのアクセスを保護するミドルウェア

export default defineNuxtRouteMiddleware((to, from) => {
    // クライアント側でのみ実行
    if (process.client) {
        const authToken = localStorage.getItem('auth_token')

        // 認証トークンがない場合はログインページへリダイレクト
        if (!authToken) {
            return navigateTo('/login')
        }
    }
})