# SmartLead CRM Pro

**Enterprise-Grade SaaS Lead Management CRM Software**

Version 1.0.0 | Laravel 12 + Bootstrap 5 + MySQL

## Features

- **Lead Management** - Scoring, pipeline, import/export, duplicate detection
- **Follow-Up Automation** - Multi-type with reminders and recurring
- **Client Management** - Portal access, documents, payments
- **Quotation System** - PDF generation, digital approval, version history
- **Sales Pipeline** - Kanban drag & drop with deal tracking
- **Gmail Integration** - Send/receive via Gmail API
- **WhatsApp Business** - Meta Cloud API messaging
- **Google Sheets Sync** - Two-way sync with column mapping
- **Email Campaigns** - Drip sequences with tracking
- **Reports & Analytics** - Lead, sales, revenue, team reports
- **Role-Based Access** - 8 roles with customizable permissions
- **White Label** - Custom branding support
- **PWA Support** - Installable on mobile
- **Dark Mode** - Light/dark theme toggle
- **cPanel Compatible** - Shared hosting deployment

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 8.2+, Laravel 12 |
| Database | MySQL 8+ |
| Frontend | Bootstrap 5, Chart.js, SortableJS |
| APIs | Google OAuth, Gmail, Sheets, Calendar, WhatsApp |
| PDF | DomPDF |

## Quick Install

1. Upload files to hosting
2. Navigate to `yourdomain.com/install`
3. Follow the installation wizard

## Manual Install

```bash
composer install --optimize-autoloader --no-dev
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize
```

## Default Login

- Email: admin@smartleadcrm.com
- Password: password

## Cron Job (cPanel)

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## API Endpoints

```
POST /api/v1/auth/login     - Get token
GET  /api/v1/leads          - List leads
POST /api/v1/leads          - Create lead
POST /api/v1/lead-capture   - Public lead capture
POST /api/v1/whatsapp/webhook - WhatsApp webhook
```

## License

Proprietary - All rights reserved.
