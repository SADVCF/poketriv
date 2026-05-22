@extends('layouts.app')
@section('title', 'PokéTrivia — Liga Pokémon')

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* silhouette / reveal */
.poke-silhouette { filter: brightness(0) contrast(1.1) !important; }
.poke-reveal     { animation: reveal-pokemon .55s ease-out forwards; }

/* answer button states */
.opt-idle    { background:var(--surface-2); border-color:var(--border-mid); color:var(--text); }
.opt-idle:hover:not(:disabled) {
    border-left-color: var(--yellow) !important;
    background: var(--surface-3);
    transform: translateX(3px);
}
.opt-correct { background:rgba(46,213,115,.14)!important; border-color:rgba(46,213,115,.7)!important; color:#2ed573!important; }
.opt-wrong   { background:rgba(255,71,87,.08)!important;  border-color:rgba(255,71,87,.25)!important;  color:rgba(255,255,255,.28)!important; }

/* HP bar */
.hp-bar-fill { transition: width .08s linear, background-color .5s ease; }
.hp-blink    { animation: hp-blink .5s ease-in-out infinite; }

/* corner markers */
.corner { position:absolute; width:14px; height:14px; border-color:var(--yellow); border-style:solid; opacity:.5; }
.corner-tl { top:0; left:0;  border-width:2px 0 0 2px; }
.corner-tr { top:0; right:0; border-width:2px 2px 0 0; }
.corner-br { bottom:0; right:0; border-width:0 2px 2px 0; }
.corner-bl { bottom:0; left:0;  border-width:0 0 2px 2px; }

/* points popup */
.pts-popup {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
    font-family:var(--font-display); font-weight:900; font-size:28px;
    color:var(--yellow); text-shadow:0 0 20px rgba(255,203,5,.6);
    pointer-events:none; white-space:nowrap;
    animation:points-float .9s ease-out forwards;
    z-index:10;
}

/* result cards */
.stat-card {
    background:var(--surface-2); border:1px solid var(--border-mid);
    border-radius:12px; padding:20px 16px; text-align:center;
}
.stat-val { font-family:var(--font-mono); font-weight:700; font-size:28px; line-height:1; }
.stat-lbl {
    font-family:var(--font-mono); font-size:9px; font-weight:700;
    letter-spacing:.15em; color:var(--text-muted); margin-top:5px;
    text-transform:uppercase;
}

/* score bump */
.score-bump { animation:score-bump .45s ease-out; }

/* life lost overlay */
@keyframes life-lost-in {
    0%   { opacity:0; transform:scale(1.2); }
    15%  { opacity:1; transform:scale(1); }
    80%  { opacity:1; }
    100% { opacity:0; }
}
.life-lost-overlay {
    position:fixed; inset:0; z-index:200;
    background:rgba(255,71,87,.12);
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    animation: life-lost-in 1.2s ease-out forwards;
    pointer-events:none;
}

/* stage badge colors */
.stage-badge-1 { color:#2ed573; border-color:rgba(46,213,115,.5); }
.stage-badge-2 { color:#ffcb05; border-color:rgba(255,203,5,.5); }
.stage-badge-3 { color:#ff7043; border-color:rgba(255,112,67,.5); }
.stage-badge-4 { color:#ff4757; border-color:rgba(255,71,87,.5); }

/* intro logo glow */
@keyframes liga-glow {
    0%,100% { text-shadow: 0 0 40px rgba(255,203,5,.4), 0 0 80px rgba(255,203,5,.12); }
    50%      { text-shadow: 0 0 60px rgba(255,203,5,.6), 0 0 120px rgba(255,203,5,.2); }
}

/* double-image for weight */
.weight-stage { display:flex; gap:12px; justify-content:center; align-items:flex-end; padding:12px 16px 8px; }
.weight-side  { flex:1; text-align:center; }
.weight-img   { width:clamp(90px,20vw,130px); height:clamp(90px,20vw,130px); object-fit:contain; transition:opacity .3s; filter:drop-shadow(0 4px 16px rgba(0,0,0,.5)); }
.weight-name  { font-family:var(--font-display); font-size:15px; font-weight:800; color:var(--text); margin-top:4px; }

/* wildcard overlay */
@keyframes wc-in { from { opacity:0; transform:scale(.92) translateY(10px); } to { opacity:1; transform:scale(1) translateY(0); } }
.wildcard-overlay {
    position:fixed; inset:0; z-index:300;
    background:rgba(7,8,15,.82);
    backdrop-filter: blur(4px);
    display:flex; align-items:center; justify-content:center;
    padding:20px;
}
.wildcard-panel {
    background:var(--surface);
    border:1px solid rgba(255,203,5,.3);
    border-radius:16px;
    padding:28px 24px;
    width:100%; max-width:380px;
    animation:wc-in .3s ease-out;
    box-shadow:0 0 60px rgba(255,203,5,.12);
}
.wc-choice-card {
    display:flex; align-items:center; gap:14px;
    width:100%; padding:14px 16px;
    background:var(--surface-2);
    border:1.5px solid var(--border-mid);
    border-radius:11px;
    cursor:pointer; transition:all .15s; text-align:left;
}
.wc-choice-card:hover {
    background:var(--surface-3);
    transform:translateX(4px);
}
.wc-choice-icon { font-size:26px; flex-shrink:0; line-height:1; }
.wc-choice-name {
    font-family:var(--font-display); font-size:18px; font-weight:900;
    letter-spacing:.04em; line-height:1; margin-bottom:3px;
}
.wc-choice-desc {
    font-family:var(--font-mono); font-size:10px; color:var(--text-muted);
    letter-spacing:.04em; line-height:1.4;
}
.op-50 { opacity: .5; }
</style>
@endpush

@section('content')
<div class="game-root" x-data="leagueGame({ playerName: @js($playerName) })" x-cloak>

{{-- ── INTRO ─────────────────────────────────────────────────────── --}}
<div x-show="phase === 'intro'" class="state-center" style="max-width:460px;width:100%">
    <div style="background:var(--surface);border:1px solid rgba(255,203,5,.35);border-radius:16px;padding:32px 28px;width:100%;position:relative;box-shadow:0 0 40px rgba(255,203,5,.08)">
        <div style="position:absolute;top:0;left:40px;right:40px;height:2px;background:linear-gradient(90deg,transparent,#ffcb05 30%,#ffcb05 70%,transparent);border-radius:0 0 4px 4px"></div>

        <div style="text-align:center;margin-bottom:24px">
            <svg width="54" height="54" viewBox="0 0 64 64" style="margin:0 auto 12px;display:block;filter:drop-shadow(0 0 20px rgba(255,203,5,.5))">
                <path d="M8 8h48v6c0 16-10 26-24 30C18 40 8 30 8 14Z" fill="#ffcb05" stroke="#c8a000" stroke-width="1.2"/>
                <path d="M8 14H2c0 10 6 16 14 18" fill="none" stroke="#ffcb05" stroke-width="4" stroke-linecap="round"/>
                <path d="M56 14h6c0 10-6 16-14 18" fill="none" stroke="#ffcb05" stroke-width="4" stroke-linecap="round"/>
                <path d="M20 22c0 7 4 13 12 15 8-2 12-8 12-15" fill="rgba(255,255,255,.1)"/>
                <rect x="29" y="38" width="6" height="11" fill="#ffcb05"/>
                <rect x="21" y="49" width="22" height="5" rx="2" fill="#ffcb05"/>
                <rect x="17" y="54" width="30" height="5" rx="2" fill="#c8a000"/>
            </svg>
            <p style="font-family:var(--font-display);font-size:11px;font-weight:700;letter-spacing:.3em;color:var(--text-muted);margin-bottom:4px">MODO ESPECIAL</p>
            <h1 style="font-family:var(--font-display);font-weight:900;font-size:44px;letter-spacing:.04em;color:var(--yellow);line-height:1;animation:liga-glow 2s ease-in-out infinite">LIGA POKÉMON</h1>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:20px">
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center">
                <div style="font-family:var(--font-display);font-size:28px;font-weight:900;color:var(--yellow);line-height:1">40</div>
                <div style="font-family:var(--font-mono);font-size:9px;letter-spacing:.15em;color:var(--text-muted);margin-top:3px">PREGUNTAS</div>
            </div>
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center">
                <div style="display:flex;justify-content:center;gap:3px;margin-bottom:3px">
                    <template x-for="i in 3" :key="i">
                        <svg width="18" height="18" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="#ff4757"/></svg>
                    </template>
                </div>
                <div style="font-family:var(--font-mono);font-size:9px;letter-spacing:.15em;color:var(--text-muted)">3 VIDAS</div>
            </div>
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center">
                <div style="font-family:var(--font-display);font-size:18px;font-weight:900;color:#2ed573;line-height:1">I→IV</div>
                <div style="font-family:var(--font-mono);font-size:9px;letter-spacing:.15em;color:var(--text-muted);margin-top:3px">4 ETAPAS</div>
            </div>
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center">
                <div style="font-family:var(--font-display);font-size:18px;font-weight:900;color:#a78bfa;line-height:1">6</div>
                <div style="font-family:var(--font-mono);font-size:9px;letter-spacing:.15em;color:var(--text-muted);margin-top:3px">TIPOS DE PREG.</div>
            </div>
        </div>

        <div style="font-family:var(--font-mono);font-size:10px;color:var(--text-muted);margin-bottom:20px;line-height:1.8;text-align:center;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:10px 12px">
            <span style="color:#2ed573">Etapa I</span> Gen I-III · 15s ·&nbsp;
            <span style="color:#ffcb05">Etapa II</span> Gen I-V · 12s<br>
            <span style="color:#ff7043">Etapa III</span> Gen I-VII · 10s ·&nbsp;
            <span style="color:#ff4757">Etapa IV</span> Gen I-IX · 8s
        </div>

        <div style="font-family:var(--font-mono);font-size:10px;letter-spacing:.1em;color:var(--text-muted);margin-bottom:12px">
            ENTRENADOR: <span style="color:var(--text);font-weight:700" x-text="playerName"></span>
        </div>

        <button @click="startLeague()" style="width:100%;padding:15px;background:var(--yellow);color:#06070d;border:none;border-radius:8px;font-family:var(--font-display);font-size:22px;font-weight:900;letter-spacing:.1em;cursor:pointer;box-shadow:0 2px 0 rgba(0,0,0,.4),0 4px 22px rgba(255,203,5,.3);transition:all .12s;margin-bottom:10px">
            ⚔️ COMENZAR LIGA
        </button>
        <a href="{{ route('home') }}" style="display:block;text-align:center;font-family:var(--font-mono);font-size:11px;color:var(--text-faint);text-decoration:none;letter-spacing:.05em">← Volver al inicio</a>
    </div>
</div>

{{-- ── LOADING ────────────────────────────────────────────────────── --}}
<div x-show="phase === 'loading'" class="state-center">
    <svg width="52" height="52" viewBox="0 0 100 100" style="animation:spin 1s linear infinite;opacity:.5">
        <circle cx="50" cy="50" r="44" fill="none" stroke="rgba(255,203,5,.4)" stroke-width="7"/>
        <path d="M6 50 A44 44 0 0 1 94 50" fill="rgba(255,203,5,.15)"/>
        <line x1="6" y1="50" x2="94" y2="50" stroke="rgba(255,255,255,.2)" stroke-width="5"/>
        <circle cx="50" cy="50" r="10" fill="#07080f" stroke="rgba(255,203,5,.4)" stroke-width="4"/>
    </svg>
    <p style="font-family:var(--font-mono);font-size:12px;color:var(--text-muted);letter-spacing:.1em">PREPARANDO LIGA...</p>
</div>

{{-- ── ERROR ──────────────────────────────────────────────────────── --}}
<div x-show="phase === 'error'" class="state-center">
    <div style="font-size:40px;margin-bottom:12px">⚠️</div>
    <p style="font-family:var(--font-display);font-size:22px;font-weight:800;margin-bottom:6px">Error de carga</p>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:18px" x-text="errorMsg"></p>
    <div style="display:flex;gap:10px">
        <button @click="startLeague()" class="btn-yellow" style="min-width:120px">Reintentar</button>
        <a href="{{ route('home') }}" class="btn-ghost" style="min-width:80px">Inicio</a>
    </div>
</div>

{{-- ── JUEGO (playing / correct / wrong / life_lost_anim) ─────────── --}}
<div x-show="['playing','correct','wrong','life_lost_anim'].includes(phase)" class="game-layout">

    {{-- Life lost overlay --}}
    <div x-show="phase === 'life_lost_anim'" class="life-lost-overlay">
        <svg width="60" height="60" viewBox="0 0 24 24" style="filter:drop-shadow(0 0 20px #ff4757)">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="none" stroke="#ff4757" stroke-width="2"/>
            <line x1="7" y1="7" x2="17" y2="17" stroke="#ff4757" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
        <p style="font-family:var(--font-display);font-size:24px;font-weight:900;color:#ff4757;margin-top:10px;letter-spacing:.05em">¡VIDA PERDIDA!</p>
        <div style="display:flex;gap:8px;margin-top:10px">
            <template x-for="i in 3" :key="i">
                <svg width="26" height="26" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                          :fill="i <= lives ? '#ff4757' : 'none'"
                          :stroke="i <= lives ? '#ff4757' : 'rgba(255,255,255,0.25)'"
                          stroke-width="2"/>
                </svg>
            </template>
        </div>
    </div>

    {{-- HUD --}}
    <div class="hud">
        {{-- Lives + stage --}}
        <div style="display:flex;flex-direction:column;gap:5px;flex-shrink:0">
            <div style="display:flex;gap:4px;align-items:center">
                <template x-for="i in 3" :key="i">
                    <svg width="18" height="18" viewBox="0 0 24 24" :style="i <= lives ? 'filter:drop-shadow(0 0 4px #ff4757)' : ''">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                              :fill="i <= lives ? '#ff4757' : 'none'"
                              :stroke="i <= lives ? '#ff4757' : 'rgba(255,255,255,0.18)'"
                              stroke-width="2"/>
                    </svg>
                </template>
                <span x-show="shieldActive" style="font-size:15px;filter:drop-shadow(0 0 6px #2ed573);margin-left:2px" title="Escudo activo">🛡️</span>
            </div>
            <div x-show="current" :class="'stage-badge-' + (current?.stage ?? 1)"
                 style="display:inline-block;border:1px solid;border-radius:3px;padding:2px 7px;font-family:var(--font-mono);font-size:9px;font-weight:700;letter-spacing:.1em;text-align:center"
                 x-text="'ETAPA ' + (current?.stage ?? 1)"></div>
        </div>

        {{-- Progress --}}
        <div class="hud-left">
            <div class="hud-q-label">
                PREGUNTA
                <span class="hud-q-num" x-text="currentIndex + 1"></span>
                <span style="color:var(--text-faint)"> / 40</span>
            </div>
            <div class="hud-bar-track">
                <div class="hud-bar-fill" :style="`width:${progress}%`"></div>
            </div>
        </div>

        {{-- Combo + score --}}
        <div class="hud-right">
            <div x-show="streak >= 2" class="combo-badge" :class="streak >= 5 ? 'combo-hot' : ''">
                <span x-show="streak >= 5" style="font-size:14px">🔥</span>
                <span style="font-family:var(--font-mono);font-weight:700">×<span x-text="streak"></span></span>
            </div>
            <div class="score-block">
                <div class="score-label">SCORE</div>
                <div class="score-value" :class="scoreFlash ? 'score-bump' : ''" x-text="score.toLocaleString()"></div>
            </div>
        </div>
    </div>

    {{-- Battle card --}}
    <div class="battle-card" :style="typeAccentStyle">
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

        {{-- Points popup --}}
        <div x-show="pointsPopup.show" class="pts-popup">+<span x-text="pointsPopup.amount"></span></div>

        {{-- Card header: gen + types (hide types for silhouette/pixelated) --}}
        <div class="card-header">
            <span class="gen-pill" x-show="current" x-text="'GEN ' + (current?.generation ?? '?')"></span>
             <div class="type-row"
                  x-show="current && (revealed || typeRevealed)">

                 <span style="font-family:var(--font-mono);font-size:9px;color:var(--text-faint);letter-spacing:.1em;margin-right:2px">TIPO</span>
                 <template x-for="t in (current ? current.types : [])" :key="t">
                     <span class="type-badge" :class="`type-${t}`" x-text="t"></span>
                 </template>
             </div>

             <div class="type-row"
                  x-show="current && current.question_type !== 'silhouette' && current.question_type !== 'pixelated' && current.question_type !== 'blur_reveal' && current.question_type !== 'flash' && current.question_type !== 'type' && current.question_type !== 'description'">
                <template x-for="t in (current?.types ?? [])" :key="t">
                    <span class="type-badge" :class="`type-${t}`" x-text="t"></span>
                </template>
            </div>
        </div>

        {{-- DUAL IMAGE (weight / size) --}}
        <div x-show="current && (current.question_type === 'weight' || current.question_type === 'size')" class="weight-stage">
            <div class="weight-side">
                <img :src="current?.artwork_url ?? ''" class="weight-img"
                     :style="`opacity:${imgLoaded ? 1 : 0}`"
                     @@load="imgLoaded = true" @@error="imgLoaded = true">
                <div class="weight-name" x-text="pokemonAName"></div>
            </div>
            <div style="font-family:var(--font-display);font-size:20px;font-weight:900;color:var(--text-faint);padding-bottom:28px;flex-shrink:0">VS</div>
            <div class="weight-side">
                <img :src="current?.artwork_url_b ?? ''" class="weight-img"
                     :style="`opacity:${imgBLoaded ? 1 : 0}`"
                     @@load="imgBLoaded = true" @@error="imgBLoaded = true">
                <div class="weight-name" x-text="current?.display_name_b ?? ''"></div>
            </div>
        </div>

        {{-- SINGLE IMAGE (includes description text) --}}
        <div x-show="current && current.question_type !== 'weight' && current.question_type !== 'size'" class="poke-stage">
            <img
                :key="currentIndex"
                :src="current ? current.artwork_url : ''"
                :alt="shouldRevealName ? (current?.pokemon_name ?? '') : '???'"
                :class="['poke-img', imgClass]"
                :style="`opacity:${imgLoaded && (revealed || (current?.question_type !== 'description' && flashVisible)) ? 1 : 0}; ${imgFilter}`"
                @@load="imgLoaded = true"
                @@error="imgLoaded = true"
            >
            <div x-show="current?.question_type === 'flash' && !flashVisible"
                 style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:var(--bg-card);border-radius:10px;font-size:40px;opacity:.5">
                💡
            </div>
            <div x-show="current?.question_type === 'description' && !revealed"
                 style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:var(--font-mono);font-size:11px;font-weight:500;color:var(--text-muted);line-height:1.5;letter-spacing:.02em;text-align:center;padding:16px;overflow-y:auto;word-break:break-word">
                <span x-text="current?.description ?? ''"></span>
            </div>
        </div>

        {{-- Pokemon name row (not weight/size/description) --}}
        <div x-show="current && current.question_type !== 'weight' && current.question_type !== 'size' && current.question_type !== 'description'" class="poke-name-row">
            <span x-show="shouldRevealName" class="poke-name" x-text="current ? current.pokemon_name : ''"></span>
            <span x-show="!shouldRevealName" class="poke-unknown">? ? ?</span>
        </div>

        {{-- Question text --}}
        <div x-show="current" style="text-align:center;padding:4px 16px 8px;font-family:var(--font-ui);font-size:13px;font-weight:600;color:var(--text-muted)"
             x-text="current?.question_text ?? ''"></div>

        {{-- Freeze indicator --}}
        <div x-show="freezeActive" x-transition
             style="margin:0 16px 6px;padding:6px 10px;border-radius:7px;background:rgba(150,217,214,.1);border:1px solid rgba(150,217,214,.35);font-family:var(--font-mono);font-size:11px;font-weight:700;color:#96D9D6;text-align:center;letter-spacing:.05em">
            ❄️ TIEMPO CONGELADO
        </div>

        {{-- Shield blocked flash --}}
        <div x-show="shieldBlocked" x-transition
             style="margin:0 16px 6px;padding:6px 10px;border-radius:7px;background:rgba(46,213,115,.12);border:1px solid rgba(46,213,115,.4);font-family:var(--font-mono);font-size:11px;font-weight:700;color:#2ed573;text-align:center;letter-spacing:.05em">
            🛡️ ¡ESCUDO ACTIVADO!
        </div>

        {{-- HP bar timer --}}
        <div class="hp-bar-section">
            <span class="hp-label">HP</span>
            <div class="hp-track">
                <div class="hp-bar-fill"
                     :class="timeLeft <= 3 ? 'hp-blink' : ''"
                     :style="`width:${Math.max(0, timeLeft / (current?.time_limit ?? 15) * 100)}%; background:${hpBarColor};`">
                </div>
            </div>
            <span class="hp-num" :style="`color:${hpBarColor}`" x-text="Math.ceil(timeLeft)"></span>
        </div>
    </div>

    {{-- Options grid --}}
    <div class="options-grid" :class="(current?.options?.length ?? 4) > 4 ? 'options-grid--6' : ''">
        <template x-for="(option, idx) in (current ? current.options : [])" :key="idx">
            <button
                x-show="!hiddenOptions.includes(option)"
                @click="selectAnswer(option)"
                :disabled="selectedAnswer !== null"
                :class="['opt-btn', getOptionClass(option)]"
            >
                <span class="opt-key" x-text="'ABCDEF'[idx]"></span>
                <template x-if="current && current.question_type === 'type'">
                    <span class="type-badge" :class="'type-' + option" x-text="option" style="font-size:10px;padding:2px 7px"></span>
                </template>
                <template x-if="!current || current.question_type !== 'type'">
                    <span class="opt-text" x-text="option"></span>
                </template>
            </button>
        </template>
    </div>

    {{-- Wildcard inventory bar --}}
    <div x-show="wildcardInventory.length > 0" style="display:flex;gap:8px;margin-top:10px;justify-content:center;flex-wrap:wrap">
        <template x-for="wc in wildcardInventory" :key="wc.id">
            <button
                @click="useWildcard(wc.id)"
                :disabled="selectedAnswer !== null || (wc.id === 'shield' && shieldActive)"
                :title="wc.name + ': ' + wc.desc"
                :style="`border:1.5px solid ${wc.color}60;background:${wc.color}12;color:${wc.color};`"
                style="display:flex;align-items:center;gap:6px;padding:7px 12px;border-radius:8px;cursor:pointer;font-family:var(--font-mono);font-size:11px;font-weight:700;transition:all .12s;letter-spacing:.03em"
                :class="selectedAnswer !== null && wc.id !== 'shield' ? 'op-50' : ''"
            >
                <span x-text="wc.icon" style="font-size:16px;line-height:1"></span>
                <span x-text="wc.name"></span>
            </button>
        </template>
    </div>

    {{-- Wildcard choice overlay --}}
    <div x-show="wildcardChoiceActive" class="wildcard-overlay">
        <div class="wildcard-panel">
            <div style="text-align:center;margin-bottom:18px">
                <div style="font-size:28px;margin-bottom:6px">🎯</div>
                <p style="font-family:var(--font-display);font-size:28px;font-weight:900;color:var(--yellow);letter-spacing:.06em;line-height:1">¡COMODÍN!</p>
                <p style="font-family:var(--font-mono);font-size:10px;color:var(--text-muted);letter-spacing:.1em;margin-top:5px">RACHA ×<span x-text="streak"></span> — Elige un poder</p>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px">
                <template x-for="wc in wildcardChoices" :key="wc.id">
                    <button @click="selectWildcard(wc)"
                            :style="`border-color:${wc.color}50;box-shadow:0 0 16px ${wc.color}15`"
                            class="wc-choice-card">
                        <span class="wc-choice-icon" x-text="wc.icon"></span>
                        <div style="flex:1;text-align:left">
                            <div class="wc-choice-name" :style="`color:${wc.color}`" x-text="wc.name"></div>
                            <div class="wc-choice-desc" x-text="wc.desc"></div>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </div>

</div>{{-- /game-layout --}}

{{-- ── GAME OVER ─────────────────────────────────────────────────── --}}
<div x-show="phase === 'game_over'" class="result-root" style="animation:fade-up .4s ease-out">
    <div class="result-hero">
        <div class="result-emoji" x-html="resultEmoji"></div>
        <h2 class="result-title" style="color:#ff4757">GAME OVER</h2>
        <p class="result-player" x-text="playerName"></p>
        <p style="font-family:var(--font-mono);font-size:11px;color:var(--text-muted);margin-top:5px">
            Detenido en la <span style="color:var(--yellow)" x-text="'Etapa ' + maxStageReached"></span>
            &nbsp;·&nbsp; pregunta <span x-text="currentIndex + 1"></span>/40
        </p>
    </div>
    <div class="result-stats">
        <div class="stat-card">
            <div class="stat-val" style="color:var(--yellow)" x-text="score.toLocaleString()"></div>
            <div class="stat-lbl">Puntuación</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color:var(--green)">
                <span x-text="correctCount"></span><span style="color:var(--text-faint);font-size:18px">/</span><span x-text="currentIndex + 1" style="font-size:18px"></span>
            </div>
            <div class="stat-lbl">Aciertos</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color:#a78bfa" x-text="formatTime(totalTime)"></div>
            <div class="stat-lbl">Tiempo</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color:var(--yellow)">
                <span style="font-size:18px;opacity:.4">#</span><span x-text="rank ?? '—'"></span>
            </div>
            <div class="stat-lbl">Posición Liga</div>
        </div>
        <div class="stat-card" x-show="saveError">
            <div class="stat-val" style="color:#ff4757;font-size:11px" x-text="saveError"></div>
            <div class="stat-lbl">Error al guardar</div>
        </div>
    </div>
    <div class="result-actions">
        <button @click="restartLeague()" class="btn-yellow">Intentar de nuevo</button>
        <a href="{{ route('ranking', ['difficulty' => 'league']) }}" class="btn-ghost">Ver ranking</a>
        <a href="{{ route('home') }}" class="btn-ghost">Inicio</a>
    </div>
</div>

{{-- ── FINISHED ──────────────────────────────────────────────────── --}}
<div x-show="phase === 'finished'" class="result-root" style="animation:fade-up .4s ease-out">
    <div class="result-hero">
        <div class="result-emoji" x-html="resultEmoji"></div>
        <h2 class="result-title" x-text="resultTitle"></h2>
        <p class="result-player" x-text="playerName"></p>
        <div style="display:flex;gap:5px;justify-content:center;margin-top:8px">
            <template x-for="i in 3" :key="i">
                <svg width="22" height="22" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                          :fill="i <= lives ? '#ff4757' : 'none'"
                          :stroke="i <= lives ? '#ff4757' : 'rgba(255,255,255,0.2)'"
                          stroke-width="2"/>
                </svg>
            </template>
        </div>
    </div>
    <div class="result-stats">
        <div class="stat-card">
            <div class="stat-val" style="color:var(--yellow)" x-text="score.toLocaleString()"></div>
            <div class="stat-lbl">Puntuación</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color:var(--green)">
                <span x-text="correctCount"></span><span style="color:var(--text-faint);font-size:18px">/40</span>
            </div>
            <div class="stat-lbl">Aciertos</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color:#a78bfa" x-text="formatTime(totalTime)"></div>
            <div class="stat-lbl">Tiempo</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color:var(--yellow)">
                <span style="font-size:18px;opacity:.4">#</span><span x-text="rank ?? '—'"></span>
            </div>
            <div class="stat-lbl">Posición Liga</div>
        </div>
        <div class="stat-card" x-show="saveError">
            <div class="stat-val" style="color:#ff4757;font-size:11px" x-text="saveError"></div>
            <div class="stat-lbl">Error al guardar</div>
        </div>
    </div>
    <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
        <span x-show="maxStreak >= 3" class="chip chip--yellow">🔥 Racha <span x-text="maxStreak"></span></span>
        <span class="chip">⚔️ Liga completada</span>
        <span x-show="lives === 3" class="chip chip--green">❤️❤️❤️ Sin perder vidas</span>
    </div>
    <div class="result-actions">
        <button @click="restartLeague()" class="btn-yellow">Jugar de nuevo</button>
        <a href="{{ route('ranking', ['difficulty' => 'league']) }}" class="btn-ghost">Ver ranking</a>
        <a href="{{ route('home') }}" class="btn-ghost">Inicio</a>
    </div>
</div>

</div>{{-- /game-root --}}

<style>
.game-root {
    min-height: calc(100vh - 54px);
    display: flex; flex-direction: column; align-items: center;
    justify-content: flex-start;
    padding: 20px 16px 32px;
}
.state-center {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 14px; padding: 40px 0;
}

/* ── HUD ─────────────────────────────────────────────────────────── */
.hud {
    width: 100%; max-width: 520px;
    display: flex; align-items: flex-end; gap: 10px;
    margin-bottom: 14px;
}
.hud-left { flex: 1; }
.hud-q-label {
    font-family: var(--font-mono); font-size: 10px; font-weight: 700;
    letter-spacing: .1em; color: var(--text-muted);
    margin-bottom: 7px;
}
.hud-q-num { font-size: 13px; font-weight: 700; color: var(--text); }
.hud-bar-track {
    height: 4px; background: rgba(255,255,255,.06);
    border-radius: 2px; overflow: hidden;
}
.hud-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--yellow) 0%, #ffda45 100%);
    border-radius: 2px;
    transition: width .4s ease;
    box-shadow: 0 0 6px rgba(255,203,5,.4);
}
.hud-right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.combo-badge {
    display: flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 6px;
    background: rgba(255,203,5,.1); border: 1px solid rgba(255,203,5,.3);
    color: var(--yellow);
    font-family: var(--font-display); font-size: 14px; font-weight: 800;
}
.combo-hot { animation: hp-blink .6s ease-in-out infinite alternate; }
.score-block { text-align: right; }
.score-label {
    font-family: var(--font-mono); font-size: 9px; font-weight: 700;
    letter-spacing: .15em; color: var(--text-faint); margin-bottom: 1px;
}
.score-value {
    font-family: var(--font-mono); font-size: 20px; font-weight: 700;
    color: var(--text);
}

/* ── Battle card ─────────────────────────────────────────────────── */
.game-layout { width: 100%; max-width: 520px; display: flex; flex-direction: column; align-items: stretch; }
.battle-card {
    width: 100%;
    background: var(--surface);
    border: 1px solid var(--border-mid);
    border-radius: 14px;
    position: relative; overflow: hidden;
    margin-bottom: 12px;
    transition: border-color .4s ease, box-shadow .4s ease;
}
.card-header {
    padding: 14px 16px 0;
    display: flex; align-items: center; justify-content: space-between;
    min-height: 36px;
}
.gen-pill {
    font-family: var(--font-mono); font-size: 9px; font-weight: 700;
    letter-spacing: .14em; color: var(--text-faint);
    background: rgba(255,255,255,.05); border: 1px solid var(--border);
    padding: 2px 8px; border-radius: 3px;
}
.type-row { display: flex; gap: 5px; }

.poke-stage {
    display: flex; align-items: center; justify-content: center;
    padding: 12px 16px 8px; min-height: 200px;
}
.poke-img {
    width: clamp(160px, 35vw, 220px);
    height: clamp(160px, 35vw, 220px);
    object-fit: contain;
    filter: drop-shadow(0 4px 24px rgba(0,0,0,.5));
    transition: filter .55s ease, opacity .3s ease;
}
.poke-name-row {
    text-align: center; min-height: 36px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 16px 4px;
}
.poke-name {
    font-family: var(--font-display); font-size: 26px; font-weight: 800;
    letter-spacing: .04em; color: var(--text);
    animation: fade-up .3s ease-out;
}
.poke-unknown {
    font-family: var(--font-mono); font-size: 20px; font-weight: 700;
    letter-spacing: .4em; color: var(--text-faint);
}

/* HP bar */
.hp-bar-section {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 16px 14px;
    border-top: 1px solid var(--border);
}
.hp-label {
    font-family: var(--font-mono); font-size: 9px; font-weight: 700;
    letter-spacing: .15em; color: var(--text-muted); min-width: 18px;
}
.hp-track {
    flex: 1; height: 7px;
    background: rgba(255,255,255,.07);
    border-radius: 4px; overflow: hidden;
}
.hp-bar-fill { height: 100%; border-radius: 4px; }
.hp-num {
    font-family: var(--font-mono); font-size: 12px; font-weight: 700;
    min-width: 22px; text-align: right;
    transition: color .5s ease;
}

/* ── Options ─────────────────────────────────────────────────────── */
.options-grid {
    width: 100%;
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.options-grid--6 { grid-template-columns: 1fr 1fr 1fr; }
@media (max-width: 480px) {
    .options-grid--6 { grid-template-columns: 1fr 1fr; }
}
.opt-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 13px 14px; border-radius: 9px;
    border: 2px solid; border-left-width: 3px;
    cursor: pointer; transition: all .12s ease;
    text-align: left;
}
.opt-btn:disabled { cursor: default; }
.opt-key {
    font-family: var(--font-mono); font-size: 11px; font-weight: 700;
    color: var(--yellow); min-width: 16px; flex-shrink: 0;
}
.opt-text { font-family: var(--font-ui); font-size: 13px; font-weight: 600; line-height: 1.3; }

/* ── Result screen ───────────────────────────────────────────────── */
.result-root {
    width: 100%; max-width: 520px;
    display: flex; flex-direction: column; gap: 16px;
    padding-top: 16px;
}
.result-hero { text-align: center; margin-bottom: 4px; }
.result-emoji { line-height:1; margin-bottom:10px; display:flex; justify-content:center; filter: drop-shadow(0 4px 20px rgba(0,0,0,.4)); }
.result-title {
    font-family: var(--font-display); font-size: 36px; font-weight: 900;
    letter-spacing: .03em; color: var(--text); margin-bottom: 4px;
}
.result-player {
    font-family: var(--font-mono); font-size: 12px; color: var(--text-muted);
    letter-spacing: .1em;
}
.result-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.result-actions { display: flex; flex-direction: column; gap: 8px; }

.btn-yellow {
    width: 100%; padding: 14px;
    background: var(--yellow); color: #06070d;
    border: none; border-radius: 8px;
    font-family: var(--font-display); font-size: 20px; font-weight: 900;
    letter-spacing: .08em; cursor: pointer;
    box-shadow: 0 2px 0 rgba(0,0,0,.4), 0 4px 20px rgba(255,203,5,.3);
    transition: all .12s;
    text-decoration: none; text-align: center; display: block;
}
.btn-yellow:hover { transform: translateY(-2px); box-shadow: 0 4px 0 rgba(0,0,0,.4), 0 8px 28px rgba(255,203,5,.4); }
.btn-ghost {
    display: block; width: 100%; padding: 13px;
    background: var(--surface-2); border: 1px solid var(--border-mid);
    border-radius: 8px;
    font-family: var(--font-ui); font-size: 14px; font-weight: 600;
    color: var(--text-muted); text-align: center; text-decoration: none;
    transition: all .12s; cursor: pointer;
}
.btn-ghost:hover { color: var(--text); border-color: var(--border-hi); background: var(--surface-3); }
.chip {
    padding: 3px 9px; border-radius: 4px;
    background: var(--surface-2); border: 1px solid var(--border);
    font-family: var(--font-mono); font-size: 10px; font-weight: 600;
    color: var(--text-muted);
}
.chip--yellow { background:rgba(255,203,5,.1); border-color:rgba(255,203,5,.3); color:var(--yellow); }
.chip--green  { background:rgba(46,213,115,.1); border-color:rgba(46,213,115,.3); color:#2ed573; }
</style>

<script>
const TYPE_COLORS = {
    fire:     { color:'#FF9C54', glow:'rgba(255,156,84,0.22)' },
    water:    { color:'#6390F0', glow:'rgba(99,144,240,0.22)' },
    grass:    { color:'#7AC74C', glow:'rgba(122,199,76,0.22)' },
    electric: { color:'#F7D02C', glow:'rgba(247,208,44,0.22)' },
    psychic:  { color:'#F95587', glow:'rgba(249,85,135,0.22)' },
    ice:      { color:'#96D9D6', glow:'rgba(150,217,214,0.22)' },
    dragon:   { color:'#6F35FC', glow:'rgba(111,53,252,0.22)' },
    dark:     { color:'#9e8878', glow:'rgba(112,87,70,0.22)' },
    fairy:    { color:'#D685AD', glow:'rgba(214,133,173,0.22)' },
    normal:   { color:'#A8A77A', glow:'rgba(168,167,122,0.15)' },
    fighting: { color:'#C22E28', glow:'rgba(194,46,40,0.22)' },
    flying:   { color:'#A98FF3', glow:'rgba(169,143,243,0.22)' },
    poison:   { color:'#A33EA1', glow:'rgba(163,62,161,0.22)' },
    ground:   { color:'#E2BF65', glow:'rgba(226,191,101,0.22)' },
    rock:     { color:'#B6A136', glow:'rgba(182,161,54,0.22)' },
    bug:      { color:'#A6B91A', glow:'rgba(166,185,26,0.22)' },
    ghost:    { color:'#735797', glow:'rgba(115,87,151,0.22)' },
    steel:    { color:'#B7B7CE', glow:'rgba(183,183,206,0.15)' },
};

const AUDIO_CTX = typeof AudioContext !== 'undefined' ? new (window.AudioContext || window.webkitAudioContext)() : null;
function playSound(type) {
    if (!AUDIO_CTX) return;
    try {
        const now = AUDIO_CTX.currentTime;
        if (type === 'correct') {
            [523,659,784].forEach((f,i)=>{
                const o=AUDIO_CTX.createOscillator(),g=AUDIO_CTX.createGain();
                o.connect(g); g.connect(AUDIO_CTX.destination);
                o.type='sine'; o.frequency.setValueAtTime(f,now+i*.1);
                g.gain.setValueAtTime(.13,now+i*.1);
                g.gain.exponentialRampToValueAtTime(.001,now+i*.1+.3);
                o.start(now+i*.1); o.stop(now+i*.1+.3);
            });
        } else if (type === 'wrong' || type === 'timeout') {
            const o=AUDIO_CTX.createOscillator(),g=AUDIO_CTX.createGain();
            o.connect(g); g.connect(AUDIO_CTX.destination);
            o.type='sawtooth'; o.frequency.setValueAtTime(200,now); o.frequency.setValueAtTime(150,now+.15);
            g.gain.setValueAtTime(.09,now); g.gain.exponentialRampToValueAtTime(.001,now+.3);
            o.start(now); o.stop(now+.3);
        } else if (type === 'life_lost') {
            [220, 180, 140].forEach((f,i) => {
                const o=AUDIO_CTX.createOscillator(),g=AUDIO_CTX.createGain();
                o.connect(g); g.connect(AUDIO_CTX.destination);
                o.type='sine'; o.frequency.setValueAtTime(f,now+i*.18);
                g.gain.setValueAtTime(.12,now+i*.18);
                g.gain.exponentialRampToValueAtTime(.001,now+i*.18+.4);
                o.start(now+i*.18); o.stop(now+i*.18+.4);
            });
        } else if (type === 'tick') {
            const o=AUDIO_CTX.createOscillator(),g=AUDIO_CTX.createGain();
            o.connect(g); g.connect(AUDIO_CTX.destination);
            o.frequency.setValueAtTime(800,now); g.gain.setValueAtTime(.04,now);
            g.gain.exponentialRampToValueAtTime(.001,now+.05);
            o.start(now); o.stop(now+.05);
        } else if (type === 'wildcard') {
            [784,1047,1319].forEach((f,i)=>{
                const o=AUDIO_CTX.createOscillator(),g=AUDIO_CTX.createGain();
                o.connect(g); g.connect(AUDIO_CTX.destination);
                o.type='sine'; o.frequency.setValueAtTime(f,now+i*.12);
                g.gain.setValueAtTime(.1,now+i*.12);
                g.gain.exponentialRampToValueAtTime(.001,now+i*.12+.25);
                o.start(now+i*.12); o.stop(now+i*.12+.25);
            });
        } else if (type === 'finish') {
            [523,659,784,1047].forEach((f,i)=>{
                const o=AUDIO_CTX.createOscillator(),g=AUDIO_CTX.createGain();
                o.connect(g); g.connect(AUDIO_CTX.destination);
                o.type='sine'; o.frequency.setValueAtTime(f,now+i*.15);
                g.gain.setValueAtTime(.11,now+i*.15);
                g.gain.exponentialRampToValueAtTime(.001,now+i*.15+.3);
                o.start(now+i*.15); o.stop(now+i*.15+.3);
            });
        }
    } catch(e) {}
}

function leagueGame({ playerName }) {
    return {
        playerName,
        phase: 'intro',
        questions: [],
        currentIndex: 0,
        lives: 3,
        livesLost: 0,
        selectedAnswer: null,
        score: 0,
        streak: 0,
        flashVisible: true,
        maxStreak: 0,
        correctCount: 0,
        maxStageReached: 1,
        revealed: false,
        scoreFlash: false,
        rank: null,
        scoreSaved: false,
        saveError: null,
        gameToken: '',
        errorMsg: '',
        timeLeft: 15,
        timerInterval: null,
        timerStart: 0,
        gameStartTime: null,
        totalTime: 0,
        pointsPopup: { show: false, amount: 0 },
        imgLoaded: false,
        imgBLoaded: false,

        // ── Comodines ──────────────────────────────────────────────
        wildcardInventory: [],
        wildcardChoiceActive: false,
        wildcardChoices: [],
        timerFrozenUntil: 0,
        shieldActive: false,
        shieldBlocked: false,
        hiddenOptions: [],
        typeRevealed: false,

        get current()     { return this.questions[this.currentIndex] ?? null; },
        get currentStage(){ return this.current?.stage ?? 1; },
        get progress()    { return (this.currentIndex / 40) * 100; },

        get pokemonAName() {
            if (!this.current || (this.current.question_type !== 'weight' && this.current.question_type !== 'size')) return '';
            return this.current.options.find(o => o !== this.current.display_name_b) ?? '';
        },

        get shouldRevealName() {
            if (!this.current) return false;
            const qt = this.current.question_type;
            if (qt === 'silhouette' || qt === 'pixelated' || qt === 'blur_reveal' || qt === 'flash' || qt === 'description') return this.revealed;
            return true;
        },

        get imgClass() {
            if (!this.current) return '';
            const qt = this.current.question_type;
            if (qt === 'silhouette' && !this.revealed) return 'poke-silhouette';
            if (qt === 'blur_reveal' && !this.revealed) return '';
            if (this.revealed) return 'poke-reveal';
            return '';
        },

        get imgFilter() {
            if (!this.current || this.revealed) return '';
            const qt = this.current.question_type;
            if (qt === 'pixelated') {
                const t = this.current.time_limit;
                const blur = Math.max(0, (this.timeLeft / t) * 18);
                return `filter: blur(${blur.toFixed(1)}px) drop-shadow(0 4px 24px rgba(0,0,0,.5))`;
            }
            if (qt === 'blur_reveal') {
                const t = this.current.time_limit;
                const blur = Math.max(0, (this.timeLeft / t) * 20);
                return `filter: blur(${blur.toFixed(1)}px) drop-shadow(0 4px 24px rgba(0,0,0,.5))`;
            }
            return '';
        },

        get typeAccentStyle() {
            if (!this.current) return '';
            const t = (this.current.types ?? [])[0] ?? 'normal';
            const c = TYPE_COLORS[t] ?? TYPE_COLORS.normal;
            return `border-color:${c.color}40; box-shadow:0 0 32px ${c.glow}, 0 0 0 1px ${c.color}18 inset;`;
        },

        get stageGenMultiplier() {
            const maxGens = {1:3, 2:5, 3:7, 4:9};
            return 1 + ((maxGens[this.currentStage] ?? 9) - 1) * 0.15;
        },

        get hpBarColor() {
            const p = this.timeLeft / (this.current?.time_limit ?? 15);
            if (p > .5) return '#2ed573';
            if (p > .25) return '#ffcb05';
            return '#ff4757';
        },

        get freezeActive() {
            return this.timerFrozenUntil > Date.now();
        },

        get resultEmoji() {
            const total = this.phase === 'finished' ? 40 : Math.max(this.currentIndex + 1, 1);
            const p = total > 0 ? this.correctCount / total : 0;
            const pika = (eyes, mouth, extra='') => `<svg width="80" height="80" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 43L10 5L36 21Z" fill="#FFCB05" stroke="#c8a000" stroke-width="1"/>
              <path d="M10 5L24 22L36 21Z" fill="#2a1a0a"/>
              <path d="M82 43L90 5L64 21Z" fill="#FFCB05" stroke="#c8a000" stroke-width="1"/>
              <path d="M90 5L76 22L64 21Z" fill="#2a1a0a"/>
              <ellipse cx="50" cy="57" rx="40" ry="38" fill="#FFCB05" stroke="#c8a000" stroke-width="1.5"/>
              <ellipse cx="21" cy="67" rx="10" ry="8" fill="#E8544A" opacity="0.9"/>
              <ellipse cx="79" cy="67" rx="10" ry="8" fill="#E8544A" opacity="0.9"/>
              <ellipse cx="50" cy="58" rx="2.5" ry="1.8" fill="#b89000"/>
              ${eyes}${mouth}${extra}
            </svg>`;
            const thrilled = pika(
                `<path d="M30 50Q38 61 46 50" fill="none" stroke="#2a1a0a" stroke-width="3.2" stroke-linecap="round"/>
                 <path d="M54 50Q62 61 70 50" fill="none" stroke="#2a1a0a" stroke-width="3.2" stroke-linecap="round"/>`,
                `<path d="M32 67Q50 84 68 67" fill="none" stroke="#2a1a0a" stroke-width="3" stroke-linecap="round"/>
                 <path d="M34 68Q50 83 66 68Q50 77 34 68Z" fill="white"/>
                 <path d="M46 74Q50 80 54 74" fill="#FF6B88"/>`,
                `<path d="M7 28L11 20L13 29L9 24Z" fill="#FFCB05" stroke="#c8a000" stroke-width="1"/>
                 <path d="M89 22L94 15L95 24L91 19Z" fill="#FFCB05" stroke="#c8a000" stroke-width="1"/>
                 <circle cx="21" cy="67" r="4" fill="white" opacity="0.35"/>
                 <circle cx="79" cy="67" r="4" fill="white" opacity="0.35"/>`
            );
            const happy = pika(
                `<ellipse cx="38" cy="50" rx="5.5" ry="6" fill="#2a1a0a"/>
                 <ellipse cx="62" cy="50" rx="5.5" ry="6" fill="#2a1a0a"/>
                 <circle cx="40" cy="48" r="2" fill="white"/>
                 <circle cx="64" cy="48" r="2" fill="white"/>`,
                `<path d="M34 66Q50 79 66 66" fill="none" stroke="#2a1a0a" stroke-width="3" stroke-linecap="round"/>`
            );
            const worried = pika(
                `<path d="M33 43Q38 39 43 42" fill="none" stroke="#2a1a0a" stroke-width="2" stroke-linecap="round"/>
                 <path d="M57 42Q62 39 67 43" fill="none" stroke="#2a1a0a" stroke-width="2" stroke-linecap="round"/>
                 <ellipse cx="38" cy="51" rx="5.5" ry="5.5" fill="#2a1a0a"/>
                 <ellipse cx="62" cy="51" rx="5.5" ry="5.5" fill="#2a1a0a"/>
                 <circle cx="40" cy="49" r="2" fill="white"/>
                 <circle cx="64" cy="49" r="2" fill="white"/>`,
                `<path d="M38 67Q50 72 62 67" fill="none" stroke="#2a1a0a" stroke-width="2.5" stroke-linecap="round"/>`
            );
            const sad = pika(
                `<path d="M32 43Q38 39 43 41" fill="none" stroke="#2a1a0a" stroke-width="2" stroke-linecap="round"/>
                 <path d="M57 41Q62 39 68 43" fill="none" stroke="#2a1a0a" stroke-width="2" stroke-linecap="round"/>
                 <ellipse cx="38" cy="52" rx="5.5" ry="5.5" fill="#2a1a0a"/>
                 <ellipse cx="62" cy="52" rx="5.5" ry="5.5" fill="#2a1a0a"/>
                 <circle cx="40" cy="50" r="2" fill="white"/>
                 <circle cx="64" cy="50" r="2" fill="white"/>
                 <path d="M32 49Q38 46 44 51" fill="#FFCB05" stroke="#FFCB05" stroke-width="2.2"/>
                 <path d="M56 51Q62 46 68 49" fill="#FFCB05" stroke="#FFCB05" stroke-width="2.2"/>`,
                `<path d="M34 70Q50 62 66 70" fill="none" stroke="#2a1a0a" stroke-width="3" stroke-linecap="round"/>`,
                `<path d="M30 72Q32 63 34 72Q35 79 32 79Q29 79 30 72Z" fill="#88c4e8" stroke="#5590b8" stroke-width="0.8"/>`
            );
            return p >= .9 ? thrilled : p >= .7 ? happy : p >= .5 ? worried : sad;
        },

        get resultTitle() {
            const p = this.correctCount / 40;
            return p >= .9 ? '¡Maestro Pokémon!' : p >= .7 ? '¡Liga superada!' : p >= .5 ? 'Bien luchado' : '¡Sigue entrenando!';
        },

        startLeague() {
            this.phase = 'loading';
            this.loadQuestions();
        },

        async loadQuestions() {
            this.errorMsg = '';
            try {
                const res = await fetch('/api/league/questions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({}),
                });
                const data = await res.json();
                if (!data.questions || data.questions.length === 0) {
                    this.errorMsg = data.error || 'Sin preguntas';
                    this.phase = 'error'; return;
                }
                this.questions  = data.questions;
                this.gameToken  = data.token;
                this.gameStartTime = Date.now();
                this.imgLoaded  = false;
                this.imgBLoaded = false;
                this.phase = 'playing';
                this.startTimer();
            } catch(e) {
                this.errorMsg = 'Error de conexión';
                this.phase = 'error';
            }
        },

        startTimer() {
            clearInterval(this.timerInterval);
            this.timerStart = Date.now();
            this.timeLeft   = this.current?.time_limit ?? 15;
            // flash: hide image after 1s
            if (this.current?.question_type === 'flash') {
                setTimeout(() => { this.flashVisible = false; }, 1000);
            }
            this.timerInterval = setInterval(() => {
                const now = Date.now();
                const limit = this.current?.time_limit ?? 15;
                if (now < this.timerFrozenUntil) {
                    // Advance timerStart so elapsed stays constant while frozen
                    this.timerStart = now - (limit - this.timeLeft) * 1000;
                    return;
                }
                const elapsed = (now - this.timerStart) / 1000;
                this.timeLeft = Math.max(0, limit - elapsed);
                if (this.timeLeft <= 3 && this.timeLeft > 0 &&
                    Math.ceil(this.timeLeft) !== Math.ceil(this.timeLeft + .1)) {
                    playSound('tick');
                }
                if (this.timeLeft <= 0) {
                    clearInterval(this.timerInterval);
                    this.handleTimeout();
                }
            }, 50);
        },

        selectAnswer(option) {
            if (this.selectedAnswer !== null) return;
            clearInterval(this.timerInterval);
            this.selectedAnswer = option;
            this.revealed = true;
            if (this.current.stage > this.maxStageReached) this.maxStageReached = this.current.stage;

            const ok = option === this.current.answer;
            if (ok) {
                this.streak++;
                if (this.streak > this.maxStreak) this.maxStreak = this.streak;
                const tb = Math.floor(this.timeLeft) * 15;
                const sb = Math.max(0, this.streak - 1) * 50;
                const gained = Math.round((200 + tb + sb) * this.stageGenMultiplier);
                this.score += gained;
                this.correctCount++;
                this.scoreFlash = true;
                setTimeout(() => { this.scoreFlash = false; }, 500);
                this.pointsPopup = { show: true, amount: gained };
                setTimeout(() => { this.pointsPopup.show = false; }, 950);
                playSound('correct');
                const hitMilestone = (this.streak % 5 === 0);
                setTimeout(() => {
                    if (hitMilestone) {
                        clearInterval(this.timerInterval);
                        this.wildcardChoices = this.pickWildcardChoices();
                        this.wildcardChoiceActive = true;
                        playSound('wildcard');
                    } else {
                        this.nextQuestion();
                    }
                }, 1400);
            } else {
                this.streak = 0;
                playSound('wrong');
                setTimeout(() => this.loseLife(), 800);
            }
        },

        handleTimeout() {
            if (this.selectedAnswer !== null) return;
            this.selectedAnswer = '__timeout__';
            this.revealed = true;
            this.streak = 0;
            if (this.current && this.current.stage > this.maxStageReached) this.maxStageReached = this.current.stage;
            playSound('timeout');
            setTimeout(() => this.loseLife(), 800);
        },

        loseLife() {
            if (this.shieldActive) {
                this.shieldActive = false;
                this.shieldBlocked = true;
                playSound('correct');
                setTimeout(() => {
                    this.shieldBlocked = false;
                    this.nextQuestion();
                }, 1200);
                return;
            }
            this.livesLost++;
            this.lives--;
            playSound('life_lost');
            this.phase = 'life_lost_anim';
            if (this.lives <= 0) {
                setTimeout(() => {
                    this.totalTime = Math.floor((Date.now() - this.gameStartTime) / 1000);
                    this.phase = 'game_over';
                    this.saveScore();
                }, 1200);
            } else {
                setTimeout(() => this.nextQuestion(), 1000);
            }
        },

        nextQuestion() {
            if (this.currentIndex >= this.questions.length - 1) {
                this.finishGame(); return;
            }
            this.imgLoaded      = false;
            this.imgBLoaded     = false;
            this.hiddenOptions  = [];
            this.typeRevealed   = false;
            this.timerFrozenUntil = 0;
            this.flashVisible   = true;
            this.$nextTick(() => {
                this.currentIndex++;
                this.selectedAnswer = null;
                this.revealed = false;
                this.phase = 'playing';
                this.startTimer();
            });
        },

        finishGame() {
            clearInterval(this.timerInterval);
            this.totalTime = Math.floor((Date.now() - this.gameStartTime) / 1000);
            this.score += this.lives * 500;
            this.maxStageReached = 4;
            this.phase = 'finished';
            playSound('finish');
            this.saveScore();
        },

        async saveScore() {
            if (this.scoreSaved) return;
            this.scoreSaved = true;
            try {
                const res = await fetch('/api/league/score', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        player_name:     this.playerName,
                        score:           this.score,
                        correct_answers: this.correctCount,
                        total_questions: this.questions.length,
                        lives_lost:      this.livesLost,
                        time_seconds:    this.totalTime,
                        max_streak:      this.maxStreak,
                        max_stage:       this.maxStageReached,
                        token:           this.gameToken,
                    }),
                });
                console.log('Score status:', res.status);
                if (!res.ok) {
                    const text = await res.text();
                    console.error('Score error body:', text.slice(0, 300));
                    this.saveError = `HTTP ${res.status}`;
                    return;
                }
                const data = await res.json();
                console.log('Score data:', data);
                this.rank = data.rank;
            } catch(e) {
                console.error('Score save error:', e);
                this.saveError = e.message || 'Error de conexión';
            }
        },

        restartLeague() {
            window.location.href = `/league?player=${encodeURIComponent(this.playerName)}`;
        },

        getOptionClass(option) {
            if (this.selectedAnswer === null) return 'opt-idle';
            if (option === this.current.answer) return 'opt-correct';
            if (option === this.selectedAnswer) return 'opt-wrong';
            return 'opt-wrong';
        },

        formatTime(s) {
            return `${Math.floor(s/60).toString().padStart(2,'0')}:${(s%60).toString().padStart(2,'0')}`;
        },

        // ── Comodines ──────────────────────────────────────────────
        pickWildcardChoices() {
			const pool = [
				{ id:'fifty_fifty',  name:'50/50',           desc:'Elimina 2 respuestas incorrectas',          icon:'⚡', color:'#ffcb05' },
				{ id:'reveal_type',  name:'Saber tipo',      desc:'Revela el tipo del Pokémon',                icon:'🔍', color:'#a78bfa' },
				{ id:'freeze_time',  name:'Congelar tiempo', desc:'Detiene el cronómetro 10 segundos',         icon:'❄️', color:'#96D9D6' },
				{ id:'shield',       name:'Escudo',          desc:'Absorbe el siguiente fallo sin perder vida',icon:'🛡️', color:'#2ed573' },
			];
			const inventoryIds = new Set(this.wildcardInventory.map(w => w.id));
			const available = pool.filter(w => {
				if (w.id === 'shield' && this.shieldActive) return false;
				if (inventoryIds.has(w.id)) return false;
				return true;
			});
            return available.sort(() => Math.random() - .5).slice(0, 3);
        },

        selectWildcard(wc) {
            this.wildcardChoiceActive = false;
            if (this.wildcardInventory.length < 3) {
                this.wildcardInventory.push(wc);
            }
            this.nextQuestion();
        },

        useWildcard(id) {
            const idx = this.wildcardInventory.findIndex(w => w.id === id);
            if (idx === -1 || this.selectedAnswer !== null) return;

            const wc = this.wildcardInventory[idx];
            this.wildcardInventory.splice(idx, 1);

            switch (id) {
                case 'fifty_fifty': {
                    const wrong = (this.current?.options ?? []).filter(o => o !== this.current.answer);
                    const shuffled = wrong.sort(() => Math.random() - .5);
                    this.hiddenOptions = shuffled.slice(0, Math.min(2, wrong.length - 1));
                    break;
                }
                case 'reveal_type':
                    this.typeRevealed = true;
                    break;
                case 'freeze_time':
                    this.timerFrozenUntil = Date.now() + 10000;
                    break;
                case 'shield':
                    this.shieldActive = true;
                    break;
            }
        },
    };
}
</script>
@endsection
