# Portfolio Pessoal — Kaiki Hirata

Aplicação web de portfolio profissional construída em Laravel + Inertia + Vue, com painel administrativo dedicado, blog público, internacionalização (PT/EN), tema claro/escuro e arquitetura de produção containerizada.

> **Live**: [https://kaikihirata.dev](https://kaikihirata.dev)

---

## Stack

**Backend**
- PHP 8.4
- Laravel 11
- MySQL 8 (sessões + cache + queue + dados de aplicação)
- Spatie Laravel Translatable (campos JSON multi-locale)

**Frontend**
- Vue 3 (Composition API)
- Inertia.js
- Tailwind CSS 3
- Vue I18n (PT-BR / EN)
- Marked + DOMPurify (renderização sanitizada de Markdown no blog)

**Infraestrutura**
- Docker multi-stage build (Node 20 → Composer → PHP-FPM 8.4 alpine)
- Nginx 1.27 alpine como front-controller
- Digital Ocean Droplet + DO Spaces (storage S3-compat)
- Let's Encrypt (TLS 1.2/1.3, HSTS, renovação automática)
- GitHub Actions (CI + Deploy + Audit semanal)
- GitHub Container Registry para imagens versionadas

---

## Features

### Painel Administrativo (`/admin`)
- Single-admin com bloqueio de registro público e rate limit no login (5/min por IP+email)
- CRUD completo: Perfil, Stacks, Experiências, Projetos, Redes Sociais, Posts (Blog)
- Reordenação drag-free de listas (stacks/projetos) via Axios + Inertia partial reload
- Upload de imagens com substituição segura (salva novo → atualiza banco → deleta antigo)
- Suporte multi-locale por campo (título PT + título EN, etc.)
- Paginação server-side em listas longas

### Landing Pública (`/`)
- Hero com avatar, headline e bio do banco
- Seções dinâmicas: Stacks em destaque, Experiência, Projetos, Redes Sociais
- Cadeia de fallback de ícones (Simple Icons → Iconify MDI → FA Brands → Logos)
- Auto-contraste em ícones de stacks com base em `prefers-color-scheme`
- Animações `reveal` com guard de `prefers-reduced-motion`

### Blog Público (`/blog`)
- Listagem paginada de posts publicados
- Renderização de Markdown com DOMPurify (XSS-safe contra comprometimento de admin)
- Posts relacionados, tempo estimado de leitura, capa, OG/Twitter meta tags

### Segurança
- HTTPS obrigatório com `URL::forceScheme('https')` em produção
- Cabeçalhos: HSTS, CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy
- Sessões cifradas em banco com `SESSION_SECURE_COOKIE=true` e `SESSION_SAME_SITE=lax`
- Validação via FormRequests dedicados em todas as rotas administrativas

---

## Arquitetura

### Camadas

```
HTTP → Controller (recebe + responde)
       ↓
       FormRequest (validação + autorização)
       ↓
       Service (regra de negócio)
       ↓
       Eloquent Model (persistência)
       ↓
       MySQL
```

Controllers permanecem finos: delegam para Services e retornam responses. Toda regra de negócio (resolução de slug único, substituição segura de imagem, derivação de `published_at`) vive em `app/Services/`.

### Persistência

Imagens vão para o disco `s3` (DO Spaces) em produção, `public` em desenvolvimento — controlado por `FILESYSTEM_DISK`. Sessões e cache vivem na própria base MySQL para sobreviver a deploys (containers efêmeros).

### Design Patterns

Service Layer, Repository (via Eloquent), Singleton (Profile), Observer (cleanup de imagens órfãs), Form Request, Facade, Dependency Injection, Strategy (locale + dark mode), Middleware Pipeline.

---

## Desenvolvimento Local

### Pré-requisitos

- PHP 8.2+ (recomendado 8.4)
- Composer 2
- Node 20+
- MySQL 8 ou SQLite (default em testes)

### Setup

```bash
git clone https://github.com/kaikiyuuji/my-portfolio-laravel.git
cd my-portfolio-laravel

cp .env.example .env
php artisan key:generate

composer install
npm install

# Banco — escolher um:
# (a) SQLite local
touch database/database.sqlite
# editar .env: DB_CONNECTION=sqlite, comentar DB_HOST/DB_DATABASE/...

# (b) MySQL local (XAMPP/Docker/etc)
# editar .env: DB_CONNECTION=mysql, DB_HOST=127.0.0.1, etc.

php artisan migrate --seed

# Subir tudo (server, queue, logs, vite) em paralelo:
composer run dev
```

A aplicação fica disponível em `http://localhost:8000`. Hot reload do Vite em `http://localhost:5173`.

### Comandos úteis

```bash
php artisan serve              # servidor PHP
npm run dev                    # Vite com HMR
npm run build                  # build de produção
php artisan test               # suite Pest/PHPUnit
./vendor/bin/pint              # formatar PHP segundo Pint
./vendor/bin/pint --test       # verificar formatação sem alterar
```

### Criando o admin local

Após `migrate --seed`, o admin de teste é criado com `email=test@example.com` (apenas em ambiente `local`/`testing`). Em produção use `tinker` (ver seção de deploy).

---

## Testes

Suite com **189 testes / 696 asserções** cobrindo:

- Autenticação Breeze adaptada (`/admin/dashboard` ao invés de `/dashboard`)
- Bloqueio incondicional de registro (`EnsureRegistrationIsDisabled`)
- CRUD de cada módulo administrativo (Profile, Stack, Experience, Project, SocialLink, Post)
- Serialização traduzível (`PortfolioTranslatableTest` como guard de regressão)
- Landing pública e blog (`PortfolioTest`, `BlogTest`)

```bash
php artisan test                         # serial
php artisan test --filter=BlogTest       # arquivo específico
```

Tests rodam em SQLite em memória com `RefreshDatabase`. Drivers `array` para cache/session/queue/mail.

---

## Deploy em Produção

### Topologia

```
Internet → Nginx (TLS, headers, static cache) → PHP-FPM 8.4 (Laravel) → MySQL 8
                                              ↓
                                              DO Spaces (S3-compat)
```

Três containers Docker: `nginx`, `app`, `mysql`. Apenas `nginx` expõe portas (`80/443`). Volumes nomeados: `app_code`, `app_storage`, `mysql_data`.

### Pipeline automático

`git push origin main` dispara o workflow `.github/workflows/deploy.yml`:

1. **CI re-run** (`ci.yml` via `workflow_call`): Pint + Pest + Vite build
2. **Docker build**: imagem multi-stage publicada no GHCR (`ghcr.io/kaikiyuuji/my-portfolio-laravel:<sha>` + `:latest`)
3. **Deploy SSH**: `git fetch + reset` no droplet, `mysqldump` pré-deploy, `docker compose pull/up`, `migrate --force`, cache de config/route/view
4. **Smoke test**: `curl https://kaikihirata.dev/up` com retries

Falha em qualquer etapa abortada — produção mantém imagem anterior.

### Workflows complementares

- `ci.yml` — lint + test + build em todo PR e push (exceto `main`)
- `audit.yml` — `composer audit` + `npm audit` semanal (segunda 03:00 UTC)
- `dependabot.yml` — updates semanais (composer, npm, github-actions, docker)

### Backup

`docker/scripts/backup-mysql.sh` agendado via cron diário (`0 2 * * *`): mysqldump + gzip + upload pro DO Spaces. Retenção 14 dias (lifecycle policy do bucket).

### SSL

Let's Encrypt via Certbot (webroot challenge). Renovação automática pelo systemd timer + hook `docker/scripts/certbot-deploy-hook.sh` que faz `nginx -s reload` pós-renovação.

---

## Estrutura do Repositório

```
my-portfolio-laravel/
├── app/
│   ├── Http/Controllers/{Admin,Public}/
│   ├── Http/Requests/Admin/
│   ├── Http/Middleware/
│   ├── Models/                          # User, Profile, Stack, Experience, Project, SocialLink, Post
│   ├── Providers/
│   └── Services/                        # Regra de negócio
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docker/
│   ├── nginx/default.conf               # vhost :80 redirect + :443 SSL
│   ├── php/Dockerfile                   # multi-stage build
│   ├── php/entrypoint.sh                # storage:link + cache pós-mount
│   └── scripts/
│       ├── backup-mysql.sh              # cron diário → DO Spaces
│       └── certbot-deploy-hook.sh       # reload nginx pós-renovação
├── resources/
│   ├── css/app.css
│   ├── js/
│   │   ├── Components/
│   │   ├── Composables/
│   │   ├── Layouts/
│   │   ├── Pages/{Admin,Public,Auth}/
│   │   ├── app.js
│   │   └── i18n.js
│   └── views/app.blade.php
├── routes/{web,auth}.php
├── tests/
│   ├── Feature/{Admin,Auth,Middleware,Public}/
│   └── Unit/Services/
├── .github/
│   ├── workflows/{ci,deploy,audit}.yml
│   └── dependabot.yml
└── docker-compose.yml
```

---

## License

MIT.
