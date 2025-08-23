# Sistema de Matrículas em Cursos

Este documento descreve o sistema completo de matrículas em cursos implementado na plataforma OnHCP.

## Visão Geral

O sistema de matrículas permite que usuários se inscrevam em cursos, acompanhem seu progresso através das aulas, e obtenham certificados de conclusão. Administradores podem gerenciar matrículas e visualizar estatísticas detalhadas.

## Modelos de Dados

### UserCourseEnrollment

Modelo principal que representa a matrícula de um usuário em um curso.

**Campos principais:**
- `user_id`: ID do usuário matriculado
- `course_id`: ID do curso
- `status`: Status da matrícula (active, completed, suspended, cancelled)
- `enrolled_at`: Data/hora da matrícula
- `completed_at`: Data/hora de conclusão (se aplicável)
- `progress_percentage`: Percentual de progresso (0-100)
- `lessons_completed`: Número de aulas concluídas
- `total_lessons`: Total de aulas no curso
- `final_grade`: Nota final (se aplicável)
- `certificate_issued`: Se o certificado foi emitido
- `certificate_issued_at`: Data/hora de emissão do certificado

**Status possíveis:**
- `active`: Matrícula ativa, usuário pode acessar o curso
- `completed`: Curso concluído com sucesso
- `suspended`: Matrícula suspensa temporariamente
- `cancelled`: Matrícula cancelada

### UserLessonProgress

Modelo que rastreia o progresso individual em cada aula.

**Campos principais:**
- `enrollment_id`: ID da matrícula
- `course_lesson_id`: ID da aula
- `started`: Se a aula foi iniciada
- `completed`: Se a aula foi concluída
- `started_at`: Data/hora de início
- `completed_at`: Data/hora de conclusão
- `watch_time_seconds`: Tempo assistido em segundos
- `total_duration_seconds`: Duração total da aula
- `completion_percentage`: Percentual de conclusão da aula
- `attempts`: Número de tentativas/acessos
- `last_accessed_at`: Último acesso à aula

## APIs Disponíveis

### APIs do Usuário

Todas as rotas requerem autenticação (`auth:sanctum`).

#### Matricular-se em um Curso
```http
POST /api/enrollments/courses/{courseId}
```

**Resposta de sucesso:**
```json
{
  "success": true,
  "message": "Matrícula realizada com sucesso",
  "data": {
    "id": 1,
    "user_id": 1,
    "course_id": 1,
    "status": "active",
    "enrolled_at": "2025-08-23T13:00:00Z",
    "progress_percentage": 0
  }
}
```

#### Listar Minhas Matrículas
```http
GET /api/enrollments/my
```

**Parâmetros de consulta opcionais:**
- `status`: Filtrar por status (active, completed, suspended, cancelled)
- `course_id`: Filtrar por curso específico

#### Detalhes de uma Matrícula
```http
GET /api/enrollments/{enrollmentId}
```

**Resposta inclui:**
- Dados da matrícula
- Informações do curso
- Progresso detalhado por aula
- Módulos e estrutura do curso

#### Atualizar Progresso de uma Aula
```http
POST /api/enrollments/{enrollmentId}/lessons/{lessonId}/progress
```

**Corpo da requisição:**
```json
{
  "watch_time_seconds": 1800,
  "completed": true,
  "completion_percentage": 100
}
```

#### Cancelar Matrícula
```http
DELETE /api/enrollments/{enrollmentId}
```

### APIs Administrativas

Todas as rotas requerem autenticação e permissões administrativas.

#### Estatísticas de Matrículas
```http
GET /api/admin/enrollments/statistics
```

**Resposta:**
```json
{
  "total_enrollments": 150,
  "active_enrollments": 120,
  "completed_enrollments": 25,
  "suspended_enrollments": 5,
  "completion_rate": 16.67,
  "popular_courses": [...],
  "recent_enrollments": [...]
}
```

#### Listar Todas as Matrículas
```http
GET /api/admin/enrollments
```

**Parâmetros de consulta:**
- `status`: Filtrar por status
- `course_id`: Filtrar por curso
- `user_id`: Filtrar por usuário
- `page`: Paginação
- `per_page`: Itens por página

#### Matricular Usuário (Admin)
```http
POST /api/admin/enrollments/courses/{courseId}/users/{userId}
```

#### Atualizar Status de Matrícula
```http
PATCH /api/admin/enrollments/{enrollmentId}/status
```

**Corpo da requisição:**
```json
{
  "status": "suspended",
  "reason": "Motivo da suspensão"
}
```

## Funcionalidades dos Modelos

### UserCourseEnrollment

