<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <header class="bg-primary-600 text-white shadow-lg">
      <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-center">OnHCP - Sistema de Onboarding</h1>
      </div>
    </header>
    <main class="container mx-auto px-4 py-8">
      <router-view></router-view>
    </main>
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>