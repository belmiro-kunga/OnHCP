import { ref } from 'vue'

const toasts = ref([])
let toastId = 0

export function useToast() {
  const showToast = (message, type = 'info', duration = 5000) => {
    const id = ++toastId
    const toast = {
      id,
      message,
      type,
      visible: true
    }
    
    toasts.value.push(toast)
    
    // Auto remove toast after duration
    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }
    
    return id
  }
  
  const removeToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }
  
  const clearToasts = () => {
    toasts.value = []
  }
  
  return {
    toasts,
    showToast,
    removeToast,
    clearToasts
  }
}

// Global toast functions for convenience
export const showToast = (message, type = 'info', duration = 5000) => {
  const { showToast: show } = useToast()
  return show(message, type, duration)
}

export const showSuccess = (message, duration = 5000) => {
  return showToast(message, 'success', duration)
}

export const showError = (message, duration = 5000) => {
  return showToast(message, 'error', duration)
}

export const showWarning = (message, duration = 5000) => {
  return showToast(message, 'warning', duration)
}

export const showInfo = (message, duration = 5000) => {
  return showToast(message, 'info', duration)
}