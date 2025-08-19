import { ref, computed } from 'vue'

// Estado global dos certificados
const certificates = ref([])
const isGenerating = ref(false)

export function useCertificate() {
  // Dados mock de templates de certificado
  const certificateTemplates = {
    default: {
      id: 'default',
      name: 'Certificado Padrão',
      background: '#ffffff',
      borderColor: '#2563eb',
      titleColor: '#1f2937',
      textColor: '#374151',
      logoUrl: '/logo.png',
      layout: 'standard'
    },
    premium: {
      id: 'premium',
      name: 'Certificado Premium',
      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      borderColor: '#fbbf24',
      titleColor: '#ffffff',
      textColor: '#f3f4f6',
      logoUrl: '/logo-white.png',
      layout: 'premium'
    }
  }

  // Computed
  const userCertificates = computed(() => {
    return certificates.value.filter(cert => cert.userId === 'user_123') // Mock user ID
  })

  const pendingCertificates = computed(() => {
    return userCertificates.value.filter(cert => cert.status === 'pending')
  })

  const issuedCertificates = computed(() => {
    return userCertificates.value.filter(cert => cert.status === 'issued')
  })

  // Funções principais
  const generateCertificate = async (simuladoData, attemptData, userData = null) => {
    if (!simuladoData.certificate || !attemptData.passed) {
      return null
    }

    isGenerating.value = true

    try {
      // Simular delay de geração
      await new Promise(resolve => setTimeout(resolve, 2000))

      const certificateId = `cert_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
      
      const certificate = {
        id: certificateId,
        userId: userData?.id || 'user_123',
        userName: userData?.name || 'João Silva',
        userEmail: userData?.email || 'joao.silva@email.com',
        simuladoId: simuladoData.id,
        simuladoTitle: simuladoData.title,
        attemptId: attemptData.id,
        score: attemptData.score,
        completionDate: attemptData.endTime || new Date().toISOString(),
        issueDate: new Date().toISOString(),
        expiryDate: simuladoData.certificateExpiry ? 
          new Date(Date.now() + (simuladoData.certificateExpiry * 24 * 60 * 60 * 1000)).toISOString() : 
          null,
        template: simuladoData.certificateTemplate || 'default',
        status: 'issued',
        verificationCode: generateVerificationCode(),
        downloadCount: 0,
        metadata: {
          duration: attemptData.duration,
          correctAnswers: attemptData.correctAnswers,
          totalQuestions: simuladoData.questions.length,
          attemptNumber: attemptData.attemptNumber,
          companyName: 'OnHCP',
          companyLogo: '/logo.png'
        }
      }

      // Salvar certificado
      certificates.value.push(certificate)
      saveCertificatesToStorage()

      // Atualizar tentativa com informação do certificado
      updateAttemptWithCertificate(attemptData.id, certificateId)

      return certificate
    } catch (error) {
      console.error('Erro ao gerar certificado:', error)
      throw error
    } finally {
      isGenerating.value = false
    }
  }

  const downloadCertificate = async (certificateId, format = 'pdf') => {
    const certificate = getCertificateById(certificateId)
    if (!certificate) {
      throw new Error('Certificado não encontrado')
    }

    try {
      // Incrementar contador de downloads
      certificate.downloadCount++
      certificate.lastDownload = new Date().toISOString()
      saveCertificatesToStorage()

      // Gerar e baixar o certificado
      if (format === 'pdf') {
        await generatePDFCertificate(certificate)
      } else if (format === 'png') {
        await generateImageCertificate(certificate)
      }

      return true
    } catch (error) {
      console.error('Erro ao baixar certificado:', error)
      throw error
    }
  }

  const verifyCertificate = (verificationCode) => {
    const certificate = certificates.value.find(cert => 
      cert.verificationCode === verificationCode && cert.status === 'issued'
    )

    if (!certificate) {
      return { valid: false, message: 'Código de verificação inválido' }
    }

    // Verificar se o certificado não expirou
    if (certificate.expiryDate && new Date(certificate.expiryDate) < new Date()) {
      return { valid: false, message: 'Certificado expirado' }
    }

    return {
      valid: true,
      certificate: {
        id: certificate.id,
        userName: certificate.userName,
        simuladoTitle: certificate.simuladoTitle,
        score: certificate.score,
        completionDate: certificate.completionDate,
        issueDate: certificate.issueDate,
        expiryDate: certificate.expiryDate,
        companyName: certificate.metadata.companyName
      }
    }
  }

  const revokeCertificate = (certificateId, reason = '') => {
    const certificate = getCertificateById(certificateId)
    if (!certificate) {
      throw new Error('Certificado não encontrado')
    }

    certificate.status = 'revoked'
    certificate.revokedDate = new Date().toISOString()
    certificate.revokeReason = reason

    saveCertificatesToStorage()
    return true
  }

  const getCertificatesBySimulado = (simuladoId) => {
    return userCertificates.value.filter(cert => cert.simuladoId === simuladoId)
  }

  const getCertificateById = (certificateId) => {
    return certificates.value.find(cert => cert.id === certificateId)
  }

  const hasCertificate = (simuladoId, userId = 'user_123') => {
    return certificates.value.some(cert => 
      cert.simuladoId === simuladoId && 
      cert.userId === userId && 
      cert.status === 'issued'
    )
  }

  // Funções auxiliares
  const generateVerificationCode = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
    let result = ''
    for (let i = 0; i < 12; i++) {
      result += chars.charAt(Math.floor(Math.random() * chars.length))
      if (i === 3 || i === 7) result += '-'
    }
    return result
  }

  const generatePDFCertificate = async (certificate) => {
    // Simular geração de PDF
    console.log('Gerando PDF para certificado:', certificate.id)
    
    // Aqui você implementaria a geração real do PDF usando uma biblioteca como jsPDF
    // Por enquanto, vamos simular o download
    const blob = new Blob(['Certificado PDF simulado'], { type: 'application/pdf' })
    const url = URL.createObjectURL(blob)
    
    const link = document.createElement('a')
    link.href = url
    link.download = `certificado_${certificate.simuladoTitle.replace(/\s+/g, '_')}_${certificate.userName.replace(/\s+/g, '_')}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    URL.revokeObjectURL(url)
  }

  const generateImageCertificate = async (certificate) => {
    // Simular geração de imagem
    console.log('Gerando imagem para certificado:', certificate.id)
    
    // Aqui você implementaria a geração real da imagem usando Canvas ou SVG
    // Por enquanto, vamos simular o download
    const canvas = document.createElement('canvas')
    canvas.width = 1200
    canvas.height = 800
    
    const ctx = canvas.getContext('2d')
    const template = certificateTemplates[certificate.template] || certificateTemplates.default
    
    // Desenhar fundo
    if (template.background.startsWith('linear-gradient')) {
      const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height)
      gradient.addColorStop(0, '#667eea')
      gradient.addColorStop(1, '#764ba2')
      ctx.fillStyle = gradient
    } else {
      ctx.fillStyle = template.background
    }
    ctx.fillRect(0, 0, canvas.width, canvas.height)
    
    // Desenhar borda
    ctx.strokeStyle = template.borderColor
    ctx.lineWidth = 10
    ctx.strokeRect(50, 50, canvas.width - 100, canvas.height - 100)
    
    // Desenhar texto
    ctx.fillStyle = template.titleColor
    ctx.font = 'bold 48px Arial'
    ctx.textAlign = 'center'
    ctx.fillText('CERTIFICADO DE CONCLUSÃO', canvas.width / 2, 200)
    
    ctx.fillStyle = template.textColor
    ctx.font = '32px Arial'
    ctx.fillText(`Certificamos que ${certificate.userName}`, canvas.width / 2, 300)
    ctx.fillText(`concluiu com sucesso o simulado`, canvas.width / 2, 350)
    
    ctx.font = 'bold 36px Arial'
    ctx.fillText(`"${certificate.simuladoTitle}"`, canvas.width / 2, 420)
    
    ctx.font = '28px Arial'
    ctx.fillText(`com nota ${certificate.score}%`, canvas.width / 2, 480)
    
    ctx.font = '24px Arial'
    const completionDate = new Date(certificate.completionDate).toLocaleDateString('pt-BR')
    ctx.fillText(`Concluído em ${completionDate}`, canvas.width / 2, 580)
    
    ctx.font = '18px Arial'
    ctx.fillText(`Código de verificação: ${certificate.verificationCode}`, canvas.width / 2, 720)
    
    // Converter para blob e baixar
    canvas.toBlob((blob) => {
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `certificado_${certificate.simuladoTitle.replace(/\s+/g, '_')}_${certificate.userName.replace(/\s+/g, '_')}.png`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      URL.revokeObjectURL(url)
    }, 'image/png')
  }

  const updateAttemptWithCertificate = (attemptId, certificateId) => {
    const attempts = JSON.parse(localStorage.getItem('simulado_attempts') || '[]')
    const attemptIndex = attempts.findIndex(a => a.id === attemptId)
    
    if (attemptIndex !== -1) {
      attempts[attemptIndex].certificateGenerated = true
      attempts[attemptIndex].certificateId = certificateId
      localStorage.setItem('simulado_attempts', JSON.stringify(attempts))
    }
  }

  const saveCertificatesToStorage = () => {
    localStorage.setItem('user_certificates', JSON.stringify(certificates.value))
  }

  const loadCertificatesFromStorage = () => {
    const stored = localStorage.getItem('user_certificates')
    if (stored) {
      try {
        certificates.value = JSON.parse(stored)
      } catch (error) {
        console.error('Erro ao carregar certificados:', error)
        certificates.value = []
      }
    }
  }

  const formatCertificateDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  }

  const getCertificateStatus = (certificate) => {
    if (certificate.status === 'revoked') {
      return { label: 'Revogado', class: 'bg-red-100 text-red-800' }
    }
    
    if (certificate.expiryDate && new Date(certificate.expiryDate) < new Date()) {
      return { label: 'Expirado', class: 'bg-yellow-100 text-yellow-800' }
    }
    
    return { label: 'Válido', class: 'bg-green-100 text-green-800' }
  }

  // Inicializar dados
  loadCertificatesFromStorage()

  return {
    // Estado
    certificates: userCertificates,
    pendingCertificates,
    issuedCertificates,
    isGenerating,
    
    // Templates
    certificateTemplates,
    
    // Funções principais
    generateCertificate,
    downloadCertificate,
    verifyCertificate,
    revokeCertificate,
    
    // Funções de consulta
    getCertificatesBySimulado,
    getCertificateById,
    hasCertificate,
    
    // Funções auxiliares
    formatCertificateDate,
    getCertificateStatus,
    
    // Funções de armazenamento
    saveCertificatesToStorage,
    loadCertificatesFromStorage
  }
}

// Função para auto-geração de certificados após aprovação
export const autoGenerateCertificate = async (simuladoData, attemptData, userData = null) => {
  const { generateCertificate } = useCertificate()
  
  if (simuladoData.certificate && attemptData.passed) {
    try {
      const certificate = await generateCertificate(simuladoData, attemptData, userData)
      console.log('Certificado gerado automaticamente:', certificate?.id)
      return certificate
    } catch (error) {
      console.error('Erro na geração automática do certificado:', error)
      return null
    }
  }
  
  return null
}