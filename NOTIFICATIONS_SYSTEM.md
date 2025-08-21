# Sistema de Notificações - OnHCP

Este documento descreve o sistema completo de notificações implementado para a plataforma OnHCP, incluindo notificações em tempo real, preferências de usuário e integração com eventos de simulados.

## 📋 Funcionalidades Implementadas

### ✅ Backend (Laravel)

1. **Modelo e Migração de Notificações**
   - Tabela `notifications` com campos para tipo, título, mensagem, dados, prioridade e status
   - Relacionamento com usuários
   - Suporte a agendamento de notificações

2. **NotificationService**
   - Serviço centralizado para criação e envio de notificações
   - Métodos específicos para eventos de simulados:
     - `simuladoAssigned()` - Simulado atribuído
     - `simuladoCompleted()` - Simulado concluído
     - `simuladoResult()` - Resultado disponível
     - `simuladoDeadlineReminder()` - Lembrete de prazo
   - Integração com email e broadcasting

3. **API Endpoints**
   - `GET /api/notifications` - Listar notificações
   - `GET /api/notifications/unread-count` - Contador de não lidas
   - `GET /api/notifications/stats` - Estatísticas
   - `POST /api/notifications/mark-all-read` - Marcar todas como lidas
   - `PATCH /api/notifications/{id}/read` - Marcar como lida
   - `DELETE /api/notifications/{id}` - Remover notificação

4. **Preferências de Notificação**
   - Coluna `notification_preferences` na tabela `users`
   - API para gerenciar preferências:
     - `GET /api/notification-preferences` - Obter preferências
     - `PUT /api/notification-preferences` - Atualizar preferências
     - `POST /api/notification-preferences/reset` - Resetar para padrão
     - `GET /api/notification-preferences/settings` - Configurações disponíveis
   - Suporte a horário de silêncio

5. **Notificações em Tempo Real**
   - Evento `NotificationSent` para broadcasting
   - Canal privado `user.{userId}` para cada usuário
   - Integração automática com criação de notificações

6. **Integração com Eventos de Simulados**
   - Notificações automáticas em:
     - Atribuição de simulado (`AdminSimuladoAssignmentController`)
     - Submissão de simulado (`SimuladoAttemptService`)
   - Comando Artisan para lembretes de prazo (`simulado:send-deadline-reminders`)
   - Agendamento diário no `Kernel.php`

### ✅ Frontend (Vue.js)

1. **Composables**
   - `useNotifications.js` - Gerenciamento de notificações
   - `useNotificationPreferences.js` - Gerenciamento de preferências
   - `useRealtimeNotifications.js` - WebSockets e notificações em tempo real

2. **Componentes**
   - `NotificationDropdown.vue` - Menu suspenso de notificações na topbar
   - `NotificationPreferences.vue` - Tela de configurações de preferências

3. **Funcionalidades**
   - Exibição de notificações não lidas
   - Marcação individual e em lote como lidas
   - Remoção de notificações
   - Indicador visual de status da conexão WebSocket
   - Sons de notificação (configurável)
   - Navegação automática baseada no tipo de notificação

## 🚀 Como Usar

### Configuração Inicial

1. **Executar Migrações**
   ```bash
   docker-compose exec backend php artisan migrate
   ```

2. **Configurar Broadcasting (Opcional)**
   - Para notificações em tempo real, configure Pusher ou Laravel WebSockets
   - Adicione as variáveis de ambiente necessárias no `.env`

3. **Configurar Agendamento**
   - O comando de lembretes de prazo roda automaticamente às 9h diariamente
   - Certifique-se de que o cron do Laravel está configurado

### Enviando Notificações Programaticamente

```php
use App\Services\NotificationService;
use App\Models\User;
use App\Models\Simulado;

$notificationService = new NotificationService();
$user = User::find(1);
$simulado = Simulado::find(1);

// Notificar sobre simulado atribuído
$notificationService->simuladoAssigned($user, $simulado, [
    'due_date' => now()->addDays(7)
]);

// Notificar sobre resultado
$notificationService->simuladoResult($user, $simulado, $attempt);
```

### Configurando Preferências de Usuário

