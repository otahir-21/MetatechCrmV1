# 🚀 Metatech CRM v1

**Advanced Multi-Tenant CRM System with Internal Management**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-yellow)](LICENSE)

---

## 📋 Table of Contents

- [Overview](#overview)
- [System Architecture](#system-architecture)
- [Key Features](#key-features)
- [Installation](#installation)
- [Usage](#usage)
- [Documentation](#documentation)

---

## 🎯 Overview

**Metatech CRM** is a comprehensive multi-tenant CRM system designed for **Metatech Digital Marketing Company**. The system consists of three main components:

1. **Product Owner Dashboard** - System administration and company management
2. **Internal CRM** - Metatech's own CRM for managing digital marketing clients
3. **Customer CRMs** - Isolated CRM instances for each client company

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     METATECH CRM SYSTEM                         │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────┐  ┌──────────────────────┐  ┌──────────────────────┐
│  PRODUCT OWNER       │  │   INTERNAL CRM       │  │   CUSTOMER CRMS      │
│  DASHBOARD           │  │   (Metatech Team)    │  │   (Client Companies) │
├──────────────────────┤  ├──────────────────────┤  ├──────────────────────┤
│                      │  │                      │  │                      │
│ • Create Companies   │  │ • Sales Management   │  │ Company A            │
│ • Manage Employees   │  │ • Lead Tracking      │  │ Company B            │
│ • System Settings    │  │ • Deal Pipeline      │  │ Company C            │
│ • Bootstrap System   │  │ • Team Management    │  │ ...                  │
│ • Audit Logs         │  │ • Projects & Tasks   │  │                      │
│                      │  │                      │  │ Isolated instances   │
│ Access: Super Admin  │  │ Access: Metatech     │  │ Access: Company      │
│         Only         │  │         Employees    │  │         Staff Only   │
└──────────────────────┘  └──────────────────────┘  └──────────────────────┘
         │                         │                          │
         └─────────────────────────┴──────────────────────────┘
                                   │
                    ┌──────────────▼──────────────┐
                    │   SHARED DATABASE           │
                    │   • Users (All Roles)       │
                    │   • Companies               │
                    │   • Projects & Tasks        │
                    │   • Leads & Deals (New)     │
                    │   • Audit Logs              │
                    └─────────────────────────────┘
```

---

## ✨ Key Features

### 🔐 **Authentication & Security**
- ✅ Multi-tenant architecture with subdomain isolation
- ✅ Role-based access control (RBAC)
- ✅ JWT authentication for API
- ✅ Session-based web authentication
- ✅ Password reset functionality
- ✅ Email invitation system

### 👥 **User Management**
- ✅ Product Owner (System Super Admin)
- ✅ Internal Employees (Metatech team)
- ✅ Company Admins
- ✅ Company Staff
- ✅ User status management (Active, Blocked, Suspended)

### 🏢 **Multi-Tenant System**
- ✅ Subdomain-based company isolation
- ✅ Company creation and management
- ✅ Company owner invitation system
- ✅ Staff invitation for companies
- ✅ Company status management

### 📊 **Internal CRM (For Metatech)**
- ✅ Employee management
- ✅ Department & designation tracking
- ✅ Internal project management
- ✅ Task assignment system
- ✅ Audit logging
- 🚧 **Sales & Lead Management** (In Development)
  - Lead/Deal tracking
  - Sales pipeline (Kanban board)
  - Client management
  - Deal stages with drag-and-drop

### 🎯 **Project & Task Management**
- ✅ Create and manage projects
- ✅ Task assignment to team members
- ✅ Task comments and collaboration
- ✅ Project access control
- ✅ Status tracking

### 📧 **Email System**
- ✅ Company owner invitations
- ✅ Employee invitations
- ✅ Staff invitations
- ✅ Password reset emails
- ✅ Custom email templates

### 📝 **Audit & Logging**
- ✅ Bootstrap audit logs
- ✅ System-wide audit trail
- ✅ User action tracking
- ✅ Change history

---

## 🔑 User Roles & Access

| Role | Access Level | Permissions |
|------|-------------|-------------|
| **Product Owner** | System-wide | Create companies, manage all users, system settings |
| **Internal Super Admin** | Internal CRM | Manage Metatech employees, view all data |
| **Internal Admin** | Internal CRM | Manage employees, limited admin access |
| **Internal User** | Internal CRM | Basic access, own assignments |
| **Company Super Admin** | Company CRM | Full control over their company |
| **Company Admin** | Company CRM | Manage company staff and data |
| **Company User** | Company CRM | Basic company access |

---

## 📊 Database Schema

### Core Tables

```
users
├── id
├── email (unique)
├── password
├── first_name, last_name, name
├── role (super_admin, admin, user)
├── company_name (nullable)
├── subdomain (nullable)
├── is_metatech_employee (boolean)
├── status (active, blocked, suspended)
├── department, designation
└── timestamps

companies
├── id
├── company_name (unique)
├── subdomain (unique)
├── company_super_admin_id (FK → users)
├── status (active, blocked, suspended, trial)
├── subscription_details (JSON)
└── timestamps

projects
├── id
├── name, description
├── status (active, completed, on-hold)
├── start_date, end_date
├── created_by (FK → users)
├── company_id (nullable, FK → companies)
└── timestamps

tasks
├── id
├── title, description
├── project_id (FK → projects)
├── assigned_to (FK → users)
├── status (pending, in_progress, completed)
├── priority (low, medium, high)
├── due_date
└── timestamps
```

### 🆕 Upcoming: Sales Module Tables

```
clients (Coming Soon)
├── id
├── name
├── contact_person
├── email, phone
├── address
├── created_by (FK → users)
└── timestamps

deals (Coming Soon)
├── id
├── title
├── client_id (FK → clients)
├── value, currency
├── stage (new_lead, contacted, qualified, proposal, negotiation, won, lost)
├── priority (low, medium, high)
├── assigned_to (FK → users)
├── expected_close_date
├── lead_source
├── notes
└── timestamps
```

---

## 🚀 Installation

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL/MariaDB
- Node.js & NPM
- XAMPP (or similar web server)

### Step 1: Clone Repository

```bash
git clone https://github.com/metatech-offical/MetatechCrmV1.git
cd MetatechCrmV1
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 3: Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret
```

### Step 4: Database Setup

```bash
# Update .env with your database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=MetatechCrmV1DB
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RolePermissionSeeder
```

### Step 5: Build Assets

```bash
npm run build
```

### Step 6: Run Server

```bash
# Development server
php artisan serve

# Access at: http://127.0.0.1:8000
```

---

## 🎮 Usage

### Access Points

| System | URL | Login Type |
|--------|-----|------------|
| **Product Owner Dashboard** | `http://127.0.0.1:8000/login` | Product Owner Login |
| **Internal CRM** | `http://127.0.0.1:8000/internal/login` | Internal CRM Login |
| **Company CRM** | `http://company.127.0.0.1:8000/login` | Company Login |

### Default Accounts

After initial setup, you can create accounts:

```bash
# Create Product Owner
php artisan tinker
> $user = User::create([
    'email' => 'admin@productowner.com',
    'password' => Hash::make('password'),
    'first_name' => 'Product',
    'last_name' => 'Owner',
    'name' => 'Product Owner',
    'role' => 'super_admin',
    'is_metatech_employee' => false,
    'company_name' => null,
    'subdomain' => null,
  ]);
```

---

## 📚 Documentation

Detailed documentation is available in the `/docs` folder:

### Setup & Deployment
- [Deployment Guide](docs/DEPLOYMENT_GUIDE.md)
- [Deployment Checklist](docs/DEPLOYMENT_CHECKLIST.md)
- [Email Setup Guide](docs/EMAIL_SETUP_GUIDE.md)
- [Single Domain Deployment](docs/SINGLE_DOMAIN_DEPLOYMENT.md)

### Internal CRM
- [Internal CRM Setup](docs/INTERNAL_CRM_SETUP.md)
- [Internal CRM Permissions](docs/INTERNAL_CRM_PERMISSIONS.md)
- [Internal CRM Implementation Plan](docs/INTERNAL_CRM_IMPLEMENTATION_PLAN.md)
- [Employee Management](docs/EMPLOYEE_MANAGEMENT_COMPLETE.md)

### Features & Guides
- [Role-Based Access Control](docs/ROLE_BASED_ACCESS_CONTROL_EXPLANATION.md)
- [Task System](docs/TASK_SYSTEM_COMPLETE.md)
- [Audit Log System](docs/AUDIT_LOG_SYSTEM_COMPLETE.md)
- [Password Reset](docs/PASSWORD_RESET_IMPLEMENTATION.md)
- [Client Owner Invitation](docs/CLIENT_OWNER_INVITATION_PLAN.md)

### API Documentation
- [API Quick Reference](docs/API_QUICK_REFERENCE.md)
- [API Status](docs/API_STATUS.md)

### Testing
- [Testing Guide](docs/TESTING_GUIDE_PHASE1_PHASE2.md)
- [Frontend Testing](docs/FRONTEND_TESTING_GUIDE.md)
- [Quick Start Testing](docs/QUICK_START_TESTING.md)

---

## 🛠️ Tech Stack

- **Backend:** Laravel 11.x
- **Frontend:** Blade Templates, Tailwind CSS
- **Database:** MySQL
- **Authentication:** JWT + Session
- **Email:** Laravel Mail
- **Permissions:** Spatie Laravel Permission
- **API:** RESTful API

---

## 🔄 Current Development

### ✅ Completed Features
- Multi-tenant architecture
- Authentication system
- User management
- Company management
- Project & task system
- Email invitations
- Audit logging
- Internal CRM dashboard

### 🚧 In Progress
- **Sales & Lead Management Module**
  - Client database
  - Deal/Lead tracking
  - Kanban pipeline board
  - Sales reporting

### 📋 Planned Features
- Customer portal
- Advanced reporting & analytics
- File management system
- Calendar & scheduling
- Mobile app API
- Notification system
- Webhooks & integrations

---

## 📊 Project Status

```
Progress: ███████████████████░░ 85%

✅ Core System: Complete
✅ Internal CRM: 90% Complete
🚧 Sales Module: In Development
📋 Customer Portal: Planned
📋 Mobile API: Planned
```

---

## 🤝 Contributing

This is a private project for Metatech. For contributions:

1. Create a feature branch
2. Make your changes
3. Submit a pull request
4. Wait for code review

---

## 📞 Support

For questions or support:
- **Email:** support@metatech.ae
- **Documentation:** `/docs` folder
- **Issues:** GitHub Issues

---

## 📄 License

Proprietary - © 2026 Metatech. All rights reserved.

---

## 🏆 Team

Developed by **Metatech Development Team**

---

**Last Updated:** January 2026
**Version:** 1.0.0

