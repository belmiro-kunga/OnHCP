# Sistema de Processamento de Vídeo

Este documento descreve o sistema de processamento de vídeo implementado na plataforma de cursos online.

## Visão Geral

O sistema de processamento de vídeo permite:
- Upload de vídeos via multipart upload ou upload direto
- Processamento automático em segundo plano usando AWS MediaConvert
- Transcodificação para múltiplos formatos (MP4, HLS)
- Geração automática de thumbnails
- Rastreamento de progresso e status

## Componentes Principais

### 1. VideoUpload Model
- Armazena metadados dos uploads de vídeo
- Rastreia status do processamento
- Relaciona vídeos com aulas do curso

### 2. VideoUploadService
- Gerencia uploads para AWS S3
- Suporte para multipart upload e upload direto
- Dispara jobs de processamento automaticamente

### 3. ProcessVideoJob
- Job em segundo plano para processamento de vídeo
- Utiliza AWS MediaConvert para transcodificação
- Atualiza status e metadados após processamento

### 4. VideoProcessingService
- Interface com AWS MediaConvert
- Configuração de jobs de transcodificação
- Geração de URLs assinadas

## Configuração

### Variáveis de Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# AWS Configuration
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_s3_bucket

# AWS MediaConvert Configuration
AWS_MEDIACONVERT_ENDPOINT=https://your-endpoint.mediaconvert.region.amazonaws.com
AWS_MEDIACONVERT_ROLE_ARN=arn:aws:iam::account:role/MediaConvertRole
AWS_MEDIACONVERT_QUEUE=Default
```

### Configuração do AWS MediaConvert

1. Crie uma role IAM com permissões para MediaConvert e S3
2. Configure um endpoint do MediaConvert na sua região
3. Crie uma fila (queue) ou use a padrão

## API Endpoints

### Upload de Vídeo

#### Inicializar Upload Multipart
```http
POST /api/video/initialize
Content-Type: application/json

{
    "lessonId": 1,
    "filename": "video.mp4",
    "fileSize": 104857600,
    "contentType": "video/mp4"
}
```

#### Upload Direto
```http
POST /api/video/direct-upload
Content-Type: multipart/form-data

lessonId: 1
video: [arquivo de vídeo]
```

#### Obter Status do Upload
```http
GET /api/video/upload/{uploadId}/status
```

#### Listar Uploads de uma Aula
```http
GET /api/lessons/{lessonId}/uploads
```

## Comandos Artisan

### Listar Uploads de Vídeo
```bash
php artisan video:list
php artisan video:list --status=processing
php artisan video:list --lesson=1
php artisan video:list --limit=20
```

### Processar Vídeo Manualmente
```bash
php artisan video:process {upload_id}
```

### Executar Fila de Jobs
```bash
php artisan queue:work
```

## Status de Processamento

- **pending**: Upload iniciado, aguardando arquivo
- **uploaded**: Arquivo enviado para S3, aguardando processamento
- **processing**: Vídeo sendo processado pelo MediaConvert
- **completed**: Processamento concluído com sucesso
- **failed**: Erro durante o processamento

## Formatos de Saída

### MP4
- Resolução: 1920x1080
- Bitrate: 5 Mbps
- Codec: H.264
- Framerate: 30 fps

### HLS (HTTP Live Streaming)
- Múltiplas qualidades:
  - Alta: 1920x1080 @ 5 Mbps
  - Média: 1280x720 @ 2.5 Mbps
  - Baixa: 854x480 @ 1 Mbps
- Segmentos de 10 segundos

### Thumbnails
- Formato: JPEG
- Resolução: 1280x720
- Capturados a cada 10 segundos
- Máximo de 10 capturas por vídeo

## Monitoramento

### Logs
Os logs de processamento são armazenados em:
- Laravel logs: `storage/logs/laravel.log`
- Job failures: Tabela `failed_jobs`

### Métricas
- Tempo de processamento
- Taxa de sucesso/falha
- Tamanho dos arquivos processados

## Troubleshooting

### Problemas Comuns

1. **Job não executa**
   - Verifique se o worker da fila está rodando
   - Confirme as configurações do banco de dados

2. **Erro de permissão AWS**
   - Verifique as credenciais AWS
   - Confirme as permissões da role do MediaConvert

3. **Processamento falha**
   - Verifique os logs do Laravel
   - Confirme o formato do arquivo de entrada
   - Verifique a configuração do MediaConvert

### Comandos de Debug

```bash
# Verificar status das migrações
php artisan migrate:status

# Verificar jobs falhados
php artisan queue:failed

# Reprocessar jobs falhados
php artisan queue:retry all

# Limpar jobs falhados
php artisan queue:flush
```

## Segurança

- URLs de reprodução são assinadas e têm expiração
- Uploads são validados por tipo de arquivo
- Acesso controlado por autenticação de usuário
- Logs de auditoria para todas as operações

## Performance

- Processamento assíncrono não bloqueia a interface
- Múltiplas qualidades para otimizar bandwidth
- CDN recomendado para distribuição de conteúdo
- Cache de metadados para melhor performance