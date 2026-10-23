<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'MQMS') }}</title>

        <!-- Typography -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <style>
            :root {
                --app-bg: #1a1d20;
                --app-primary: #6366f1;
                --app-primary-hover: #4f46e5;
                --app-text: #f8f9fa;
                --app-text-muted: #94a3b8;
                --app-border: #373b3e;
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

            html, body {
                height: 100%;
                width: 100%;
                overflow: hidden;
            }

            body {
                background-color: var(--app-bg);
                color: var(--app-text);
                font-family: var(--font-sans);
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
            }

            /* Animated Canvas Background */
            #tech-canvas {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 0;
                pointer-events: auto;
            }

            /* Center Stage */
            .center-container {
                position: relative;
                z-index: 1;
                text-align: center;
                padding: 2rem;
                user-select: none;
            }

            .main-title {
                font-size: clamp(3.5rem, 8vw, 6.5rem);
                font-weight: 700;
                letter-spacing: 0.12em;
                line-height: 1;
                color: var(--app-text);
                margin-bottom: 0.85rem;
                text-shadow: 0 0 30px rgba(99, 102, 241, 0.25);
            }

            .sub-title {
                font-family: var(--font-mono);
                font-size: clamp(0.85rem, 2vw, 1.15rem);
                letter-spacing: 0.18em;
                text-transform: uppercase;
                color: var(--app-text-muted);
                margin-bottom: 2.5rem;
            }

            /* Entry Action Button */
            .btn-enter {
                display: inline-flex;
                align-items: center;
                gap: 0.65rem;
                padding: 0.75rem 2rem;
                background-color: rgba(99, 102, 241, 0.12);
                color: var(--app-text);
                border: 1px solid var(--app-primary);
                border-radius: 4px;
                font-family: var(--font-mono);
                font-size: 0.95rem;
                font-weight: 500;
                letter-spacing: 0.08em;
                text-decoration: none;
                transition: all 0.25s ease;
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }

            .btn-enter:hover {
                background-color: var(--app-primary);
                color: #ffffff;
                box-shadow: 0 0 24px rgba(99, 102, 241, 0.45);
                transform: translateY(-2px);
            }

            .btn-enter:focus-visible {
                outline: 2px solid #ffffff;
                outline-offset: 3px;
            }

            @media (prefers-reduced-motion: reduce) {
                #tech-canvas {
                    display: none;
                }
                .btn-enter {
                    transition: none;
                    transform: none;
                }
            }
        </style>
    </head>
    <body>
        <!-- Technological Animated Canvas -->
        <canvas id="tech-canvas" aria-hidden="true"></canvas>

        <!-- Centered Title & Entry -->
        <main class="center-container">
            <h1 class="main-title">MQMS</h1>
            <div class="sub-title">Material &amp; Quantity Management System</div>

            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/home') }}" class="btn-enter">
                        <span>Enter Dashboard</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-enter">
                        <span>Sign In</span>
                        <i class="bi bi-box-arrow-in-right"></i>
                    </a>
                @endauth
            @endif
        </main>

        <!-- High-Performance Technological Node Animation -->
        <script>
            (function() {
                const canvas = document.getElementById('tech-canvas');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                if (!ctx) return;

                let width, height, dpr;
                let nodes = [];
                const maxDistance = 140;
                const nodeCount = Math.min(Math.floor(window.innerWidth / 22), 65);

                const mouse = {
                    x: null,
                    y: null,
                    radius: 120
                };

                function resize() {
                    dpr = window.devicePixelRatio || 1;
                    width = window.innerWidth;
                    height = window.innerHeight;
                    canvas.width = width * dpr;
                    canvas.height = height * dpr;
                    ctx.scale(dpr, dpr);
                }

                function initNodes() {
                    nodes = [];
                    for (let i = 0; i < nodeCount; i++) {
                        nodes.push({
                            x: Math.random() * width,
                            y: Math.random() * height,
                            vx: (Math.random() - 0.5) * 0.45,
                            vy: (Math.random() - 0.5) * 0.45,
                            radius: Math.random() * 1.5 + 1
                        });
                    }
                }

                function draw() {
                    ctx.clearRect(0, 0, width, height);

                    // Draw connecting lines
                    for (let i = 0; i < nodes.length; i++) {
                        for (let j = i + 1; j < nodes.length; j++) {
                            const dx = nodes[i].x - nodes[j].x;
                            const dy = nodes[i].y - nodes[j].y;
                            const dist = Math.sqrt(dx * dx + dy * dy);

                            if (dist < maxDistance) {
                                const alpha = (1 - dist / maxDistance) * 0.22;
                                ctx.strokeStyle = `rgba(99, 102, 241, ${alpha})`;
                                ctx.lineWidth = 1;
                                ctx.beginPath();
                                ctx.moveTo(nodes[i].x, nodes[i].y);
                                ctx.lineTo(nodes[j].x, nodes[j].y);
                                ctx.stroke();
                            }
                        }

                        // Mouse connection line
                        if (mouse.x !== null && mouse.y !== null) {
                            const mdx = nodes[i].x - mouse.x;
                            const mdy = nodes[i].y - mouse.y;
                            const mdist = Math.sqrt(mdx * mdx + mdy * mdy);
                            if (mdist < mouse.radius) {
                                const mAlpha = (1 - mdist / mouse.radius) * 0.35;
                                ctx.strokeStyle = `rgba(99, 102, 241, ${mAlpha})`;
                                ctx.lineWidth = 1.2;
                                ctx.beginPath();
                                ctx.moveTo(nodes[i].x, nodes[i].y);
                                ctx.lineTo(mouse.x, mouse.y);
                                ctx.stroke();
                            }
                        }
                    }

                    // Draw nodes
                    for (let i = 0; i < nodes.length; i++) {
                        const n = nodes[i];

                        // Position update
                        n.x += n.vx;
                        n.y += n.vy;

                        // Boundary wrap
                        if (n.x < 0) n.x = width;
                        else if (n.x > width) n.x = 0;
                        if (n.y < 0) n.y = height;
                        else if (n.y > height) n.y = 0;

                        ctx.fillStyle = 'rgba(148, 163, 184, 0.7)';
                        ctx.beginPath();
                        ctx.arc(n.x, n.y, n.radius, 0, Math.PI * 2);
                        ctx.fill();
                    }

                    requestAnimationFrame(draw);
                }

                window.addEventListener('resize', () => {
                    resize();
                    initNodes();
                });

                window.addEventListener('mousemove', (e) => {
                    mouse.x = e.clientX;
                    mouse.y = e.clientY;
                });

                window.addEventListener('mouseleave', () => {
                    mouse.x = null;
                    mouse.y = null;
                });

                resize();
                initNodes();
                requestAnimationFrame(draw);
            })();
        </script>
    </body>
</html>
