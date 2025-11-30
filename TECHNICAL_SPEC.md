# Scout Family Management System - Technical Specification

## 1. System Architecture

### 1.1 Technology Stack

#### Backend
- **Framework:** Laravel 10.x (PHP 8.1+)
- **Database:** MySQL 8.0
- **Authentication:** WordPress REST API integration
- **Web Scraper:** Symfony Panther (Chrome-based) or Goutte (lightweight)
- **Task Scheduling:** Laravel Task Scheduler (cron-based)
- **API:** RESTful JSON API

#### Frontend
- **Framework:** React 18.x
- **Build Tool:** Vite
- **State Management:** React Context API + React Query
- **UI Library:** Tailwind CSS + Headless UI
- **Forms:** React Hook Form + Zod validation
- **Data Tables:** TanStack Table (React Table v8)
- **Routing:** React Router v6

#### Deployment
- **Platform:** HostGator Shared Hosting
- **Web Server:** Apache (mod_rewrite enabled)
- **Process Manager:** cron for scheduled tasks
- **Version Control:** Git

### 1.2 Project Structure

```
scoutdb/
├── backend/                  # Laravel application
│   ├── app/
│   │   ├── Console/
│   │   │   └── Commands/
│   │   │       └── SyncScoutbook.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── FamilyController.php
│   │   │   │   ├── PersonController.php
│   │   │   │   ├── ScoutController.php
│   │   │   │   ├── ImportController.php
│   │   │   │   ├── ExportController.php
│   │   │   │   └── AuthController.php
│   │   │   ├── Middleware/
│   │   │   │   ├── WordPressAuth.php
│   │   │   │   └── CheckRole.php
│   │   │   └── Requests/
│   │   ├── Models/
│   │   │   ├── Family.php
│   │   │   ├── Person.php
│   │   │   ├── Scout.php
│   │   │   ├── AdultLeader.php
│   │   │   ├── UserPermission.php
│   │   │   └── AuditLog.php
│   │   ├── Services/
│   │   │   ├── ScoutbookScraperService.php
│   │   │   ├── MailchimpExportService.php
│   │   │   ├── FamilyGroupingService.php
│   │   │   └── ImportService.php
│   │   └── Traits/
│   │       └── HasAuditLog.php
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   └── api.php
│   ├── storage/
│   │   ├── app/
│   │   │   ├── imports/       # Uploaded CSV files
│   │   │   ├── exports/       # Generated Mailchimp CSVs
│   │   │   └── roster/        # Scoutbook scraped data
│   │   └── logs/
│   ├── tests/
│   ├── .env.example
│   ├── composer.json
│   └── artisan
├── frontend/                 # React application
│   ├── public/
│   ├── src/
│   │   ├── api/
│   │   │   └── client.js      # Axios instance
│   │   ├── components/
│   │   │   ├── families/
│   │   │   ├── persons/
│   │   │   ├── shared/
│   │   │   └── layout/
│   │   ├── hooks/
│   │   ├── pages/
│   │   │   ├── Dashboard.jsx
│   │   │   ├── FamilyList.jsx
│   │   │   ├── FamilyDetail.jsx
│   │   │   ├── PersonList.jsx
│   │   │   ├── PersonDetail.jsx
│   │   │   ├── Import.jsx
│   │   │   ├── Export.jsx
│   │   │   ├── Settings.jsx
│   │   │   └── UserManagement.jsx
│   │   ├── utils/
│   │   ├── App.jsx
│   │   └── main.jsx
│   ├── package.json
│   ├── vite.config.js
│   └── tailwind.config.js
├── files/                    # Data files (not in production)
│   ├── Roster_Report.csv
│   └── subscribed_email_audience_export_89e98b0792.csv
├── docs/
├── .gitignore
├── REQUIREMENTS.md
├── TECHNICAL_SPEC.md
└── README.md
```

### 1.3 Deployment Architecture

```
pack97.com (HostGator)
├── public_html/              # WordPress installation
│   ├── wp-admin/
│   ├── wp-content/
│   ├── wp-includes/
│   ├── index.php
│   └── scoutdb/              # ScoutDB installation
│       ├── index.html        # React SPA entry point
│       ├── assets/           # Compiled JS/CSS
│       │   ├── index.js
│       │   └── index.css
│       └── api/              # Laravel API
│           ├── public/
│           │   └── index.php # API entry point
│           ├── app/
│           ├── config/
│           ├── storage/
│           └── vendor/
```

**URL Mapping:**
- WordPress: `https://www.pack97.com/`
- ScoutDB Frontend: `https://www.pack97.com/scoutdb/`
- ScoutDB API: `https://www.pack97.com/scoutdb/api/`

---

## 2. Database Schema

### 2.1 Entity Relationship Diagram