```javascript
import { useNotificationPreferences } from '@/composables/useNotificationPreferences'

const { updatePreferences } = useNotificationPreferences()

// Atualizar preferências
await updatePreferences({
  simulado_assigned: true,
  simulado_deadline: false,
  email_notifications: true,
  quiet_hours_enabled: true,
  quiet_hours_start: '22:00',
  quiet_hours_end: '08:00'
})
```

### Integrando Notificações em Tempo Real

```javascript
import { useRealtimeNotifications } from '@/composables/useRealtimeNotifications'

const { isConnected, connect, disconnect } = useRealtimeNotifications()

// Conectar automaticamente
connect()

// Escutar eventos customizados
window.addEventListener('notification-received', (event) => {
  const notification = event.detail
  console.log('Nova notificação:', notification)
})
```

## 🔧 Configurações

### Preferências Padrão

```javascript
{
  simulado_assigned: true,      // Simulado atribuído
  simulado_completed: true,     // Simulado concluído
  simulado_result: true,        // Resultado disponível
  simulado_deadline: true,      // Lembretes de prazo
  email_notifications: true,    // Notificações por email
  quiet_hours_enabled: false,   // Horário de silêncio
  quiet_hours_start: '22:00',   // Início do silêncio
  quiet_hours_end: '08:00'      // Fim do silêncio
}
```

### Tipos de Notificação

- `simulado_assigned` - Quando um simulado é atribuído
- `simulado_completed` - Quando um simulado é concluído
- `simulado_result` - Quando o resultado está disponível
- `simulado_deadline` - Lembretes de prazo (7, 3 e 1 dia antes)

### Prioridades

- `normal` - Notificações padrão
- `high` - Notificações importantes (lembretes de prazo)
- `urgent` - Notificações críticas (prazo de 1 dia)

## 🎨 Personalização

### Adicionando Novos Tipos de Notificação

1. **Backend**:
   ```php
   // No modelo Notification
   const TYPE_NEW_TYPE = 'new_type';
   
   // No NotificationService
   public function newTypeNotification(User $user, $data): Notification
   {
       return $this->createNotification(
           $user->id,
           Notification::TYPE_NEW_TYPE,
           'Título',
           'Mensagem',
           $data,
           Notification::PRIORITY_NORMAL
       );
   }
   ```

2. **Frontend**:
   ```javascript
   // Adicionar ao useRealtimeNotifications
   case 'new_type':
     // Lógica de navegação específica
     break;
   ```

### Customizando Sons de Notificação

1. Adicione arquivos de áudio em `public/sounds/`
2. Configure no `useRealtimeNotifications.js`:
   ```javascript
   const soundFile = priority === 'urgent' ? 'urgent.mp3' : 'notification.mp3'
   ```

## 🐛 Troubleshooting

### Notificações não aparecem em tempo real

1. Verifique se o broadcasting está configurado
2. Confirme se o WebSocket está conectado (indicador verde no botão)
3. Verifique o console do navegador para erros

### Emails não são enviados

1. Verifique a configuração de email no `.env`
2. Confirme se as preferências do usuário permitem emails
3. Verifique se não está no horário de silêncio

### Lembretes de prazo não funcionam

1. Confirme se o cron do Laravel está rodando
2. Verifique se o comando está agendado no `Kernel.php`
3. Execute manualmente: `php artisan simulado:send-deadline-reminders`

## 📚 Arquivos Principais

### Backend
- `app/Models/Notification.php`
- `app/Services/NotificationService.php`
- `app/Http/Controllers/NotificationController.php`
- `app/Http/Controllers/Api/NotificationPreferencesController.php`
- `app/Events/NotificationSent.php`
- `app/Console/Commands/SendSimuladoDeadlineReminders.php`
- `routes/channels.php`

### Frontend
- `src/composables/useNotifications.js`
- `src/composables/useNotificationPreferences.js`
- `src/composables/useRealtimeNotifications.js`
- `src/components/notifications/NotificationDropdown.vue`
- `src/components/notifications/NotificationPreferences.vue`

## 🔄 Próximos Passos

1. **Configurar Pusher/WebSockets** para notificações em tempo real em produção
2. **Adicionar testes unitários** para os serviços e componentes
3. **Implementar notificações push** para dispositivos móveis
4. **Adicionar analytics** de engajamento com notificações
5. **Criar templates de email** personalizados para cada tipo de notificação

---

*Sistema implementado com foco em escalabilidade, performance e experiência do usuário.*