# Ambiente Docker: Laravel (backend) + Vue (frontend) + MySQL

Este guia monta o ambiente com `docker-compose` e Dockerfiles já adicionados no repositório.

- Backend (PHP 8.2 FPM) em `backend/`
- Nginx servindo o Laravel em `http://localhost:8080`
- Frontend (Vue + Vite) em `frontend/` exposto em `http://localhost:5173`
- MySQL 8 em `localhost:3306` (usuários configurados no compose)

## Pré-requisitos
- Docker e Docker Compose

## Estrutura criada
- `docker-compose.yml`
- `backend/Dockerfile` (PHP-FPM + Composer)
- `backend/docker-php.ini`
- `docker/nginx/default.conf`
- `frontend/Dockerfile` (Node 18 + Vite)

## Primeira execução
1) Build das imagens

```bash
docker compose build
```

2) Subir os serviços

```bash
docker compose up -d
```

Serviços:
- Laravel via Nginx: http://localhost:8080
- Vite dev server: http://localhost:5173
- MySQL: 3306 (host: `mysql` dentro dos containers, `localhost` no host)

Credenciais MySQL (ajuste se quiser em `docker-compose.yml`):
- DB: `app`
- User: `app`
- Pass: `app`
- Root pass: `root`

## Inicializar um novo projeto Laravel (se você ainda não tem o código em `backend/`)
1) Entre no container do backend:

```bash
docker compose exec backend bash
```

2) Crie o projeto Laravel (substitua `app` pelo nome desejado; aqui vamos usar o diretório atual `/var/www`):

```bash
composer create-project laravel/laravel .
```

3) Configure o `.env` do Laravel (edite `backend/.env` no host ou dentro do container) com os valores do MySQL:

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=app
DB_USERNAME=app
DB_PASSWORD=app
```

4) Gere a application key e rode migrations:

```bash
php artisan key:generate
php artisan migrate
```

5) Verifique no navegador: http://localhost:8080

Obs.: O Nginx aponta para `root /var/www/public;` já preparado para Laravel.

## Inicializar um projeto Vue 3 + Vite (se você ainda não tem o código em `frontend/`)
1) Entre no container do frontend (ou rode no host; o container já roda `npm install && npm run dev` por padrão):

```bash
docker compose exec frontend sh
```

2) Crie o projeto dentro de `/app` (ou no host em `frontend/`):

```bash
npm create vite@latest . -- --template vue
npm install
```

3) Inicie o dev server (se não estiver rodando):

```bash
npm run dev -- --host
```

Acesse: http://localhost:5173

## Comunicando Frontend -> Backend
- Em dev, o backend está em `http://localhost:8080`. Configure uma variável no Vite, por exemplo `VITE_API_URL=http://localhost:8080/api` em `frontend/.env`.
- No código Vue, use `import.meta.env.VITE_API_URL`.
- Alternativamente, configure um proxy do Vite em `vite.config.js`:

```js
server: {
  host: true,
  port: 5173,
  proxy: {
    '/api': {
      target: 'http://backend:9000', // php-fpm direto não serve HTTP, prefira Nginx
      changeOrigin: true,
    },
    '/': {
      target: 'http://nginx:80',
      changeOrigin: true,
    },
  },
}
```

Mais simples é apontar direto para `http://localhost:8080` nas chamadas à API.

## Comandos úteis
- Logs dos containers:

```bash
docker compose logs -f --tail=200
```

- Reiniciar containers:

```bash
docker compose restart
```

- Derrubar o ambiente:

```bash
docker compose down
```

- Derrubar e remover volume do MySQL (APAGA DADOS):

```bash
docker compose down -v
```

## Notas
- O volume `db_data` persiste os dados do MySQL.
- Para produção, ajuste o Nginx, desative `npm run dev` (construa com `npm run build`) e sirva arquivos estáticos do build.