```
┌──────────────┐         ┌──────────────────┐
│   families   │────1:N──│     persons      │
└──────────────┘         └──────────────────┘
                                  │
                         ┌────────┴────────┐
                         │                 │
                   ┌─────▼──────┐   ┌─────▼──────────┐
                   │   scouts   │   │ adult_leaders  │
                   └────────────┘   └────────────────┘

┌──────────────────┐         ┌──────────────────┐
│  wp_users (WP)   │────1:1──│ user_permissions │
└──────────────────┘         └──────────────────┘
                                      │
                                      │1:1
                                      ▼
                              ┌──────────────┐
                              │   persons    │
                              └──────────────┘

┌──────────────────┐
│   audit_logs     │  (tracks all changes)
└──────────────────┘

┌──────────────────┐
│   sync_logs      │  (tracks Scoutbook syncs)
└──────────────────┘
```

### 2.2 Table Definitions

#### `families`
```sql
CREATE TABLE families (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    street_address VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(2) NULL,
    zip VARCHAR(10) NULL,
    primary_phone VARCHAR(20) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_name (name),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `persons`
```sql
CREATE TABLE persons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    family_id BIGINT UNSIGNED NULL,
    bsa_member_id VARCHAR(20) NULL,
    person_type ENUM('scout', 'parent', 'sibling', 'adult_leader') NOT NULL,
    prefix VARCHAR(10) NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(10) NULL,
    nickname VARCHAR(100) NULL,
    gender ENUM('M', 'F', 'Other') NULL,
    date_of_birth DATE NULL,
    age INT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE SET NULL,
    INDEX idx_family (family_id),
    INDEX idx_bsa_member (bsa_member_id),
    INDEX idx_email (email),
    INDEX idx_name (last_name, first_name),
    INDEX idx_type (person_type),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `scouts`
```sql
CREATE TABLE scouts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL,
    grade VARCHAR(50) NULL,
    rank VARCHAR(50) NULL,
    den VARCHAR(50) NULL,
    registration_expiration_date DATE NULL,
    registration_status VARCHAR(50) NULL,
    ypt_status VARCHAR(50) NULL,
    program VARCHAR(50) DEFAULT 'Cub Scouting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
    INDEX idx_person (person_id),
    INDEX idx_expiration (registration_expiration_date),
    INDEX idx_den (den),
    INDEX idx_rank (rank)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `adult_leaders`
```sql
CREATE TABLE adult_leaders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL,
    positions JSON NULL,  -- Array of position names
    ypt_status VARCHAR(50) NULL,
    ypt_completion_date DATE NULL,
    ypt_expiration_date DATE NULL,
    registration_expiration_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
    INDEX idx_person (person_id),
    INDEX idx_ypt_expiration (ypt_expiration_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `user_permissions`
```sql
CREATE TABLE user_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wordpress_user_id BIGINT UNSIGNED NOT NULL,  -- References wp_users.ID
    person_id BIGINT UNSIGNED NULL,              -- Links to person record
    role ENUM('admin', 'editor', 'viewer') NOT NULL,
    granted_by BIGINT UNSIGNED NULL,             -- User ID who granted permission
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE SET NULL,
    INDEX idx_wp_user (wordpress_user_id),
    INDEX idx_role (role),
    UNIQUE KEY unique_wp_user (wordpress_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `audit_logs`
```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,               -- WordPress user ID
    entity_type VARCHAR(50) NOT NULL,           -- 'family', 'person', 'scout', etc.
    entity_id BIGINT UNSIGNED NOT NULL,
    action ENUM('create', 'update', 'delete') NOT NULL,
    changes JSON NULL,                          -- Before/after values
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `sync_logs`
```sql
CREATE TABLE sync_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sync_type ENUM('scoutbook', 'mailchimp_import') NOT NULL,
    status ENUM('running', 'completed', 'failed') NOT NULL,
    started_at TIMESTAMP NOT NULL,
    completed_at TIMESTAMP NULL,
    records_processed INT DEFAULT 0,
    records_created INT DEFAULT 0,
    records_updated INT DEFAULT 0,
    records_skipped INT DEFAULT 0,
    errors JSON NULL,                           -- Array of error messages
    triggered_by BIGINT UNSIGNED NULL,          -- User ID (null if automated)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (sync_type),
    INDEX idx_status (status),
    INDEX idx_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `settings`
```sql
CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    is_encrypted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Common Settings:**
- `scoutbook.username`
- `scoutbook.password` (encrypted)
- `scoutbook.unit_number`
- `scoutbook.auto_sync_enabled`
- `scoutbook.sync_time`
- `mailchimp.api_key` (encrypted)
- `mailchimp.audience_id`
- `pack.name`
- `pack.number`
- `system.timezone`

---

## 3. API Design

### 3.1 Authentication

**WordPress Cookie-Based Auth:**
- Frontend sends requests with WordPress session cookie
- Laravel middleware validates cookie against WordPress database
- Extract WordPress user ID, check `user_permissions` table for role

**Middleware Chain:**
```
Request → WordPressAuth → CheckRole → Controller
```

**Headers:**
```
Authorization: Bearer {wordpress_auth_cookie}
X-WP-Nonce: {wp_nonce}
```

### 3.2 REST API Endpoints

#### Authentication
```
POST   /api/auth/check          # Verify WordPress session, return user + role
POST   /api/auth/logout         # Clear session
```

#### Families
```
GET    /api/families                      # List all families (paginated, searchable)
POST   /api/families                      # Create new family
GET    /api/families/{id}                 # Get family details with all members
PUT    /api/families/{id}                 # Update family
DELETE /api/families/{id}                 # Soft delete family
POST   /api/families/{id}/members         # Add person to family
DELETE /api/families/{id}/members/{personId}  # Remove person from family
POST   /api/families/merge                # Merge two families
```

**Query Parameters for GET /api/families:**
- `page` (default: 1)
- `per_page` (default: 25)
- `search` (search family name, member names, emails)
- `sort_by` (name, updated_at, scout_count)
- `sort_order` (asc, desc)

**Response Example:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Johnson Family",
      "street_address": "123 Main St",
      "city": "Austin",
      "state": "TX",
      "zip": "78681",
      "primary_phone": "512-555-1234",
      "notes": "",
      "scout_count": 2,
      "adult_count": 2,
      "sibling_count": 0,
      "primary_emails": ["parent1@example.com", "parent2@example.com"],
      "updated_at": "2025-11-30T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 150,
    "last_page": 6
  }
}
```

#### Persons
```
GET    /api/persons                       # List all persons (paginated, searchable)
POST   /api/persons                       # Create new person
GET    /api/persons/{id}                  # Get person details
PUT    /api/persons/{id}                  # Update person
DELETE /api/persons/{id}                  # Soft delete person
GET    /api/persons/orphaned              # List persons without family
POST   /api/persons/merge                 # Merge duplicate persons
```

**Query Parameters for GET /api/persons:**
- `page`, `per_page`
- `search` (name, email, BSA ID)
- `person_type` (scout, parent, sibling, adult_leader)
- `family_id` (filter by family)
- `orphaned` (true/false)

**Response Example:**
```json
{
  "data": [
    {
      "id": 1,
      "family_id": 1,
      "family_name": "Johnson Family",
      "bsa_member_id": "12345678",
      "person_type": "scout",
      "first_name": "Alex",
      "last_name": "Johnson",
      "email": null,
      "phone": "512-555-5678",
      "scout_details": {
        "grade": "Third Grade",
        "rank": "Wolf",
        "den": "Den 3",
        "registration_expiration_date": "2025-12-31",
        "days_until_expiration": 365
      }
    }
  ],
  "meta": { /* ... */ }
}
```

#### Scouts (Sub-resource of Persons)
```
GET    /api/scouts                        # List all scouts
GET    /api/scouts/expiring               # List scouts expiring soon
PUT    /api/persons/{id}/scout            # Update scout-specific details
```

#### Adult Leaders (Sub-resource of Persons)
```
GET    /api/adult-leaders                 # List all adult leaders
GET    /api/adult-leaders/ypt-expiring    # List leaders with expiring YPT
PUT    /api/persons/{id}/adult-leader     # Update leader-specific details
```

#### Import
```
POST   /api/import/roster                 # Upload and preview roster CSV
POST   /api/import/roster/commit          # Commit previewed import
POST   /api/import/mailchimp              # Upload and preview Mailchimp CSV
POST   /api/import/mailchimp/commit       # Commit previewed import
```

**Request (multipart/form-data):**
```
POST /api/import/roster
Content-Type: multipart/form-data

