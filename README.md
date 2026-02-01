<p align="center">
  <h1 align="center">Nexora</h1>
  <p align="center">
    Plataforma de Gestão Inteligente — Arquitetura Limpa &amp; API Segura
  </p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php" />
  <img src="https://img.shields.io/badge/Laravel-12+-FF2D20?style=for-the-badge&logo=laravel" />
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js" />
  <img src="https://img.shields.io/badge/MySQL-8+-4479A1?style=for-the-badge&logo=mysql" />
  <img src="https://img.shields.io/badge/Redis-7-DC382D?style=for-the-badge&logo=redis" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" />
</p>

---

## Sobre o Projeto

O **Nexora** é um projeto de estudos focado em **Arquitetura Limpa (SOLID)** e **Segurança em API REST**. Construído como uma SPA com **Vue 3 (Composition API)** consumindo uma **API REST versionada em Laravel 12** com cache Redis.

O objetivo principal é demonstrar, na prática, como organizar um backend escalável seguindo:

- **S**ingle Responsibility — cada camada tem uma única responsabilidade
- **O**pen/Closed — extensão via contratos (interfaces), não via modificação
- **L**iskov Substitution — repositórios substituíveis via Contracts
- **I**nterface Segregation — contratos enxutos por recurso
- **D**ependency Inversion — Controllers dependem de abstrações, não de implementações

---

## Funcionalidades

- Dashboard analítico com Quick Stats, feed de atividades e painel de compromissos
- **Integração com Google Agenda** — conecte o ID da sua agenda via `PUT /api/v1/users/calendar`
- **Sincronização automática de Tarefas** — ao definir `due_date`, um `TaskObserver` cria o evento no Google Calendar do usuário dono da tarefa, de forma assíncrona e silenciosa (falhas são apenas logadas)
- Autenticação JWT com rate limiting (5 tentativas/min no login)
- Controle de acesso por papéis (RBAC — Admin, Manager, User)
- Gestão de Tarefas com `due_date`, conclusão rápida (toggle one-click) e Soft Deletes
- Activity Logs — auditoria completa de eventos CUD
- API REST versionada (`/api/v1/`) com resposta padronizada

---

## Arquitetura

### Camadas por recurso

```
Controller (Api/V1/)
    └── FormRequest (validação + autorização)
    └── DTO (::fromRequest → toArray, sem lógica)
         └── Service (regras de negócio, hash, cache)
              └── Repository (via Contract/interface)
                   └── Model (Eloquent)
```

Cada camada só conhece a imediatamente inferior — Controllers nunca tocam Models diretamente.

### Estrutura backend

```
app/
 ├── Contracts/         # Interfaces dos repositórios
 ├── DTOs/              # Data Transfer Objects (imutáveis via readonly)
 ├── Http/
 │   ├── Controllers/Api/V1/
 │   │   ├── BaseApiController.php   # CRUD genérico via $service + $dtoClass
 │   │   ├── AuthController.php
 │   │   ├── CalendarController.php  # PUT /users/calendar — conecta Google Agenda
 │   │   ├── DashboardController.php
 │   │   ├── TaskController.php      # store/update com FormRequest + PATCH /status
 │   │   └── UserController.php
 │   ├── Middleware/    # JwtMiddleware, RoleMiddleware
 │   └── Requests/      # StoreTaskRequest, UpdateTaskRequest, StoreUserRequest…
 ├── Models/            # Eloquent + casts (due_date → Carbon, google_calendar_id)
 ├── Observers/
 │   ├── ActivityLogObserver.php     # Auditoria CUD em Tasks
 │   └── TaskObserver.php            # Sync Google Calendar em created/updated
 ├── Repositories/      # Implementações concretas dos contratos
 └── Services/
     ├── CalendarService.php         # connect() + syncTaskToCalendar()
     ├── TaskService.php
     └── UserService.php
```

### Fluxo de sincronização automática

```
POST /api/v1/tasks  (com due_date)
    ↓
TaskController::store → TaskService::create → TaskRepository::create
    ↓
Task::create() — Eloquent dispara eventos Eloquent
    ↓
TaskObserver::created(Task $task)
    ↓
CalendarService::syncTaskToCalendar(Task $task)
    → guard 1: task->due_date existe?
    → guard 2: task->user->google_calendar_id existe?
    → guard 3: GOOGLE_CALENDAR_ACCESS_TOKEN configurado?
    ↓ (todos aprovados)
Http::withToken($token)->post(Google Calendar API)
    ↓ (em caso de falha)
Log::warning(...) — request continua normalmente
```

### Estrutura frontend (Vue 3)

