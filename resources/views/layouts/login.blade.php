<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MQMS') }}</title>

    <!-- Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://unpkg.com/htmx.org@2.0.0"></script>

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
        }

        ::selection {
            background: var(--app-primary);
            color: #ffffff;
        }

        html, body {
            min-height: 100vh;
            width: 100%;
        }

        body {
            background-color: var(--app-bg) !important;
            color: var(--app-text);
            font-family: var(--font-sans);
            position: relative;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-x: hidden;
        }

        /* Animated Canvas Background (identical to welcome page) */
        #tech-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            padding: 2.5rem 1rem;
        }

        @media (prefers-reduced-motion: reduce) {
            #tech-canvas {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Technological Animated Canvas Background -->
    <canvas id="tech-canvas" aria-hidden="true"></canvas>

    <div class="login-wrapper">
        @yield('content')
    </div>

    <!-- High-Performance Technological Node Animation (identical to welcome page) -->
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
