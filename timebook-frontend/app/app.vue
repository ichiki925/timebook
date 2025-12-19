<template>
  <div>
    <!-- ログインページ以外でヘッダーを表示 -->
    <template v-if="!isLoginPage">
      <!-- 管理者ページの場合は AdminHeader -->
      <AdminHeader v-if="isAdminPage" />
      <!-- それ以外は PublicHeader -->
      <PublicHeader v-else />
    </template>

    <main class="main-content">
      <NuxtPage />
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import PublicHeader from '~/components/PublicHeader.vue'
import AdminHeader from '~/components/AdminHeader.vue'

const route = useRoute()
const { restoreAuth } = useAuth()

// ログインページかどうかを判定
const isLoginPage = computed(() => route.path === '/login')

// 管理者ページかどうかを判定（新しく追加）
const isAdminPage = computed(() => route.path.startsWith('/admin'))

onMounted(() => {
  restoreAuth()
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  background: #f5f7fa;
  color: #333;
}

.main-content {
  min-height: calc(100vh - 80px);
}
</style>