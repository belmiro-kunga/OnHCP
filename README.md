# OnHCP
Sistema de onboarde paa a empresa HCP

## Docker (Laravel + Vue + MySQL)

Um ambiente Docker foi adicionado ao repositório.

- Compose e serviços: `docker-compose.yml`
- Backend (Laravel/PHP-FPM): `backend/`
- Frontend (Vue/Vite): `frontend/`
- Nginx: `docker/nginx/default.conf`

Guia completo: veja `DOCKER_SETUP.md`.

Atalhos rápidos:

```bash
# Build das imagens
docker compose build

# Subir em segundo plano
docker compose up -d

# Acessos
# Laravel via Nginx: http://localhost:8080
# Frontend Vite:    http://localhost:5173
