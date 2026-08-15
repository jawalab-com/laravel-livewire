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
                font-size: clamp(3.5rem, 12vw, 11rem);
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

            .hint {
                margin-top: 2.2rem;
                font-size: 0.78rem;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: var(--muted);
                opacity: 0.7;
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
        {{-- Three.js fractal tree scene --}}
        <canvas id="scene" aria-hidden="true"></canvas>

        <div class="vignette" aria-hidden="true"></div>
        <div class="grain" aria-hidden="true"></div>

        <div class="stage">
            <header>
                <a href="{{ url('/') }}" class="brand">
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
                        <a href="{{ route('dashboard') }}" class="btn btn-glow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3v18h18"/>
                                <path d="m7 14 4-4 3 3 5-6"/>
                            </svg>
                            Go to dashboard
                        </a>
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

                <div class="reveal hint">
                    Move to sway &middot; click to bloom
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
            import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
            import { EffectComposer } from 'three/addons/postprocessing/EffectComposer.js';
            import { RenderPass } from 'three/addons/postprocessing/RenderPass.js';
            import { UnrealBloomPass } from 'three/addons/postprocessing/UnrealBloomPass.js';

            /* ------------------------------------------------- */
            /*  Epic recursive fractal tree with bloom           */
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

            /* --- recursion state --- */
            const treeGroup = new THREE.Group();
            scene.add(treeGroup);

            const rand = mulberry32(1337);
            const palettes = [
                [0x6ee7ff, 0xa78bfa, 0xf472b6],
                [0x34d399, 0x6ee7ff, 0xfbbf24],
                [0xa78bfa, 0xfb7185, 0xfbbf24],
            ];
            const palette = palettes[Math.floor(rand() * palettes.length)];

            /* Shared geometry + materials (huge perf win over per-branch allocs) */
            const branchGeo = new THREE.CylinderGeometry(0.5, 0.78, 1, 5);

            const trunkMat = new THREE.MeshStandardMaterial({
                color: 0x2a2f52,
                roughness: 0.6,
                metalness: 0.2,
            });

            const branchMat = new THREE.MeshStandardMaterial({
                color: new THREE.Color(palette[0]),
                emissive: new THREE.Color(palette[0]),
                emissiveIntensity: 0.35,
                roughness: 0.5,
                metalness: 0.2,
            });

            const tipGeo = new THREE.SphereGeometry(1, 8, 8);
            const tipMat = new THREE.MeshBasicMaterial({ color: new THREE.Color(palette[2]) });

            /* --- recursive tree builder --- */
            const DEPTH = 7;
            const CHILDREN = 3;
            const SCALE = 0.76;

            /* joints collected during build: child groups carry the fan angle */
            const swayBones = [];

            function buildTree() {
                growBranch(treeGroup, 1.7, 0.09, DEPTH, 0.32, 0);
            }

            function growBranch(parent, length, thickness, depth, spread, lean) {
                const isLeaf = depth <= 2;

                const mesh = new THREE.Mesh(branchGeo, isLeaf ? branchMat : trunkMat);
                mesh.scale.set(thickness * 2, length, thickness * 2);
                mesh.position.y = length / 2;
                parent.add(mesh);

                if (depth <= 1) {
                    const tip = new THREE.Mesh(tipGeo, tipMat);
                    tip.scale.setScalar(thickness * 2.6);
                    tip.position.y = length;
                    mesh.add(tip);
                    return;
                }

                for (let i = 0; i < CHILDREN; i++) {
                    const angle = (i / (CHILDREN - 1)) * Math.PI - Math.PI / 2;

                    const pivot = new THREE.Group();
                    pivot.position.y = length * 0.98;
                    pivot.rotation.z = (rand() - 0.5) * 0.3;

                    const child = new THREE.Group();
                    child.rotation.x = angle + lean;
                    pivot.add(child);

                    const childLen = length * (SCALE + (rand() - 0.5) * 0.06);
                    const childThick = thickness * (SCALE + (rand() - 0.5) * 0.04);

                    /* capture major joints (capped) so spread + mouse can animate them */
                    if (depth <= DEPTH - 3 && swayBones.length < 130) {
                        swayBones.push({ node: child, angle, lean, baseZ: pivot.rotation.z });
                    }

                    growBranch(child, childLen, childThick, depth - 1, spread, lean + (rand() - 0.5) * 0.08);

                    parent.add(pivot);
                }
            }

            buildTree();

            /* --- grow-in animation state --- */
            const growth = { t: 0 };
            let grown = false;

            function grow(delta) {
                if (!grown && growth.t < 1) {
                    growth.t = Math.min(1, growth.t + delta * 1.6);
                    return;
                }
                if (growth.t >= 1 && !grown) {
                    grown = true;
                    treeGroup.visible = true;
                }
            }

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

            /* --- interaction --- */
            const controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.06;
            controls.enableZoom = false;
            controls.enablePan = false;
            controls.minPolarAngle = Math.PI / 3.2;
            controls.maxPolarAngle = Math.PI / 2.05;
            controls.minAzimuthAngle = -Math.PI / 3;
            controls.maxAzimuthAngle = Math.PI / 3;
            controls.target.set(0, 3.2, 0);
            controls.autoRotate = true;
            controls.autoRotateSpeed = 0.6;

            let mouseNX = 0, mouseNY = 0;
            let targetNX = 0, targetNY = 0;
            let prevNX = 0, prevNY = 0;

            window.addEventListener('pointermove', (e) => {
                targetNX = (e.clientX / window.innerWidth) * 2 - 1;
                targetNY = (e.clientY / window.innerHeight) * 2 - 1;
            }, { passive: true });

            /* click -> bloom burst */
            let burst = 0;
            window.addEventListener('pointerdown', () => {
                burst = 1;
            });

            /* slow opening of the fractal */
            let spreadFactor = 0.4;

            const clock = new THREE.Clock();

            function animate() {
                const delta = Math.min(clock.getDelta(), 0.05);
                const elapsed = clock.elapsedTime;

                /* smooth cursor + its velocity */
                prevNX = mouseNX;
                prevNY = mouseNY;
                mouseNX += (targetNX - mouseNX) * 0.08;
                mouseNY += (targetNY - mouseNY) * 0.08;
                const velNX = mouseNX - prevNX;
                const velNY = mouseNY - prevNY;

                /* tree turns toward the cursor, eased */
                treeGroup.rotation.y += (mouseNX * 0.45 - treeGroup.rotation.y) * 0.05;

                /* spread opens slowly over ~25s, then keeps creeping wider */
                spreadFactor = Math.min(spreadFactor + delta * 0.018, 1.9);

                /* camera auto-rotate eases out while the mouse is active */
                controls.autoRotate = Math.abs(mouseNX) < 0.15;
                controls.update();

                /* branches rotate with mouse velocity (snap) + idle wind */
                const wind = Math.sin(elapsed * 0.8) * 0.02;
                const snap = Math.min(0.3, Math.abs(velNX) * 3 + Math.abs(velNY) * 3);
                for (let i = 0; i < swayBones.length; i++) {
                    const { node, angle, lean, baseZ } = swayBones[i];
                    const damp = 1 / (i * 0.12 + 1);

                    /* spread: fan angle grows over time */
                    const spreadX = angle * spreadFactor;

                    /* mouse: rotate branches toward cursor */
                    const rotX = mouseNY * 0.35 * damp;
                    const rotZ = mouseNX * 0.25 * damp + wind + Math.sin(elapsed * 1.4 + i * 0.35) * 0.012;

                    node.rotation.x = spreadX + lean + rotX + snap * velNY * 0.8;
                    node.rotation.z = baseZ + rotZ;
                }

                /* bloom pulse on click */
                bloom.strength = 0.9 + burst * 1.6;
                burst = Math.max(0, burst - delta * 1.4);

                /* dust drifts */
                dust.rotation.y = elapsed * 0.012;

                /* growth reveal */
                const reveal = Math.min(1, growth.t * 3);
                treeGroup.scale.setScalar(Math.max(0.001, reveal));
                treeGroup.position.y = 3.2 * (1 - reveal);

                grow(delta);

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
