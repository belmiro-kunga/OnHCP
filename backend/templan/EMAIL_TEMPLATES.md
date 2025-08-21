# Templates de Email Personalizados - OnHCP

## Visão Geral

O sistema OnHCP agora possui templates de email personalizados para notificações de simulados, proporcionando uma experiência mais rica e profissional para os usuários.

## Templates Disponíveis

### 1. Layout Base (`emails/layout.blade.php`)
- Template base que define a estrutura comum de todos os emails
- Inclui estilos CSS responsivos
- Define seções para cabeçalho, conteúdo e rodapé
- Suporte a diferentes tipos de notificação com cores específicas

### 2. Simulado Atribuído (`emails/simulado-assigned.blade.php`)
- **Tipo**: `simulado_assigned`
- **Uso**: Quando um simulado é atribuído a um usuário
- **Dados disponíveis**:
  - `$user`: Usuário destinatário
  - `$simulado`: Objeto do simulado
  - `$dueDate`: Data limite (se definida)
  - `$maxAttempts`: Número máximo de tentativas

### 3. Lembrete de Prazo (`emails/simulado-deadline.blade.php`)
- **Tipo**: `simulado_deadline`
- **Uso**: Lembretes automáticos de prazo próximo
- **Dados disponíveis**:
  - `$user`: Usuário destinatário
  - `$simulado`: Objeto do simulado
  - `$dueDate`: Data limite
  - `$daysRemaining`: Dias restantes até o prazo

### 4. Resultado Disponível (`emails/simulado-result.blade.php`)
- **Tipos**: `simulado_passed`, `simulado_failed`
- **Uso**: Quando o resultado de um simulado está disponível
- **Dados disponíveis**:
  - `$user`: Usuário destinatário
  - `$simulado`: Objeto do simulado
  - `$score`: Pontuação obtida
  - `$passed`: Boolean indicando aprovação
  - `$minScore`: Pontuação mínima para aprovação
  - `$attempt`: Objeto da tentativa

### 5. Simulado Concluído (`emails/simulado-completed.blade.php`)
- **Tipo**: `simulado_completed`
- **Uso**: Confirmação de conclusão do simulado
- **Dados disponíveis**:
  - `$user`: Usuário destinatário
  - `$simulado`: Objeto do simulado
  - `$submittedAt`: Data/hora de submissão
  - `$attempt`: Objeto da tentativa

## Como Funciona

### 1. Seleção Automática de Template
O `NotificationService` automaticamente seleciona o template correto baseado no tipo de notificação:

```php
private function getEmailTemplate(string $type): ?string
{
    $templates = [
        Notification::TYPE_SIMULADO_ASSIGNED => 'emails.simulado-assigned',
        Notification::TYPE_SIMULADO_DEADLINE => 'emails.simulado-deadline',
        Notification::TYPE_SIMULADO_PASSED => 'emails.simulado-result',
        Notification::TYPE_SIMULADO_FAILED => 'emails.simulado-result',
        Notification::TYPE_SIMULADO_COMPLETED => 'emails.simulado-completed',
    ];

    return $templates[$type] ?? null;
}
```

### 2. Preparação de Dados
Os dados são preparados automaticamente baseados no tipo de notificação:

```php
private function prepareEmailData(User $user, Notification $notification): array
{
    // Dados base sempre disponíveis
    $data = [
        'user' => $user,
        'notification' => $notification,
        'title' => $notification->title,
        'message' => $notification->message,
        'data' => $notification->data,
    ];

    // Dados específicos por tipo...
}
```

### 3. Fallback
Se um template específico não for encontrado, o sistema usa um email simples como fallback.

## Personalização

### Modificando Templates Existentes
1. Edite os arquivos `.blade.php` em `resources/views/emails/`
2. Mantenha as variáveis existentes para compatibilidade
3. Teste as mudanças enviando notificações de teste

### Criando Novos Templates
1. Crie um novo arquivo `.blade.php` em `resources/views/emails/`
2. Estenda o layout base: `@extends('emails.layout')`
3. Adicione o mapeamento no método `getEmailTemplate()`
4. Atualize o método `prepareEmailData()` se necessário

### Exemplo de Novo Template
```blade
@extends('emails.layout')

@section('content')
<div class="notification-content">
    <h2>{{ $title }}</h2>
    <p>{{ $message }}</p>
    
    <!-- Conteúdo específico -->
    @if($simulado)
        <div class="simulado-info">
            <h3>{{ $simulado->title }}</h3>
            <p>{{ $simulado->description }}</p>
        </div>
    @endif
</div>
@endsection
```

## Estilos CSS

### Classes Disponíveis
- `.notification-assigned`: Verde - para simulados atribuídos
- `.notification-deadline`: Laranja - para lembretes de prazo
- `.notification-result`: Azul - para resultados
- `.notification-completed`: Roxo - para simulados concluídos

### Componentes
- `.simulado-info`: Container para informações do simulado
- `.action-button`: Botões de ação principais
- `.stats-grid`: Grid para estatísticas
- `.feedback-section`: Seção de feedback

## Variáveis de Ambiente

Certifique-se de que as seguintes variáveis estão configuradas no `.env`:

```env
# Configuração de Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@onhcp.com
MAIL_FROM_NAME="OnHCP Platform"
```

## Teste dos Templates

### Comando Artisan (Recomendado)
```bash
# Criar comando personalizado para teste
php artisan make:command TestEmailTemplates
```

### Teste Manual
```php
// No Tinker ou controller de teste
$user = User::find(1);
$simulado = Simulado::find(1);
$notificationService = app(NotificationService::class);

// Testar simulado atribuído
$notificationService->simuladoAssigned($user, $simulado, [
    'max_attempts' => 3,
    'due_at' => now()->addDays(7)
]);
```

## Monitoramento

### Logs
Todos os erros de envio de email são registrados:
```php
Log::error('Failed to send notification email', [
    'notification_id' => $notification->id,
    'user_id' => $user->id,
    'error' => $e->getMessage()
]);
```

### Verificação de Entrega
O sistema marca automaticamente quando um email é enviado:
```php
$notification->markEmailAsSent();
```

## Próximos Passos

1. **Teste em Ambiente de Desenvolvimento**
   - Configure SMTP local ou use Mailtrap
   - Teste todos os tipos de notificação

2. **Configuração de Produção**
   - Configure SMTP de produção
   - Teste com usuários reais
   - Monitore logs de erro

3. **Melhorias Futuras**
   - Templates para outros tipos de notificação
   - Suporte a múltiplos idiomas
   - Templates personalizáveis por instituição
   - Estatísticas de abertura de email

## Solução de Problemas

### Email não está sendo enviado
1. Verifique configurações SMTP no `.env`
2. Verifique logs em `storage/logs/laravel.log`
3. Teste conectividade SMTP

### Template não está sendo aplicado
1. Verifique se o arquivo existe em `resources/views/emails/`
2. Verifique mapeamento no `getEmailTemplate()`
3. Limpe cache de views: `php artisan view:clear`

### Dados não aparecem no template
1. Verifique se os dados estão sendo preparados no `prepareEmailData()`
2. Verifique se as variáveis estão sendo passadas corretamente
3. Use `{{ dd($variavel) }}` para debug

---

**Documentação criada em:** {{ date('d/m/Y') }}  
**Versão:** 1.0  
**Autor:** Sistema OnHCP