file: [Roster_Report.csv]
```

**Response (Preview):**
```json
{
  "preview": {
    "total_rows": 120,
    "new_persons": 15,
    "updated_persons": 100,
    "duplicates": 5,
    "errors": [
      {
        "row": 23,
        "email": "",
        "message": "Missing pgprimaryemail, person will be orphaned"
      }
    ]
  },
  "import_id": "abc123",  // Token to commit import
  "expires_at": "2025-11-30T11:00:00Z"
}
```

#### Export
```
POST   /api/export/mailchimp              # Generate Mailchimp CSV
GET    /api/export/mailchimp/validate     # Pre-export validation
GET    /api/export/roster                 # Generate roster report
GET    /api/export/directory              # Generate family directory
```

**Request:**
```json
POST /api/export/mailchimp
{
  "include_prospective": true,
  "expiration_cutoff": "2026-12-31"
}
```

**Response:**
```json
{
  "file_url": "/api/downloads/mailchimp_export_20251130_103045.csv",
  "expires_at": "2025-11-30T12:00:00Z",
  "record_count": 150,
  "parent_contacts": 182  // Multiple parents per family
}
```

#### Scoutbook Sync
```
POST   /api/sync/scoutbook                # Trigger manual sync
GET    /api/sync/scoutbook/status         # Get current sync status
GET    /api/sync/scoutbook/history        # Get sync history (last 30 days)
```

**Response (Sync Status):**
```json
{
  "status": "running",
  "started_at": "2025-11-30T10:30:00Z",
  "progress": {
    "current_step": "Parsing roster data",
    "percent_complete": 45
  }
}
```

#### Settings
```
GET    /api/settings                      # Get all settings (admin only)
PUT    /api/settings                      # Update settings (admin only)
POST   /api/settings/scoutbook/test       # Test Scoutbook connection
```

#### User Management
```
GET    /api/users/permissions             # List all users with permissions
POST   /api/users/permissions             # Grant permission to user
PUT    /api/users/permissions/{id}        # Update user role
DELETE /api/users/permissions/{id}        # Revoke permission
GET    /api/users/wordpress               # Search WordPress users
```

#### Dashboard
```
GET    /api/dashboard/stats               # Get dashboard statistics
GET    /api/dashboard/recent-activity     # Get recent changes
```

**Response (Stats):**
```json
{
  "total_families": 150,
  "total_scouts": 180,
  "scouts_by_rank": {
    "Lion": 25,
    "Tiger": 30,
    "Wolf": 35,
    "Bear": 40,
    "Webelos": 35,
    "Arrow of Light": 15
  },
  "total_adult_leaders": 45,
  "scouts_expiring_30_days": 12,
  "scouts_expiring_60_days": 28,
  "last_scoutbook_sync": "2025-11-30T02:00:00Z",
  "orphaned_persons": 3
}
```

#### Audit Logs
```
GET    /api/audit-logs                    # Get audit logs (admin only)
```

---

## 4. Core Services

### 4.1 ScoutbookScraperService

**Purpose:** Scrape roster data from Scoutbook website using PHP browser automation.

**Technology:** Symfony Panther (Chrome/Firefox via WebDriver)

**Process:**
1. Launch headless browser
2. Navigate to Scoutbook login page
3. Enter credentials from settings
4. Navigate to roster report page
5. Download roster CSV or scrape HTML table
6. Parse data into structured format
7. Return array of person records

**Method Signature:**
```php
class ScoutbookScraperService
{
    public function scrapeRoster(): array
    {
        // Returns array of person data
    }

