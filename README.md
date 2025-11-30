# Scout Family Management System

A web-based family management system for Pack 97 Cub Scouts that groups scouts, parents, siblings, and adult leaders into family units, with bidirectional synchronization to Mailchimp for communication purposes.

## 📋 Documentation

- **[REQUIREMENTS.md](REQUIREMENTS.md)** - Complete functional requirements and specifications
- **[TECHNICAL_SPEC.md](TECHNICAL_SPEC.md)** - System architecture, API design, and technical details
- **[DATABASE_SCHEMA.sql](DATABASE_SCHEMA.sql)** - Complete MySQL database schema

## 🛠 Technology Stack

### Backend
- **Framework:** Laravel 12.x (PHP 8.5+)
- **Database:** MySQL 8.0
- **Authentication:** WordPress REST API integration
- **Web Scraper:** Symfony Panther or Goutte (for Scoutbook sync)

### Frontend
- **Framework:** React 18.x
- **Build Tool:** Vite
- **State Management:** React Query + Context API
- **UI Library:** Tailwind CSS + Headless UI
- **Routing:** React Router v6

### Deployment
- **Platform:** HostGator Shared Hosting
- **Web Server:** Apache
- **Domain:** https://www.pack97.com/scoutdb

## 🚀 Quick Start

### Prerequisites

- PHP 8.1+ with extensions: pdo_mysql, mbstring, xml, curl
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+
- WordPress installation (for authentication)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/scoutdb.git
   cd scoutdb
   ```

2. **Backend Setup:**
   ```bash
   cd backend

   # Install PHP dependencies
   composer install

   # Copy environment file
   cp .env.example .env

   # Update .env with your database credentials:
   # - DB_DATABASE, DB_USERNAME, DB_PASSWORD
   # - WP_DB_DATABASE, WP_DB_USERNAME, WP_DB_PASSWORD

   # Generate application key
   php artisan key:generate

   # Run database migrations
   php artisan migrate

   # Seed initial settings (optional)
   php artisan db:seed
   ```

3. **Frontend Setup:**
   ```bash
   cd ../frontend

   # Install Node dependencies
   npm install

   # Start development server
   npm run dev
   ```

4. **Start Laravel development server:**
   ```bash
   cd ../backend
   php artisan serve
   ```

5. **Access the application:**
   - Frontend: http://localhost:5173
   - Backend API: http://localhost:8000

## 📂 Project Structure

```
scoutdb/
├── backend/              # Laravel application
│   ├── app/
│   │   ├── Console/      # Artisan commands (Scoutbook sync)
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/       # Eloquent models
│   │   └── Services/     # Business logic services
│   ├── config/
│   ├── database/
│   │   └── migrations/
│   ├── routes/
│   │   └── api.php
│   └── storage/
│
├── frontend/             # React application
│   ├── src/
│   │   ├── api/          # API client
│   │   ├── components/   # React components
│   │   ├── hooks/        # Custom React hooks
│   │   ├── pages/        # Page components
│   │   └── utils/        # Utility functions
│   └── public/
│
├── files/                # Data files (excluded from git)
│   ├── Roster_Report.csv
│   └── mailchimp_export_*.csv
│
├── docs/                 # Additional documentation
├── REQUIREMENTS.md       # Functional requirements
├── TECHNICAL_SPEC.md     # Technical specification
├── DATABASE_SCHEMA.sql   # Database schema
└── README.md             # This file
```

## 🔑 Key Features

### Phase 1 (Current)
- ✅ Laravel backend setup
- ✅ Database configuration (MySQL + WordPress)
- ✅ Environment configuration
- ⏳ Database migrations
- ⏳ Eloquent models
- ⏳ React frontend setup
- ⏳ API routes and controllers

### Phase 2 (Planned)
- Family management CRUD operations
- Person management (scouts, parents, siblings, leaders)
- WordPress authentication integration
- Scoutbook roster import (manual CSV upload)
- Mailchimp CSV export

### Phase 3 (Planned)
- Automated daily Scoutbook sync (PHP web scraper)
- Advanced family grouping algorithms
- Data validation and duplicate detection
- User permissions management

### Phase 4 (Future)
- Direct Mailchimp API integration
- Advancement tracking
- Event management
- Communication features (email templates, reminders)

## 📊 Database Schema

The application uses the following main tables:

- `families` - Family household units
- `persons` - All individuals (scouts, parents, siblings, leaders)
- `scouts` - Scout-specific attributes
- `adult_leaders` - Leader positions and YPT tracking
- `user_permissions` - WordPress user access control
- `audit_logs` - Change tracking
- `sync_logs` - Scoutbook/Mailchimp sync history
- `settings` - Application configuration

See [DATABASE_SCHEMA.sql](DATABASE_SCHEMA.sql) for complete schema with views, procedures, and functions.

## 🔐 Security

- WordPress session-based authentication
- Role-based access control (Admin, Editor, Viewer)
- CSRF protection (Laravel built-in)
- Encrypted storage for sensitive settings (Scoutbook credentials)
- Input validation and sanitization
- SQL injection protection (Eloquent ORM)

## 🧪 Testing

```bash
# Backend tests
cd backend
php artisan test

# Frontend tests
cd frontend
npm run test
```

## 📦 Deployment

### HostGator Shared Hosting

1. Build frontend:
   ```bash
   cd frontend
   npm run build
   ```

2. Upload files via FTP/SSH:
   - `frontend/dist/*` → `/public_html/scoutdb/`
   - `backend/*` → `/public_html/scoutdb/api/`

3. Configure .htaccess for routing

4. Set up cron job for daily Scoutbook sync:
   ```
   0 2 * * * cd /path/to/scoutdb/api && php artisan schedule:run
   ```

See [TECHNICAL_SPEC.md](TECHNICAL_SPEC.md) for detailed deployment instructions.

## 🤝 Contributing

This is a private project for Pack 97. If you're a pack leader interested in contributing:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is proprietary software for Pack 97 Cub Scouts.

## 📞 Contact

- **Project Lead:** Pack 97 Cubmaster
- **Email:** admin@pack97.com
- **Website:** https://www.pack97.com

## 🙏 Acknowledgments

- BSA Scoutbook for roster data
- Mailchimp for communication platform
- Laravel and React communities

---

**Version:** 1.0.0-alpha
**Last Updated:** 2025-11-30
