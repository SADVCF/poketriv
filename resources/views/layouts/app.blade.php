<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PokéTrivia')</title>

    {{-- Google Search Console --}}
    <meta name="google-site-verification" content="WMWFOWGYeDA4LEDRCk8j5W23BZkw62sRw61q7fMnxL4">

    {{-- SEO --}}
    <meta name="description" content="@yield('description', 'PokéTrivia — El mejor trivia Pokémon gratuito. Adivina quién es ese Pokémon, supera la Liga y compite en el ranking. Sin anuncios, sin registro.')">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="{{ url()->current() }}">
    <meta property="og:title"       content="@yield('title', 'PokéTrivia')">
    <meta property="og:description" content="@yield('description', 'El mejor trivia Pokémon gratuito. Adivina quién es ese Pokémon, supera la Liga y compite en el ranking.')">
    <meta property="og:image"       content="{{ asset('og-image.png') }}">
    <meta property="og:locale"      content="{{ app()->getLocale() === 'en' ? 'en_US' : 'es_ES' }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="@yield('title', 'PokéTrivia')">
    <meta name="twitter:description" content="@yield('description', 'El mejor trivia Pokémon gratuito. Sin anuncios, sin registro.')">
    <meta name="twitter:image"       content="{{ asset('og-image.png') }}">

    {{-- JSON-LD --}}
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebApplication",
      "name": "PokéTrivia",
      "url": "https://poketriv.com",
      "description": "El mejor trivia Pokémon gratuito. Adivina quién es ese Pokémon, supera la Liga y compite en el ranking global.",
      "applicationCategory": "GameApplication",
      "operatingSystem": "Any",
      "inLanguage": "es",
      "offers": { "@@type": "Offer", "price": "0", "priceCurrency": "EUR" }
    }
    </script>

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
        @keyframes reveal-pokemon { from{filter:brightness(0) contrast(1.1) drop-shadow(0 4px 24px rgba(0,0,0,.5))} to{filter:brightness(1) drop-shadow(0 4px 24px rgba(0,0,0,.5))} }
        @keyframes spin      { to{transform:rotate(360deg)} }
        @keyframes score-bump { 0%{color:var(--text)} 40%{transform:scale(1.3);color:var(--yellow)} 100%{transform:scale(1);color:var(--text)} }
        @keyframes points-float { 0%{opacity:1;transform:translateY(0) scale(1)} 100%{opacity:0;transform:translateY(-48px) scale(1.25)} }
        @keyframes hp-blink  { 0%,100%{opacity:1} 50%{opacity:.45} }
        @keyframes slide-right { from{opacity:0;transform:translateX(24px)} to{opacity:1;transform:translateX(0)} }
        @keyframes border-scan { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }

        /* ── Navbar ──────────────────────────────────────────────────── */
        .pkt-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: 40px;
            background: rgba(7,8,15,0.96);
            border-bottom: 1px solid var(--border-mid);
        }
        .pkt-nav-inner {
            max-width: 960px; margin: 0 auto; height: 100%;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 16px;
        }
        .pkt-logo {
            display: flex; align-items: center; gap: 6px;
            font-family: var(--font-display); font-size: 16px; font-weight: 900;
            letter-spacing: 0.04em; color: var(--text); text-decoration: none;
        }
        .pkt-logo em { color: var(--yellow); font-style: normal; }
        .pkt-logo-ball {
            width: 20px; height: 20px; position: relative; flex-shrink: 0;
        }
        .pkt-nav-links { display: flex; gap: 1px; }
        .pkt-nav-link {
            padding: 4px 10px; border-radius: 5px;
            font-size: 11px; font-weight: 600; letter-spacing: 0.03em;
            color: var(--text-muted); text-decoration: none;
            transition: color .15s, background .15s;
        }
        .pkt-nav-link:hover   { color: var(--text); background: rgba(255,255,255,.05); }
        .pkt-nav-link.is-active { color: var(--yellow); }
        .pkt-lang-btn {
            border: 1px solid var(--border-mid);
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .06em;
        }
        .pkt-lang-btn:hover { border-color: var(--yellow); color: var(--yellow); background: rgba(255,203,5,.05); }

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
            <a href="{{ route('home') }}"    class="pkt-nav-link {{ request()->routeIs('home')    ? 'is-active' : '' }}">{{ __('ui.nav_home') }}</a>
            <a href="{{ route('ranking') }}" class="pkt-nav-link {{ request()->routeIs('ranking') ? 'is-active' : '' }}">{{ __('ui.nav_ranking') }}</a>
            <a href="{{ route('lang.switch', app()->getLocale() === 'es' ? 'en' : 'es') }}"
               class="pkt-nav-link pkt-lang-btn"
               title="{{ app()->getLocale() === 'es' ? 'Switch to English' : 'Cambiar a Español' }}">
               <img src="{{ app()->getLocale() === 'es' ? 'https://flagcdn.com/20x15/gb.png' : 'https://flagcdn.com/20x15/es.png' }}"
                    width="20" height="15"
                    alt="{{ app()->getLocale() === 'es' ? 'EN' : 'ES' }}"
                    style="display:inline-block;vertical-align:middle;border-radius:2px;">
            </a>
        </div>
    </div>