    public function testConnection(): bool
    {
        // Test login credentials
    }
}
```

**Error Handling:**
- Invalid credentials: Throw `AuthenticationException`
- Scoutbook site changes: Throw `ScraperException` with screenshot
- Network timeout: Retry 3 times, then fail

### 4.2 FamilyGroupingService

**Purpose:** Group persons into families based on shared email addresses.

**Algorithm:**
```php
class FamilyGroupingService
{
    public function groupByEmail(array $persons): array
    {
        // 1. Extract unique parent emails (pgprimaryemail)
        // 2. For each email, find all persons with that email
        // 3. Create or update family record
        // 4. Assign all persons to that family
        // 5. Handle orphaned persons (no email)
        // 6. Return grouping results
    }

    public function detectSplitFamilies(): array
    {
        // Find families with same last name, different emails, same address
        // Return potential matches for manual review
    }
}
```

**Multi-Parent Handling:**
- If family has scouts with different `pgprimaryemail`:
  - Create one family
  - Create separate `Person` records for each parent email
  - Both parents linked to same family
  - Mailchimp export creates separate rows for each parent

### 4.3 MailchimpExportService

**Purpose:** Generate Mailchimp-compatible CSV export.

**Process:**
1. Query all families with at least one email address
2. For each family:
   - Get all parents (with email)
   - Get all scouts (up to 3, sorted by expiration date desc)
   - Get all siblings (up to 3, sorted by age asc)
   - Get adult leader positions (for parents who are leaders)
3. For each parent in family:
   - Create CSV row with parent email
   - Populate scout fields (Scout 1/2/3, Den, Expiration)
   - Populate sibling fields (Sibling 1/2/3)
   - Populate leader position fields
   - Add tags ("existing" or "prospective")
4. Write to CSV file with UTF-8 BOM encoding
5. Return file path

**Method Signature:**
```php
class MailchimpExportService
{
    public function generateCSV(array $options = []): string
    {
        // Options: include_prospective, expiration_cutoff
        // Returns: file path
    }

    public function validate(): array
    {
        // Returns array of validation issues
    }

    private function formatPhoneNumber(string $phone): string
    {
        // Standardize to: 512-555-1234
    }

    private function formatAddress(Family $family): string
    {
        // Concatenate: street, city, state zip
    }
}
```

**Mailchimp CSV Structure:**
```csv
Email Address,First Name,Last Name,Phone Number,BSA Member ID,ADDRESS,Scout 1,Scout 1 Den,Scout 1 Expiration,Scout 2,Scout 2 Den,Scout 2 Expiration,Scout 3,Scout 3 Den,Scout 3 Expiration,Sibling 1,Sibling 2,Sibling 3,Leader Position 1,Leader Position 2,Leader Position 4,TAGS
parent@example.com,John,Doe,512-555-1234,12345678,"123 Main St, Austin, TX 78681",Alex Doe,Den 3,12/31/2025,Sam Doe,Den 1,11/30/2026,,,,,,,Cubmaster,Key 3 Delegate,,existing
```

### 4.4 ImportService

**Purpose:** Parse and import CSV files (Roster, Mailchimp audience).

**Process:**
1. Validate CSV structure (check headers)
2. Parse rows into structured data
3. For each row:
   - Check if person exists (by BSA ID or email)
   - Determine action: create, update, or skip
   - Store in staging area (session or temp table)
4. Return preview data
5. On commit:
   - Execute all creates/updates in database transaction
   - Log import in `sync_logs`
   - Return results

**Method Signature:**
```php
class ImportService
{
    public function previewRosterImport(UploadedFile $file): array
    {
        // Returns: preview data + import_id
    }

