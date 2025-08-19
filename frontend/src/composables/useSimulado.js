import { ref, computed, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import { autoGenerateCertificate } from './useCertificate'
import { simuladoApi } from './simuladoApi'

export function useSimulado() {
  const router = useRouter()
  
  // Estado do simulado
  const currentSimulado = ref(null)
  const currentQuestion = ref(0)
  const answers = ref({})
  const timeRemaining = ref(0)
  const attemptId = ref(null)
  const isActive = ref(false)
  const isPaused = ref(false)
  const isCompleted = ref(false)
  const showResults = ref(false)
  const results = ref(null)
  const loading = ref(false)
  const error = ref(null)
  
  // Timer
  let timer = null
  
  // Lista de simulados vinda do backend
  const simulados = ref([])
  
  // Histórico de tentativas do usuário
  const userAttempts = ref([
    {
      id: 1,
      simuladoId: 1,
      simuladoTitle: 'Simulado de Segurança no Trabalho',
      date: '2024-01-15T10:00:00Z',
      score: 85,
      passed: true,
      duration: 1800,
      answers: { 1: 'b', 2: 'b', 3: 'a' }
    }
  ])
  
  // Computed properties
  const totalQuestions = computed(() => {
    return currentSimulado.value?.questions?.length || 0
  })
  
  const progress = computed(() => {
    if (totalQuestions.value === 0) return 0
    return Math.round((Object.keys(answers.value).length / totalQuestions.value) * 100)
  })
  
  const canNavigateBack = computed(() => {
    return currentSimulado.value?.allowNavigation && currentQuestion.value > 0
  })
  
  const canNavigateForward = computed(() => {
    return currentQuestion.value < totalQuestions.value - 1
  })
  
  const timeFormatted = computed(() => {
    const hours = Math.floor(timeRemaining.value / 3600)
    const minutes = Math.floor((timeRemaining.value % 3600) / 60)
    const seconds = timeRemaining.value % 60
    
    if (hours > 0) {
      return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
    }
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
  })
  
  const currentQuestionData = computed(() => {
    return currentSimulado.value?.questions?.[currentQuestion.value] || null
  })
  
  // Métodos
  const startTimer = () => {
    if (timer) clearInterval(timer)
    
    timer = setInterval(() => {
      if (timeRemaining.value > 0 && isActive.value && !isPaused.value) {
        timeRemaining.value--
      } else if (timeRemaining.value === 0) {
        finishSimulado()
      }
    }, 1000)
  }
  
  const pauseTimer = () => {
    isPaused.value = true
  }
  
  const resumeTimer = () => {
    isPaused.value = false
  }
  
  const stopTimer = () => {
    if (timer) {
      clearInterval(timer)
      timer = null
    }
    isActive.value = false
    isPaused.value = false
  }
  
  const loadSimulados = async () => {
    loading.value = true
    error.value = null
    try {
      const list = await simuladoApi.list()
      simulados.value = Array.isArray(list) ? list : []
      return simulados.value
    } catch (err) {
      error.value = err?.message || 'Erro ao carregar simulados'
      throw err
    } finally {
      loading.value = false
    }
  }

  const loadSimulado = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const simulado = await simuladoApi.get(id).catch(() => simulados.value.find(s => s.id === id))
      if (!simulado) {
        throw new Error('Simulado não encontrado')
      }
      
      currentSimulado.value = simulado
      return simulado
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }
  
  // Sistema de salvamento de progresso

  
  const clearProgress = (simuladoId) => {
    localStorage.removeItem(`simulado_progress_${simuladoId}`)
  }
  
  const hasProgress = (simuladoId) => {
    return localStorage.getItem(`simulado_progress_${simuladoId}`) !== null
  }
  
  // Auto-save a cada mudança de resposta ou navegação
  const autoSave = () => {
    if (currentSimulado.value?.allowSaveProgress && isActive.value) {
      saveProgress()
    }
  }

  const startSimulado = async (simulado) => {
    currentSimulado.value = simulado
    currentQuestion.value = 0
    answers.value = {}
    timeRemaining.value = simulado.duration
    isActive.value = true
    isPaused.value = false
    isCompleted.value = false
    showResults.value = false
    results.value = null
    
    // Iniciar tentativa no backend
    try {
      const started = await simuladoApi.startAttempt(simulado.id, { resume: false })
      attemptId.value = started?.attemptId || started?.id || null
      if (typeof started?.timeRemaining === 'number') timeRemaining.value = started.timeRemaining
      if (typeof started?.currentQuestion === 'number') currentQuestion.value = started.currentQuestion
      if (started?.answers) answers.value = started.answers
    } catch (_) {
      attemptId.value = null
    }

    startTimer()
  }
  
  const resumeSimulado = async (simulado) => {
    try {
      const resumed = await simuladoApi.startAttempt(simulado.id, { resume: true })
      currentSimulado.value = simulado
      attemptId.value = resumed?.attemptId || resumed?.id || null
      currentQuestion.value = typeof resumed?.currentQuestion === 'number' ? resumed.currentQuestion : 0
      answers.value = resumed?.answers || {}
      timeRemaining.value = typeof resumed?.timeRemaining === 'number' ? resumed.timeRemaining : simulado.duration
      isActive.value = true
      isPaused.value = false
      isCompleted.value = false
      showResults.value = false
      results.value = null
      startTimer()
      return true
    } catch (_) {
      // Fallback: tentar progresso local
      const hasProgressSaved = await loadProgress(simulado.id)
      if (hasProgressSaved && simulado.allowSaveProgress) {
        currentSimulado.value = simulado
        isActive.value = true
        isPaused.value = false
        isCompleted.value = false
        showResults.value = false
        results.value = null
        startTimer()
        return true
      }
      return false
    }
  }
  
  const saveAnswer = (questionId, answer) => {
    answers.value[questionId] = answer
    
    // Auto-save após responder
    autoSave()
  }
  
  const saveProgress = async () => {
    try {
      const payload = {
        currentQuestion: currentQuestion.value,
        answers: answers.value,
        timeRemaining: timeRemaining.value
      }
      if (attemptId.value) {
        await simuladoApi.saveAttempt(attemptId.value, payload)
      }
      // Sempre manter um cache local como fallback
      localStorage.setItem(`simulado_progress_${currentSimulado.value.id}`, JSON.stringify({ simuladoId: currentSimulado.value.id, ...payload }))
    } catch (err) {
      console.error('Erro ao salvar progresso:', err)
    }
  }
  
  const loadProgress = async (simuladoId) => {
    try {
      const saved = localStorage.getItem(`simulado_progress_${simuladoId}`)
      if (saved) {
        const progressData = JSON.parse(saved)
        currentQuestion.value = progressData.currentQuestion
        answers.value = progressData.answers
        timeRemaining.value = progressData.timeRemaining
        return true
      }
      return false
    } catch (err) {
      console.error('Erro ao carregar progresso:', err)
      return false
    }
  }
  
  const nextQuestion = () => {
    if (canNavigateForward.value) {
      currentQuestion.value++
      autoSave()
    }
  }
  
  const previousQuestion = () => {
    if (canNavigateBack.value) {
      currentQuestion.value--
      autoSave()
    }
  }
  
  const goToQuestion = (index) => {
    if (currentSimulado.value?.allowNavigation && index >= 0 && index < totalQuestions.value) {
      currentQuestion.value = index
      autoSave()
    }
  }
  
  const calculateScore = () => {
    if (!currentSimulado.value?.questions) return 0
    
    let correct = 0
    const total = currentSimulado.value.questions.length
    
    currentSimulado.value.questions.forEach(question => {
      if (answers.value[question.id] === question.correctAnswer) {
        correct++
      }
    })
    
    return Math.round((correct / total) * 100)
  }
  
  const finishSimulado = async () => {
    stopTimer()
    isCompleted.value = true
    let finalResult = null
    try {
      if (attemptId.value) {
        finalResult = await simuladoApi.submitAttempt(attemptId.value, answers.value)
      }
    } catch (e) {
      finalResult = null
    }

    if (finalResult) {
      results.value = {
        score: finalResult.score,
        passed: finalResult.passed,
        duration: finalResult.duration,
        totalQuestions: finalResult.totalQuestions,
        correctAnswers: finalResult.correctAnswers,
        wrongAnswers: finalResult.wrongAnswers,
        details: finalResult.details,
        certificateEligible: !!finalResult.certificateEligible,
        certificateId: finalResult.certificateId,
      }
    } else {
      // Fallback local (mock)
      const score = calculateScore()
      const passed = score >= currentSimulado.value.minScore
      const duration = currentSimulado.value.duration - timeRemaining.value
      const detailedResults = currentSimulado.value.questions.map(question => {
        const userAnswer = answers.value[question.id]
        const isCorrect = userAnswer === question.correctAnswer
        return {
          questionId: question.id,
          question: question.question,
          userAnswer,
          correctAnswer: question.correctAnswer,
          isCorrect,
          explanation: currentSimulado.value.showFeedback ? question.explanation : null,
          options: question.options
        }
      })
      results.value = {
        score,
        passed,
        duration,
        totalQuestions: totalQuestions.value,
        correctAnswers: detailedResults.filter(r => r.isCorrect).length,
        wrongAnswers: detailedResults.filter(r => !r.isCorrect).length,
        details: detailedResults,
        certificateEligible: passed && currentSimulado.value.type === 'obrigatorio'
      }
      // Gerar certificado no cliente como fallback
      if (results.value.certificateEligible) {
        try {
          const certificate = await autoGenerateCertificate(
            currentSimulado.value,
            { id: Date.now(), score, duration, correctAnswers: results.value.correctAnswers, attemptNumber: 1 },
            { id: 'user_123', name: 'João Silva', email: 'joao.silva@email.com' }
          )
          if (certificate) {
            results.value.certificateId = certificate.id
          }
        } catch (error) {
          console.error('Erro ao gerar certificado (fallback):', error)
        }
      }
    }

    // Limpar progresso salvo
    if (currentSimulado.value?.id) clearProgress(currentSimulado.value.id)
    showResults.value = true
  }
  
  const generateCertificate = async () => {
    try {
      // Simular geração de certificado
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      const certificate = {
        id: Date.now(),
        simuladoId: currentSimulado.value.id,
        simuladoTitle: currentSimulado.value.title,
        userName: 'Usuário Atual', // Seria obtido do contexto de autenticação
        score: results.value.score,
        date: new Date().toISOString(),
        certificateNumber: `CERT-${Date.now()}`
      }
      
      // Adicionar certificado aos resultados
      results.value.certificate = certificate
      
      console.log('Certificado gerado:', certificate)
    } catch (err) {
      console.error('Erro ao gerar certificado:', err)
    }
  }
  
  const resetSimulado = () => {
    stopTimer()
    currentSimulado.value = null
    currentQuestion.value = 0
    answers.value = {}
    timeRemaining.value = 0
    isActive.value = false
    isPaused.value = false
    isCompleted.value = false
    showResults.value = false
    results.value = null
    error.value = null
  }
  
  const getSimuladoAttempts = (simuladoId) => {
    return userAttempts.value.filter(attempt => attempt.simuladoId === simuladoId)
  }
  
  const canAttemptSimulado = (simulado) => {
    const attempts = getSimuladoAttempts(simulado.id)
    return attempts.length < simulado.maxAttempts
  }
  
  const getLastAttempt = (simuladoId) => {
    const attempts = getSimuladoAttempts(simuladoId)
    return attempts.length > 0 ? attempts[0] : null
  }
  
  // Cleanup
  const cleanup = () => {
    stopTimer()
  }
  
  // Watch para salvar progresso automaticamente
  watch([currentQuestion, answers], () => {
    autoSave()
  }, { deep: true })
  
  return {
    // Estado
    simulados,
    currentSimulado,
    currentQuestion,
    answers,
    timeRemaining,
    attemptId,
    isActive,
    isPaused,
    isCompleted,
    showResults,
    results,
    loading,
    error,
    userAttempts,
    
    // Computed
    totalQuestions,
    progress,
    canNavigateBack,
    canNavigateForward,
    timeFormatted,
    currentQuestionData,
    
    // Métodos
    loadSimulados,
    loadSimulado,
    startSimulado,
    resumeSimulado,
    saveAnswer,
    saveProgress,
    loadProgress,
    clearProgress,
    hasProgress,
    autoSave,
    nextQuestion,
    previousQuestion,
    goToQuestion,
    finishSimulado,
    resetSimulado,
    pauseTimer,
    resumeTimer,
    getSimuladoAttempts,
    canAttemptSimulado,
    getLastAttempt,
    cleanup
  }
}