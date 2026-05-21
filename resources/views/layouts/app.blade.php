<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PokéTrivia')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,600;0,700;0,800;0,900;1,800&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* ── Design tokens ───────────────────────────────────────────── */
        :root {
            --bg:          #07080f;
            --surface:     #0d1120;
            --surface-2:   #131a2e;
            --surface-3:   #1a2340;
            --border:      rgba(255,255,255,0.09);
            --border-mid:  rgba(255,255,255,0.16);
            --border-hi:   rgba(255,255,255,0.28);

            --yellow:      #ffcb05;
            --yellow-glow: rgba(255,203,5,0.30);
            --yellow-dim:  rgba(255,203,5,0.12);
            --yellow-text: #ffda45;

            --blue:        #4b7bec;
            --red:         #ff4757;
            --green:       #2ed573;

            --text:        #dde4f0;
            --text-muted:  rgba(221,228,240,0.42);
            --text-faint:  rgba(221,228,240,0.20);

            --font-display: 'Barlow Condensed', sans-serif;
            --font-ui:      'Space Grotesk', sans-serif;
            --font-mono:    'JetBrains Mono', monospace;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-ui);
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Dot-grid texture — subtle but adds depth */
        body::after {
            content: '';
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image: radial-gradient(circle, rgba(255,255,255,0.022) 1px, transparent 1px);
            background-size: 26px 26px;
        }

        /* ── Type badges ─────────────────────────────────────────────── */
        :root {
            --type-fire:#FF9C54; --type-water:#6390F0; --type-grass:#7AC74C;
            --type-electric:#F7D02C; --type-psychic:#F95587; --type-ice:#96D9D6;
            --type-dragon:#6F35FC; --type-dark:#705746; --type-fairy:#D685AD;
            --type-normal:#A8A77A; --type-fighting:#C22E28; --type-flying:#A98FF3;
            --type-poison:#A33EA1; --type-ground:#E2BF65; --type-rock:#B6A136;
            --type-bug:#A6B91A; --type-ghost:#735797; --type-steel:#B7B7CE;
            --type-stellar:#4FDBD6;
        }
        .type-badge {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 9px 3px; border-radius: 3px;
            font-family: var(--font-mono); font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.07em; color: #fff;
        }
        .type-fire     { background: var(--type-fire); }
        .type-water    { background: var(--type-water); }
        .type-grass    { background: var(--type-grass); }
        .type-electric { background: var(--type-electric); color: #1a1200; }
        .type-psychic  { background: var(--type-psychic); }
        .type-ice      { background: var(--type-ice); color: #0a2a2a; }
        .type-dragon   { background: var(--type-dragon); }
        .type-dark     { background: var(--type-dark); }
        .type-fairy    { background: var(--type-fairy); }
        .type-normal   { background: var(--type-normal); color: #1a1a0a; }
        .type-fighting { background: var(--type-fighting); }
        .type-flying   { background: var(--type-flying); }
        .type-poison   { background: var(--type-poison); }
        .type-ground   { background: var(--type-ground); color: #1a1400; }
        .type-rock     { background: var(--type-rock); }
        .type-bug      { background: var(--type-bug); color: #0a1400; }
        .type-ghost    { background: var(--type-ghost); }
        .type-steel    { background: var(--type-steel); color: #0a0a14; }
        .type-stellar  { background: var(--type-stellar); color: #0a1a1a; }

        /* ── Global animations ───────────────────────────────────────── */
        @keyframes fade-up   { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        @keyframes fade-in   { from{opacity:0} to{opacity:1} }
        @keyframes shake     { 0%,100%{transform:translateX(0)} 20%,60%{transform:translateX(-5px)} 40%,80%{transform:translateX(5px)} }
        @keyframes pop-scale { 0%{transform:scale(1)} 40%{transform:scale(1.07)} 100%{transform:scale(1)} }
        @keyframes reveal-pokemon { from{filter:brightness(0) contrast(1.1)} to{filter:brightness(1)} }
        @keyframes spin      { to{transform:rotate(360deg)} }
        @keyframes score-bump { 0%{color:var(--text)} 40%{transform:scale(1.3);color:var(--yellow)} 100%{transform:scale(1);color:var(--text)} }
        @keyframes points-float { 0%{opacity:1;transform:translateY(0) scale(1)} 100%{opacity:0;transform:translateY(-48px) scale(1.25)} }
        @keyframes hp-blink  { 0%,100%{opacity:1} 50%{opacity:.45} }
        @keyframes slide-right { from{opacity:0;transform:translateX(24px)} to{opacity:1;transform:translateX(0)} }
        @keyframes border-scan { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }

        /* ── Navbar ──────────────────────────────────────────────────── */
        .pkt-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: 54px;
            background: rgba(7,8,15,0.96);
            border-bottom: 1px solid var(--border-mid);
        }
        .pkt-nav-inner {
            max-width: 960px; margin: 0 auto; height: 100%;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 20px;
        }
        .pkt-logo {
            display: flex; align-items: center; gap: 10px;
            font-family: var(--font-display); font-size: 21px; font-weight: 900;
            letter-spacing: 0.04em; color: var(--text); text-decoration: none;
        }
        .pkt-logo em { color: var(--yellow); font-style: normal; }
        .pkt-logo-ball {
            width: 28px; height: 28px; position: relative; flex-shrink: 0;
        }
        .pkt-nav-links { display: flex; gap: 2px; }
        .pkt-nav-link {
            padding: 6px 14px; border-radius: 6px;
            font-size: 13px; font-weight: 600; letter-spacing: 0.03em;
            color: var(--text-muted); text-decoration: none;
            transition: color .15s, background .15s;
        }
        .pkt-nav-link:hover   { color: var(--text); background: rgba(255,255,255,.05); }
        .pkt-nav-link.is-active { color: var(--yellow); }

        /* ── Scrollbar ───────────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 3px; }
    </style>

    @stack('styles')
</head>
<body>

<nav class="pkt-nav">
    <div class="pkt-nav-inner">
        <a href="{{ route('home') }}" class="pkt-logo">
            <svg class="pkt-logo-ball" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="44" fill="none" stroke="rgba(255,203,5,.45)" stroke-width="5"/>
                <path d="M6 50 A44 44 0 0 1 94 50" fill="rgba(255,203,5,.08)"/>
                <line x1="6" y1="50" x2="94" y2="50" stroke="rgba(255,255,255,.18)" stroke-width="4"/>
                <circle cx="50" cy="50" r="11" fill="#07080f" stroke="rgba(255,203,5,.5)" stroke-width="4"/>
                <circle cx="50" cy="50" r="4.5" fill="rgba(255,203,5,.6)"/>
            </svg>
            POKÉ<em>TRIVIA</em>
        </a>
        <div class="pkt-nav-links">
            <a href="{{ route('home') }}"    class="pkt-nav-link {{ request()->routeIs('home')    ? 'is-active' : '' }}">Inicio</a>
            <a href="{{ route('ranking') }}" class="pkt-nav-link {{ request()->routeIs('ranking') ? 'is-active' : '' }}">Ranking</a>
        </div>
    </div>
</nav>

<main style="padding-top:54px; position:relative; z-index:1;">
    @yield('content')
</main>

</body>
</html>