    public function commitRosterImport(string $importId): array
    {
        // Returns: import results (counts, errors)
    }

    public function previewMailchimpImport(UploadedFile $file): array
    {
        // Similar to roster
    }

    public function commitMailchimpImport(string $importId): array
    {
        // Similar to roster
    }

    private function detectDuplicates(array $persons): array
    {
        // Find potential duplicates by name + age
    }
}
```

---

## 5. WordPress Integration

### 5.1 Authentication Flow

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   Browser    │         │  WordPress   │         │   ScoutDB    │
│  (React App) │         │              │         │   (Laravel)  │
└──────┬───────┘         └──────┬───────┘         └──────┬───────┘
       │                        │                        │
       │  1. Login to WP        │                        │
       ├───────────────────────>│                        │
       │                        │                        │
       │  2. WP sets auth cookie│                        │
       │<───────────────────────┤                        │
       │                        │                        │
       │  3. Request ScoutDB API with cookie             │
       ├─────────────────────────────────────────────────>│
       │                        │                        │
       │                        │  4. Validate cookie    │
       │                        │<───────────────────────┤
       │                        │                        │
       │                        │  5. Return user data   │
       │                        │───────────────────────>│
       │                        │                        │
       │  6. Return API response with user permissions   │
       │<─────────────────────────────────────────────────┤
```

### 5.2 WordPress Auth Middleware

**File:** `app/Http/Middleware/WordPressAuth.php`

```php
class WordPressAuth
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Get WordPress auth cookie from request
        $authCookie = $request->cookie('wordpress_logged_in_...');

        // 2. Connect to WordPress database
        $wpDb = DB::connection('wordpress');

        // 3. Validate cookie and get user ID
        $userId = $this->validateWordPressCookie($authCookie);

        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 4. Check user_permissions table
        $permission = UserPermission::where('wordpress_user_id', $userId)->first();

        if (!$permission) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // 5. Attach user and role to request
        $request->merge([
            'auth_user_id' => $userId,
            'auth_role' => $permission->role,
            'auth_person_id' => $permission->person_id
        ]);

        return $next($request);
    }
}
```

### 5.3 WordPress Database Connection

**File:** `config/database.php`

```php
'connections' => [
    'mysql' => [
        // ScoutDB database
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_DATABASE', 'scoutdb'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        // ...
    ],

    'wordpress' => [
        // WordPress database (read-only)
        'driver' => 'mysql',
        'host' => env('WP_DB_HOST', '127.0.0.1'),
        'database' => env('WP_DB_DATABASE', 'wordpress'),
        'username' => env('WP_DB_USERNAME', 'root'),
        'password' => env('WP_DB_PASSWORD', ''),
        'prefix' => env('WP_DB_PREFIX', 'wp_'),
        'strict' => false,
        // ...
    ],
],
```

### 5.4 WordPress Menu Integration

**Option 1: WordPress Plugin**
Create a small WordPress plugin to add menu item:

**File:** `wp-content/plugins/scoutdb-menu/scoutdb-menu.php`

```php
<?php
/*
Plugin Name: ScoutDB Menu
Description: Adds ScoutDB link to WordPress admin menu
Version: 1.0
*/

add_action('admin_menu', 'scoutdb_add_menu');

function scoutdb_add_menu() {
    add_menu_page(
        'Scout Database',      // Page title
        'Scout Database',      // Menu title
        'read',               // Capability (all logged-in users)
        'scoutdb',            // Menu slug
        'scoutdb_redirect',   // Callback
        'dashicons-groups',   // Icon
        30                    // Position
    );
}

function scoutdb_redirect() {
    wp_redirect(home_url('/scoutdb/'));
    exit;
}
```

**Option 2: WordPress Theme Functions**
Add to `functions.php`:

```php
add_action('admin_menu', function() {
    add_menu_page(
        'Scout Database',
        'Scout Database',
        'read',
        'scoutdb_external',
        function() {
            echo '<script>window.location.href = "' . home_url('/scoutdb/') . '";</script>';
        },
        'dashicons-groups',
        30
    );
});
```

---

## 6. Frontend Architecture

### 6.1 State Management Strategy

**React Query for Server State:**
- All API data fetching
- Automatic caching, refetching, invalidation
- Optimistic updates

**React Context for Global UI State:**
- Current user + permissions
- Toast notifications
- Modal state

**Component Local State:**
- Form inputs
- UI toggles (collapsed sections, filters)

### 6.2 Key React Hooks

**useAuth:**
```javascript
function useAuth() {
  return useQuery({
    queryKey: ['auth'],
    queryFn: () => api.get('/auth/check'),
    staleTime: Infinity,
    retry: false
  });
}
```

**useFamilies:**
```javascript
function useFamilies(filters = {}) {
  return useQuery({
    queryKey: ['families', filters],
    queryFn: () => api.get('/families', { params: filters })
  });
}
```

**useCreateFamily:**
```javascript
function useCreateFamily() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data) => api.post('/families', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['families'] });
      toast.success('Family created successfully');
    }
  });
}
```

### 6.3 Component Structure

**Page Components:**
- Handle routing, layout, data fetching
- Render child components
- Example: `FamilyListPage` → `FamilyTable` → `FamilyRow`