**Métodos de verificação:**
- `isActive()`: Verifica se a matrícula está ativa
- `isCompleted()`: Verifica se o curso foi concluído
- `hasCertificate()`: Verifica se possui certificado

**Métodos de progresso:**
- `calculateProgress()`: Calcula o progresso baseado nas aulas
- `updateProgress()`: Atualiza o progresso automaticamente
- `markAsCompleted()`: Marca como concluído

**Métodos de gestão:**
- `suspend()`: Suspende a matrícula
- `cancel()`: Cancela a matrícula
- `reactivate()`: Reativa a matrícula
- `issueCertificate()`: Emite certificado

**Scopes disponíveis:**
- `active()`: Matrículas ativas
- `completed()`: Matrículas concluídas
- `withCertificate()`: Com certificado emitido
- `enrolledBetween($start, $end)`: Matriculadas em período

### UserLessonProgress

**Métodos principais:**
- `markAsStarted()`: Marca aula como iniciada
- `markAsCompleted()`: Marca aula como concluída
- `updateWatchTime($seconds)`: Atualiza tempo assistido
- `updateLastAccessed()`: Atualiza último acesso
- `resetProgress()`: Reseta o progresso

**Verificações:**
- `isStarted()`: Verifica se foi iniciada
- `isCompleted()`: Verifica se foi concluída

**Acessores:**
- `remaining_time_formatted`: Tempo restante formatado
- `watch_time_formatted`: Tempo assistido formatado

## Relacionamentos

### User Model
- `courseEnrollments()`: Todas as matrículas do usuário
- `enrolledCourses()`: Cursos em que está matriculado
- `activeCourses()`: Cursos ativos
- `completedCourses()`: Cursos concluídos

### Course Model
- `enrollments()`: Todas as matrículas do curso
- `enrolledUsers()`: Usuários matriculados
- `activeUsers()`: Usuários com matrícula ativa
- `completedUsers()`: Usuários que concluíram

**Métodos úteis do Course:**
- `isUserEnrolled($userId)`: Verifica se usuário está matriculado
- `getUserEnrollment($userId)`: Obtém matrícula específica
- `getTotalLessonsAttribute()`: Total de aulas
- `getDurationHoursAttribute()`: Duração em horas
- `getEnrolledCountAttribute()`: Número de matriculados
- `getCompletionRateAttribute()`: Taxa de conclusão

## Fluxo de Uso

### Para Usuários

1. **Matrícula**: Usuário se matricula em um curso disponível
2. **Acesso**: Acessa as aulas do curso em ordem
3. **Progresso**: Sistema rastreia automaticamente o progresso
4. **Conclusão**: Ao completar todas as aulas, curso é marcado como concluído
5. **Certificado**: Certificado é emitido automaticamente (se configurado)

### Para Administradores

1. **Monitoramento**: Visualizam estatísticas e relatórios
2. **Gestão**: Podem matricular usuários manualmente
3. **Controle**: Podem suspender/cancelar matrículas
4. **Análise**: Acompanham taxas de conclusão e engajamento

## Configuração e Instalação

### Migrações

Execute as migrações para criar as tabelas:

```bash
php artisan migrate
```

### Seeders

Para criar dados de exemplo:

```bash
php artisan db:seed --class=EnrollmentSeeder
```

### Rotas

As rotas são automaticamente registradas no arquivo `routes/api.php`.

## Segurança

- Todas as rotas de usuário requerem autenticação
- Usuários só podem acessar suas próprias matrículas
- Administradores têm acesso completo via rotas específicas
- Validações impedem matrículas duplicadas
- Status de matrícula controla acesso ao conteúdo

## Performance

- Índices otimizados para consultas frequentes
- Eager loading configurado nos relacionamentos
- Paginação implementada nas listagens
- Cache pode ser implementado para estatísticas

## Próximos Passos

1. **Sistema de Certificados**: Geração automática de PDFs
2. **Avaliações**: Integração com sistema de quiz/provas
3. **Notificações**: Alertas de progresso e conclusão
4. **Analytics**: Relatórios avançados de engajamento
5. **Gamificação**: Pontos e badges por conclusão

## Troubleshooting

### Problemas Comuns

1. **Matrícula duplicada**: Verificar constraint de unicidade
2. **Progresso não atualiza**: Verificar relacionamentos entre aulas
3. **Certificado não emite**: Verificar se todas as aulas foram concluídas
4. **Performance lenta**: Verificar índices e eager loading

### Logs

O sistema registra eventos importantes:
- Novas matrículas
- Conclusões de curso
- Emissão de certificados
- Suspensões/cancelamentos

### Monitoramento

Recomenda-se monitorar:
- Taxa de conclusão por curso
- Tempo médio de conclusão
- Pontos de abandono nas aulas
- Engajamento por período