# Portfolio Pessoal — Kaiki Hirata

Aplicação web de portfolio profissional construída em Laravel + Inertia + Vue, com painel administrativo dedicado, blog público, internacionalização (PT/EN) e tema claro/escuro.

> **Status**: arquitetura de deploy e infraestrutura ainda a definir. Este repositório está configurado para rodar **localmente do zero**.

---

## Stack

**Backend**
- PHP 8.4
- Laravel 11
- MySQL 8 ou SQLite (default local/testes)
- Spatie Laravel Translatable (campos JSON multi-locale)

**Frontend**
- Vue 3 (Composition API)
- Inertia.js
- Tailwind CSS 3
- Vue I18n (PT-BR / EN)
- Marked + DOMPurify (renderização sanitizada de Markdown no blog)

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

---

## Arquitetura da Aplicação

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
       Banco de dados
```

Controllers permanecem finos: delegam para Services e retornam responses. Toda regra de negócio (resolução de slug único, substituição segura de imagem, derivação de `published_at`) vive em `app/Services/`.

### Persistência

Imagens vão para o disco configurado em `FILESYSTEM_DISK` (`public`/`local` em desenvolvimento). Sessões, cache e filas usam o driver `database` por padrão.

### Design Patterns

Service Layer, Repository (via Eloquent), Singleton (Profile), Observer (cleanup de imagens órfãs), Form Request, Facade, Dependency Injection, Strategy (locale + dark mode), Middleware Pipeline.

---

## Desenvolvimento Local

### Pré-requisitos

- PHP 8.2+ (recomendado 8.4)
- Composer 2
- Node 20+
- SQLite (default) ou MySQL 8

### Setup

```bash
git clone https://github.com/kaikiyuuji/my-portfolio-laravel.git
cd my-portfolio-laravel

cp .env.example .env
php artisan key:generate

composer install
npm install

# Banco — escolher um:
# (a) SQLite local (default do .env.example)
touch database/database.sqlite

# (b) MySQL local
# editar .env: DB_CONNECTION=mysql e descomentar DB_HOST/DB_DATABASE/...

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

Após `migrate --seed`, o admin de teste é criado com `email=test@example.com` (apenas em ambiente `local`/`testing`).

---

## Testes

Suite Pest/PHPUnit cobrindo:

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

## Deploy & Infraestrutura

Ainda **não definidos**. CI/CD, containerização e topologia de produção serão decididos em uma etapa posterior. Por ora o projeto roda apenas localmente.

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
└── tests/
    ├── Feature/{Admin,Auth,Middleware,Public}/
    └── Unit/Services/
```

---

## License

MIT.