**Feature Components:**
- Self-contained features (forms, tables, modals)
- Accept data via props
- Emit events via callbacks
- Example: `FamilyForm`, `PersonForm`, `ImportWizard`

**Shared Components:**
- Reusable UI elements
- Example: `Button`, `Input`, `Modal`, `DataTable`, `SearchBar`

### 6.4 Routing Structure

```javascript
<BrowserRouter basename="/scoutdb">
  <Routes>
    <Route path="/" element={<Dashboard />} />

    <Route path="/families">
      <Route index element={<FamilyList />} />
      <Route path=":id" element={<FamilyDetail />} />
      <Route path="new" element={<FamilyForm />} />
      <Route path=":id/edit" element={<FamilyForm />} />
    </Route>

    <Route path="/persons">
      <Route index element={<PersonList />} />
      <Route path=":id" element={<PersonDetail />} />
      <Route path="new" element={<PersonForm />} />
      <Route path=":id/edit" element={<PersonForm />} />
    </Route>

    <Route path="/import" element={<Import />} />
    <Route path="/export" element={<Export />} />

    <Route path="/settings" element={<Settings />} />
    <Route path="/users" element={<UserManagement />} />

    <Route path="/audit-logs" element={<AuditLogs />} />

    <Route path="*" element={<NotFound />} />
  </Routes>
</BrowserRouter>
```

---

## 7. Deployment Process

### 7.1 Build Process

**Backend (Laravel):**
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
```

**Frontend (React):**
```bash
# Install dependencies
npm install

# Build for production
npm run build

# Output: frontend/dist/
#   - index.html
#   - assets/index-[hash].js
#   - assets/index-[hash].css
```

### 7.2 Deployment to HostGator

**Directory Structure on Server:**
```
/home/username/
├── public_html/              # WordPress root
│   ├── scoutdb/              # ScoutDB deployment
│   │   ├── index.html        # Frontend entry point
│   │   ├── assets/           # Frontend JS/CSS
│   │   ├── api/              # Laravel backend
│   │   │   ├── .env
│   │   │   ├── public/
│   │   │   │   └── index.php
│   │   │   ├── app/
│   │   │   ├── config/
│   │   │   ├── storage/
│   │   │   └── vendor/
│   │   └── .htaccess
```

**Apache .htaccess for /scoutdb/:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # API routes
    RewriteCond %{REQUEST_URI} ^/scoutdb/api/
    RewriteRule ^api/(.*)$ api/public/index.php [L]

    # Frontend routes (SPA)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.html [L]
</IfModule>
```

**Deployment Steps:**

1. **Via Git (Recommended):**
   ```bash
   # On server (SSH)
   cd /home/username/public_html/scoutdb
   git pull origin main

   # Backend
   cd api
   composer install --no-dev
   php artisan migrate --force
   php artisan config:cache

   # Frontend
   cd ../
   # Upload pre-built files from frontend/dist/
   ```

2. **Via FTP:**
   - Upload `frontend/dist/*` to `/public_html/scoutdb/`
   - Upload `backend/*` to `/public_html/scoutdb/api/`
   - Set .env file with production credentials

3. **Initial Setup:**
   - Create MySQL database via cPanel
   - Import initial data (if any)
   - Run migrations
   - Create first admin user manually in database

### 7.3 Cron Job Setup

**Laravel Task Scheduler:**

Laravel uses a single cron entry that calls all scheduled tasks:

**cPanel Cron Job:**
```
# Run Laravel scheduler every minute
* * * * * cd /home/username/public_html/scoutdb/api && php artisan schedule:run >> /dev/null 2>&1
```

**Laravel Schedule (app/Console/Kernel.php):**
```php
protected function schedule(Schedule $schedule)
{
    // Daily Scoutbook sync at 2:00 AM
    $schedule->command('scoutbook:sync')
             ->dailyAt('02:00')
             ->timezone('America/Chicago')
             ->emailOutputOnFailure('admin@pack97.com');

    // Weekly duplicate detection
    $schedule->command('persons:detect-duplicates')
             ->weekly()
             ->mondays()
             ->at('03:00');
}
```

### 7.4 Environment Variables

**.env.example:**
```env
APP_NAME="Scout Family Management"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://www.pack97.com/scoutdb

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pack97_scoutdb
DB_USERNAME=pack97_scoutdb_user
DB_PASSWORD=

# WordPress Database (read-only)
WP_DB_HOST=127.0.0.1
WP_DB_DATABASE=pack97_wordpress
WP_DB_USERNAME=pack97_wp_user
WP_DB_PASSWORD=
WP_DB_PREFIX=wp_

# Scoutbook (stored in database settings, not .env)
# SCOUTBOOK_USERNAME=
# SCOUTBOOK_PASSWORD=

# Mailchimp (future)
# MAILCHIMP_API_KEY=
# MAILCHIMP_AUDIENCE_ID=

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=admin@pack97.com
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=admin@pack97.com
MAIL_FROM_NAME="Pack 97 Scout Database"

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=1440

# Timezone
APP_TIMEZONE=America/Chicago
```

---

