<template>
  <div id="app">
    <header class="header">
      <h1>OnHCP - Sistema de Onboarding</h1>
    </header>
    <main>
      <router-view />
    </main>
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
}

.header {
  background-color: #42b883;
  color: white;
  padding: 1rem;
  text-align: center;
}

.header h1 {
  margin: 0;
}

main {
  padding: 2rem;
}
</style>