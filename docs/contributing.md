# Contributing to MQMS

Thank you for your interest in contributing to the **Material and Quantity Management System (MQMS)**! MQMS is an enterprise resource planning (ERP) system designed for construction, engineering, and large-scale manufacturing to track project resources, contracts, components, procurement, and site consumption.

This guide outlines our development workflow, coding conventions, architectural standards, and submission guidelines to ensure a smooth and productive collaboration.

---

## Table of Contents
1. [Code of Conduct](#code-of-conduct)
2. [Getting Started](#getting-started)
   - [Prerequisites](#prerequisites)
   - [Local Development Setup](#local-development-setup)
   - [Running the Application](#running-the-application)
3. [Git Workflow & Branching Strategy](#git-workflow--branching-strategy)
   - [Branch Naming Conventions](#branch-naming-conventions)
   - [Commit Guidelines](#commit-guidelines)
4. [Coding Standards & Architectural Guidelines](#coding-standards--architectural-guidelines)
   - [Backend (PHP / Laravel 10)](#backend-php--laravel-10)
   - [Audit Trails & Data Integrity](#audit-trails--data-integrity)
   - [Role-Based Access Control (RBAC)](#role-based-access-control-rbac)
   - [API & Third-Party Integrations](#api--third-party-integrations)
   - [Frontend & UI Development](#frontend--ui-development)
   - [UI & Styling Rules](#ui--styling-rules)
5. [Testing & Quality Assurance](#testing--quality-assurance)
   - [Running Automated Tests](#running-automated-tests)
   - [Writing Tests](#writing-tests)
   - [Code Formatting](#code-formatting)
6. [Submitting Changes (Pull Request Process)](#submitting-changes-pull-request-process)
   - [Pre-Submission Checklist](#pre-submission-checklist)
   - [Opening a Pull Request](#opening-a-pull-request)
   - [Code Review Process](#code-review-process)
7. [Reporting Issues & Security Vulnerabilities](#reporting-issues--security-vulnerabilities)

---

## Code of Conduct

We are committed to providing a welcoming, inclusive, and harassment-free experience for everyone. Please be respectful, constructive, and professional in all communications, issue discussions, and pull request reviews.

---

## Getting Started

### Prerequisites
Before you begin, ensure you have the following installed on your machine:
- **PHP**: `^8.1` or higher (PHP 8.2 or 8.3 recommended) with extensions: `pdo`, `pdo_mysql`, `sqlite3`, `mbstring`, `xml`, `curl`, `gd`
- **Composer**: `v2.x`
- **Node.js**: `v18.x` or higher and **npm**
- **Database**: MySQL 8.0+ / MariaDB 10.5+ (or SQLite for local development and testing)
- **Git**

### Local Development Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/jplataquin/mqms.git
   cd mqms
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies:**
   ```bash
   npm install
   ```

4. **Environment Configuration:**
   Copy the example environment file and generate the application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database:**
   Update your `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mqms
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   ```

6. **Run Migrations and Seeders:**
   Initialize the database tables and reference seed data:
   ```bash
   php artisan migrate --seed
   ```

7. **Passport Setup (if required):**
   ```bash
   php artisan passport:keys
   ```

### Running the Application

To run both Vite for asset compilation and the Laravel local server concurrently:
```bash
npm run dev
```
By default, this serves the application on `http://localhost:8002` and runs Vite for hot module replacement.

---

## Git Workflow & Branching Strategy

We follow a branch-based workflow. All development work should be done in dedicated branches and merged into `main` via pull requests.

### Branch Naming Conventions
Use descriptive branch names prefixed by their purpose:
- `feature/<short-description>`: Adding new functionality (e.g., `feature/contract-item-export`)
- `bugfix/<short-description>` or `fix/<short-description>`: Resolving a bug (e.g., `bugfix/po-item-quantity-calc`)
- `refactor/<short-description>`: Code improvements without behavioral changes (e.g., `refactor/material-request-service`)
- `docs/<short-description>`: Documentation changes only (e.g., `docs/contributing-guide`)
- `test/<short-description>`: Adding or modifying test suites

### Commit Guidelines
- **Atomic Commits**: Keep commits focused and logically grouped. Avoid combining unrelated changes into a single commit.
- **Commit Messages**:
  - Write clear, concise subject lines in the imperative mood (e.g., `Add export functionality for material requests`).
  - Provide additional context in the commit body when necessary to explain *why* the change was made.
  - Reference relevant issue numbers when applicable (e.g., `Fixes #42`).

---

## Coding Standards & Architectural Guidelines

### Backend (PHP / Laravel 10)
- **Code Style**: Strictly adhere to **PSR-12** standards and standard Laravel conventions.
- **Strict Typing**: Use proper typehints and return types on class methods where practical.
- **Eloquent Models & Relationships**:
  - Define clear relationships (`hasMany`, `belongsTo`, `belongsToMany`, etc.).
  - Explicitly define `$fillable` or `$guarded` attributes on models.
  - Utilize `SoftDeletes` on models that manage critical entity lifecycles to prevent accidental loss of historical records.
- **Fat Models, Thin Controllers**:
  - Keep controllers focused on handling requests and formatting responses.
  - Encapsulate business logic into dedicated services, helpers, or model methods.

### Audit Trails & Data Integrity
MQMS enforces strict audit tracking across domain models. Whenever creating, updating, or deleting entities:
- Maintain `created_by`, `updated_by`, and `deleted_by` fields referencing the authenticated user ID.
- Utilize comments/audit logs for status transitions and approval workflows (e.g., Material Quantity Requests, Purchase Orders).

### Role-Based Access Control (RBAC)
- All privileged actions must be gated by the project's RBAC system.
- Ensure appropriate access codes (`AccessCode`) and user roles (`Role`, `UserRole`) are verified using existing middleware or authorization listeners (`LoadRolesAndAccessCodes`).

### API & Third-Party Integrations
- All external API endpoints follow the `/api/call/{endpoint}` URL pattern.
- Requests are authenticated via HMAC-SHA256 signatures with `X-API-KEY`, `X-TIMESTAMP`, and `X-SIGNATURE` headers.
- Refer to [API_DOCUMENTATION.md](../API_DOCUMENTATION.md) for full endpoint specifications and authentication requirements.

### Frontend & UI Development
- **Framework**: Adarna JS framework integrated with Laravel UI and Bootstrap 5.
- **Asset Bundling**: Managed via Vite.
- Ensure JavaScript modules are organized cleanly within `resources/js/` and UI component templates within `resources/ui_components/` or `resources/views/`.

### UI & Styling Rules
To maintain visual consistency, dark/light theme compatibility, and visual hierarchy:
- **Background Restrictions**: Do **NOT** use `bg-light` or `bg-white` CSS utility classes unless explicitly requested. Allow components, cards, tables, and dialogs to inherit their natural theme styles, borders, or subtle theme-tinted backgrounds (e.g., `bg-primary bg-opacity-10`).
- **Visual Polish**: Ensure clean alignment, consistent spacing, intuitive empty states, and legible contrasts across desktop and mobile screen sizes.

---

## Testing & Quality Assurance

All contributions must be accompanied by relevant automated tests. We strive to maintain comprehensive test coverage across critical business flows.

### Running Automated Tests
Run the PHPUnit test suite:
```bash
php artisan test
```
or run individual test classes:
```bash
./vendor/bin/phpunit tests/Feature/MaterialItemTest.php
```

### Writing Tests
- **Location**:
  - Unit tests: `tests/Unit/`
  - Feature & integration tests: `tests/Feature/`
- **Database Testing**:
  - Use the `Illuminate\Foundation\Testing\RefreshDatabase` trait for database isolation.
  - Follow the existing pattern of configuring an in-memory SQLite database in test setups for fast, reproducible execution:
    ```php
    public function createApplication()
    {
        $app = parent::createApplication();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);

        return $app;
    }
    ```
- **Bug Fixes**: Always include a regression test that fails before the fix and passes after the fix.

### Code Formatting
Format your PHP code using Laravel Pint before submitting:
```bash
./vendor/bin/pint
```
Verify frontend assets compile without issues:
```bash
npm run build
```

---

## Submitting Changes (Pull Request Process)

### Pre-Submission Checklist
Before submitting a pull request, ensure you have completed the following:
- [ ] Your code complies with the project's coding standards (PSR-12, Pint-formatted).
- [ ] No extraneous debug logs, `dd()`, `dump()`, or `console.log()` statements remain.
- [ ] All automated tests pass (`php artisan test`).
- [ ] Any new feature is covered with unit/feature tests.
- [ ] Frontend assets build cleanly (`npm run build`).
- [ ] UI changes comply with the UI & Styling Rules (no unintended `bg-light`/`bg-white`).
- [ ] Documentation has been updated (e.g., API documentation or guides if new endpoints or parameters were introduced).

### Opening a Pull Request
1. Push your branch to the remote repository:
   ```bash
   git push origin <your-branch-name>
   ```
2. Open a Pull Request targeting the `main` branch.
3. Fill out the PR description with:
   - **Summary**: High-level explanation of the changes.
   - **Motivation**: Why this change is necessary and what problem it solves.
   - **Testing Performed**: Explanation of test cases executed.
   - **Visuals**: Screenshots or recordings for any UI/UX modifications.
   - **Related Issues**: Closes or references `#<issue-number>`.

### Code Review Process
- A repository maintainer will review your pull request for code quality, architectural consistency, and test coverage.
- Address any requested changes promptly.
- Once approved, your pull request will be merged into `main`.

---

## Reporting Issues & Security Vulnerabilities

- **Bug Reports**: Open an issue on GitHub describing the bug, including steps to reproduce, expected vs. actual behavior, and environment details.
- **Feature Requests**: Open an issue describing the proposed feature, user stories, and potential implementation approaches.
- **Security Vulnerabilities**: If you discover a security vulnerability, please do **NOT** open a public issue. Instead, report it privately to the maintainers so it can be resolved securely.

---

Thank you for helping make MQMS better!
