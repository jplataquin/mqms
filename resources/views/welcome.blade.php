<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'MQMS') }} // Aeon Resource Command</title>

        <!-- Typography -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Rajdhani:wght@500;600;700&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <style>
            :root {
                --bg-base: #070a12;
                --bg-surface: #0e1526;
                --bg-surface-elevated: #141e34;
                --bg-surface-subtle: rgba(255, 255, 255, 0.02);
                --border-subtle: rgba(255, 255, 255, 0.08);
                --border-strong: rgba(99, 102, 241, 0.4);
                --text-primary: #f8fafc;
                --text-secondary: #94a3b8;
                --text-tertiary: #64748b;
                --accent-primary: #6366f1;
                --accent-primary-hover: #4f46e5;
                --accent-cyan: #06b6d4;
                --accent-emerald: #10b981;
                --accent-amber: #f59e0b;
                --font-display: 'Rajdhani', -apple-system, BlinkMacSystemFont, sans-serif;
                --font-body: 'Zen Kaku Gothic New', -apple-system, BlinkMacSystemFont, sans-serif;
                --font-mono: 'JetBrains Mono', monospace;
            }

            *, *::before, *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            ::selection {
                background: var(--accent-primary);
                color: #ffffff;
            }

            html {
                font-size: 16px;
                scroll-behavior: smooth;
                -webkit-text-size-adjust: 100%;
            }

            body {
                background-color: var(--bg-base);
                color: var(--text-primary);
                font-family: var(--font-body);
                line-height: 1.6;
                min-height: 100vh;
                position: relative;
                overflow-x: hidden;
            }

            /* Ambient Glow Overlays */
            body::before {
                content: '';
                position: absolute;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 100vw;
                height: 800px;
                background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.15) 0%, rgba(6, 182, 212, 0.04) 45%, transparent 75%);
                pointer-events: none;
                z-index: 0;
            }

            /* Focus states for accessibility */
            a:focus-visible, button:focus-visible {
                outline: 2px solid var(--accent-cyan);
                outline-offset: 3px;
            }

            .container {
                width: 100%;
                max-width: 1240px;
                margin-left: auto;
                margin-right: auto;
                padding-left: 1.5rem;
                padding-right: 1.5rem;
                position: relative;
                z-index: 1;
            }

            /* Top Bar */
            .top-nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1.25rem 0;
                border-bottom: 1px solid var(--border-subtle);
                margin-bottom: 3.5rem;
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }

            .brand-mark {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                text-decoration: none;
                color: var(--text-primary);
            }

            .brand-hex {
                width: 36px;
                height: 36px;
                background: var(--bg-surface-elevated);
                border: 1px solid var(--border-strong);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--accent-cyan);
                font-family: var(--font-mono);
                font-weight: 700;
                font-size: 0.95rem;
                clip-path: polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%);
                transition: transform 0.25s ease, border-color 0.25s ease;
            }

            .brand-mark:hover .brand-hex {
                transform: rotate(30deg);
                border-color: var(--accent-cyan);
            }

            .brand-text {
                display: flex;
                flex-direction: column;
            }

            .brand-name {
                font-family: var(--font-display);
                font-weight: 700;
                font-size: 1.15rem;
                letter-spacing: 0.05em;
                color: var(--text-primary);
            }

            .brand-tag {
                font-family: var(--font-mono);
                font-size: 0.68rem;
                color: var(--accent-cyan);
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }

            .nav-meta {
                display: flex;
                align-items: center;
                gap: 1.5rem;
            }

            .telemetry-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.35rem 0.8rem;
                background: rgba(16, 185, 129, 0.08);
                border: 1px solid rgba(16, 185, 129, 0.25);
                border-radius: 9999px;
                font-family: var(--font-mono);
                font-size: 0.75rem;
                color: var(--accent-emerald);
            }

            .pulse-dot {
                width: 7px;
                height: 7px;
                background-color: var(--accent-emerald);
                border-radius: 50%;
                box-shadow: 0 0 8px var(--accent-emerald);
                animation: pulse-glow 2s infinite ease-in-out;
            }

            @keyframes pulse-glow {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.4; transform: scale(0.85); }
            }

            .btn-nav {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1.25rem;
                font-family: var(--font-display);
                font-weight: 600;
                font-size: 0.88rem;
                text-decoration: none;
                border-radius: 6px;
                transition: all 0.2s ease;
            }

            .btn-outline-cyber {
                color: var(--text-primary);
                background: transparent;
                border: 1px solid var(--border-subtle);
            }

            .btn-outline-cyber:hover {
                border-color: var(--accent-primary);
                background: rgba(99, 102, 241, 0.08);
                color: var(--text-primary);
            }

            .btn-primary-cyber {
                color: #ffffff;
                background: var(--accent-primary);
                border: 1px solid var(--accent-primary);
            }

            .btn-primary-cyber:hover {
                background: var(--accent-primary-hover);
                box-shadow: 0 4px 16px rgba(99, 102, 241, 0.35);
                color: #ffffff;
            }

            /* Hero Section */
            .hero-section {
                padding: 2rem 0 4rem;
                display: grid;
                grid-template-columns: 1.15fr 0.85fr;
                gap: 3.5rem;
                align-items: center;
            }

            .hero-content {
                display: flex;
                flex-direction: column;
            }

            .hud-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                width: fit-content;
                font-family: var(--font-mono);
                font-size: 0.78rem;
                color: var(--accent-cyan);
                background: rgba(6, 182, 212, 0.08);
                border: 1px solid rgba(6, 182, 212, 0.25);
                padding: 0.3rem 0.75rem;
                border-radius: 4px;
                margin-bottom: 1.5rem;
                letter-spacing: 0.04em;
            }

            .hero-title {
                font-family: var(--font-display);
                font-size: 3.5rem;
                font-weight: 700;
                line-height: 1.1;
                letter-spacing: -0.02em;
                color: var(--text-primary);
                margin-bottom: 1.25rem;
            }

            .hero-title .title-accent {
                color: var(--accent-primary);
            }

            .hero-subtitle {
                font-size: 1.15rem;
                color: var(--text-secondary);
                max-width: 58ch;
                line-height: 1.7;
                margin-bottom: 2.25rem;
            }

            .hero-actions {
                display: flex;
                align-items: center;
                gap: 1.25rem;
                flex-wrap: wrap;
                margin-bottom: 3rem;
            }

            .btn-action-main {
                display: inline-flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.85rem 1.85rem;
                background: var(--accent-primary);
                color: #ffffff;
                font-family: var(--font-display);
                font-size: 1rem;
                font-weight: 600;
                text-decoration: none;
                border-radius: 6px;
                border: 1px solid var(--accent-primary);
                box-shadow: 0 4px 20px rgba(99, 102, 241, 0.28);
                transition: all 0.2s ease;
            }

            .btn-action-main:hover {
                background: var(--accent-primary-hover);
                transform: translateY(-2px);
                box-shadow: 0 8px 24px rgba(99, 102, 241, 0.45);
                color: #ffffff;
            }

            .btn-action-secondary {
                display: inline-flex;
                align-items: center;
                gap: 0.65rem;
                padding: 0.85rem 1.65rem;
                background: var(--bg-surface);
                color: var(--text-primary);
                font-family: var(--font-display);
                font-size: 1rem;
                font-weight: 600;
                text-decoration: none;
                border-radius: 6px;
                border: 1px solid var(--border-subtle);
                transition: all 0.2s ease;
            }

            .btn-action-secondary:hover {
                border-color: var(--accent-cyan);
                background: var(--bg-surface-elevated);
                color: var(--text-primary);
            }

            /* Live Telemetry Meter Row */
            .telemetry-strip {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1.25rem;
                padding-top: 2rem;
                border-top: 1px solid var(--border-subtle);
            }

            .telemetry-item {
                display: flex;
                flex-direction: column;
            }

            .telemetry-num {
                font-family: var(--font-mono);
                font-size: 1.4rem;
                font-weight: 700;
                color: var(--text-primary);
            }

            .telemetry-label {
                font-family: var(--font-mono);
                font-size: 0.74rem;
                color: var(--text-tertiary);
                letter-spacing: 0.05em;
                text-transform: uppercase;
                margin-top: 0.2rem;
            }

            /* Interactive Terminal Card */
            .terminal-viewport {
                background: var(--bg-surface);
                border: 1px solid var(--border-subtle);
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
            }

            .terminal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.75rem 1.25rem;
                background: #090e1a;
                border-bottom: 1px solid var(--border-subtle);
            }

            .terminal-controls {
                display: flex;
                gap: 0.45rem;
            }

            .t-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: var(--border-subtle);
            }

            .t-dot.red { background: #ef4444; opacity: 0.75; }
            .t-dot.yellow { background: #f59e0b; opacity: 0.75; }
            .t-dot.green { background: #10b981; opacity: 0.75; }

            .terminal-title {
                font-family: var(--font-mono);
                font-size: 0.75rem;
                color: var(--text-tertiary);
                letter-spacing: 0.04em;
            }

            .terminal-body {
                padding: 1.25rem 1.5rem;
                font-family: var(--font-mono);
                font-size: 0.82rem;
                line-height: 1.7;
                color: var(--text-secondary);
                overflow-x: auto;
            }

            .t-prompt {
                color: var(--accent-cyan);
                margin-right: 0.4rem;
            }

            .t-cmd {
                color: var(--text-primary);
                font-weight: 600;
            }

            .t-tag {
                color: var(--accent-primary);
            }

            .t-str {
                color: #38bdf8;
            }

            .t-num {
                color: var(--accent-amber);
            }

            .t-key {
                color: #a5b4fc;
            }

            .t-divider {
                height: 1px;
                background: var(--border-subtle);
                margin: 0.85rem 0;
            }

            /* Architecture Grid */
            .section-header-block {
                margin-top: 4.5rem;
                margin-bottom: 2.5rem;
            }

            .section-headline {
                font-family: var(--font-display);
                font-size: 2.15rem;
                font-weight: 700;
                color: var(--text-primary);
                margin-bottom: 0.5rem;
            }

            .section-lead {
                color: var(--text-secondary);
                font-size: 1.05rem;
                max-width: 60ch;
            }

            .matrix-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
                margin-bottom: 5rem;
            }

            .matrix-card {
                background: var(--bg-surface);
                border: 1px solid var(--border-subtle);
                border-radius: 8px;
                padding: 1.85rem;
                display: flex;
                flex-direction: column;
                position: relative;
                overflow: hidden;
                transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            }

            .matrix-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 2px;
                background: linear-gradient(90deg, var(--accent-primary), transparent);
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            .matrix-card:hover {
                transform: translateY(-3px);
                border-color: rgba(99, 102, 241, 0.45);
                box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
            }

            .matrix-card:hover::before {
                opacity: 1;
            }

            .card-icon-box {
                width: 44px;
                height: 44px;
                border-radius: 6px;
                background: var(--bg-surface-elevated);
                border: 1px solid var(--border-subtle);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                color: var(--accent-cyan);
                margin-bottom: 1.25rem;
            }

            .matrix-card:nth-child(2) .card-icon-box { color: var(--accent-primary); }
            .matrix-card:nth-child(3) .card-icon-box { color: var(--accent-emerald); }
            .matrix-card:nth-child(4) .card-icon-box { color: var(--accent-amber); }

            .card-title {
                font-family: var(--font-display);
                font-size: 1.25rem;
                font-weight: 700;
                color: var(--text-primary);
                margin-bottom: 0.65rem;
            }

            .card-text {
                font-size: 0.95rem;
                color: var(--text-secondary);
                line-height: 1.65;
                margin-bottom: 1.25rem;
                flex-grow: 1;
            }

            .card-meta-list {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                list-style: none;
                margin-top: auto;
            }

            .card-meta-tag {
                font-family: var(--font-mono);
                font-size: 0.72rem;
                padding: 0.2rem 0.55rem;
                background: var(--bg-surface-elevated);
                border: 1px solid var(--border-subtle);
                border-radius: 4px;
                color: var(--text-tertiary);
            }

            /* Protocol Integration Banner */
            .protocol-banner {
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(6, 182, 212, 0.06) 100%);
                border: 1px solid rgba(99, 102, 241, 0.35);
                border-radius: 10px;
                padding: 2.5rem;
                margin-bottom: 5rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 2rem;
            }

            .protocol-info h3 {
                font-family: var(--font-display);
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--text-primary);
                margin-bottom: 0.5rem;
            }

            .protocol-info p {
                color: var(--text-secondary);
                max-width: 60ch;
                font-size: 0.98rem;
            }

            .protocol-actions {
                display: flex;
                align-items: center;
                gap: 1rem;
                flex-shrink: 0;
            }

            /* Footer */
            .site-footer {
                border-top: 1px solid var(--border-subtle);
                padding: 2.5rem 0 3rem;
                margin-top: 3rem;
            }

            .footer-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 1.5rem;
            }

            .footer-specs {
                display: flex;
                align-items: center;
                gap: 1.5rem;
                font-family: var(--font-mono);
                font-size: 0.78rem;
                color: var(--text-tertiary);
            }

            .spec-bullet {
                color: var(--border-strong);
            }

            .footer-links {
                display: flex;
                align-items: center;
                gap: 1.5rem;
            }

            .footer-link {
                color: var(--text-secondary);
                text-decoration: none;
                font-family: var(--font-mono);
                font-size: 0.8rem;
                transition: color 0.15s ease;
            }

            .footer-link:hover {
                color: var(--accent-cyan);
            }

            /* Responsive Adjustments */
            @media (max-width: 992px) {
                .hero-section {
                    grid-template-columns: 1fr;
                    gap: 3rem;
                }
                .hero-title {
                    font-size: 2.75rem;
                }
                .matrix-grid {
                    grid-template-columns: 1fr;
                }
                .protocol-banner {
                    flex-direction: column;
                    align-items: flex-start;
                }
                .protocol-actions {
                    width: 100%;
                    flex-wrap: wrap;
                }
            }

            @media (max-width: 640px) {
                .top-nav {
                    margin-bottom: 2rem;
                }
                .telemetry-pill {
                    display: none;
                }
                .hero-title {
                    font-size: 2.25rem;
                }
                .telemetry-strip {
                    grid-template-columns: 1fr;
                    gap: 1rem;
                }
                .hero-actions {
                    flex-direction: column;
                    align-items: stretch;
                }
                .btn-action-main, .btn-action-secondary {
                    justify-content: center;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                * {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Navigation -->
            <header class="top-nav">
                <a href="{{ url('/') }}" class="brand-mark" aria-label="MQMS Home">
                    <div class="brand-hex">
                        <i class="bi bi-cpu"></i>
                    </div>
                    <div class="brand-text">
                        <span class="brand-name">MQMS // AEON</span>
                        <span class="brand-tag">Resource Command Core</span>
                    </div>
                </a>

                <div class="nav-meta">
                    <div class="telemetry-pill">
                        <div class="pulse-dot"></div>
                        <span>GATEWAY ACTIVE &bull; MCP READY</span>
                    </div>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/home') }}" class="btn-nav btn-primary-cyber">
                                <i class="bi bi-grid-1x2"></i>
                                <span>Mission Control</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-nav btn-outline-cyber">
                                <i class="bi bi-shield-lock"></i>
                                <span>Sign In</span>
                            </a>
                        @endauth
                    @endif
                </div>
            </header>

            <!-- Hero Section -->
            <main>
                <section class="hero-section">
                    <div class="hero-content">
                        <div class="hud-badge">
                            <i class="bi bi-terminal"></i>
                            <span>SYSTEM ARCHITECTURE &bull; ERP V2.4</span>
                        </div>

                        <h1 class="hero-title">
                            Precision Resource <br>
                            <span class="title-accent">Intelligence & Flow</span>
                        </h1>

                        <p class="hero-subtitle">
                            Autonomous lifecycle orchestration for engineering WBS hierarchies, procurement pipelines, dynamic material canvassing, and AI agent execution.
                        </p>

                        <div class="hero-actions">
                            @auth
                                <a href="{{ url('/home') }}" class="btn-action-main">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>Launch Mission Dashboard</span>
                                </a>
                                <a href="{{ url('/personal_access_tokens') }}" class="btn-action-secondary">
                                    <i class="bi bi-key"></i>
                                    <span>Manage MCP Tokens</span>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn-action-main">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Initialize Operator Session</span>
                                </a>
                                <a href="#capabilities" class="btn-action-secondary">
                                    <i class="bi bi-diagram-3"></i>
                                    <span>Explore Architecture</span>
                                </a>
                            @endauth
                        </div>

                        <div class="telemetry-strip">
                            <div class="telemetry-item">
                                <span class="telemetry-num">28</span>
                                <span class="telemetry-label">Active Entities</span>
                            </div>
                            <div class="telemetry-item">
                                <span class="telemetry-num">&lt; 1 ms</span>
                                <span class="telemetry-label">Query Latency</span>
                            </div>
                            <div class="telemetry-item">
                                <span class="telemetry-num">MCP 1.0</span>
                                <span class="telemetry-label">AI RPC Protocol</span>
                            </div>
                        </div>
                    </div>

                    <!-- Live Terminal Output Simulation -->
                    <div class="terminal-viewport" aria-label="Terminal telemetry display">
                        <div class="terminal-header">
                            <div class="terminal-controls">
                                <div class="t-dot red"></div>
                                <div class="t-dot yellow"></div>
                                <div class="t-dot green"></div>
                            </div>
                            <div class="terminal-title">mcp://mqms-cluster/telemetry</div>
                        </div>
                        <div class="terminal-body">
                            <div><span class="t-prompt">$</span><span class="t-cmd">curl -X POST /api/mcp -H "Authorization: Bearer ***"</span></div>
                            <div class="t-divider"></div>
                            <div><span class="t-key">"jsonrpc"</span>: <span class="t-str">"2.0"</span>,</div>
                            <div><span class="t-key">"method"</span>: <span class="t-str">"tools/call"</span>,</div>
                            <div><span class="t-key">"params"</span>: {</div>
                            <div style="padding-left: 1.25rem;"><span class="t-key">"name"</span>: <span class="t-str">"get_material_price_stats"</span>,</div>
                            <div style="padding-left: 1.25rem;"><span class="t-key">"arguments"</span>: { <span class="t-key">"material_item_id"</span>: <span class="t-num">42</span> }</div>
                            <div>}</div>
                            <div class="t-divider"></div>
                            <div style="color: var(--accent-emerald);">// Response &bull; 200 OK &bull; 0.42ms</div>
                            <div>{</div>
                            <div style="padding-left: 1.25rem;"><span class="t-key">"material"</span>: <span class="t-str">"Type 1P Portland Cement"</span>,</div>
                            <div style="padding-left: 1.25rem;"><span class="t-key">"average_price"</span>: <span class="t-num">230.00</span>,</div>
                            <div style="padding-left: 1.25rem;"><span class="t-key">"pipeline_sources"</span>: [<span class="t-str">"standard"</span>, <span class="t-str">"direct"</span>],</div>
                            <div style="padding-left: 1.25rem;"><span class="t-key">"status"</span>: <span class="t-str">"SYNCHRONIZED"</span></div>
                            <div>}</div>
                        </div>
                    </div>
                </section>

                <!-- Core Pillars Matrix -->
                <section id="capabilities">
                    <div class="section-header-block">
                        <h2 class="section-headline">Command Core Subsystems</h2>
                        <p class="section-lead">Engineered for uncompromising fidelity across capital engineering lifecycles and modern algorithmic procurement.</p>
                    </div>

                    <div class="matrix-grid">
                        <div class="matrix-card">
                            <div class="card-icon-box">
                                <i class="bi bi-diagram-3-fill"></i>
                            </div>
                            <h3 class="card-title">Work Breakdown Structure</h3>
                            <p class="card-text">
                                Multi-tiered hierarchical breakdown across Projects, Sections, Contract Items, and Component technical assemblies. Provides granular calculation models for all bill-of-quantities specifications.
                            </p>
                            <ul class="card-meta-list">
                                <li class="card-meta-tag">WBS Mapping</li>
                                <li class="card-meta-tag">Contract Pricing</li>
                                <li class="card-meta-tag">Component Units</li>
                            </ul>
                        </div>

                        <div class="matrix-card">
                            <div class="card-icon-box">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <h3 class="card-title">Dual-Stream Canvassing Engine</h3>
                            <p class="card-text">
                                Real-time price tracking uniting standard and direct purchase canvasses. Aggregates multi-vendor price histories, min/max variances, and automated supplier quotation rankings.
                            </p>
                            <ul class="card-meta-list">
                                <li class="card-meta-tag">Price Statistics</li>
                                <li class="card-meta-tag">Supplier Quotations</li>
                                <li class="card-meta-tag">Variance Audits</li>
                            </ul>
                        </div>

                        <div class="matrix-card">
                            <div class="card-icon-box">
                                <i class="bi bi-box-seam-fill"></i>
                            </div>
                            <h3 class="card-title">Material & Quantity Logistics</h3>
                            <p class="card-text">
                                Formal warehouse requisition workflows with multi-tier approval states, purchase order generation, item receiving audits, and site-level consumption monitoring.
                            </p>
                            <ul class="card-meta-list">
                                <li class="card-meta-tag">MQR Workflows</li>
                                <li class="card-meta-tag">PO Generation</li>
                                <li class="card-meta-tag">Receiving Logs</li>
                            </ul>
                        </div>

                        <div class="matrix-card">
                            <div class="card-icon-box">
                                <i class="bi bi-robot"></i>
                            </div>
                            <h3 class="card-title">Autonomous AI & MCP Protocol</h3>
                            <p class="card-text">
                                Native Model Context Protocol (MCP) server integration. Autonomous external agents query schema records, perform telemetry audits, and calculate stats via JSON-RPC 2.0 with Sanctum security.
                            </p>
                            <ul class="card-meta-list">
                                <li class="card-meta-tag">JSON-RPC 2.0</li>
                                <li class="card-meta-tag">Sanctum Tokens</li>
                                <li class="card-meta-tag">Agent Tools</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- AI / MCP Gateway Callout -->
                <section class="protocol-banner">
                    <div class="protocol-info">
                        <h3>Model Context Protocol Enabled</h3>
                        <p>
                            Connect your IDE, Claude Desktop, or Gemini CLI agent directly to MQMS. Leverage real-time project metrics and material statistics in your development workflows.
                        </p>
                    </div>
                    <div class="protocol-actions">
                        @auth
                            <a href="{{ url('/personal_access_tokens') }}" class="btn-action-main">
                                <i class="bi bi-key-fill"></i>
                                <span>Get Access Token</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-action-main">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span>Sign In to Connect</span>
                            </a>
                        @endauth
                    </div>
                </section>
            </main>

            <!-- Footer -->
            <footer class="site-footer">
                <div class="footer-row">
                    <div class="footer-specs">
                        <span>MQMS // AEON CORE</span>
                        <span class="spec-bullet">&bull;</span>
                        <span>LARAVEL {{ Illuminate\Foundation\Application::VERSION }}</span>
                        <span class="spec-bullet">&bull;</span>
                        <span>PHP {{ PHP_VERSION }}</span>
                    </div>

                    <div class="footer-links">
                        <span class="footer-link">Protocol 2024-11-05</span>
                        <span class="footer-link">&copy; {{ date('Y') }} PATRILA MQMS</span>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
