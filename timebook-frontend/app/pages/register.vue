<template>
    <div class="container">
        <div class="register-box">
            <h1 class="title">新規会員登録</h1>
            <p class="subtitle">TimeBookの会員登録</p>

            <!-- 成功メッセージ -->
            <div v-if="successMessage" class="success-message">
                {{ successMessage }}
            </div>

            <!-- エラーメッセージ -->
            <div v-if="errorMessages.general" class="error-message">
                {{ errorMessages.general[0] }}
            </div>

            <!-- 会員登録フォーム -->
            <form @submit.prevent="handleRegister" class="register-form">
                <!-- お名前 -->
                <div class="form-group">
                    <label for="name">お名前 <span class="required">*</span></label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="山田太郎"
                        required
                        :disabled="loading"
                    >
                    <span v-if="errorMessages.name" class="field-error">
                        {{ errorMessages.name[0] }}
                    </span>
                </div>

                <!-- メールアドレス -->
                <div class="form-group">
                    <label for="email">メールアドレス <span class="required">*</span></label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="example@email.com"
                        required
                        :disabled="loading"
                    >
                    <span v-if="errorMessages.email" class="field-error">
                        {{ errorMessages.email[0] }}
                    </span>
                </div>

                <!-- パスワード -->
                <div class="form-group">
                    <label for="password">パスワード <span class="required">*</span></label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="8文字以上"
                        required
                        minlength="8"
                        :disabled="loading"
                    >
                    <span v-if="errorMessages.password" class="field-error">
                        {{ errorMessages.password[0] }}
                    </span>
                    <span class="hint">8文字以上で入力してください</span>
                </div>

                <!-- パスワード確認 -->
                <div class="form-group">
                    <label for="password_confirmation">パスワード（確認） <span class="required">*</span></label>
                    <input
                        id="password_confirmation"
                        v-model="form.passwordConfirmation"
                        type="password"
                        placeholder="パスワードを再入力"
                        required
                        minlength="8"
                        :disabled="loading"
                    >
                </div>

                <!-- 登録ボタン -->
                <button
                    type="submit"
                    class="register-button"
                    :disabled="loading"
                >
                    {{ loading ? '登録中...' : '会員登録' }}
                </button>
            </form>

            <!-- ログインリンク -->
            <div class="login-link">
                <p>すでにアカウントをお持ちの方</p>
                <NuxtLink to="/login" class="link">ログイン</NuxtLink>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useStudentAuth } from '~/composables/useStudentAuth'

const { register } = useStudentAuth()

const form = reactive({
    name: '',
    email: '',
    password: '',
    passwordConfirmation: '',
})

const loading = ref(false)
const successMessage = ref('')
const errorMessages = ref<any>({})

// 会員登録処理
async function handleRegister() {
    loading.value = true
    successMessage.value = ''
    errorMessages.value = {}

    const result = await register(
        form.name,
        form.email,
        form.password,
        form.passwordConfirmation
    )

    if (result.success) {
        // 登録成功
        successMessage.value = result.message || '会員登録が完了しました。ログインしてください。'

        // フォームをクリア
        form.name = ''
        form.email = ''
        form.password = ''
        form.passwordConfirmation = ''

        // 3秒後にログインページへリダイレクト
        setTimeout(() => {
            navigateTo('/login')
        }, 3000)
    } else {
        // エラーメッセージを表示
        errorMessages.value = result.errors || {}
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

.register-box {
    background: white;
    border-radius: 16px;
    padding: 3rem;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
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

.success-message {
    background-color: #f0fff4;
    border: 2px solid #9ae6b4;
    color: #2f855a;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
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

.register-form {
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

.required {
    color: #e53e3e;
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
    border-color: #5dade2;
    box-shadow: 0 0 0 3px rgba(93, 173, 226, 0.1);
}

.form-group input:disabled {
    background-color: #f7fafc;
    cursor: not-allowed;
}

.field-error {
    color: #e53e3e;
    font-size: 0.875rem;
    margin-top: -0.25rem;
}

.hint {
    color: #a0aec0;
    font-size: 0.875rem;
    margin-top: -0.25rem;
}

.register-button {
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

.register-button:hover:not(:disabled) {
    background: #4a9fd1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(93, 173, 226, 0.3);
}

.register-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.login-link {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
    text-align: center;
}

.login-link p {
    color: #718096;
    margin: 0 0 0.75rem 0;
    font-size: 0.95rem;
}

.login-link .link {
    color: #5dade2;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: color 0.2s;
}

.login-link .link:hover {
    color: #4a9fd1;
    text-decoration: underline;
}

/* スマホ対応 */
@media (max-width: 640px) {
    .container {
        padding: 0;
        background: white;
        min-height: 100vh;
        display: block;  /* flexをやめる */
    }

    .register-box {
        padding: 1rem 1.5rem 2rem 1.5rem;
        box-shadow: none;
        border-radius: 0;
        max-width: 100%;
    }

    .title {
        font-size: 1.5rem;
        margin: 1.5rem 0 1.5rem 0;
    }

    .subtitle {
        font-size: 0.9rem;
        margin: 0 0 1.5rem 0;
    }
}
</style>