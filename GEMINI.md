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
- **Supplier Management**: Database of vendors and payment terms.
- **Material Canvassing**: Price comparison and quote management.
- **Purchase Orders (PO)**: Generation and tracking of orders tied to specific projects or material requests.

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

## Technical Foundation
- **Framework**: Laravel 10 (PHP)
- **ORM**: Eloquent (Heavy use of SoftDeletes and complex relationships)
- **Security**: Laravel Passport & Sanctum, plus custom 3rd-party API Key/Secret middleware.
- **Frontend/UI**: Laravel UI/Vite integration, Adarna JS framework for dynamic UI.
- **Utilities**: `spipu/html2pdf` for PDF generation (POs, MQRs)
