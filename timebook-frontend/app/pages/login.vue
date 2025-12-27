<template>
    <ClientOnly>
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h1>
                        <MusicalNoteIcon class="title-icon" />
                        TimeBook
                    </h1>
                    <p>管理画面ログイン</p>
                </div>

                <form @submit.prevent="handleLogin" class="login-form">
                    <div class="form-group">
                        <label for="email">メールアドレス</label>
                        <input
                            id="email"
                            v-model="email"
                            type="email"
                            required
                            placeholder="test@example.com"
                            class="form-input"
                        />
                    </div>

                    <div class="form-group">
                        <label for="password">パスワード</label>
                        <input
                            id="password"
                            v-model="password"
                            type="password"
                            required
                            placeholder="パスワードを入力"
                            class="form-input"
                        />
                    </div>

                    <div v-if="errorMessage" class="error-message">
                        {{ errorMessage }}
                    </div>

                    <button type="submit" class="login-button" :disabled="loading">
                        {{ loading ? 'ログイン中...' : 'ログイン' }}
                    </button>
                </form>

                <div class="login-footer">
                    <NuxtLink to="/" class="back-link">← トップページに戻る</NuxtLink>
                </div>
            </div>
        </div>
    </ClientOnly>
</template>

<script setup lang="ts">
import { MusicalNoteIcon } from '@heroicons/vue/24/outline'
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

const { login } = useAuth()
const router = useRouter()

const handleLogin = async () => {
    errorMessage.value = ''
    loading.value = true

    try {
        const result = await login(email.value, password.value)

        if (result.success) {
            // ログイン成功 → 管理画面にリダイレクト
            router.push('/admin/dashboard')
        } else {
            errorMessage.value = result.message || 'ログインに失敗しました'
        }
    } catch (error) {
        errorMessage.value = 'ログインに失敗しました'
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.login-container {
    min-height: 100vh;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #5dade2;
    padding: 1rem;
}

.login-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    padding: 2rem;
    width: 100%;
    max-width: 400px;
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-header h1 {
    font-size: 2rem;
    color: #5dade2;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.title-icon {
    width: 2rem;
    height: 2rem;
    color: #5dade2;
}

.login-header p {
    color: #666;
    font-size: 1rem;
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
    color: #333;
}

.form-input {
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-input:focus {
    outline: none;
    border-color: #5dade2;
}

.error-message {
    background: #fee;
    color: #c33;
    padding: 0.75rem;
    border-radius: 8px;
    font-size: 0.9rem;
}

.login-button {
    background: #5dade2;
    color: white;
    padding: 1rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
}

.login-button:hover:not(:disabled) {
    transform: translateY(-2px);
}

.login-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.login-footer {
    margin-top: 1.5rem;
    text-align: center;
}

.back-link {
    color: #5dade2;
    text-decoration: none;
    font-size: 0.9rem;
}

.back-link:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .login-container {
        background: white;
        padding: 2rem 0 0 0;
        min-height: 100vh;
        height: auto;
        display: block;
    }

    .login-card {
        box-shadow: none;
        border-radius: 0;
        padding: 1.5rem 1.5rem;
        max-width: 100%;
    }

    .login-header {
        margin-bottom: 1.5rem;
    }


    .login-header h1 {
        color: #5dade2;
    }

    .login-header p {
        font-size: 0.9rem;
    }

    .login-form {
        gap: 1.25rem;
    }

    .login-button {
        margin-top: 2rem;
    }

}
</style>