</nav>

<main style="padding-top:40px; position:relative; z-index:1; min-height:0;">
    @yield('content')
</main>

<footer style="position:relative;z-index:1;border-top:1px solid var(--border);background:rgba(7,8,15,.85);padding:10px 16px;text-align:center;font-family:var(--font-mono);font-size:9px;color:var(--text-muted);letter-spacing:.04em;line-height:1.4">
    <div style="max-width:960px;margin:0 auto;display:flex;flex-direction:column;align-items:center;gap:2px">
        <span style="color:rgba(221,228,240,0.65);font-weight:400">Pok&eacute;mon &copy; Nintendo &middot; Game Freak &middot; Creatures Inc.</span>
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;justify-content:center;margin-top:1px">
            <a href="https://ko-fi.com/sadvcfgmailcom" target="_blank" rel="noopener"
               style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px 4px 10px;border-radius:5px;
                      background:rgba(0,230,118,.06);border:1px solid rgba(0,230,118,.25);color:#00e676;text-decoration:none;
                      font-weight:700;font-size:9px;transition:all .12s">
                <svg width="12" height="12" viewBox="0 0 28 24" fill="#00e676" style="flex-shrink:0">
                    <path d="M3,3 C5,10 5,18 4,22 L7,21 C7,15 8,8 8,3 Z"/>
                    <path d="M11,3 C12,8 13,14 14,18 C15,14 16,8 17,3 Z"/>
                    <path d="M20,3 C20,8 21,15 22,21 L25,22 C24,18 24,10 26,3 Z"/>
                    <path d="M4,19 L7,20 L11,16 L16,16 L21,20 L24,19" stroke="#00e676" stroke-width="1.2" fill="none"/>
                </svg>
                Inv&iacute;tame un Monster
            </a>
            <a href="https://twitter.com/intent/tweet?text=%C2%BFTe+atreves+a+superar+la+Liga+Pok%C3%A9mon%3F+50+preguntas%2C+5+etapas%2C+vidas+y+comodines.+Sin+anuncios%2C+sin+trampas.+%C2%A1El+mejor+trivia+Pok%C3%A9mon+gratuito+en+Pok%C3%A9Trivia%21+%F0%9F%94%A5%F0%9F%8F%86&url=https%3A%2F%2Fpoketriv.com"
               target="_blank" rel="noopener"
               style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;
                      background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text-muted);text-decoration:none;
                      font-size:9px;font-weight:600;transition:all .12s">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                Compartir en X
            </a>
        </div>
    </div>
</footer>

</body>
</html>
