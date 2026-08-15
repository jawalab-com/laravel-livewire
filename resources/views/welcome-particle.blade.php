<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }} — Where ideas take flight</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        <style>
            *,
            *::before,
            *::after {
                box-sizing: border-box;
            }

            :root {
                --bg: #05060f;
                --ink: #eef0ff;
                --muted: rgba(238, 240, 255, 0.55);
                --accent-1: #6ee7ff;
                --accent-2: #a78bfa;
                --accent-3: #f472b6;
                --accent-4: #fbbf24;
                --ring: rgba(238, 240, 255, 0.14);
            }

            html,
            body {
                margin: 0;
                padding: 0;
            }

            body {
                font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
                color: var(--ink);
                background: var(--bg);
                min-height: 100vh;
                overflow-x: hidden;
                -webkit-font-smoothing: antialiased;
            }

            /* ------- ambient aurora blobs ------- */
            .aurora {
                position: fixed;
                inset: 0;
                z-index: 0;
                overflow: hidden;
                pointer-events: none;
            }

            .aurora::before,
            .aurora::after {
                content: "";
                position: absolute;
                width: 60vmax;
                height: 60vmax;
                border-radius: 50%;
                filter: blur(90px);
                opacity: 0.5;
                mix-blend-mode: screen;
            }

            .aurora::before {
                background: radial-gradient(circle at 30% 30%, var(--accent-1), transparent 60%);
                animation: drift-a 26s ease-in-out infinite alternate;
            }

            .aurora::after {
                background: radial-gradient(circle at 70% 70%, var(--accent-2), transparent 60%);
                animation: drift-b 32s ease-in-out infinite alternate;
            }

            .aurora .orb {
                position: absolute;
                width: 38vmax;
                height: 38vmax;
                border-radius: 50%;
                filter: blur(110px);
                mix-blend-mode: screen;
                animation: drift-c 40s ease-in-out infinite alternate;
            }

            .aurora .orb:nth-child(1) { background: radial-gradient(circle, var(--accent-3), transparent 60%); top: -20%; right: -10%; }
            .aurora .orb:nth-child(2) { background: radial-gradient(circle, var(--accent-4), transparent 60%); bottom: -25%; left: -15%; animation-delay: -18s; }

            @keyframes drift-a {
                from { transform: translate3d(-8%, -4%, 0) scale(1); }
                to   { transform: translate3d(10%, 8%, 0) scale(1.25); }
            }

            @keyframes drift-b {
                from { transform: translate3d(6%, 6%, 0) scale(1.15); }
                to   { transform: translate3d(-10%, -8%, 0) scale(0.95); }
            }

            @keyframes drift-c {
                from { transform: translate3d(0, 0, 0) scale(1) rotate(0deg); }
                to   { transform: translate3d(-14%, 10%, 0) scale(1.3) rotate(40deg); }
            }

            /* ------- fine grain ------- */
            .grain {
                position: fixed;
                inset: 0;
                z-index: 1;
                pointer-events: none;
                opacity: 0.05;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            }

            /* ------- particle canvas ------- */
            #field {
                position: fixed;
                inset: 0;
                z-index: 2;
                pointer-events: none;
            }

            /* ------- cursor glow ------- */
            #glow {
                position: fixed;
                top: 0;
                left: 0;
                width: 620px;
                height: 620px;
                margin: -310px 0 0 -310px;
                border-radius: 50%;
                z-index: 1;
                pointer-events: none;
                background: radial-gradient(circle, rgba(110, 231, 255, 0.10), transparent 65%);
                transition: transform 0.25s ease-out;
                will-change: transform;
            }

            /* ------- layout ------- */
            .stage {
                position: relative;
                z-index: 3;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                max-width: 1180px;
                margin: 0 auto;
                padding: 2rem 1.5rem;
            }

            header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 0.7rem;
                font-weight: 700;
                font-size: 1.05rem;
                letter-spacing: -0.01em;
                text-decoration: none;
                color: var(--ink);
            }

            .brand-mark {
                display: grid;
                place-items: center;
                width: 38px;
                height: 38px;
                border-radius: 12px;
                background: linear-gradient(135deg, var(--accent-1), var(--accent-2) 55%, var(--accent-3));
                box-shadow: 0 8px 30px rgba(110, 231, 255, 0.35);
                color: #05060f;
            }

            .brand-mark svg { width: 20px; height: 20px; }

            nav {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .nav-link {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.55rem 1.1rem;
                border-radius: 999px;
                border: 1px solid var(--ring);
                background: rgba(238, 240, 255, 0.04);
                color: var(--ink);
                text-decoration: none;
                font-size: 0.9rem;
                font-weight: 600;
                backdrop-filter: blur(8px);
                transition: border-color 0.2s ease, background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
                position: relative;
                overflow: hidden;
            }

            .nav-link:hover {
                border-color: rgba(110, 231, 255, 0.5);
                background: rgba(110, 231, 255, 0.08);
                transform: translateY(-1px);
            }

            .nav-link.primary {
                background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
                border-color: transparent;
                color: #05060f;
                box-shadow: 0 10px 34px rgba(110, 231, 255, 0.3);
            }

            .nav-link.primary:hover {
                box-shadow: 0 14px 46px rgba(167, 139, 250, 0.45);
            }

            /* ------- hero ------- */
            main {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 6rem 0 4rem;
            }

            .eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.4rem 1rem;
                border-radius: 999px;
                border: 1px solid var(--ring);
                background: rgba(238, 240, 255, 0.05);
                font-size: 0.8rem;
                font-weight: 600;
                color: var(--muted);
                letter-spacing: 0.06em;
                text-transform: uppercase;
                margin-bottom: 1.8rem;
            }

            .eyebrow .pulse {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: var(--accent-1);
                box-shadow: 0 0 0 0 rgba(110, 231, 255, 0.6);
                animation: ping 1.8s cubic-bezier(0, 0, 0.2, 1) infinite;
            }

            @keyframes ping {
                0%   { box-shadow: 0 0 0 0 rgba(110, 231, 255, 0.55); }
                70%  { box-shadow: 0 0 0 9px rgba(110, 231, 255, 0); }
                100% { box-shadow: 0 0 0 0 rgba(110, 231, 255, 0); }
            }

            h1 {
                margin: 0;
                font-size: clamp(2.6rem, 7vw, 5.4rem);
                line-height: 1.02;
                font-weight: 800;
                letter-spacing: -0.03em;
                max-width: 16ch;
            }

            .gradient-text {
                background: linear-gradient(100deg, var(--accent-1) 0%, var(--accent-2) 35%, var(--accent-3) 70%, var(--accent-4) 100%);
                background-size: 220% 100%;
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
                animation: shimmer 6s ease-in-out infinite;
            }

            @keyframes shimmer {
                0%, 100% { background-position: 0% 50%; }
                50%      { background-position: 100% 50%; }
            }

            .lede {
                margin: 1.6rem auto 2.4rem;
                max-width: 46ch;
                font-size: clamp(1rem, 2.2vw, 1.25rem);
                line-height: 1.7;
                color: var(--muted);
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 1rem;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 0.55rem;
                padding: 0.9rem 1.9rem;
                border-radius: 999px;
                font-size: 1rem;
                font-weight: 700;
                text-decoration: none;
                position: relative;
                overflow: hidden;
                transition: transform 0.15s ease, box-shadow 0.2s ease;
            }

            .btn-glow {
                background: linear-gradient(135deg, var(--accent-1), var(--accent-2) 60%, var(--accent-3));
                color: #05060f;
                box-shadow: 0 16px 50px rgba(110, 231, 255, 0.35);
            }

            .btn-glow:hover {
                transform: translateY(-2px) scale(1.02);
                box-shadow: 0 22px 66px rgba(167, 139, 250, 0.5);
            }

            .btn-ghost {
                border: 1px solid var(--ring);
                background: rgba(238, 240, 255, 0.05);
                color: var(--ink);
                backdrop-filter: blur(8px);
            }

            .btn-ghost:hover {
                border-color: rgba(110, 231, 255, 0.5);
                transform: translateY(-2px);
            }

            .btn svg { width: 18px; height: 18px; }

            .ripple {
                position: absolute;
                border-radius: 50%;
                transform: scale(0);
                background: rgba(255, 255, 255, 0.5);
                animation: ripple 0.7s ease-out forwards;
                pointer-events: none;
            }

            @keyframes ripple {
                to { transform: scale(4); opacity: 0; }
            }

            .stats {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 2.5rem;
                margin-top: 4rem;
            }

            .stat {
                display: flex;
                flex-direction: column;
                gap: 0.2rem;
            }

            .stat b {
                font-size: 1.7rem;
                font-weight: 800;
                letter-spacing: -0.02em;
                background: linear-gradient(100deg, var(--accent-1), var(--accent-3));
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }

            .stat span {
                font-size: 0.82rem;
                color: var(--muted);
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding-top: 1rem;
                border-top: 1px solid var(--ring);
                font-size: 0.82rem;
                color: var(--muted);
            }

            footer a { color: var(--muted); text-decoration: none; transition: color 0.2s ease; }
            footer a:hover { color: var(--ink); }

            .reveal {
                opacity: 0;
                transform: translateY(26px);
                transition: opacity 0.9s ease, transform 0.9s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .reveal.in { opacity: 1; transform: none; }

            @media (max-width: 640px) {
                .stats { gap: 1.4rem; }
                footer { flex-direction: column; text-align: center; }
            }

            @media (prefers-reduced-motion: reduce) {
                .aurora::before, .aurora::after, .aurora .orb, .gradient-text, .eyebrow .pulse { animation: none !important; }
            }
        </style>
    </head>
    <body>
        {{-- Ambient background --}}
        <div class="aurora" aria-hidden="true">
            <div class="orb"></div>
            <div class="orb"></div>
        </div>
        <div class="grain" aria-hidden="true"></div>

        {{-- Cursor-tracking glow --}}
        <div id="glow" aria-hidden="true"></div>

        {{-- Interactive particle field --}}
        <canvas id="field" aria-hidden="true"></canvas>

        <div class="stage">
            <header>
                <a href="{{ url('/') }}" class="brand" wire:navigate>
                    <span class="brand-mark">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>
                        </svg>
                    </span>
                    <span>{{ config('app.name', 'Laravel') }}</span>
                </a>

                @if (Route::has('login'))
                    <nav>
                        @auth
                            <a href="{{ route('dashboard') }}" class="nav-link primary" wire:navigate>
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="nav-link" wire:navigate>
                                Sign in
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="nav-link primary" wire:navigate>
                                    Register
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <main>
                <div class="reveal eyebrow">
                    <span class="pulse"></span>
                    {{ __('Welcome to') }} {{ config('app.name', 'Laravel') }}
                </div>

                <h1 class="reveal">
                    <span class="gradient-text">{{ config('app.name', 'Laravel') }}</span>
                </h1>

                <p class="reveal lede">
                    {{ __('The platform for bold builders. A modern foundation for your next great product — real-time everything, delightful UX, and a team experience that just works.') }}
                </p>

                <div class="reveal actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-glow" wire:navigate>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3v18h18"/>
                                <path d="m7 14 4-4 3 3 5-6"/>
                            </svg>
                            Go to dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-glow" wire:navigate>
                            Get started
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/>
                                <path d="m13 6 6 6-6 6"/>
                            </svg>
                        </a>

                        <a href="{{ route('login') }}" class="btn btn-ghost" wire:navigate>
                            Sign in
                        </a>
                    @endauth
                </div>

                <div class="reveal stats">
                    <div class="stat">
                        <b id="stat-realtime">Real-time</b>
                        <span>Everywhere</span>
                    </div>
                    <div class="stat">
                        <b>Zero-drama</b>
                        <span>Onboarding</span>
                    </div>
                    <div class="stat">
                        <b>Built on Laravel</b>
                        <span>Modern stack</span>
                    </div>
                </div>
            </main>

            <footer>
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Crafted with care.</span>
                <span>
                    <a href="https://laravel.com" target="_blank">Laravel</a>
                    &middot;
                    <a href="https://livewire.laravel.com" target="_blank">Livewire</a>
                </span>
            </footer>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                /* ---------- reveal on scroll ---------- */
                const reveals = document.querySelectorAll('.reveal');
                if ('IntersectionObserver' in window && !prefersReduced) {
                    const io = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('in');
                                io.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.15 });
                    reveals.forEach((el, i) => {
                        el.style.transitionDelay = (i * 0.12) + 's';
                        io.observe(el);
                    });
                } else {
                    reveals.forEach((el) => el.classList.add('in'));
                }

                /* ---------- cursor glow ---------- */
                const glow = document.getElementById('glow');
                let mx = window.innerWidth / 2;
                let my = window.innerHeight / 2;
                let gx = mx, gy = my;

                window.addEventListener('pointermove', (e) => {
                    mx = e.clientX;
                    my = e.clientY;
                    glow.style.opacity = '1';
                }, { passive: true });

                (function animateGlow() {
                    gx += (mx - gx) * 0.09;
                    gy += (my - gy) * 0.09;
                    glow.style.transform = `translate3d(${gx}px, ${gy}px, 0)`;
                    requestAnimationFrame(animateGlow);
                })();

                /* ---------- particle field ---------- */
                const canvas = document.getElementById('field');
                const ctx = canvas.getContext('2d');
                let particles = [];
                let clicks = [];
                const mouse = { x: -9999, y: -9999 };

                const PALETTE = ['#6ee7ff', '#a78bfa', '#f472b6', '#fbbf24'];

                function resize() {
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                    spawn();
                }

                function spawn() {
                    const count = Math.min(Math.floor((canvas.width * canvas.height) / 16000), 150);
                    particles = [];
                    for (let i = 0; i < count; i++) {
                        particles.push({
                            x: Math.random() * canvas.width,
                            y: Math.random() * canvas.height,
                            vx: (Math.random() - 0.5) * 0.35,
                            vy: (Math.random() - 0.5) * 0.35,
                            r: Math.random() * 1.8 + 0.7,
                            c: PALETTE[Math.floor(Math.random() * PALETTE.length)],
                            o: Math.random() * 0.7 + 0.2,
                            homeX: 0,
                            homeY: 0,
                        });
                    }
                }

                function draw(t) {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    const linkDist = 130;
                    for (let i = 0; i < particles.length; i++) {
                        const p = particles[i];

                        /* cursor attraction / repulsion */
                        const dx = p.x - mouse.x;
                        const dy = p.y - mouse.y;
                        const dist = Math.hypot(dx, dy);
                        const reach = 180;
                        if (dist < reach && dist > 0.01) {
                            const force = (reach - dist) / reach;
                            p.vx += (dx / dist) * force * 0.06;
                            p.vy += (dy / dist) * force * 0.06;
                        }

                        /* gentle drift toward home */
                        p.vx += (p.homeX - p.x) * 0.00002;
                        p.vy += (p.homeY - p.y) * 0.00002;

                        /* velocity dampening */
                        p.vx *= 0.98;
                        p.vy *= 0.98;

                        p.x += p.vx;
                        p.y += p.vy;

                        /* wrap edges */
                        if (p.x < -20) p.x = canvas.width + 20;
                        if (p.x > canvas.width + 20) p.x = -20;
                        if (p.y < -20) p.y = canvas.height + 20;
                        if (p.y > canvas.height + 20) p.y = -20;

                        /* pulse */
                        const pulse = Math.sin(t * 0.001 + i) * 0.35 + 1;

                        /* draw particle */
                        ctx.globalAlpha = p.o * pulse;
                        ctx.fillStyle = p.c;
                        ctx.beginPath();
                        ctx.arc(p.x, p.y, p.r * pulse, 0, Math.PI * 2);
                        ctx.fill();
                    }

                    /* connection lines */
                    ctx.globalAlpha = 0.5;
                    ctx.lineWidth = 1;
                    for (let i = 0; i < particles.length; i++) {
                        for (let j = i + 1; j < particles.length; j++) {
                            const a = particles[i];
                            const b = particles[j];
                            const dx = a.x - b.x;
                            const dy = a.y - b.y;
                            const d2 = dx * dx + dy * dy;
                            if (d2 < linkDist * linkDist) {
                                const alpha = (1 - Math.sqrt(d2) / linkDist) * 0.14;
                                ctx.strokeStyle = `rgba(150, 180, 255, ${alpha})`;
                                ctx.beginPath();
                                ctx.moveTo(a.x, a.y);
                                ctx.lineTo(b.x, b.y);
                                ctx.stroke();
                            }
                        }
                    }

                    /* click shockwaves */
                    clicks = clicks.filter((c) => c.life > 0);
                    for (const c of clicks) {
                        const life = c.life / c.max;
                        ctx.globalAlpha = life * 0.6;
                        ctx.strokeStyle = c.color;
                        ctx.lineWidth = 2.5 * life + 0.5;
                        ctx.beginPath();
                        ctx.arc(c.x, c.y, c.radius, 0, Math.PI * 2);
                        ctx.stroke();

                        c.radius += 2.4;
                        c.life -= 1;
                    }

                    ctx.globalAlpha = 1;
                }

                function loop(t) {
                    draw(t);
                    requestAnimationFrame(loop);
                }

                window.addEventListener('resize', resize);
                window.addEventListener('pointermove', (e) => {
                    mouse.x = e.clientX;
                    mouse.y = e.clientY;
                }, { passive: true });
                window.addEventListener('pointerleave', () => {
                    mouse.x = -9999;
                    mouse.y = -9999;
                });

                document.addEventListener('pointerdown', (e) => {
                    clicks.push({
                        x: e.clientX,
                        y: e.clientY,
                        radius: 4,
                        life: 22,
                        max: 22,
                        color: PALETTE[Math.floor(Math.random() * PALETTE.length)],
                    });

                    /* little burst of sparks */
                    for (let i = 0; i < 10; i++) {
                        const ang = Math.random() * Math.PI * 2;
                        const speed = Math.random() * 1.6 + 0.4;
                        particles.push({
                            x: e.clientX,
                            y: e.clientY,
                            vx: Math.cos(ang) * speed,
                            vy: Math.sin(ang) * speed,
                            r: Math.random() * 1.6 + 0.8,
                            c: PALETTE[Math.floor(Math.random() * PALETTE.length)],
                            o: 0.9,
                            homeX: e.clientX,
                            homeY: e.clientY,
                        });
                    }
                });

                /* ripples on buttons */
                document.querySelectorAll('.btn, .nav-link').forEach((btn) => {
                    btn.addEventListener('pointerdown', (e) => {
                        const rect = btn.getBoundingClientRect();
                        const d = Math.max(rect.width, rect.height) * 1.2;
                        const span = document.createElement('span');
                        span.className = 'ripple';
                        span.style.width = span.style.height = d + 'px';
                        span.style.left = (e.clientX - rect.left - d / 2) + 'px';
                        span.style.top = (e.clientY - rect.top - d / 2) + 'px';
                        btn.appendChild(span);
                        setTimeout(() => span.remove(), 700);
                    });
                });

                resize();
                requestAnimationFrame(loop);
            });
        </script>
    </body>
</html>
