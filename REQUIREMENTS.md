# Scout Family Management System - Requirements Document

## Project Overview

A web-based family management system for Pack 97 Cub Scouts that groups scouts, parents, siblings, and adult leaders into family units, with bidirectional synchronization to Mailchimp for communication purposes.

**Domain:** https://www.pack97.com/scoutdb
**Platform:** HostGator Shared Hosting
**Technology Stack:** PHP (Laravel) + React Frontend + MySQL

---

## 1. Core Objectives

1. **Family Grouping:** Organize all pack members (scouts, parents, siblings, adult leaders) into logical family units
2. **Mailchimp Integration:** Sync family data to Mailchimp audience with support for multiple parents per family
3. **Scoutbook Integration:** Daily automated import of BSA roster data from Scoutbook
4. **Data Management:** Provide comprehensive tools for managing member information and family relationships
5. **Authentication:** Leverage WordPress authentication for secure access control

---

## 2. User Roles & Permissions

### 2.1 User Types

#### Administrator
- **Who:** Cubmaster, Committee Chair, or designated by existing admin
- **Permissions:**
  - Full access to all data (read, create, update, delete)
  - Manage user permissions (grant/revoke admin, edit, view-only access)
  - Configure system settings
  - Execute Scoutbook sync
  - Execute Mailchimp sync
  - Export CSV files
  - View audit logs

#### Editor
- **Who:** Den Leaders, Assistant Cubmaster, designated parents
- **Permissions:**
  - View all pack data
  - Edit family information
  - Edit member information
  - Add/remove family members
  - Create new families
  - Cannot manage user permissions
  - Cannot delete families or members

#### Viewer
- **Who:** Committee members, designated volunteers
- **Permissions:**
  - View all pack data (read-only)
  - Export reports
  - Cannot modify any data

### 2.2 Authentication & Authorization

- **Primary Method:** WordPress authentication integration
  - Users log in with WordPress credentials
  - Session shared between WordPress and ScoutDB application
  - Logout from either system logs out of both

- **Initial Admin Setup:**
  - First-time setup creates admin accounts for Cubmaster and Committee Chair
  - Admins can promote other users from the member database

- **WordPress Menu Integration:**
  - Add "Scout Database" menu item to WordPress admin menu
  - Link to `/scoutdb` application

---

## 3. Data Model

### 3.1 Entities

#### Family
- **Description:** A household unit containing one or more persons
- **Attributes:**
  - Family ID (auto-generated)
  - Family Name (e.g., "Johnson Family", "Smith-Jones Family")
  - Primary Address (street, city, state, zip)
  - Primary Phone Number
  - Notes (free-text field for internal use)
  - Created Date
  - Last Modified Date

#### Person
- **Description:** An individual member (scout, parent, sibling, or adult leader)
- **Attributes:**
  - Person ID (auto-generated)
  - BSA Member ID (if applicable, from roster)
  - Family ID (foreign key, nullable for orphaned persons)
  - Person Type (Scout, Parent, Sibling, Adult Leader)
  - Prefix (Mr., Mrs., Ms., Dr., etc.)
  - First Name
  - Middle Name
  - Last Name
  - Suffix (Jr., Sr., III, etc.)
  - Nickname
  - Gender
  - Date of Birth / Age
  - Email Address
  - Phone Number
  - Created Date
  - Last Modified Date

#### Scout (extends Person)
- **Additional Attributes:**
  - Grade (First Grade, Second Grade, etc.)
  - Rank (Lion, Tiger, Wolf, Bear, Webelos, Arrow of Light)
  - Den Assignment
  - Registration Expiration Date
  - Registration Status (New, Re-Registered, Transfer, Multiple)
  - Youth Protection Training Status
  - BSA Program (always "Cub Scouting" for now)

#### Adult Leader (extends Person)
- **Additional Attributes:**
  - Leadership Positions (array/multi-select):
    - Den Leader
    - Assistant Den Leader
    - Cubmaster
    - Assistant Cubmaster
    - Committee Chair
    - Committee Member
    - Unit Treasurer
    - Unit Advancement Chair
    - Key 3 Delegate
    - Council Unit Representative
    - Executive Officer
  - Youth Protection Training Status
  - YPT Completion Date
  - YPT Expiration Date
  - Registration Expiration Date

#### User Permissions
- **Attributes:**
  - User ID (links to WordPress user)
  - Person ID (links to Person in database)
  - Role (Admin, Editor, Viewer)
  - Granted By (User ID of admin who granted permission)
  - Granted Date

