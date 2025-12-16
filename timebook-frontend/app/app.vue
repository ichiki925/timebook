<template>
  <div>
    <AppHeader v-if="!isLoginPage" />
    <main class="main-content">
      <NuxtPage />
    </main>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const { restoreAuth } = useAuth()

// ログインページかどうかを判定
const isLoginPage = computed(() => route.path === '/login')

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
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #f5f7fa;
  color: #333;
}

.main-content {
  min-height: calc(100vh - 80px);
}
</style>
