<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PokéTrivia')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: #0a0a1a;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(99,144,240,0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(249,85,135,0.06) 0%, transparent 50%);
            min-height: 100vh;
            color: #e2e8f0;
        }

        /* Pokemon type colors */
        :root {
            --type-fire:     #FF9C54;
            --type-water:    #6390F0;
            --type-grass:    #7AC74C;
            --type-electric: #F7D02C;
            --type-psychic:  #F95587;
            --type-ice:      #96D9D6;
            --type-dragon:   #6F35FC;
            --type-dark:     #705746;
            --type-fairy:    #D685AD;
            --type-normal:   #A8A77A;
            --type-fighting: #C22E28;
            --type-flying:   #A98FF3;
            --type-poison:   #A33EA1;
            --type-ground:   #E2BF65;
            --type-rock:     #B6A136;
            --type-bug:      #A6B91A;
            --type-ghost:    #735797;
            --type-steel:    #B7B7CE;
        }

        .type-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #fff;
        }
        .type-fire     { background: var(--type-fire); }
        .type-water    { background: var(--type-water); }
        .type-grass    { background: var(--type-grass); }
        .type-electric { background: var(--type-electric); color: #1a1a1a; }
        .type-psychic  { background: var(--type-psychic); }
        .type-ice      { background: var(--type-ice); color: #1a1a1a; }
        .type-dragon   { background: var(--type-dragon); }
        .type-dark     { background: var(--type-dark); }
        .type-fairy    { background: var(--type-fairy); }
        .type-normal   { background: var(--type-normal); color: #1a1a1a; }
        .type-fighting { background: var(--type-fighting); }
        .type-flying   { background: var(--type-flying); }
        .type-poison   { background: var(--type-poison); }
        .type-ground   { background: var(--type-ground); color: #1a1a1a; }
        .type-rock     { background: var(--type-rock); }
        .type-bug      { background: var(--type-bug); }
        .type-ghost    { background: var(--type-ghost); }
        .type-steel    { background: var(--type-steel); color: #1a1a1a; }

        .glass {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(12px);
        }

        .pokeball-spin {
            animation: pokeball-rotate 1s linear infinite;
        }
        @keyframes pokeball-rotate {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        @keyframes slide-up {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .slide-up { animation: slide-up 0.35s ease-out forwards; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60%  { transform: translateX(-6px); }
            40%, 80%  { transform: translateX(6px); }
        }
        .shake { animation: shake 0.4s ease-in-out; }

        @keyframes pop {
            0%   { transform: scale(1); }
            50%  { transform: scale(1.08); }
            100% { transform: scale(1); }
        }
        .pop { animation: pop 0.3s ease-out; }

        @keyframes reveal-glow {
            from { filter: brightness(0) contrast(1); }
            to   { filter: brightness(1); }
        }
        .reveal { animation: reveal-glow 0.5s ease-out forwards; }

        .answer-btn {
            transition: all 0.15s ease;
            position: relative;
            overflow: hidden;
        }
        .answer-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0);
            transition: background 0.15s ease;
        }
        .answer-btn:not(:disabled):hover::before {
            background: rgba(255,255,255,0.06);
        }
        .answer-btn:not(:disabled):active {
            transform: scale(0.97);
        }

        .score-flash {
            animation: score-pop 0.5s ease-out;
        }
        @keyframes score-pop {
            0%   { transform: scale(1); color: inherit; }
            30%  { transform: scale(1.3); color: #7AC74C; }
            100% { transform: scale(1); color: inherit; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }
    </style>

    @stack('styles')
</head>
<body>
    <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-white/5">
        <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-black text-lg tracking-tight">
                <svg width="28" height="28" viewBox="0 0 100 100" class="flex-shrink-0">
                    <circle cx="50" cy="50" r="48" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="4"/>
                    <path d="M2 50 Q50 50 98 50" stroke="rgba(255,255,255,0.2)" stroke-width="4" fill="none"/>
                    <circle cx="50" cy="50" r="12" fill="#0a0a1a" stroke="rgba(255,255,255,0.2)" stroke-width="4"/>
                    <circle cx="50" cy="50" r="6" fill="rgba(255,255,255,0.3)"/>
                    <path d="M2 50 A48 48 0 0 1 98 50" fill="rgba(255,255,255,0.1)"/>
                </svg>
                <span>Poké<span class="text-indigo-400">Trivia</span></span>
            </a>
            <div class="flex items-center gap-1">
                <a href="{{ route('home') }}" class="px-3 py-1.5 text-sm text-white/60 hover:text-white transition-colors rounded-lg hover:bg-white/5">Inicio</a>
                <a href="{{ route('ranking') }}" class="px-3 py-1.5 text-sm text-white/60 hover:text-white transition-colors rounded-lg hover:bg-white/5">Ranking</a>
            </div>
        </div>
    </nav>

    <main class="pt-14">
        @yield('content')
    </main>
</body>
</html>
