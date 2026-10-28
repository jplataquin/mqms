# MQMS - Material and Quantity Management System

## Overview
MQMS is a specialized ERP-style application designed for construction, engineering, or large-scale manufacturing industries. It manages the full lifecycle of project resources, from initial contract definition to procurement and site-level consumption.

## Core Functional Pillars

### 1. Project & Work Breakdown Structure (WBS)
- **Projects & Sections**: High-level organization of work sites or phases.
- **Contract Items**: Specific work packages or deliverables defined in the project contract.
- **Components**: Granular breakdown of contract items into technical parts for precise material/labor estimation.

### 2. Material & Quantity Control
- **Material Registry**: Centralized management of material types and specifications.
- **Quantity Estimation**: Defining material requirements for each component.
- **Material Quantity Requests (MQR)**: Formal workflow for requesting materials from the warehouse, including approval/rejection stages.

### 3. Procurement & Supply Chain
- **Lifecycle Flow**: `Material Quantity Request (MQR) -> Purchase Order (PO) (1-to-many: multiple POs per MQR) -> Purchase Order Received (1-to-many: multiple delivery receipts per PO)`.
- **Supplier Management**: Database of vendors and payment terms.
- **Material Canvassing**: Price comparison and quote management.
- **Purchase Orders (PO)**: Generation and tracking of orders tied to specific projects or material requests.
- **Receiving & Delivery**: Tracking actual physical deliveries against purchase orders.

### 4. Specialized Resource Tracking
- **Manpower Registry**: Labor resource and personnel tracking.
- **Coupon System**: Specialized module (e.g., fuel/site vouchers) tracking vehicle plate numbers and actual quantities.
- **Budgeting**: Monitoring "Contract Price" vs. "Budget Price" for profitability and cost control.

### 5. Administration & Governance
- **Role-Based Access Control (RBAC)**: Permission system using roles and "Access Codes".
- **Audit Trails**: Tracking of `created_by`, `updated_by`, and comments across entities.

### 6. API & 3rd-Party Integration
- **API Credential Management**: Users with appropriate access can generate and delete API Keys and Secret Keys for external system integrations.
- **Secure Authentication**: 3rd-party requests are authenticated via a dedicated middleware checking `X-API-KEY` and `X-SECRET-KEY` headers.
- **Integration Guide**: Detailed technical instructions can be found in [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

### 7. AI & Model Context Protocol (MCP) Integration
- **MCP Server**: Implements the Model Context Protocol (via `laravel/mcp`) exposing authenticated endpoints (`/api/mcp`) and local endpoints (`/mcp`) for AI clients (Gemini CLI, Claude Desktop, Cursor).
- **Personal Access Tokens**: Managed in the web UI under `/personal_access_tokens` via Laravel Sanctum Bearer token authentication.
- **MCP Documentation**: Detailed technical specifications, tool schemas, and client setup guides can be found in [MCP_DOCUMENTATION.md](MCP_DOCUMENTATION.md).

## Technical Foundation
- **Framework**: Laravel 10 (PHP)
- **ORM**: Eloquent (Heavy use of SoftDeletes and complex relationships)
- **Security**: Laravel Passport & Sanctum, plus custom 3rd-party API Key/Secret middleware.
- **Frontend/UI**: Laravel UI/Vite integration, Adarna JS framework for dynamic UI.
- **Utilities**: `spipu/html2pdf` for PDF generation (POs, MQRs)

## UI & Styling Standards
- **Backgrounds:** Do NOT use the `bg-light` or `bg-white` classes unless explicitly specified by the user. Allow components, cards, tables, and dialogs to inherit their natural theme styles, borders, or subtle theme-tinted backgrounds (e.g. `bg-primary bg-opacity-10`) to preserve contrast, dark mode compatibility, crisp visual hierarchy, and theme consistency.

## Testing Standards
- **Targeted Unit Testing:** Spend minimal time in unit tests. There is no need to run the entire application test suite for small changes or new features. Always run targeted tests (e.g., using `php artisan test --filter ...` or targeting specific test files). Only write new unit tests or run tests that directly concern the specific code that was touched or newly introduced.

## Parallel Agent Coordination
You are operating in a multi-agent environment where multiple Gemini sessions run in parallel git worktrees created via `gemini -w <name>`. To prevent merge conflicts and optimize work across concurrent agents, follow this coordination and shipping protocol:

1. **Self-Identification & Setup**:
   - Determine your agent ID from your workspace directory name (e.g., if working in `.gemini/worktrees/task-1`, your ID is `task-1`).
   - Automatically ensure the shared state folder is symlinked by running:
     `mkdir -p ../../.agent-states && ln -sfn ../../.agent-states .agent-states`
   - Your dedicated state file is `.agent-states/<your_agent_id>.md`.

2. **Heartbeat & Broadcasting**:
   - As soon as you define your plan or change intent, write/update your state file with:
     ```markdown
     # Agent: <your_agent_id>
     - **Status**: Active
     - **Updated At**: YYYY-MM-DD HH:MM UTC
     - **Current Intent / Task**: <brief description of what you are doing>
     - **Components Modified**:
       - <file or component 1>
       - <file or component 2>
     ```

3. **Dynamic Conflict Checking**:
   - Before executing code edits (`replace`, `write_file`, etc.), inspect all other files in `.agent-states/`.
   - Ignore your own state file.
   - Ignore state files with `Status: Idle` or with an `Updated At` older than 30 minutes (stale).
   - If an active agent lists overlapping files or components in `Components Modified`, adjust your plan, wait, or ask the user before proceeding.

4. **Automated Completion & Shipping Workflow (Ship to Main)**:
   Once your task is completed and verified, automatically ship your changes to `main`:
   a. **Descriptive Commit**:
      - Stage changed files: `git add <files>` (avoid committing untracked environment or temporary files).
      - Commit with a clear, descriptive message: `git commit -m "<type>(<scope>): <concise description of work>"`.
   b. **Sync with Main**:
      - Fetch latest remote changes: `git fetch origin main`
      - Merge main into your branch: `git merge origin/main`
   c. **Reconcile Conflicts (if any)**:
      - If conflict markers exist (`<<<<<<<`, `=======`, `>>>>>>>`), inspect and intelligently merge the code so neither your changes nor incoming changes are lost.
      - Perform syntax checks on touched files (e.g., `php -l <file>`).
      - Stage and commit the merge: `git commit -m "chore: merge origin/main and resolve conflicts"`
   d. **Direct Push to Main**:
      - Push your branch directly to the remote main branch: `git -c credential.helper=store push origin HEAD:main`
   e. **Clean State**:
      - Mark your state file `.agent-states/<your_agent_id>.md` as `Status: Idle` or remove it.
      - Announce: `🚀 Task successfully verified and shipped directly to main!`
