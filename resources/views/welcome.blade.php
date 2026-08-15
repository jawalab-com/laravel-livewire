<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

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
                height: 100vh;
                overflow: hidden;
                -webkit-font-smoothing: antialiased;
            }

            /* ------- three.js canvas ------- */
            #scene {
                position: fixed;
                inset: 0;
                z-index: 0;
                display: block;
            }

            /* ------- vignette ------- */
            .vignette {
                position: fixed;
                inset: 0;
                z-index: 1;
                pointer-events: none;
                background:
                    radial-gradient(120% 90% at 50% 20%, transparent 55%, rgba(5, 6, 15, 0.7) 100%);
            }

            .grain {
                position: fixed;
                inset: 0;
                z-index: 1;
                pointer-events: none;
                opacity: 0.05;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            }

            /* ------- overlay ui ------- */
            .stage {
                position: relative;
                z-index: 2;
                height: 100vh;
                display: flex;
                flex-direction: column;
                max-width: 1180px;
                margin: 0 auto;
                padding: 2rem 1.5rem;
                pointer-events: none;
            }

            header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                pointer-events: auto;
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
                text-shadow: 0 2px 20px rgba(0, 0, 0, 0.6);
            }

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
                background: rgba(10, 12, 28, 0.55);
                color: var(--ink);
                text-decoration: none;
                font-size: 0.9rem;
                font-weight: 600;
                backdrop-filter: blur(12px);
                transition: border-color 0.2s ease, background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
                position: relative;
                overflow: hidden;
            }

            .nav-link:hover {
                border-color: rgba(110, 231, 255, 0.5);
                background: rgba(110, 231, 255, 0.12);
                transform: translateY(-1px);
            }

            .nav-link.primary {
                background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
                border-color: transparent;
                color: #05060f;
                box-shadow: 0 10px 34px rgba(110, 231, 255, 0.3);
            }

            /* ------- hero ------- */
            main {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                pointer-events: none;
            }

            h1 {
                margin: 0;
                font-size: clamp(4.5rem, 16vw, 15rem);
                line-height: 1;
                font-weight: 800;
                letter-spacing: -0.03em;
                text-shadow: 0 4px 60px rgba(0, 0, 0, 0.8);
            }

            .gradient-text {
                background: linear-gradient(100deg, var(--accent-1) 0%, var(--accent-2) 35%, var(--accent-3) 70%, #fbbf24 100%);
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

            .actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 1rem;
                pointer-events: auto;
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
                background: rgba(10, 12, 28, 0.55);
                color: var(--ink);
                backdrop-filter: blur(12px);
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

            .reveal {
                opacity: 0;
                transform: translateY(22px);
                transition: opacity 1s ease, transform 1s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .reveal.in { opacity: 1; transform: none; }

            @media (max-width: 640px) {
                main { justify-content: center; }
            }
        </style>
    </head>
    <body>
        {{-- Three.js flying object swarm scene --}}
        <canvas id="scene" aria-hidden="true"></canvas>

        <div class="vignette" aria-hidden="true"></div>
        <div class="grain" aria-hidden="true"></div>

        <div class="stage">
            <header>
                <a href="{{ url('/') }}" class="brand">
                    <span>{{ config('app.name', 'Laravel') }}</span>
                </a>

                @if (Route::has('login'))
                    <nav>
                        @auth
                            <a href="{{ route('dashboard') }}" class="nav-link primary">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="nav-link">
                                Sign in
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="nav-link primary">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <main>
                <h1 class="reveal">
                    <span class="gradient-text">{{ config('app.name', 'Laravel') }}</span>
                </h1>

                <div class="reveal actions">
                    @auth
                    @else
                        <a href="{{ route('register') }}" class="btn btn-glow">
                            Get started
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/>
                                <path d="m13 6 6 6-6 6"/>
                            </svg>
                        </a>

                        <a href="{{ route('login') }}" class="btn btn-ghost">
                            Sign in
                        </a>
                    @endauth
                </div>
            </main>
        </div>

        <script type="importmap">
            {
                "imports": {
                    "three": "https://unpkg.com/three@0.170.0/build/three.module.js",
                    "three/addons/": "https://unpkg.com/three@0.170.0/examples/jsm/"
                }
            }
        </script>

        <script type="module">
            import * as THREE from 'three';
            import { EffectComposer } from 'three/addons/postprocessing/EffectComposer.js';
            import { RenderPass } from 'three/addons/postprocessing/RenderPass.js';
            import { UnrealBloomPass } from 'three/addons/postprocessing/UnrealBloomPass.js';

            /* ------------------------------------------------- */
            /*  Flying object swarm with cursor + bloom          */
            /* ------------------------------------------------- */

            const canvas = document.getElementById('scene');

            const renderer = new THREE.WebGLRenderer({
                canvas,
                antialias: true,
                alpha: true,
                powerPreference: 'high-performance',
            });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 1.2;

            const scene = new THREE.Scene();
            scene.fog = new THREE.FogExp2(0x05060f, 0.0016);

            const camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.1, 600);
            camera.position.set(0, 1.6, 10);

            /* --- lights --- */
            scene.add(new THREE.AmbientLight(0x6ee7ff, 0.35));

            const key = new THREE.DirectionalLight(0xffffff, 1.1);
            key.position.set(4, 8, 5);
            scene.add(key);

            const fill = new THREE.DirectionalLight(0xa78bfa, 0.7);
            fill.position.set(-6, 3, -4);
            scene.add(fill);

            const rim = new THREE.DirectionalLight(0xf472b6, 0.8);
            rim.position.set(0, 2, -8);
            scene.add(rim);

            /* --- soft floating dust --- */
            const dustGeo = new THREE.BufferGeometry();
            const dustCount = 350;
            const dustPos = new Float32Array(dustCount * 3);
            const dustCol = new Float32Array(dustCount * 3);
            const dustPalette = [
                new THREE.Color(0x6ee7ff),
                new THREE.Color(0xa78bfa),
                new THREE.Color(0xf472b6),
            ];
            for (let i = 0; i < dustCount; i++) {
                dustPos[i * 3]     = (Math.random() - 0.5) * 60;
                dustPos[i * 3 + 1] = Math.random() * 22 - 2;
                dustPos[i * 3 + 2] = (Math.random() - 0.5) * 40;
                const c = dustPalette[i % dustPalette.length];
                dustCol[i * 3]     = c.r;
                dustCol[i * 3 + 1] = c.g;
                dustCol[i * 3 + 2] = c.b;
            }
            dustGeo.setAttribute('position', new THREE.BufferAttribute(dustPos, 3));
            dustGeo.setAttribute('color', new THREE.BufferAttribute(dustCol, 3));
            const dustMat = new THREE.PointsMaterial({
                size: 0.06,
                vertexColors: true,
                transparent: true,
                opacity: 0.7,
                depthWrite: false,
                blending: THREE.AdditiveBlending,
            });
            const dust = new THREE.Points(dustGeo, dustMat);
            scene.add(dust);

            /* --- flying object swarm --- */
            const rand = mulberry32(1337);
            const palettes = [
                [0x6ee7ff, 0xa78bfa, 0xf472b6],
                [0x34d399, 0x6ee7ff, 0xfbbf24],
                [0xa78bfa, 0xfb7185, 0xfbbf24],
            ];
            const palette = palettes[Math.floor(rand() * palettes.length)];

            /* shared geometries + per-object emissive materials */
            const shapes = [
                new THREE.IcosahedronGeometry(0.16, 0),
                new THREE.OctahedronGeometry(0.17, 0),
                new THREE.TetrahedronGeometry(0.2, 0),
                new THREE.BoxGeometry(0.2, 0.2, 0.2),
                new THREE.TorusKnotGeometry(0.11, 0.04, 48, 6),
            ];

            const swarmGroup = new THREE.Group();
            scene.add(swarmGroup);

            const SWARM_COUNT = 160;
            const swarm = [];
            for (let i = 0; i < SWARM_COUNT; i++) {
                const color = new THREE.Color(palette[i % palette.length]);
                const mat = new THREE.MeshStandardMaterial({
                    color,
                    emissive: color,
                    emissiveIntensity: 0.9,
                    roughness: 0.3,
                    metalness: 0.2,
                    flatShading: true,
                });
                const mesh = new THREE.Mesh(shapes[i % shapes.length], mat);

                /* cluster near the center, ready to burst outward */
                const theta = rand() * Math.PI * 2;
                const phi = Math.acos(2 * rand() - 1);
                const radius = 0.5 + rand() * 3.5;
                mesh.position.set(
                    radius * Math.sin(phi) * Math.cos(theta),
                    radius * Math.cos(phi),
                    radius * Math.sin(phi) * Math.sin(theta),
                );
                mesh.rotation.set(rand() * Math.PI * 2, rand() * Math.PI * 2, rand() * Math.PI * 2);

                mesh.userData = {
                    /* spread target across the whole scene */
                    home: new THREE.Vector3(
                        (rand() - 0.5) * 46,
                        (rand() - 0.5) * 26,
                        (rand() - 0.5) * 30,
                    ),
                    velocity: new THREE.Vector3(),
                    spin: new THREE.Vector3(
                        (rand() - 0.5) * 2.2,
                        (rand() - 0.5) * 2.2,
                        (rand() - 0.5) * 2.2,
                    ),
                    phase: rand() * Math.PI * 2,
                };

                swarmGroup.add(mesh);
                swarm.push(mesh);
            }

            /* --- spread progress: burst open over time --- */
            let spread = 0.05;

            /* --- bloom post-processing --- */
            const composer = new EffectComposer(renderer);
            composer.addPass(new RenderPass(scene, camera));

            const bloom = new UnrealBloomPass(
                new THREE.Vector2(window.innerWidth, window.innerHeight),
                0.9,   /* strength */
                0.6,   /* radius */
                0.2,   /* threshold */
            );
            composer.addPass(bloom);

            /* --- cursor interaction --- */
            let mouseNX = 0, mouseNY = 0;
            let targetNX = 0, targetNY = 0;

            window.addEventListener('pointermove', (e) => {
                targetNX = (e.clientX / window.innerWidth) * 2 - 1;
                targetNY = (e.clientY / window.innerHeight) * 2 - 1;
            }, { passive: true });

            /* click -> bloom burst + scatter impulse */
            let burst = 0;
            let burstPower = 0;
            window.addEventListener('pointerdown', () => {
                burst = 1;
                burstPower = 14;
            });

            /* cursor position in scene space (at z=0 plane) */
            const cursor = new THREE.Vector3(0, 0, 0);

            const clock = new THREE.Clock();

            function animate() {
                const delta = Math.min(clock.getDelta(), 0.05);
                const elapsed = clock.elapsedTime;

                /* smooth cursor + map to scene coords */
                mouseNX += (targetNX - mouseNX) * 0.08;
                mouseNY += (targetNY - mouseNY) * 0.08;
                cursor.set(mouseNX * 20, mouseNY * 11, 0);

                /* spread continues outward over time */
                spread = Math.min(spread + delta * 0.03, 1);

                const wander = Math.sin(elapsed * 0.6);
                const amp = 1.2 + wander * 0.35;

                for (let i = 0; i < swarm.length; i++) {
                    const obj = swarm[i];
                    const d = obj.userData;
                    const pos = obj.position;

                    /* gentle orbital drift around its home */
                    const tx = d.home.x * spread + Math.sin(elapsed * 0.7 + d.phase) * amp;
                    const ty = d.home.y * spread + Math.cos(elapsed * 0.55 + d.phase) * amp * 0.8;
                    const tz = d.home.z * spread + Math.sin(elapsed * 0.4 + d.phase * 2) * amp * 1.4;

                    /* spring toward target */
                    d.velocity.x += (tx - pos.x) * delta * 1.2;
                    d.velocity.y += (ty - pos.y) * delta * 1.2;
                    d.velocity.z += (tz - pos.z) * delta * 1.2;

                    /* cursor interaction: repel near cursor, pull into a ring around it */
                    const dx = pos.x - cursor.x;
                    const dy = pos.y - cursor.y;
                    const dist = Math.sqrt(dx * dx + dy * dy) + 0.001;

                    if (dist < 3.5) {
                        const strength = (1 - dist / 3.5) * 22 * delta;
                        d.velocity.x += (dx / dist) * strength;
                        d.velocity.y += (dy / dist) * strength;
                        d.velocity.z += Math.sin(elapsed * 3 + d.phase) * 0.08;
                    }

                    /* click scatter shockwave */
                    if (burstPower > 0) {
                        const shock = (dx / dist) * burstPower * delta * 3 + (dy / dist) * burstPower * delta * 3;
                        d.velocity.x += shock;
                        d.velocity.y += shock;
                        d.velocity.z += (Math.random() - 0.5) * burstPower * delta * 3;
                    }

                    /* integrate */
                    d.velocity.multiplyScalar(0.985);
                    pos.x += d.velocity.x * delta;
                    pos.y += d.velocity.y * delta;
                    pos.z += d.velocity.z * delta;

                    /* tumble */
                    obj.rotation.x += d.spin.x * delta;
                    obj.rotation.y += d.spin.y * delta;
                    obj.rotation.z += d.spin.z * delta;
                }

                /* decay click effects */
                burstPower = Math.max(0, burstPower - delta * 8);
                bloom.strength = 0.9 + burst * 1.6;
                burst = Math.max(0, burst - delta * 1.4);

                /* dust drifts */
                dust.rotation.y = elapsed * 0.012;

                /* subtle camera sway toward cursor */
                camera.position.x += (mouseNX * 1.4 - camera.position.x) * 0.02;
                camera.position.y += (mouseNY * 0.8 - camera.position.y) * 0.02;
                camera.lookAt(0, 0, 0);

                composer.render();
                requestAnimationFrame(animate);
            }

            window.addEventListener('resize', () => {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
                composer.setSize(window.innerWidth, window.innerHeight);
            });

            animate();

            /* reveal-on-load */
            document.querySelectorAll('.reveal').forEach((el, i) => {
                setTimeout(() => el.classList.add('in'), 200 + i * 120);
            });

            /* button ripples */
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

            /* deterministic RNG */
            function mulberry32(a) {
                return function () {
                    a |= 0; a = a + 0x6D2B79F5 | 0;
                    let t = Math.imul(a ^ a >>> 15, 1 | a);
                    t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
                    return ((t ^ t >>> 14) >>> 0) / 4294967296;
                };
            }
        </script>
    </body>
</html>
