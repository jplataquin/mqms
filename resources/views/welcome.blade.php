<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'MQMS') }} // Resource Management</title>

        <!-- Typography -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <style>
            :root {
                --app-bg: #212529;
                --app-bar: #282727;
                --app-card: #2b3035;
                --app-card-hover: #32383e;
                --app-border: #373b3e;
                --app-border-subtle: #495057;
                --app-primary: #6366f1;
                --app-primary-hover: #4f46e5;
                --app-text: #f8f9fa;
                --app-text-muted: #adb5bd;
                --app-status-online: #198754;
                --font-sans: 'Zen Kaku Gothic New', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                --font-mono: 'JetBrains Mono', SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            }

            *, *::before, *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            ::selection {
                background: var(--app-primary);
                color: #ffffff;
            }

            html {
                font-size: 16px;
                scroll-behavior: smooth;
            }

            body {
                background-color: var(--app-bg);
                color: var(--app-text);
                font-family: var(--font-sans);
                line-height: 1.5;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            a:focus-visible, button:focus-visible {
                outline: 2px solid var(--app-primary);
                outline-offset: 2px;
            }

            /* Corporate Header Bar matching #bar in application */
            #bar {
                background-color: var(--app-bar);
                border-bottom: 1px solid var(--app-border);
                height: 54px;
                position: sticky;
                top: 0;
                z-index: 100;
            }

            .bar-inner {
                max-width: 1200px;
                margin: 0 auto;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 1.5rem;
            }

            .brand-link {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                text-decoration: none;
                color: var(--app-text);
            }

            .brand-badge {
                background-color: var(--app-primary);
                color: #ffffff;
                font-family: var(--font-mono);
                font-weight: 700;
                font-size: 0.85rem;
                padding: 0.2rem 0.55rem;
                border-radius: 4px;
                letter-spacing: 0.05em;
            }

            .brand-title {
                font-weight: 700;
                font-size: 1.05rem;
                letter-spacing: 0.02em;
            }

            .bar-actions {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .status-indicator {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                font-family: var(--font-mono);
                font-size: 0.75rem;
                color: var(--app-text-muted);
                padding: 0.25rem 0.65rem;
                background-color: rgba(255, 255, 255, 0.04);
                border: 1px solid var(--app-border);
                border-radius: 4px;
            }

            .status-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background-color: var(--app-status-online);
            }

            /* Buttons */
            .btn-corp {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.45rem 1rem;
                font-size: 0.88rem;
                font-weight: 600;
                text-decoration: none;
                border-radius: 4px;
                transition: background-color 0.15s ease, border-color 0.15s ease;
                cursor: pointer;
            }

            .btn-corp-primary {
                background-color: var(--app-primary);
                border: 1px solid var(--app-primary);
                color: #ffffff;
            }

            .btn-corp-primary:hover {
                background-color: var(--app-primary-hover);
                border-color: var(--app-primary-hover);
                color: #ffffff;
            }

            .btn-corp-outline {
                background-color: transparent;
                border: 1px solid var(--app-border-subtle);
                color: var(--app-text);
            }

            .btn-corp-outline:hover {
                border-color: var(--app-primary);
                color: #ffffff;
                background-color: rgba(99, 102, 241, 0.08);
            }

            /* Main Layout */
            .main-content {
                flex: 1;
                max-width: 1200px;
                width: 100%;
                margin: 0 auto;
                padding: 3.5rem 1.5rem 4rem;
            }

            /* Hero Section */
            .hero-block {
                max-width: 840px;
                margin-bottom: 3rem;
            }

            .hero-system-tag {
                font-family: var(--font-mono);
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: var(--app-primary);
                margin-bottom: 0.75rem;
            }

            .hero-heading {
                font-size: 2.75rem;
                font-weight: 700;
                line-height: 1.15;
                letter-spacing: -0.01em;
                margin-bottom: 1rem;
                color: var(--app-text);
            }

            .hero-summary {
                font-size: 1.15rem;
                color: var(--app-text-muted);
                line-height: 1.6;
                margin-bottom: 2rem;
            }

            .hero-cta {
                display: flex;
                align-items: center;
                gap: 1rem;
                flex-wrap: wrap;
            }

            /* Metrics Row */
            .metrics-panel {
                background-color: var(--app-card);
                border: 1px solid var(--app-border);
                border-radius: 6px;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                margin-bottom: 3.5rem;
            }

            .metric-cell {
                padding: 1.25rem 1.5rem;
                border-right: 1px solid var(--app-border);
            }

            .metric-cell:last-child {
                border-right: none;
            }

            .metric-value {
                font-family: var(--font-mono);
                font-size: 1.35rem;
                font-weight: 700;
                color: var(--app-text);
                display: block;
            }

            .metric-label {
                font-size: 0.8rem;
                color: var(--app-text-muted);
                margin-top: 0.25rem;
                display: block;
            }

            /* Modules Section */
            .section-label {
                font-family: var(--font-mono);
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: var(--app-text-muted);
                margin-bottom: 1.25rem;
            }

            .modules-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
                margin-bottom: 3.5rem;
            }

            .module-card {
                background-color: var(--app-card);
                border: 1px solid var(--app-border);
                border-radius: 4px;
                padding: 1.5rem;
                transition: background-color 0.15s ease, border-color 0.15s ease;
            }

            .module-card:hover {
                background-color: var(--app-card-hover);
                border-color: var(--app-primary);
            }

            .module-header {
                display: flex;
                align-items: center;
                gap: 0.65rem;
                margin-bottom: 0.5rem;
            }

            .module-icon {
                font-size: 1.15rem;
                color: var(--app-primary);
            }

            .module-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: var(--app-text);
            }

            .module-desc {
                font-size: 0.92rem;
                color: var(--app-text-muted);
                line-height: 1.55;
            }

            /* Specifications Bar */
            .specs-panel {
                background-color: var(--app-bar);
                border: 1px solid var(--app-border);
                border-radius: 4px;
                padding: 0.85rem 1.5rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 1rem;
                font-family: var(--font-mono);
                font-size: 0.78rem;
                color: var(--app-text-muted);
            }

            .spec-item strong {
                color: var(--app-text);
                font-weight: 600;
            }

            /* Footer */
            .app-footer {
                background-color: var(--app-bar);
                border-top: 1px solid var(--app-border);
                padding: 1.25rem 0;
                margin-top: auto;
            }

            .footer-inner {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 1.5rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 0.8rem;
                color: var(--app-text-muted);
            }

            /* Responsive */
            @media (max-width: 900px) {
                .hero-heading {
                    font-size: 2.15rem;
                }
                .metrics-panel {
                    grid-template-columns: repeat(2, 1fr);
                }
                .metric-cell:nth-child(2) {
                    border-right: none;
                }
                .metric-cell:nth-child(1), .metric-cell:nth-child(2) {
                    border-bottom: 1px solid var(--app-border);
                }
                .modules-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 576px) {
                .bar-inner {
                    padding: 0 1rem;
                }
                .status-indicator {
                    display: none;
                }
                .hero-heading {
                    font-size: 1.85rem;
                }
                .metrics-panel {
                    grid-template-columns: 1fr;
                }
                .metric-cell {
                    border-right: none;
                    border-bottom: 1px solid var(--app-border);
                }
                .metric-cell:last-child {
                    border-bottom: none;
                }
                .hero-cta {
                    flex-direction: column;
                    align-items: stretch;
                }
                .footer-inner {
                    flex-direction: column;
                    gap: 0.5rem;
                    text-align: center;
                }
            }
        </style>
    </head>
    <body>
        <!-- System Top Bar -->
        <header id="bar">
            <div class="bar-inner">
                <a href="{{ url('/') }}" class="brand-link" aria-label="MQMS Home">
                    <span class="brand-badge">MQMS</span>
                    <span class="brand-title">Material & Quantity Management</span>
                </a>

                <div class="bar-actions">
                    <div class="status-indicator">
                        <span class="status-dot"></span>
                        <span>SYSTEM ONLINE</span>
                    </div>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/home') }}" class="btn-corp btn-corp-primary">
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-corp btn-corp-outline">
                                <i class="bi bi-person-fill"></i>
                                <span>Login</span>
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="main-content">
            <!-- Hero -->
            <section class="hero-block">
                <div class="hero-system-tag">Enterprise Resource Architecture</div>
                <h1 class="hero-heading">Engineering &amp; Quantity Control Platform</h1>
                <p class="hero-summary">
                    Centralized management for work breakdown structures, multi-vendor procurement pipelines, site material logistics, and Model Context Protocol integrations.
                </p>

                <div class="hero-cta">
                    @auth
                        <a href="{{ url('/home') }}" class="btn-corp btn-corp-primary">
                            <i class="bi bi-speedometer2"></i>
                            <span>Access Dashboard</span>
                        </a>
                        <a href="{{ url('/personal_access_tokens') }}" class="btn-corp btn-corp-outline">
                            <i class="bi bi-key-fill"></i>
                            <span>API &amp; MCP Tokens</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-corp btn-corp-primary">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Operator Sign In</span>
                        </a>
                        <a href="#modules" class="btn-corp btn-corp-outline">
                            <i class="bi bi-cpu"></i>
                            <span>System Capabilities</span>
                        </a>
                    @endauth
                </div>
            </section>

            <!-- Metrics Panel -->
            <div class="metrics-panel">
                <div class="metric-cell">
                    <span class="metric-value">28</span>
                    <span class="metric-label">Registered Schema Entities</span>
                </div>
                <div class="metric-cell">
                    <span class="metric-value">Dual-Stream</span>
                    <span class="metric-label">Standard &amp; Direct Canvassing</span>
                </div>
                <div class="metric-cell">
                    <span class="metric-value">JSON-RPC 2.0</span>
                    <span class="metric-label">MCP Protocol Gateway</span>
                </div>
                <div class="metric-cell">
                    <span class="metric-value">Sanctum</span>
                    <span class="metric-label">Token Authentication</span>
                </div>
            </div>

            <!-- Core Modules -->
            <section id="modules">
                <div class="section-label">Core System Modules</div>

                <div class="modules-grid">
                    <div class="module-card">
                        <div class="module-header">
                            <i class="bi bi-diagram-3-fill module-icon"></i>
                            <h2 class="module-title">Work Breakdown Structure</h2>
                        </div>
                        <p class="module-desc">
                            Hierarchical organization linking Projects, Sections, Contract Items, and technical Component assemblies for accurate material estimation.
                        </p>
                    </div>

                    <div class="module-card">
                        <div class="module-header">
                            <i class="bi bi-cart-check-fill module-icon"></i>
                            <h2 class="module-title">Procurement &amp; Canvassing</h2>
                        </div>
                        <p class="module-desc">
                            Price comparison engine evaluating multi-vendor quotations with min/max spread analysis and automated purchase order generation.
                        </p>
                    </div>

                    <div class="module-card">
                        <div class="module-header">
                            <i class="bi bi-box-seam-fill module-icon"></i>
                            <h2 class="module-title">Material &amp; Quantity Requests</h2>
                        </div>
                        <p class="module-desc">
                            Formal site requisition workflows supporting multi-level approvals, warehouse receiving logs, and consumption monitoring.
                        </p>
                    </div>

                    <div class="module-card">
                        <div class="module-header">
                            <i class="bi bi-robot module-icon"></i>
                            <h2 class="module-title">AI &amp; MCP Integration</h2>
                        </div>
                        <p class="module-desc">
                            Standardized Model Context Protocol server exposing models, record lookup, and pricing calculations to external AI agents.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Technical Specifications Strip -->
            <div class="specs-panel">
                <div class="spec-item">Architecture: <strong>MQMS Enterprise Core</strong></div>
                <div class="spec-item">Framework: <strong>Laravel {{ Illuminate\Foundation\Application::VERSION }}</strong></div>
                <div class="spec-item">Environment: <strong>PHP {{ PHP_VERSION }}</strong></div>
                <div class="spec-item">Protocol: <strong>MCP 2024-11-05</strong></div>
            </div>
        </main>

        <!-- Corporate Footer -->
        <footer class="app-footer">
            <div class="footer-inner">
                <div>MQMS &bull; Material &amp; Quantity Management System</div>
                <div>&copy; {{ date('Y') }} PATRILA MQMS. All Rights Reserved.</div>
            </div>
        </footer>
    </body>
</html>