### 3.2 Relationships

- **Family → Person:** One-to-Many (a family has multiple persons)
- **Person → Family:** Many-to-One (a person belongs to one family, or null if orphaned)
- **Parent → Multiple Families:** A person can be associated with multiple email addresses in Mailchimp, creating separate parent records per family

---

## 4. Functional Requirements

### 4.1 Family Management

#### FR-1.1: View Families
- Display paginated list of all families
- Show family name, number of scouts, number of adults, primary contact email
- Search/filter by family name, scout name, email address
- Sort by family name, last modified date, number of scouts

#### FR-1.2: Create Family
- Create new family with name and address
- Optionally add members during creation
- Validate required fields (family name)
- Generate unique family ID

#### FR-1.3: Edit Family
- Update family name, address, phone, notes
- Add existing persons to family
- Remove persons from family
- Merge duplicate families (with confirmation)

#### FR-1.4: Delete Family
- Admin-only permission
- Soft delete (mark as inactive, don't hard delete)
- Cascade options:
  - Orphan all members (remove family association)
  - Delete all members (with confirmation)
- Require confirmation with family name entry

#### FR-1.5: View Family Details
- Display complete family roster:
  - All parents (with email, phone)
  - All scouts (with den, rank, expiration date)
  - All siblings (with age if available)
  - All adult leaders in the family
- Show Mailchimp sync status (synced, pending, error)
- Display last Scoutbook import date for each member

### 4.2 Person Management

#### FR-2.1: View Persons
- Display paginated list of all persons
- Show name, type (Scout/Parent/Sibling/Leader), family, email
- Filter by person type, family, orphaned status
- Search by name, email, BSA Member ID
- Highlight orphaned persons (no family assignment)

#### FR-2.2: Create Person
- Create new person with type selection (Scout/Parent/Sibling/Leader)
- Dynamic form based on person type:
  - Scout: show rank, grade, den, expiration fields
  - Adult Leader: show position checkboxes, YPT fields
  - Parent: show email, phone (required)
  - Sibling: show basic info only
- Optionally assign to family during creation
- Validate email format, phone format
- Check for duplicate BSA Member IDs (warn, don't block)

#### FR-2.3: Edit Person
- Update all person attributes
- Change person type (e.g., promote sibling to scout)
- Reassign to different family
- Add/remove leadership positions for adults
- Handle character encoding properly (support names like Gastón, Sebastián)

#### FR-2.4: Delete Person
- Admin-only permission
- Soft delete (mark as inactive)
- Warn if person has active scout with future expiration date
- Require confirmation

#### FR-2.5: Merge Duplicate Persons
- Detect potential duplicates (same first + last name, similar age)
- Display side-by-side comparison
- Allow admin to select primary record
- Merge BSA Member IDs, family associations, update Mailchimp

### 4.3 Scoutbook Integration

#### FR-3.1: Manual Roster Import
- Upload CSV file (Roster_Report.csv format)
- Preview import results before committing
- Display:
  - New persons to be created
  - Existing persons to be updated
  - Potential duplicates requiring review
  - Data quality issues (missing emails, encoding errors)
- Map scouts to families using `pgprimaryemail` logic
- Handle adults with multiple positions (deduplicate by BSA Member ID)
- Log import results (# added, # updated, # skipped, errors)

#### FR-3.2: Automated Daily Sync
- Server-side scheduled job (Laravel cron)
- Use PHP-based web scraper (Playwright PHP or Goutte) to:
  - Authenticate to Scoutbook
  - Download current roster
  - Parse roster data
  - Execute import logic (same as manual import)
- Run daily at 2:00 AM server time
- Email admin on success/failure with summary
- Store scraper credentials securely (encrypted in database or .env file)

#### FR-3.3: Scoutbook Sync Configuration
- Admin settings page for Scoutbook credentials
- Test connection button
- Enable/disable auto-sync toggle
- Set sync schedule (time of day)
- View sync history log (last 30 days)

### 4.4 Mailchimp Integration

#### FR-4.1: Mailchimp Data Model
- Support Mailchimp's flat structure with these fields:
  - **Email Address** (primary key)
  - **First Name** (parent first name)
  - **Last Name** (parent last name)
  - **Phone Number** (family phone)
  - **BSA Member ID** (parent's BSA ID if leader, or primary scout's ID)
  - **ADDRESS** (street, city, state, zip combined)
  - **Scout 1** (scout full name)
  - **Scout 1 Den** (den/grade)
  - **Scout 1 Expiration** (expiration date MM/DD/YYYY)
  - **Scout 2** (scout full name)
  - **Scout 2 Den** (den/grade)
  - **Scout 2 Expiration** (expiration date MM/DD/YYYY)
  - **Scout 3** (scout full name)
  - **Scout 3 Den** (den/grade)
  - **Scout 3 Expiration** (expiration date MM/DD/YYYY)
  - **Sibling 1** (non-scout child full name)
  - **Sibling 2** (non-scout child full name)
  - **Sibling 3** (non-scout child full name)
  - **Leader Position 1** (adult position)
  - **Leader Position 2** (adult position)
  - **Leader Position 4** (adult position)
  - **TAGS** (comma-separated: "existing", "prospective")

#### FR-4.2: Multi-Parent Handling
- For families with multiple parent emails:
  - Create separate Mailchimp contact for each parent email
  - Each contact includes ALL scouts in the family
  - Each contact includes ALL siblings in the family
  - Each contact shows the parent's own leadership positions (if applicable)
- Example: Smith family with two parents with different email addresses:
  - Contact 1: parent1@example.com → First Name: Parent1, Scout 1: [child], Scout 2: [child], Leader Position 1: Cubmaster
  - Contact 2: parent2@example.com → First Name: Parent2, Scout 1: [child], Scout 2: [child]

#### FR-4.3: Export Mailchimp CSV
- Generate CSV file in exact Mailchimp import format
- Include all active families with at least one email address
- Handle families with 3+ scouts (limit to first 3 by expiration date, newest first)
- Handle families with 3+ siblings (limit to first 3 by age, youngest first)
- Properly format:
  - Phone numbers (standardize to format: 512-555-1234)
  - Addresses (concatenate to single line)
  - Dates (MM/DD/YYYY format)
  - Character encoding (UTF-8 with BOM for Excel compatibility)
- Generate separate rows for multi-parent families
- Include TAGS column:
  - "existing" for persons in BSA roster
  - "prospective" for persons in database but not in roster
- Download filename: `mailchimp_export_YYYYMMDD_HHMMSS.csv`

#### FR-4.4: Import Mailchimp Audience
- Upload Mailchimp audience export CSV
- Match existing contacts by email address
- Create new persons for unmatched emails
- Preserve Mailchimp-only data (notes, tags, custom fields)
- Handle orphaned contacts (in Mailchimp, not in roster):
  - Create person records with "prospective" tag
  - Flag for admin review
  - Option to keep, archive, or delete

#### FR-4.5: Mailchimp Sync Validation
- Pre-export validation report:
  - Families with no email addresses (warn, exclude from export)
  - Invalid email formats (error, require fix)
  - Families with 4+ scouts (warn, only first 3 exported)
  - Missing required fields (scout name, expiration)
  - Character encoding issues (detect corrupted characters)
  - Duplicate email addresses (warn, show conflicts)
- Allow admin to review and fix issues before export

### 4.5 Data Import & Migration

#### FR-5.1: Initial Data Migration
- **Step 1:** Import Roster_Report.csv
  - Create Person records for all scouts and adults
  - Create Family records by grouping on `pgprimaryemail`
  - Auto-assign persons to families
  - Flag orphaned persons (no email)

- **Step 2:** Import Mailchimp Audience
  - Match contacts to existing persons by email
  - Create new Person records for unmatched emails
  - Preserve Mailchimp tags ("existing", "prospective")
  - Extract sibling names from Sibling 1/2/3 fields if present

- **Step 3:** Manual Review & Cleanup
  - Review orphaned persons, manually assign to families
  - Review duplicate persons (same name), merge if appropriate
  - Review split families (same last name, different emails), link if appropriate
  - Validate family groupings

#### FR-5.2: Ongoing Data Quality
- **Duplicate Detection:**
  - Automated weekly scan for potential duplicates
  - Alert admin via dashboard notification
  - Criteria: same first + last name, age within 1 year

- **Orphaned Person Report:**
  - List all persons without family assignment
  - Filter by person type (scouts should rarely be orphaned)
  - One-click assign to existing family or create new family

- **Data Completeness Report:**
  - Show persons missing critical fields (email, phone, address)
  - Show scouts missing expiration dates
  - Show adults missing YPT status

### 4.6 Reporting & Exports

#### FR-6.1: Standard Reports
- **Family Directory:**
  - PDF/CSV export of all families
  - Grouped by den or alphabetically
  - Includes addresses, phone numbers, emails
  - Optional: include photos (future enhancement)

- **Scout Roster by Den:**
  - List all scouts grouped by den/grade
  - Show scout name, parent names, parent emails, expiration date
  - Highlight expiring soon (within 60 days)

- **Adult Leader Roster:**
  - List all adult leaders grouped by position
  - Show name, email, phone, YPT status, YPT expiration
  - Highlight YPT expiring soon (within 90 days)

- **Expiration Dashboard:**
  - Visual dashboard showing:
    - Scouts expiring in next 30 days
    - Scouts expiring in 31-60 days
    - Scouts expiring in 61-90 days
    - Expired scouts (past expiration)
  - One-click export to CSV
  - Email reminder capability (future enhancement)

#### FR-6.2: Custom Exports
- Allow admin to select:
  - Fields to include
  - Filter criteria (den, expiration range, person type)
  - Sort order
  - Export format (CSV, Excel, PDF)

### 4.7 User Interface Requirements

#### FR-7.1: Dashboard (Home Page)
- **Summary Statistics:**
  - Total families
  - Total scouts (by rank)
  - Total adult leaders (by position)
  - Scouts expiring in next 30 days
  - Last Scoutbook sync date/time

- **Quick Actions:**
  - Add Family
  - Add Person
  - Import Roster
  - Export Mailchimp CSV
  - Sync from Scoutbook Now

- **Recent Activity:**
  - Last 10 modified families
  - Last 10 modified persons
  - Recent import/sync logs

#### FR-7.2: Family List View
- Data table with columns:
  - Family Name
  - # Scouts
  - # Adults
  - Primary Email
  - Primary Phone
  - Last Modified
  - Actions (View, Edit, Delete)
- Search bar (filters as you type)
- Pagination (25/50/100 per page)
- Bulk actions: export selected, delete selected (admin only)

#### FR-7.3: Family Detail View
- **Family Information Panel:**
  - Family name (editable inline)
  - Address (editable inline)
  - Phone (editable inline)
  - Notes (expandable text area)

- **Members Section:**
  - **Parents Tab:**
    - List all parents with email, phone
    - Add existing person as parent
    - Create new parent
    - Remove parent from family

  - **Scouts Tab:**
    - List all scouts with den, rank, expiration
    - Color-coded expiration status (green: >90 days, yellow: 30-90 days, red: <30 days, gray: expired)
    - Add existing person as scout
    - Create new scout
    - Remove scout from family

  - **Siblings Tab:**
    - List all non-scout children
    - Add existing person as sibling
    - Create new sibling
    - Remove sibling from family

  - **Adult Leaders Tab:**
    - List all adult leaders in family with positions
    - Add existing person as adult leader
    - Create new adult leader
    - Remove adult leader from family

- **Mailchimp Preview:**
  - Show how this family will appear in Mailchimp
  - Display all parent emails that will receive synced data
  - Show scout/sibling data per parent contact

#### FR-7.4: Person Detail View
- **Person Information Panel:**
  - All attributes editable based on person type
  - Photo upload placeholder (future enhancement)
  - BSA Member ID (linked to Scoutbook if available)
  - Family assignment (dropdown to change family)

- **Activity Log:**
  - Show when person was created
  - Show when person was last modified (and by whom)
  - Show Scoutbook sync history for this person
  - Show Mailchimp sync history for this person

#### FR-7.5: Import/Sync Interface
- **Scoutbook Sync Page:**
  - Manual trigger button
  - Sync status (running, completed, failed)
  - Real-time progress indicator
  - Results summary after completion
  - Sync history table (last 30 days)

- **CSV Import Page:**
  - Drag-and-drop file upload
  - File type selection (Roster_Report, Mailchimp Audience)
  - Preview table showing first 10 rows
  - Column mapping (if headers don't match expected)
  - Import button with confirmation
  - Results summary after import

#### FR-7.6: Mailchimp Export Interface
- **Pre-Export Validation:**
  - Run validation checks, display results
  - Group errors by severity (blocking, warning, info)
  - Links to fix issues (e.g., click family name to edit)

- **Export Options:**
  - Include prospective members (checkbox)
  - Date range filter (only scouts expiring before X date)
  - Preview first 10 rows before download

- **Download:**
  - Generate CSV button
  - Progress indicator for large datasets
  - Auto-download file when ready

#### FR-7.7: User Management Interface (Admin Only)
- **User List:**
  - All WordPress users who have been granted permissions
  - Show user name, email, role (Admin/Editor/Viewer), granted date
  - Actions: Edit role, Revoke access

- **Grant Access:**
  - Search for WordPress users
  - Select person record from database to link to user
  - Assign role (Admin/Editor/Viewer)
  - Save

### 4.8 System Configuration

#### FR-8.1: Settings Page (Admin Only)
- **Scoutbook Configuration:**
  - Scoutbook username/password (encrypted storage)
  - Unit number
  - Auto-sync enabled (toggle)
  - Sync schedule (time picker, timezone-aware)
  - Test connection button

- **Mailchimp Configuration:**
  - Mailchimp API key (for future direct sync)
  - Audience ID
  - Default tags

- **General Settings:**
  - Pack name
  - Pack number
  - Timezone
  - Date format preference
  - Contact email for error notifications

#### FR-8.2: Audit Logging
- Log all data modifications:
  - Who (user ID)
  - What (entity type, entity ID, action: create/update/delete)
  - When (timestamp)
  - Changes (before/after values for updates)
- Retain logs for 1 year
- Admin can view audit log, filter by user/date/entity type

---

## 5. Non-Functional Requirements

### 5.1 Performance
- Page load time: < 2 seconds for family/person list views
- Search/filter results: < 1 second
- CSV export generation: < 5 seconds for 500 families
- Scoutbook sync: < 2 minutes for 150 members

### 5.2 Security
- **Authentication:**
  - Leverage WordPress session management
  - Session timeout: 24 hours of inactivity
  - Secure session cookies (HttpOnly, Secure flags)

- **Authorization:**
  - Role-based access control enforced on all API endpoints
  - Validate permissions server-side (never trust client)

- **Data Protection:**
  - Encrypt Scoutbook credentials at rest (AES-256)
  - Use HTTPS for all traffic (already enforced by WordPress)
  - Sanitize all user inputs to prevent XSS, SQL injection
  - Use Laravel's built-in protection (CSRF tokens, prepared statements)

- **Data Privacy:**
  - PII (Personally Identifiable Information) should be visible only to authorized users
  - No public-facing pages (all require authentication)
  - Secure file uploads (validate file types, scan for malware)

### 5.3 Compatibility
- **Browser Support:**
  - Modern browsers: Chrome, Firefox, Safari, Edge (latest 2 versions)
  - Mobile browsers: iOS Safari, Chrome Android
  - Graceful degradation for older browsers

- **Responsive Design:**
  - Mobile-friendly (320px - 768px)
  - Tablet-optimized (768px - 1024px)
  - Desktop (1024px+)

- **Database:**
  - MySQL 5.7+ or MySQL 8.0+
  - Use Laravel migrations for schema management

### 5.4 Reliability
- **Data Integrity:**
  - Foreign key constraints to prevent orphaned records
  - Soft deletes to allow recovery
  - Database backups (manual export feature, recommend HostGator's auto-backup)

- **Error Handling:**
  - User-friendly error messages
  - Detailed error logging for debugging
  - Graceful degradation (if Scoutbook sync fails, don't crash app)

### 5.5 Maintainability
- **Code Quality:**
  - Follow Laravel and React best practices
  - Use ESLint for JavaScript, PHP_CodeSniffer for PHP
  - Comprehensive inline documentation

- **Version Control:**
  - Git repository on GitHub or Bitbucket
  - Use semantic versioning (e.g., v1.0.0)

- **Deployment:**
  - Deployment script for HostGator (via Git, FTP, or SSH)
  - Environment-specific configuration (.env files)
  - Database migration scripts

---

## 6. Data Migration & Transition Plan

### 6.1 Phase 1: Initial Setup (Week 1)
1. Install Laravel application on HostGator at `/scoutdb` path
2. Create MySQL database and user
3. Run database migrations
4. Configure WordPress authentication integration
5. Grant admin access to Cubmaster and Committee Chair

### 6.2 Phase 2: Data Import (Week 2)
1. Import Roster_Report.csv (manual upload)
2. Import Mailchimp audience export (manual upload)
3. Review auto-generated family groupings
4. Manual cleanup:
   - Assign orphaned persons to families
   - Merge duplicate persons
   - Link split families (if applicable)
   - Add missing siblings manually

### 6.3 Phase 3: Scoutbook Integration (Week 3)
1. Configure Scoutbook scraper credentials
2. Test manual Scoutbook sync
3. Enable automated daily sync
4. Monitor sync logs for errors

### 6.4 Phase 4: Mailchimp Workflow (Week 4)
1. Export first Mailchimp CSV from new system
2. Compare with current Mailchimp audience (validate data)
3. Upload to Mailchimp (manual)
4. Verify all contacts received correct data
5. Establish ongoing export schedule (weekly or as needed)

### 6.5 Phase 5: User Onboarding (Week 5)
1. Grant access to Den Leaders (Editor role)
2. Grant access to Committee Members (Viewer role)
3. Provide training/documentation
4. Collect feedback and address issues

---

## 7. Future Enhancements (Out of Scope for v1.0)

### 7.1 Potential Features
- **Communication:**
  - Email template builder for pack announcements
  - Automated expiration reminders (30/60/90 days)
  - Den-specific email blasts
  - SMS notifications via Twilio integration

- **Family Portal:**
  - Read-only portal for parents to view their family info
  - Parent self-service: update contact info, upload photos
  - View scout advancement progress

- **Advancement Tracking:**
  - Track rank advancement progress
  - Log completed adventures/electives
  - Generate advancement reports for Pack Committee
  - Sync advancement data from Scoutbook

- **Attendance Tracking:**
  - Check-in/check-out for den meetings and pack events
  - Attendance reports by scout, den, event
  - Export for insurance/recharter purposes

- **Event Management:**
  - Pack calendar integration
  - RSVP tracking for events
  - Event-specific communications

- **Photo Gallery:**
  - Upload photos from pack events
  - Tag scouts in photos
  - Parent access to download photos

- **Direct Mailchimp API Sync:**
  - Replace CSV export with direct API integration
  - Real-time sync (create/update/archive contacts)
  - Bidirectional sync (pull engagement data from Mailchimp)

- **Fundraising/Financial Tracking:**
  - Track popcorn sales, other fundraisers
  - Record pack dues payments
  - Generate financial reports for Treasurer

---

## 8. Success Criteria

### 8.1 Launch Readiness (v1.0)
- ✅ All Roster_Report.csv data successfully imported
- ✅ All Mailchimp audience contacts mapped to families
- ✅ <5% orphaned persons requiring manual review
- ✅ Automated Scoutbook sync runs successfully for 7 consecutive days
- ✅ First Mailchimp CSV export matches current audience data (95%+ accuracy)
- ✅ 3+ admins trained and able to use all features
- ✅ 5+ editors tested family/person management features
- ✅ WordPress authentication working for all user roles

### 8.2 Post-Launch Metrics (Month 1)
- Daily Scoutbook sync success rate: >95%
- Average family data completeness: >90% (all required fields populated)
- User adoption: 80%+ of Den Leaders log in at least once
- Data quality: <2% duplicate persons
- CSV export accuracy: >98% match with source data

---

## 9. Assumptions & Dependencies

### 9.1 Assumptions
- HostGator shared hosting supports PHP 8.0+, MySQL 8.0+, cron jobs
- WordPress installation at pack97.com is WordPress 5.0+ with REST API enabled
- Scoutbook website structure remains stable (for scraper)
- Current Mailchimp audience structure can accommodate "Sibling 1/2/3" fields
- User base is <200 families (scalability not a concern for v1.0)

### 9.2 Dependencies
- **External Systems:**
  - Scoutbook (BSA's system) availability for daily sync
  - Mailchimp API (future) or CSV upload capability
  - WordPress installation stability

- **Technical Resources:**
  - HostGator shared hosting access (cPanel, SSH, MySQL)
  - WordPress admin access
  - Mailchimp account with appropriate permissions

- **Human Resources:**
  - Admin availability for initial data cleanup
  - Den Leaders willing to test and provide feedback
  - Developer availability for bug fixes and enhancements

---

## 10. Glossary

- **BSA:** Boy Scouts of America
- **Cub Scouting:** BSA program for youth in grades K-5
- **Pack:** Local Cub Scout unit (e.g., Pack 97)
- **Den:** Sub-group within a pack, typically organized by grade level
- **Rank:** Scout advancement level (Lion, Tiger, Wolf, Bear, Webelos, Arrow of Light)
- **Scoutbook:** BSA's online platform for managing rosters, advancement, events
- **YPT:** Youth Protection Training (required for all adult leaders)
- **Recharter:** Annual renewal process for BSA units
- **Mailchimp:** Email marketing platform used for pack communications
- **Orphaned Person:** A person record not assigned to any family
- **Prospective Member:** Someone in Mailchimp audience but not in BSA roster (future scout or aged-out scout)

---

## Document Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-11-30 | Initial | Complete requirements based on stakeholder interview |

---

**Approved By:** [Pending]
**Approval Date:** [Pending]
