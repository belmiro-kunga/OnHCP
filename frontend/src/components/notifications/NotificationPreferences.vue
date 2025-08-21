<template>
  <div class="notification-preferences">
    <div class="preferences-header">
      <h3 class="preferences-title">
        <i class="fas fa-cog"></i>
        Preferências de Notificação
      </h3>
      <p class="preferences-subtitle">
        Configure como e quando você deseja receber notificações
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>Carregando preferências...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-triangle"></i>
      <span>{{ error }}</span>
      <button @click="initialize" class="retry-btn">
        <i class="fas fa-redo"></i>
        Tentar novamente
      </button>
    </div>

    <!-- Preferences Form -->
    <form v-else @submit.prevent="handleSave" class="preferences-form">
      <!-- Tipos de Notificação -->
      <div class="preference-section">
        <h4 class="section-title">
          <i class="fas fa-bell"></i>
          Tipos de Notificação
        </h4>
        <p class="section-description">
          Escolha quais tipos de notificação você deseja receber
        </p>
        
        <div class="preference-grid">
          <div 
            v-for="(label, type) in notificationTypes" 
            :key="type"
            class="preference-item"
          >
            <label class="preference-label">
              <input
                type="checkbox"
                v-model="formData[type]"
                class="preference-checkbox"
              >
              <span class="checkbox-custom"></span>
              <div class="preference-info">
                <span class="preference-name">{{ label }}</span>
                <span class="preference-desc">{{ getNotificationDescription(type) }}</span>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Configurações de Email -->
      <div class="preference-section">
        <h4 class="section-title">
          <i class="fas fa-envelope"></i>
          Notificações por Email
        </h4>
        
        <div class="preference-item">
          <label class="preference-label">
            <input
              type="checkbox"
              v-model="formData.email_notifications"
              class="preference-checkbox"
            >
            <span class="checkbox-custom"></span>
            <div class="preference-info">
              <span class="preference-name">Receber emails</span>
              <span class="preference-desc">Receba notificações também por email</span>
            </div>
          </label>
        </div>
      </div>

      <!-- Horário de Silêncio -->
      <div class="preference-section">
        <h4 class="section-title">
          <i class="fas fa-moon"></i>
          Horário de Silêncio
        </h4>
        <p class="section-description">
          Configure um período em que não deseja receber notificações por email
        </p>
        
        <div class="preference-item">
          <label class="preference-label">
            <input
              type="checkbox"
              v-model="formData.quiet_hours_enabled"
              class="preference-checkbox"
            >
            <span class="checkbox-custom"></span>
            <div class="preference-info">
              <span class="preference-name">Ativar horário de silêncio</span>
              <span class="preference-desc">Não enviar emails durante o período configurado</span>
            </div>
          </label>
        </div>

        <div v-if="formData.quiet_hours_enabled" class="quiet-hours-config">
          <div class="time-inputs">
            <div class="time-input-group">
              <label for="quiet-start">Início:</label>
              <input
                id="quiet-start"
                type="time"
                v-model="formData.quiet_hours_start"
                class="time-input"
              >
            </div>
            <div class="time-input-group">
              <label for="quiet-end">Fim:</label>
              <input
                id="quiet-end"
                type="time"
                v-model="formData.quiet_hours_end"
                class="time-input"
              >
            </div>
          </div>
          <p class="quiet-hours-preview">
            <i class="fas fa-info-circle"></i>
            Silêncio ativo das {{ formData.quiet_hours_start }} às {{ formData.quiet_hours_end }}
          </p>
        </div>
      </div>

      <!-- Ações -->
      <div class="preferences-actions">
        <button 
          type="button" 
          @click="handleReset" 
          class="btn btn-secondary"
          :disabled="loading"
        >
          <i class="fas fa-undo"></i>
          Restaurar Padrões
        </button>
        
        <button 
          type="submit" 
          class="btn btn-primary"
          :disabled="loading || !hasChanges"
        >
          <i class="fas fa-save"></i>
          {{ loading ? 'Salvando...' : 'Salvar Preferências' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import { useNotificationPreferences } from '@/composables/useNotificationPreferences'

export default {
  name: 'NotificationPreferences',
  
  setup() {
    const {
      preferences,
      settings,
      loading,
      error,
      isLoaded,
      updatePreferences,
      resetPreferences,
      initialize
    } = useNotificationPreferences()

    const formData = ref({})
    const originalData = ref({})

    // Computed para tipos de notificação
    const notificationTypes = computed(() => {
      return settings.value?.notification_types || {
        simulado_assigned: 'Simulado Atribuído',
        simulado_completed: 'Simulado Concluído',
        simulado_result: 'Resultado do Simulado',
        simulado_deadline: 'Lembrete de Prazo'
      }
    })

    // Verificar se há mudanças
    const hasChanges = computed(() => {
      return JSON.stringify(formData.value) !== JSON.stringify(originalData.value)
    })

    // Observar mudanças nas preferências carregadas
    watch(preferences, (newPreferences) => {
      if (newPreferences) {
        formData.value = { ...newPreferences }
        originalData.value = { ...newPreferences }
      }
    }, { immediate: true })

    /**
     * Obter descrição da notificação
     */
    const getNotificationDescription = (type) => {
      const descriptions = {
        simulado_assigned: 'Quando um novo simulado for atribuído a você',
        simulado_completed: 'Quando você concluir um simulado',
        simulado_result: 'Quando o resultado de um simulado estiver disponível',
        simulado_deadline: 'Lembretes sobre prazos próximos de simulados'
      }
      return descriptions[type] || ''
    }

    /**
     * Salvar preferências
     */
    const handleSave = async () => {
      const success = await updatePreferences(formData.value)
      if (success) {
        originalData.value = { ...formData.value }
      }
    }

    /**
     * Resetar para valores padrão
     */
    const handleReset = async () => {
      if (confirm('Tem certeza que deseja restaurar as configurações padrão?')) {
        const success = await resetPreferences()
        if (success) {
          // As preferências serão atualizadas automaticamente pelo watcher
        }
      }
    }

    // Inicializar ao montar o componente
    onMounted(() => {
      initialize()
    })

    return {
      // Estado
      formData,
      loading,
      error,
      isLoaded,
      
      // Computed
      notificationTypes,
      hasChanges,
      
      // Métodos
      getNotificationDescription,
      handleSave,
      handleReset,
      initialize
    }
  }
}
</script>

<style scoped>
.notification-preferences {
  max-width: 800px;
  margin: 0 auto;
  padding: 24px;
}

.preferences-header {
  margin-bottom: 32px;
  text-align: center;
}

.preferences-title {
  font-size: 24px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.preferences-subtitle {
  color: #6b7280;
  font-size: 14px;
  margin: 0;
}

.loading-state,
.error-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 48px;
  text-align: center;
  color: #6b7280;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid #e5e7eb;
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state {
  color: #dc2626;
  flex-direction: column;
}

.retry-btn {
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
}

.retry-btn:hover {
  background: #2563eb;
}

.preferences-form {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.preference-section {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 24px;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-description {
  color: #6b7280;
  font-size: 14px;
  margin-bottom: 20px;
}

.preference-grid {
  display: grid;
  gap: 16px;
}

.preference-item {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  transition: border-color 0.2s;
}

.preference-item:hover {
  border-color: #d1d5db;
}

.preference-label {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  cursor: pointer;
  width: 100%;
}

.preference-checkbox {
  display: none;
}

.checkbox-custom {
  width: 20px;
  height: 20px;
  border: 2px solid #d1d5db;
  border-radius: 4px;
  position: relative;
  flex-shrink: 0;
  margin-top: 2px;
  transition: all 0.2s;
}

.preference-checkbox:checked + .checkbox-custom {
  background: #3b82f6;
  border-color: #3b82f6;
}

.preference-checkbox:checked + .checkbox-custom::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 12px;
  font-weight: bold;
}

.preference-info {
  flex: 1;
}

.preference-name {
  display: block;
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 4px;
}

.preference-desc {
  display: block;
  font-size: 13px;
  color: #6b7280;
  line-height: 1.4;
}

.quiet-hours-config {
  margin-top: 16px;
  padding: 16px;
  background: #f3f4f6;
  border-radius: 8px;
}

.time-inputs {
  display: flex;
  gap: 16px;
  margin-bottom: 12px;
}

.time-input-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.time-input-group label {
  font-size: 13px;
  font-weight: 500;
  color: #374151;
}

.time-input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
}

.time-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.quiet-hours-preview {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #6b7280;
  margin: 0;
}

.preferences-actions {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover:not(:disabled) {
  background: #e5e7eb;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

@media (max-width: 640px) {
  .notification-preferences {
    padding: 16px;
  }
  
  .preference-section {
    padding: 16px;
  }
  
  .preferences-actions {
    flex-direction: column;
  }
  
  .time-inputs {
    flex-direction: column;
  }
}
</style>