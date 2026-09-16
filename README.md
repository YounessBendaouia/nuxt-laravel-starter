# 🚀 Nuxt 4 + Laravel 12 Fullstack Starter Kit

[![Nuxt](https://img.shields.io/badge/Nuxt-4.x-00DC82?style=flat-square&logo=nuxt.js)](https://nuxt.com/)
[![Vue](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vue.js)](https://vuejs.org/)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com/)
[![Sanctum](https://img.shields.io/badge/Sanctum-SPA_Cookie_Auth-F05340?style=flat-square)](https://laravel.com/docs/sanctum)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?style=flat-square&logo=tailwind-css)](https://tailwindcss.com/)
[![shadcn-vue](https://img.shields.io/badge/shadcn--vue-Components-000000?style=flat-square)](https://www.shadcn-vue.com/)
[![TypeScript](https://img.shields.io/badge/TypeScript-Ready-3178C6?style=flat-square&logo=typescript)](https://www.typescriptlang.org/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)](LICENSE)

A production-ready fullstack monorepo starter combining **Laravel 12** API backend (Sanctum SPA cookie authentication, Fortify 2FA, rate limiting) with **Nuxt 4 / Vue 3** frontend (shadcn-vue, Tailwind CSS v4, Pinia, dark mode).

---

## ⚡ Key Features

- **🔐 Robust Authentication Flow**:
  - Secure first-party SPA cookie-based session authentication via Laravel Sanctum & `nuxt-auth-sanctum`.
  - CSRF cookie protection (`XSRF-TOKEN`).
  - Two-Factor Authentication (2FA / TOTP) with QR code setup and emergency recovery codes.
  - Password reset request & reset flows with cryptographically signed tokens.
  - Secure signed email verification.
  - Login rate limiting and throttling.
- **🎨 Modern Frontend Architecture**:
  - Built on **Nuxt 4** & **Vue 3** (SPA mode with full TypeScript support).
  - Modern UI built with **shadcn-vue** (Reka UI primitives).
  - Styled with **Tailwind CSS v4** and CSS variables for seamless dark/light theme switching.
  - Form validation with **Vee-Validate** and **Zod**.
  - Interactive datatables with **TanStack Table**.
  - State management powered by **Pinia**.
  - Toast notifications via **Vue Sonner**.
- **⚙️ Backend Architecture**:
  - **Laravel 12** RESTful API with Eloquent ORM.
  - Out-of-the-box **SQLite** database support (zero extra setup required, fully adaptable to PostgreSQL/MySQL).
  - Model factories and database seeders with mock dashboard statistics.
  - Dedicated API routes with fine-grained rate limits.

---

## 📁 Repository Structure

```text
├── back-end/                  # Laravel 12 API backend
│   ├── app/                   # Models, Controllers, Middleware, Actions
│   ├── config/                # Laravel configuration files
│   ├── database/              # Migrations, factories, and seeders
│   ├── routes/                # api.php, web.php, console.php
│   ├── .env.example           # Backend environment template
│   └── composer.json          # PHP dependencies
│
├── front-end/                 # Nuxt 4 / Vue 3 frontend
│   ├── app/                   # Pages, layouts, components, composables
│   │   ├── components/ui/     # shadcn-vue UI components
│   │   ├── composables/       # useAuth, useTwoFactor
│   │   ├── layouts/           # Auth and Dashboard layouts
│   │   └── pages/             # Auth and Dashboard page views
│   ├── public/                # Static assets (avatars, icons)
│   ├── nuxt.config.ts         # Nuxt configuration
│   ├── .env.example           # Frontend environment template
│   └── package.json           # Node.js dependencies
│
├── .gitignore                 # Monorepo git ignore rules
└── README.md                  # Project documentation
```

---

## 🛠️ Prerequisites

Ensure you have the following installed on your local environment:

- **PHP**: 8.3 or higher with extensions (`pdo_sqlite`, `curl`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- **Composer**: 2.x
- **Node.js**: 20.x or higher (Node 22 recommended)
- **Package Manager**: `npm`, `pnpm`, or `yarn`

---

## 🚀 Quickstart Guide

### 1. Clone the Repository

```bash
git clone <your-repo-url>.git
cd starter
```

---

### 2. Backend Setup (`back-end`)

Open a terminal and navigate to the backend directory:

```bash
cd back-end

# Install PHP dependencies
composer install

# Copy environment configuration
cp .env.example .env

# Generate application encryption key
php artisan key:generate

# Run database migrations and seed the database
php artisan migrate --seed

# Start the Laravel development server (runs on http://localhost:8000)
php artisan serve
```

---

### 3. Frontend Setup (`front-end`)

Open a second terminal and navigate to the frontend directory:

```bash
cd front-end

# Install JavaScript dependencies
npm install

# Copy frontend environment configuration
cp .env.example .env

# Start the Nuxt development server (runs on http://localhost:3000)
npm run dev
```

---

### 4. Access the Application

- **Frontend Application**: [http://localhost:3000](http://localhost:3000)
- **Backend API**: [http://localhost:8000](http://localhost:8000)

#### Default Demo Credentials (from seeder)
- **Email**: `test@example.com`
- **Password**: `password`

---

## 🔐 Environment Variables

### Backend (`back-end/.env`)

| Variable | Default Value | Description |
|---|---|---|
| `APP_URL` | `http://localhost:8000` | Backend API URL |
| `FRONTEND_URL` | `http://localhost:3000` | Nuxt frontend application URL |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost:3000,localhost` | Allowed SPA origins for cookie sessions |
| `SESSION_DOMAIN` | `.localhost` | Cookie domain for cross-port localhost sharing |
| `SESSION_DRIVER` | `database` | Storage driver for user sessions |
| `DB_CONNECTION` | `sqlite` | Default database connection |

### Frontend (`front-end/.env`)

| Variable | Default Value | Description |
|---|---|---|
| `NUXT_PUBLIC_API_BASE` | `http://localhost:8000` | API base URL for standard requests |
| `NUXT_SANCTUM_BASE_URL` | `http://localhost:8000` | Base URL for Sanctum CSRF & auth routes |

---

## 📜 License

This project is open-sourced software licensed under the [MIT license](LICENSE).
