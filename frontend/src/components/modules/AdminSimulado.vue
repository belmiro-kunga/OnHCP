<template>
  <section class="p-4 md:p-6">
    <header class="mb-4 flex items-center justify-between">
      <h2 class="text-xl md:text-2xl font-semibold text-text-primary">Simulado</h2>
    </header>

    <div class="rounded-lg border border-border bg-surface p-4 text-text-secondary">
      <p>
        Este é um placeholder para o módulo <strong>Simulado</strong>.
        Personalize este componente em <code>src/components/modules/AdminSimulado.vue</code>.
      </p>
      <ul class="list-disc pl-6 mt-3 space-y-1">
        <li>Listagem de simulados</li>
        <li>Criação/edição de simulado</li>
        <li>Atribuição a utilizadores/grupos</li>
        <li>Relatórios e métricas</li>
      </ul>
    </div>
  </section>
</template>

<script>
export default {
  name: 'AdminSimulado',
}
</script>

<style scoped>
/* Estilos básicos; ajuste conforme o design do projeto */
</style>
