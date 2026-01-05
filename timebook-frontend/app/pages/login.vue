<template>
    <div class="container">
        <div class="login-box">
            <h1 class="title">ログイン</h1>
            <p class="subtitle">会員専用ページへのログイン</p>

            <!-- エラーメッセージ -->
            <div v-if="errorMessage" class="error-message">
                {{ errorMessage }}
            </div>

            <!-- ログインフォーム -->
            <form @submit.prevent="handleLogin" class="login-form">
                <!-- メールアドレス -->
                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="example@email.com"
                        required
                        :disabled="loading"
                    >
                </div>

                <!-- パスワード -->
                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="パスワード"
                        required
                        :disabled="loading"
                    >
                </div>

                <!-- ログインボタン -->
                <button
                    type="submit"
                    class="login-button"
                    :disabled="loading"
                >
                    {{ loading ? 'ログイン中...' : 'ログイン' }}
                </button>
            </form>

            <!-- 会員登録リンク -->
            <div class="register-link">
                <p>アカウントをお持ちでない方</p>
                <NuxtLink to="/register" class="link">新規会員登録</NuxtLink>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useStudentAuth } from '~/composables/useStudentAuth'

const { login, isAuthenticated } = useStudentAuth()

const form = ref({
    email: '',
    password: '',
})

const loading = ref(false)
const errorMessage = ref('')

// 既にログイン済みの場合はトップページにリダイレクト
onMounted(() => {
    if (isAuthenticated.value) {
        navigateTo('/')
    }
})

// ログイン処理
async function handleLogin() {
    loading.value = true
    errorMessage.value = ''

    const result = await login(form.value.email, form.value.password)

    if (result.success) {
        // ログイン成功 - トップページにリダイレクト
        await navigateTo('/')
    } else {
        // エラーメッセージを表示
        if (result.errors?.general) {
            errorMessage.value = result.errors.general[0]
        } else {
            errorMessage.value = 'ログインに失敗しました'
        }
    }

    loading.value = false
}
</script>

<style scoped>
.container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: #faf8f3;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.login-box {
    background: white;
    border-radius: 16px;
    padding: 3rem;
    width: 100%;
    max-width: 450px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.title {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 0.5rem 0;
    text-align: center;
}

.subtitle {
    font-size: 1rem;
    color: #718096;
    margin: 0 0 2rem 0;
    text-align: center;
}

.error-message {
    background-color: #fff5f5;
    border: 2px solid #feb2b2;
    color: #c53030;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
    text-align: center;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: #4a5568;
    font-size: 0.95rem;
}

.form-group input {
    padding: 0.875rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
}

.form-group input:focus {
    outline: none;
    border-color: #f7fafc;
    box-shadow: 0 0 0 3px rgba(93, 173, 226, 0.3);
}

.form-group input:disabled {
    background-color: #f7fafc;
    cursor: not-allowed;
}

.login-button {
    background: #5dade2;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 1rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 0.5rem;
}

.login-button:hover:not(:disabled) {
    background: #4a9fd1;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(93, 173, 226, 0.3);
}

.login-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.register-link {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
    text-align: center;
}

.register-link p {
    color: #718096;
    margin: 0 0 0.75rem 0;
    font-size: 0.95rem;
}

.register-link .link {
    color: #5dade2;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: color 0.2s;
}

.register-link .link:hover {
    color: #4a9fd1;
    text-decoration: underline;
}

/* スマホ対応 */
@media (max-width: 640px) {
    .container {
        padding: 0;
        background: white;  /* スマホでは白背景 */
        min-height: 100vh;
        display: block;
    }

    .login-box {
        padding: 1.5rem 1.5rem 2rem 1.5rem;  /* 上の余白を削減 */
        box-shadow: none;  /* カード形式をやめる */
        border-radius: 0;  /* 角丸をなくす */
        max-width: 100%;
    }

    .title {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .subtitle {
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }
}
</style>