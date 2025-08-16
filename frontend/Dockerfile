# Frontend (Vue 3 + Vite)
FROM node:18-alpine

WORKDIR /app

# Instala dependências do SO úteis (opcional)
RUN apk add --no-cache libc6-compat

# Copia package.* se já existir, para cache de dependências (não obrigatório)
# COPY package*.json ./
# RUN npm ci

EXPOSE 5173
CMD ["sh", "-c", "npm install && npm run dev -- --host"]
