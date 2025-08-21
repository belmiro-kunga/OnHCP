# Configuração do Cron para Laravel - OnHCP

Este documento explica como configurar o cron do Laravel para executar tarefas agendadas em produção, incluindo os lembretes automáticos de prazo de simulados.

## 📋 Tarefas Agendadas Configuradas

### ✅ Lembretes de Prazo de Simulados
- **Comando**: `simulado:send-deadline-reminders`
- **Frequência**: Diariamente às 9:00
- **Função**: Envia lembretes automáticos para usuários com simulados próximos do prazo
- **Configuração**: `withoutOverlapping()` e `runInBackground()`

### ✅ Sincronização LDAP (se habilitada)
- **Comando**: `ldap:sync-groups --limit=500`
- **Frequência**: A cada 30 minutos
- **Condição**: Apenas se `LDAP_SYNC_ENABLED=true`

## 🚀 Configuração em Produção

### 1. Configuração do Crontab (Linux/Unix)

```bash
# Editar o crontab
crontab -e

# Adicionar a seguinte linha:
* * * * * cd /caminho/para/seu/projeto && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Configuração com Docker

Se estiver usando Docker, adicione ao seu `docker-compose.yml`:

```yaml
services:
  scheduler:
    build: ./backend
    command: >
      sh -c "while true; do
        php artisan schedule:run
        sleep 60
      done"
    volumes:
      - ./backend/templan:/var/www/html
    depends_on:
      - backend
      - database
```

Ou crie um container separado para o scheduler:

```yaml
services:
  cron:
    build: ./backend
    command: cron -f
    volumes:
      - ./backend/templan:/var/www/html
      - ./docker/cron/laravel-cron:/etc/cron.d/laravel-cron
    depends_on:
      - backend
```

E crie o arquivo `docker/cron/laravel-cron`:

```bash
* * * * * www-data cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Configuração com Supervisor (Recomendado)

Crie o arquivo `/etc/supervisor/conf.d/laravel-scheduler.conf`:

```ini
[program:laravel-scheduler]
process_name=%(program_name)s_%(process_num)02d
command=/bin/bash -c 'while [ true ]; do (php /caminho/para/projeto/artisan schedule:run --verbose --no-interaction &); sleep 60; done'
directory=/caminho/para/projeto
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/laravel-scheduler.log
stopwaitsecs=3600
```

Reiniciar o supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-scheduler:*
```

## 🔧 Verificação e Monitoramento

### Verificar se o Cron está Funcionando

```bash
# Verificar logs do cron
sudo tail -f /var/log/cron

# Verificar logs do Laravel
tail -f storage/logs/laravel.log

# Executar manualmente para testar
php artisan schedule:run --verbose
```

### Monitorar Tarefas Específicas

```bash
# Testar comando de lembretes
php artisan simulado:send-deadline-reminders --verbose

# Listar todas as tarefas agendadas
php artisan schedule:list
```

## 🐛 Troubleshooting

### Problema: Tarefas não executam

1. **Verificar permissões**:
   ```bash
   # Dar permissão de execução
   chmod +x artisan
   
   # Verificar proprietário dos arquivos
   chown -R www-data:www-data /caminho/para/projeto
   ```

2. **Verificar PATH do PHP**:
   ```bash
   # No crontab, usar caminho completo
   * * * * * cd /caminho/para/projeto && /usr/bin/php artisan schedule:run
   ```

3. **Verificar logs de erro**:
   ```bash
   # Redirecionar erros para arquivo
   * * * * * cd /caminho/para/projeto && php artisan schedule:run >> /var/log/laravel-cron.log 2>&1
   ```

### Problema: Lembretes não são enviados

1. **Verificar configuração de email**:
   ```bash
   # Testar envio de email
   php artisan tinker
   Mail::raw('Teste', function($msg) { $msg->to('test@example.com')->subject('Teste'); });
   ```

2. **Verificar dados de simulados**:
   ```bash
   # Verificar simulados com prazo próximo
   php artisan tinker
   \App\Models\Simulado::where('due_date', '>=', now())->where('due_date', '<=', now()->addDays(7))->get();
   ```

3. **Executar comando manualmente**:
   ```bash
   php artisan simulado:send-deadline-reminders --dry-run
   ```

## 📊 Logs e Monitoramento

### Configurar Logs Específicos

Adicione ao `config/logging.php`:

```php
'channels' => [
    // ... outros canais
    
    'scheduler' => [
        'driver' => 'single',
        'path' => storage_path('logs/scheduler.log'),
        'level' => 'info',
    ],
    
    'notifications' => [
        'driver' => 'single',
        'path' => storage_path('logs/notifications.log'),
        'level' => 'info',
    ],
],
```

### Usar nos Commands

```php
// No comando SendSimuladoDeadlineReminders
use Illuminate\Support\Facades\Log;

public function handle()
{
    Log::channel('scheduler')->info('Iniciando envio de lembretes de prazo');
    
    // ... lógica do comando
    
    Log::channel('scheduler')->info("Enviados {$count} lembretes");
}
```

## 🔄 Próximos Passos

1. **Implementar em produção** usando uma das opções acima
2. **Configurar monitoramento** com ferramentas como New Relic ou Sentry
3. **Adicionar alertas** para falhas de execução
4. **Criar dashboard** para acompanhar estatísticas de envio
5. **Implementar retry logic** para falhas temporárias

## 📝 Comandos Úteis

```bash
# Listar todas as tarefas agendadas
php artisan schedule:list

# Executar scheduler uma vez
php artisan schedule:run

# Executar comando específico
php artisan simulado:send-deadline-reminders

# Verificar próxima execução
php artisan schedule:list --next

# Executar em modo debug
php artisan schedule:run --verbose
```

---

*Configuração testada e otimizada para ambiente de produção.*