## 8. Security Considerations

### 8.1 Authentication & Authorization
- ✅ WordPress session validation on every request
- ✅ Role-based access control (RBAC) via middleware
- ✅ CSRF protection (Laravel built-in)
- ✅ Secure session cookies (HttpOnly, Secure, SameSite)

### 8.2 Data Protection
- ✅ Encrypt sensitive settings (Scoutbook credentials) using Laravel's encryption
- ✅ Use HTTPS for all traffic (enforced by WordPress)
- ✅ Sanitize all inputs (Laravel validation + sanitization)
- ✅ Use parameterized queries (Eloquent ORM)
- ✅ Prevent XSS (React auto-escapes, use `dangerouslySetInnerHTML` carefully)

### 8.3 File Uploads
- ✅ Validate file types (only .csv allowed)
- ✅ Limit file size (max 5MB)
- ✅ Store uploads outside public directory
- ✅ Generate unique filenames (prevent overwrites)
- ✅ Scan for malware (if possible on shared hosting)

### 8.4 API Security
- ✅ Rate limiting (60 requests/minute per user)
- ✅ Input validation on all endpoints
- ✅ Return appropriate HTTP status codes
- ✅ Don't expose sensitive data in error messages

### 8.5 Database Security
- ✅ Use separate database users for ScoutDB and WordPress
- ✅ Limit WordPress database user to SELECT only (read-only)
- ✅ Regularly backup database (HostGator auto-backup recommended)
- ✅ Use soft deletes (allow recovery)

---

## 9. Testing Strategy

### 9.1 Backend Testing (PHPUnit)

**Unit Tests:**
- Service layer methods (FamilyGroupingService, MailchimpExportService)
- Model methods (Person relationships, Scout validation)

**Feature Tests:**
- API endpoint responses
- Authentication/authorization
- CSV import/export logic

**Example Test:**
```php
public function test_can_group_persons_by_email()
{
    $persons = [
        ['first_name' => 'John', 'pgprimaryemail' => 'parent@example.com'],
        ['first_name' => 'Jane', 'pgprimaryemail' => 'parent@example.com'],
    ];

    $service = new FamilyGroupingService();
    $result = $service->groupByEmail($persons);

    $this->assertCount(1, $result['families']);
    $this->assertEquals(2, $result['families'][0]['member_count']);
}
```

### 9.2 Frontend Testing (Vitest + React Testing Library)

**Component Tests:**
- Render components with various props
- Test user interactions (clicks, form submissions)
- Test conditional rendering

**Integration Tests:**
- Mock API responses
- Test full user flows (create family, add scout, etc.)

**Example Test:**
```javascript
test('displays family list', async () => {
  const mockFamilies = [
    { id: 1, name: 'Smith Family', scout_count: 2 }
  ];

  server.use(
    rest.get('/api/families', (req, res, ctx) => {
      return res(ctx.json({ data: mockFamilies }));
    })
  );

  render(<FamilyList />);

  expect(await screen.findByText('Smith Family')).toBeInTheDocument();
});
```

### 9.3 Manual Testing Checklist

**Pre-Launch:**
- [ ] WordPress authentication works
- [ ] All user roles have correct permissions
- [ ] Roster CSV import creates families correctly
- [ ] Mailchimp CSV export matches expected format
- [ ] Scoutbook sync runs successfully
- [ ] Multi-parent families create separate Mailchimp rows
- [ ] Orphaned persons display in UI
- [ ] Duplicate detection works
- [ ] Audit logs capture all changes
- [ ] Mobile responsive on iOS/Android
- [ ] All forms validate inputs properly

---

## 10. Performance Optimization

### 10.1 Backend Optimizations
- **Database Indexing:** Ensure all foreign keys and frequently queried columns are indexed
- **Eager Loading:** Use `with()` to prevent N+1 queries
- **Query Optimization:** Use `select()` to limit columns, `paginate()` for large datasets
- **Caching:** Cache settings, dashboard stats (5-minute TTL)

**Example:**
```php
// Bad (N+1 query)
$families = Family::all();
foreach ($families as $family) {
    echo $family->persons->count();
}

// Good (eager loading)
$families = Family::withCount('persons')->get();
foreach ($families as $family) {
    echo $family->persons_count;
}
```

### 10.2 Frontend Optimizations
- **Code Splitting:** Lazy load routes with React.lazy()
- **Bundle Optimization:** Use Vite's tree-shaking, minification
- **Image Optimization:** Compress images, use WebP format (future)
- **React Query Caching:** Set appropriate `staleTime` and `cacheTime`

**Example:**
```javascript
const FamilyDetail = React.lazy(() => import('./pages/FamilyDetail'));

<Route path="/families/:id" element={
  <Suspense fallback={<Loading />}>
    <FamilyDetail />
  </Suspense>
} />
```

### 10.3 CSV Export Optimization
- **Streaming:** For large datasets (500+ families), stream CSV generation
- **Background Jobs:** Use Laravel queue for exports (if available on HostGator)
- **Compression:** Offer gzip download option for large files

---

## 11. Monitoring & Logging

### 11.1 Application Logs