```
resources/js/
 ├── router/        # Vue Router com guards de autenticação
 ├── views/         # Dashboard, TaskList, UserList, Login
 └── components/
     ├── Tasks/     # TaskForm
     ├── Users/     # UserForm (com seção Google Agenda)
     └── UI/        # BaseButton, BaseInput, Modal, InfoTooltip
```

---

## Segurança da API

| Camada | Mecanismo |
|---|---|
| Autenticação | JWT (`tymon/jwt-auth`) com refresh token |
| Autorização | RBAC via `RoleMiddleware` (`role:admin,manager`) |
| Rate Limiting | 5 tentativas/minuto no endpoint de login |
| Validação | FormRequests estritos em todas as entradas |
| Hash de senha | Bcrypt via `Hash::make()` no Service (nunca no DTO) |
| Auditoria | ActivityLogObserver em todos os eventos CUD |
| Soft Deletes | Tasks nunca apagadas fisicamente |

---

## Endpoints da API

```
POST   /api/v1/auth/login              Público (throttle: 5/min)
GET    /api/v1/auth/me                 JWT
POST   /api/v1/auth/logout             JWT
POST   /api/v1/auth/refresh            JWT

GET    /api/v1/dashboard               JWT — métricas cacheadas (Redis, 60s)
GET    /api/v1/logs                    JWT + role:admin — activity logs

GET    /api/v1/users                   JWT + role:admin
POST   /api/v1/users                   JWT + role:admin
PUT    /api/v1/users/{id}              JWT + role:admin
DELETE /api/v1/users/{id}             JWT + role:admin
PUT    /api/v1/users/calendar          JWT + role:admin — Google Agenda

GET    /api/v1/tasks                   JWT
POST   /api/v1/tasks                   JWT
PUT    /api/v1/tasks/{id}              JWT
PATCH  /api/v1/tasks/{id}/status       JWT — conclusão rápida
DELETE /api/v1/tasks/{id}             JWT
```

Resposta padrão:

```json
{
  "success": true,
  "message": "Operação realizada com sucesso",
  "data": {}
}
```

---

## Integração Google Agenda

O Nexora permite vincular um **ID de agenda do Google Calendar** a cada usuário e sincroniza automaticamente as tarefas com prazo definido.

### Pré-requisitos

1. Obtenha um **OAuth2 Access Token** do Google:
   - Acesse [Google Cloud Console](https://console.cloud.google.com)
   - Habilite a **Google Calendar API**
   - Crie credenciais OAuth2 e obtenha o token
2. Configure no `.env`: `GOOGLE_CALENDAR_ACCESS_TOKEN=ya29.xxx`

### Conectar o ID da agenda

1. Acesse o Google Calendar e abra as configurações da agenda desejada
2. Vá em **Configurações e compartilhamento → Integrar agenda**
3. Copie o **ID da agenda** (formato: `xxxxxxx@group.calendar.google.com`)
4. No Nexora, acesse **Usuários → Editar** e cole o ID na seção **Google Agenda**
5. Clique em **Conectar**

```bash
curl -X PUT /api/v1/users/calendar \
  -H "Authorization: Bearer <jwt>" \
  -d '{"google_calendar_id": "xxxxxxx@group.calendar.google.com", "user_id": 1}'
```

### Sincronização automática de Tarefas

Ao criar ou editar uma tarefa com `due_date`, o `TaskObserver` chama `CalendarService::syncTaskToCalendar` automaticamente. A sincronização **não é disparada** quando:

- A tarefa não tem `due_date`
- O usuário dono da tarefa não tem `google_calendar_id` configurado
- `GOOGLE_CALENDAR_ACCESS_TOKEN` não está definido no `.env`

Falhas na API do Google são capturadas e logadas como `warning` — a tarefa é salva normalmente independente do resultado da sincronização.

---

## Instalação

### Docker (recomendado)

```bash
git clone https://github.com/DevMireski/Nexora.git && cd Nexora
docker-compose up -d
docker-compose exec app bash

composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
```

Acesse: `http://localhost:8081`

### Local

```bash
composer install && npm install
cp .env.example .env
php artisan key:generate && php artisan jwt:secret
php artisan migrate --seed
composer dev   # server + queue + vite concurrently
```

Acesse: `http://localhost:8000`

Credenciais padrão: `admin@nexora.test` / `password`

---

## Escalabilidade

O Nexora está estruturado para evoluir com:

- SaaS Multi-Tenant (isolamento por tenant no Repository)
- Sincronização real com Google Calendar API (OAuth2)
- Filas e Jobs Redis para operações assíncronas
- CI/CD com GitHub Actions
- Deploy containerizado (Docker / AWS ECS)

---

## Licença

MIT

---

## Autor

**Gustavo Mireski Demetrio** — Full Stack Developer — Imbuia, SC

<p align="center">Se este projeto te ajudou, considere dar uma estrela!</p>