**Laravel Log Channels:**
- `daily`: Default log file (rotates daily)
- `slack`: Critical errors to Slack (optional)
- `email`: Email admin on critical errors

**Log Important Events:**
- Scoutbook sync start/end/errors
- Import commit (who, when, how many records)
- User permission grants/revokes
- Failed authentication attempts

**Example:**
```php
Log::channel('daily')->info('Scoutbook sync started', [
    'triggered_by' => $userId,
    'timestamp' => now()
]);
```

### 11.2 Error Tracking

**Laravel Exception Handler:**
- Log all exceptions to `storage/logs/laravel.log`
- Send critical errors via email (configure in .env)
- Don't expose stack traces to users in production

### 11.3 Performance Monitoring

**Manual Monitoring:**
- Check `storage/logs/laravel.log` weekly
- Monitor database size growth (via cPanel)
- Check Scoutbook sync success rate

**Future Enhancement:**
- Integrate with service like Sentry, Rollbar, or New Relic

---

## 12. Maintenance & Support

### 12.1 Regular Maintenance Tasks

**Weekly:**
- Review Scoutbook sync logs
- Check for duplicate persons (automated report)
- Monitor orphaned persons list

**Monthly:**
- Database backup (manual export via cPanel)
- Review audit logs for unusual activity
- Check disk usage (storage/logs, storage/app)

**Annually:**
- Update dependencies (Composer, npm)
- Review and archive old sync logs (keep 1 year)
- Recharter cleanup (remove aged-out scouts)

### 12.2 Backup Strategy

**Database Backups:**
- HostGator automatic daily backups (retained 30 days)
- Manual monthly exports stored off-server

**File Backups:**
- Version control (Git) for code
- Backup `.env` file securely (contains credentials)
- Export critical data (families, persons) to CSV monthly

### 12.3 Update Process

**Backend Updates:**
```bash
cd /home/username/public_html/scoutdb/api
composer update
php artisan migrate
php artisan config:cache
```

**Frontend Updates:**
```bash
# Local machine
cd frontend
npm update
npm run build

# Upload dist/* to server
```

---

## 13. Future Enhancements

### 13.1 Direct Mailchimp API Integration

**Phase 2 Feature:**
- Replace CSV export with direct API sync
- Use Mailchimp Marketing API v3
- Bidirectional sync (update, create, archive contacts)
- Real-time sync triggers

**Implementation:**
- Install Mailchimp PHP SDK: `composer require mailchimp/marketing`
- Store API key and Audience ID in settings
- Create `MailchimpSyncService`
- Add `/api/sync/mailchimp` endpoint

### 13.2 Advanced Scoutbook Integration

**Potential Features:**
- Sync advancement data (rank progress, adventures completed)
- Sync event attendance
- Two-way sync (update Scoutbook from app)

### 13.3 Mobile App

**React Native App:**
- Share API with web app
- Offline mode (store data locally)
- Push notifications for expiration reminders
- QR code check-in for events

### 13.4 Advanced Reporting

**Custom Report Builder:**
- Drag-and-drop field selection
- Visual filters (date ranges, multi-select)
- Export to Excel with formatting
- Scheduled email reports

---

## 14. Appendix

### 14.1 Technology References

**Laravel:**
- Documentation: https://laravel.com/docs/10.x
- Eloquent ORM: https://laravel.com/docs/10.x/eloquent
- Task Scheduling: https://laravel.com/docs/10.x/scheduling

**React:**
- Documentation: https://react.dev/
- React Query: https://tanstack.com/query/latest/docs/react/overview
- React Router: https://reactrouter.com/

**Symfony Panther (Web Scraping):**
- Documentation: https://github.com/symfony/panther

**HostGator:**
- PHP Support: https://www.hostgator.com/help/article/supported-php-versions
- Cron Jobs: https://www.hostgator.com/help/article/cron-jobs

### 14.2 Third-Party Libraries

**Backend:**
- `laravel/framework` - Core framework
- `symfony/panther` - Web scraper
- `league/csv` - CSV parsing/generation
- `predis/predis` - Redis client (if caching needed)

**Frontend:**
- `react`, `react-dom` - UI library
- `react-router-dom` - Routing
- `@tanstack/react-query` - Server state management
- `axios` - HTTP client
- `react-hook-form` - Form handling
- `zod` - Schema validation
- `@headlessui/react` - Accessible components
- `tailwindcss` - Utility CSS
- `date-fns` - Date formatting

### 14.3 Database Size Estimates

**Assumptions:**
- 150 families
- 200 persons (scouts, parents, siblings, leaders)
- 1 year of audit logs
- 365 sync logs

**Storage:**
- `families`: ~50 KB
- `persons`: ~100 KB
- `scouts` + `adult_leaders`: ~50 KB
- `audit_logs` (1 year): ~5 MB
- `sync_logs` (1 year): ~1 MB
- **Total:** ~6.2 MB (negligible)

**Growth Rate:**
- +10% annually (new families join, some age out)
- Audit logs: +5 MB/year
- **Expected size after 5 years:** ~30 MB

---

## Document Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-11-30 | Initial | Complete technical specification |

---

**Reviewed By:** [Pending]
**Review Date:** [Pending]
