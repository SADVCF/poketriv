@extends('layouts.app')
@section('title', 'PokéTrivia — ¿Quién es ese Pokémon?')
@section('description', 'Juega al trivia Pokémon gratis. Adivina el Pokémon por su silueta, responde preguntas de tipos y evoluciones. Sin anuncios, sin registro. ¿Cuántos conoces?')

@section('content')
<div class="home-root" x-data="homeForm()">

    {{-- Background Pokéball --}}
    <div class="bg-ball" aria-hidden="true">
        <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="100" cy="100" r="96" stroke="rgba(255,203,5,0.07)" stroke-width="2"/>
            <path d="M4 100 A96 96 0 0 1 196 100" fill="rgba(255,203,5,0.03)"/>
            <line x1="4" y1="100" x2="196" y2="100" stroke="rgba(255,255,255,0.06)" stroke-width="3"/>
            <circle cx="100" cy="100" r="22" fill="#07080f" stroke="rgba(255,203,5,0.12)" stroke-width="3"/>
            <circle cx="100" cy="100" r="9" fill="rgba(255,203,5,0.08)" stroke="rgba(255,203,5,0.15)" stroke-width="2"/>
        </svg>
    </div>

    {{-- Ambient glows --}}
    <div class="glow glow-yellow" aria-hidden="true"></div>
    <div class="glow glow-blue" aria-hidden="true"></div>

    {{-- Top marquee --}}
    <div class="marquee-wrap" x-show="top10.length > 0">
        <div class="marquee-track">
            <div class="marquee-content">
                <template x-for="(s, i) in top10Dup" :key="i">
                    <span class="marquee-item">
                        <span class="marquee-pos" x-text="'#' + (i % top10.length + 1)"></span>
                        <span class="marquee-name" x-text="s.name"></span>
                        <span class="marquee-pts" x-text="s.score.toLocaleString() + ' pts'"></span>
                        <span class="marquee-badge" x-text="s.mode.toUpperCase()"></span>
                    </span>
                </template>
            </div>
        </div>
    </div>

    {{-- Two-column layout --}}
    <div class="home-layout">

        {{-- LEFT: Branding + Social proof --}}
        <div class="home-hero">
            <div class="hero-eyebrow">POKE</div>
            <h1 class="hero-title">TRIVIA</h1>
            <p class="hero-tagline">¿Eres un verdadero<br>Maestro Pokémon?</p>

            {{-- Top 3 mini-ranking --}}
            <div class="hero-ranking" x-show="top10.length >= 3">
                <div class="ranking-label">TOP JUGADORES</div>
                <template x-for="(s, i) in top10.slice(0,3)" :key="i">
                    <div class="ranking-row" :class="i === 0 ? 'ranking-row--gold' : ''">
                        <span class="ranking-pos" x-text="'#' + (i+1)"></span>
                        <span class="ranking-name" x-text="s.name"></span>
                        <span class="ranking-score" x-text="s.score.toLocaleString()"></span>
                    </div>
                </template>
                <a href="{{ route('ranking') }}" class="ranking-link">Ver ranking completo →</a>
            </div>
        </div>

        {{-- RIGHT: Game card --}}
        <div class="home-card" style="animation:fade-up .4s ease-out">
            <div class="home-card-accent"></div>

            {{-- Liga hero --}}
            <div class="league-hero" @click="startLeague()">
                <div class="league-hero-inner">
                    <div class="league-hero-icon">⚔️</div>
                    <div>
                        <div class="league-hero-title">LIGA POKÉMON</div>
                        <div class="league-hero-sub">50 preguntas · 5 etapas · 3 vidas · Comodines</div>
                    </div>
                </div>
                <div class="league-hero-cta">ENTRAR EN LA LIGA →</div>
            </div>

            {{-- Divider --}}
            <div class="divider">
                <span class="divider-line"></span>
                <span class="divider-text">O JUEGA CLÁSICO</span>
                <span class="divider-line"></span>
            </div>

            {{-- Sub-modes --}}
            <div class="submode-row">
                <template x-for="d in submodes" :key="d.value">
                    <button type="button" @click="selectSubmode(d.value)"
                        :class="['submode-btn', submode === d.value ? 'submode-btn--on submode-btn--' + d.value : '']">
                        <span class="submode-icon" x-html="d.iconHtml"></span>
                        <div class="submode-info">
                            <span class="submode-name" x-text="d.label"></span>
                            <span class="submode-sub" x-text="d.sub"></span>
                        </div>
                    </button>
                </template>
            </div>

            {{-- Config panel --}}
            <div x-show="submode !== null" x-transition:enter.duration.200ms style="overflow:hidden">

                <div class="form-field">
                    <div class="form-label-row">
                        <label class="form-label" style="margin:0">HASTA GEN</label>
                        <span class="gen-info-badge" x-text="genInfo"></span>
                    </div>
                    <div class="gen-row">
                        <template x-for="g in generations" :key="g.num">
                            <button type="button" @click="maxGen = g.num"
                                :class="['gen-btn', maxGen === g.num ? 'gen-btn--on' : '', maxGen >= g.num ? 'gen-btn--reached' : '']"
                                :title="g.region">
                                <span class="gen-num" x-text="g.roman"></span>
                                <span class="gen-region" x-text="g.region"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="form-field">
                    <div class="form-label-row">
                        <label class="form-label" style="margin:0">PREGUNTAS</label>
                        <span class="form-label" style="margin:0;color:var(--yellow)" x-text="questionCount"></span>
                    </div>
                    <div class="qcount-row">
                        <template x-for="n in [5,10,15,20,25]" :key="n">
                            <button type="button" @click="questionCount = n"
                                :class="['qcount-btn', questionCount === n ? 'qcount-btn--on' : '']"
                                x-text="n">
                            </button>
                        </template>
                    </div>
                </div>

                <button type="button" @click="startClassic()" class="start-btn">EMPEZAR</button>
            </div>

        </div>
    </div>
</div>

<style>
/* ── Root ───────────────────────────────────────────────────────────── */
.home-root {
    min-height: calc(100vh - 40px - 56px);
    display: flex; flex-direction: column; align-items: stretch;
    padding: 0 0 0; position: relative; overflow: hidden;
}

/* ── Background ball ────────────────────────────────────────────────── */
.bg-ball {
    position: absolute; pointer-events: none; z-index: 0;
    width: min(900px, 90vw); height: min(900px, 90vw);
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    opacity: 1;
}
.bg-ball svg { width: 100%; height: 100%; }

/* ── Ambient glows ──────────────────────────────────────────────────── */
.glow {
    position: absolute; pointer-events: none; z-index: 0;
    border-radius: 50%; filter: blur(80px);
}
.glow-yellow {
    width: 500px; height: 400px;
    top: 30%; left: 55%;
    transform: translate(-50%, -50%);
    background: radial-gradient(ellipse, rgba(255,203,5,.09) 0%, transparent 70%);
}
.glow-blue {
    width: 400px; height: 300px;
    bottom: 20%; left: 20%;
    background: radial-gradient(ellipse, rgba(75,123,236,.07) 0%, transparent 70%);
}

/* ── Marquee ────────────────────────────────────────────────────────── */
.marquee-wrap {
    width: 100%; overflow: hidden;
    background: rgba(255,203,5,.03); border-bottom: 1px solid rgba(255,203,5,.1);
    position: relative; z-index: 1; flex-shrink: 0;
}
.marquee-track { overflow: hidden; }
.marquee-content {
    display: flex; gap: 28px; padding: 7px 16px;
    animation: marqueeScroll 35s linear infinite; width: max-content;
}
@keyframes marqueeScroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.marquee-item {
    display: inline-flex; align-items: center; gap: 6px;
    font-family: var(--font-mono); font-size: 10px; white-space: nowrap;
}
.marquee-pos  { color: var(--text-faint); font-weight: 700; }
.marquee-name { color: var(--text); font-weight: 600; }
.marquee-pts  { color: var(--yellow); font-weight: 700; }
.marquee-badge {
    font-size: 7px; font-weight: 800; letter-spacing: .06em;
    padding: 1px 5px; border-radius: 3px; background: rgba(255,203,5,.12); color: var(--yellow);
}

/* ── Two-column layout ──────────────────────────────────────────────── */
.home-layout {
    flex: 1; display: flex; align-items: center; justify-content: center;
    gap: clamp(32px, 4vw, 72px); padding: 24px clamp(24px, 4vw, 72px);
    position: relative; z-index: 1;
    width: 100%; max-width: 1400px; margin: 0 auto;
}
@media (max-width: 960px) {
    .home-layout { flex-direction: column; gap: 20px; padding: 16px; align-items: stretch; }
    .marquee-wrap { display: none; }
}

/* ── Left: Hero branding ────────────────────────────────────────────── */
.home-hero {
    flex: 0 0 clamp(340px, 35vw, 500px); display: flex; flex-direction: column;
    align-items: flex-start; gap: 0;
}
@media (max-width: 960px) {
    .home-hero { flex: none; align-items: center; text-align: center; }
}
.hero-eyebrow {
    font-family: var(--font-display); font-weight: 700;
    font-size: clamp(22px, 2.2vw, 32px); letter-spacing: .35em; color: var(--text-muted);
    line-height: 1; margin-bottom: 2px;
}
.hero-title {
    font-family: var(--font-display); font-weight: 900;
    font-size: clamp(80px, 11vw, 130px); letter-spacing: .03em; line-height: .9;
    color: var(--yellow);
    text-shadow: 0 0 60px rgba(255,203,5,.45), 0 0 120px rgba(255,203,5,.18);
    margin-bottom: 16px;
}
.hero-tagline {
    font-family: var(--font-display); font-weight: 700;
    font-size: clamp(20px, 2vw, 28px); line-height: 1.25; letter-spacing: .02em;
    color: var(--text-muted); margin-bottom: 24px;
}
.hero-pills {
    display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 28px;
}
.hero-pill {
    font-family: var(--font-mono); font-size: 10px; font-weight: 700;
    letter-spacing: .06em; padding: 4px 10px; border-radius: 20px;
    background: rgba(255,255,255,.04); border: 1px solid var(--border-mid);
    color: var(--text-muted);
}

/* ── Mini ranking ───────────────────────────────────────────────────── */
.hero-ranking {
    width: 100%; background: rgba(255,255,255,.02);
    border: 1px solid var(--border); border-radius: 12px;
    padding: 12px 14px; display: flex; flex-direction: column; gap: 6px;
}
.ranking-label {
    font-family: var(--font-mono); font-size: 9px; font-weight: 800;
    letter-spacing: .14em; color: var(--text-faint); margin-bottom: 2px;
}
.ranking-row {
    display: flex; align-items: center; gap: 8px;
    font-family: var(--font-mono); font-size: 11px;
    padding: 4px 0; border-bottom: 1px solid rgba(255,255,255,.04);
}
.ranking-row:last-of-type { border-bottom: none; }
.ranking-row--gold .ranking-pos { color: var(--yellow); }
.ranking-row--gold .ranking-name { color: var(--text); }
.ranking-pos { font-weight: 800; color: var(--text-faint); width: 20px; flex-shrink: 0; }
.ranking-name { flex: 1; color: var(--text-muted); font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ranking-score { color: var(--yellow); font-weight: 700; }
.ranking-link {
    font-family: var(--font-mono); font-size: 9px; font-weight: 700;
    letter-spacing: .06em; color: var(--text-faint); text-decoration: none;
    margin-top: 2px; transition: color .12s;
}
.ranking-link:hover { color: var(--yellow); }

/* ── Game card ──────────────────────────────────────────────────────── */
.home-card {
    flex: 1; min-width: 0; max-width: clamp(460px, 45vw, 640px);
    background: var(--surface);
    border: 1px solid var(--border-mid);
    border-radius: 18px;
    padding: clamp(20px, 2.5vw, 32px) clamp(22px, 2.8vw, 36px) clamp(18px, 2vw, 28px);
    position: relative;
}
@media (max-width: 960px) { .home-card { max-width: 100%; } }
.home-card-accent {
    position: absolute; top: 0; left: 40px; right: 40px; height: 2px;
    background: linear-gradient(90deg, transparent, var(--yellow) 30%, var(--yellow) 70%, transparent);
    border-radius: 0 0 4px 4px;
}

/* ── League hero ────────────────────────────────────────────────────── */
.league-hero {
    background: linear-gradient(135deg, rgba(255,203,5,.1), rgba(255,203,5,.03));
    border: 1px solid rgba(255,203,5,.35);
    border-radius: 14px; padding: 16px 18px;
    cursor: pointer; transition: all .15s; position: relative; overflow: hidden;
    display: flex; flex-direction: column; gap: 12px;
    user-select: none; -webkit-user-select: none;
}
.league-hero::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,203,5,.06) 0%, transparent 60%);
    pointer-events: none;
}
.league-hero:hover { border-color: var(--yellow); box-shadow: 0 0 32px rgba(255,203,5,.22); transform: translateY(-2px); }
.league-hero:active { transform: translateY(1px); }
.league-hero-inner { display: flex; align-items: center; gap: 14px; }
.league-hero-icon { font-size: clamp(36px, 3.5vw, 48px); line-height: 1; flex-shrink: 0; }
.league-hero-title {
    font-family: var(--font-display); font-weight: 900; font-size: clamp(26px, 2.4vw, 34px);
    letter-spacing: .07em; color: var(--yellow);
    text-shadow: 0 0 30px rgba(255,203,5,.35); line-height: 1;
}
.league-hero-sub {
    font-family: var(--font-mono); font-size: clamp(10px, .9vw, 13px); font-weight: 600;
    color: var(--text-muted); letter-spacing: .05em; margin-top: 4px;
}
.league-hero-cta {
    display: block; width: 100%; padding: clamp(11px, 1.2vw, 15px) 0; text-align: center;
    background: var(--yellow); color: #06070d; border-radius: 8px;
    font-family: var(--font-display); font-weight: 900; font-size: clamp(16px, 1.4vw, 20px);
    letter-spacing: .08em; box-shadow: 0 2px 0 rgba(0,0,0,.3), 0 6px 20px rgba(255,203,5,.3);
    transition: all .12s;
}
.league-hero:hover .league-hero-cta { box-shadow: 0 4px 0 rgba(0,0,0,.3), 0 10px 28px rgba(255,203,5,.4); }

/* ── Divider ────────────────────────────────────────────────────────── */
.divider {
    display: flex; align-items: center; gap: 8px; margin: 12px 0 10px;
}
.divider-line { flex: 1; height: 1px; background: var(--border); }
.divider-text {
    font-family: var(--font-mono); font-size: 9px;
    color: var(--text-muted); letter-spacing: .1em;
}

/* ── Sub-modes ──────────────────────────────────────────────────────── */
.submode-row { display: flex; gap: 5px; }
.submode-btn {
    flex: 1; display: flex; align-items: center; gap: 6px;
    padding: 10px 8px; border-radius: 10px;
    background: var(--bg); border: 1px solid var(--border);
    cursor: pointer; transition: all .12s;
}
.submode-btn:hover { border-color: var(--border-hi); background: var(--surface-2); }
.submode-btn--on { background: var(--surface-2); transform:scale(1.02); }
.submode-btn--easy.submode-btn--on    { border-color: #2ed573; box-shadow: 0 0 16px rgba(46,213,115,.25), inset 0 0 8px rgba(46,213,115,.06); }
.submode-btn--medium.submode-btn--on  { border-color: var(--yellow); box-shadow: 0 0 16px rgba(255,203,5,.28), inset 0 0 8px rgba(255,203,5,.08); }
.submode-btn--hard.submode-btn--on    { border-color: #ff4757; box-shadow: 0 0 16px rgba(255,71,87,.22), inset 0 0 8px rgba(255,71,87,.06); }
.submode-icon { flex-shrink: 0; line-height: 1; display: flex; }
.submode-info { display: flex; flex-direction: column; align-items: flex-start; gap: 1px; }
.submode-name {
    font-family: var(--font-display); font-weight: 800; font-size: clamp(13px, 1.2vw, 16px);
    letter-spacing: .04em; color: var(--text); line-height: 1;
}
.submode-btn--easy.submode-btn--on .submode-name   { color: #2ed573; }
.submode-btn--medium.submode-btn--on .submode-name { color: var(--yellow); }
.submode-btn--hard.submode-btn--on .submode-name   { color: #ff4757; }
.submode-sub { font-family: var(--font-mono); font-size: 10px; color: var(--text-muted); line-height: 1; }

/* ── Form fields ────────────────────────────────────────────────────── */
.form-field { margin: 10px 0 8px; }
.form-label-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.form-label {
    display: block; margin-bottom: 8px;
    font-family: var(--font-mono); font-size: 11px; font-weight: 700;
    letter-spacing: .18em; color: var(--text-muted);
}
.gen-info-badge {
    font-family: var(--font-mono); font-size: 10px; font-weight: 700;
    color: var(--yellow); letter-spacing: .05em;
}
.gen-row { display: grid; grid-template-columns: repeat(9, 1fr); gap: 3px; }
.gen-btn {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 5px; padding: 5px 1px;
    cursor: pointer; transition: all .12s;
    display: flex; flex-direction: column; align-items: center; gap: 1px;
}
.gen-btn:hover { border-color: var(--border-hi); background: var(--surface-2); }
.gen-btn--reached { border-color: rgba(255,203,5,.2); background: rgba(255,203,5,.04); }
.gen-btn--on {
    background: rgba(255,203,5,.15) !important;
    border-color: var(--yellow) !important;
    box-shadow: 0 0 6px rgba(255,203,5,.18);
}
.gen-num { font-family: var(--font-display); font-weight: 800; font-size: 12px; color: var(--text); }
.gen-region {
    font-family: var(--font-mono); font-size: 8px; color: var(--text-faint);
    white-space: nowrap; overflow: hidden; width: 100%; text-align: center; text-overflow: ellipsis;
}
@media (max-width: 400px) { .gen-region { display: none; } }

/* ── Question count ─────────────────────────────────────────────────── */
.qcount-row { display: flex; gap: 5px; }
.qcount-btn {
    flex: 1; padding: 9px 4px;
    background: var(--bg); border: 1px solid var(--border); border-radius: 7px;
    font-family: var(--font-mono); font-size: 14px; font-weight: 700;
    color: var(--text-muted); cursor: pointer; transition: all .12s; text-align: center;
}
.qcount-btn:hover { border-color: var(--border-hi); color: var(--text); background: var(--surface-2); }
.qcount-btn--on {
    background: rgba(255,203,5,.15); border-color: var(--yellow);
    color: var(--yellow); box-shadow: 0 0 6px rgba(255,203,5,.15);
}

/* ── Start CTA ──────────────────────────────────────────────────────── */
.start-btn {
    width: 100%; padding: clamp(13px, 1.3vw, 17px);
    background: var(--yellow); color: #06070d;
    border: none; border-radius: 8px;
    font-family: var(--font-display); font-size: clamp(18px, 1.6vw, 22px); font-weight: 900;
    letter-spacing: .1em; cursor: pointer;
    box-shadow: 0 2px 0 rgba(0,0,0,.4), 0 4px 18px rgba(255,203,5,.28);
    transition: all .12s; margin-top: 10px;
}
.start-btn:hover { transform:translateY(-2px); box-shadow:0 4px 0 rgba(0,0,0,.4), 0 8px 26px rgba(255,203,5,.38); }
.start-btn:active { transform:translateY(1px); }
</style>

<script>
function homeForm() {
    return {
        submode:       null,
        maxGen:        2,
        questionCount: 10,
        top10: @json($top10),

        get top10Dup() {
            return [...this.top10, ...this.top10];
        },

        submodes: [
            { value:'easy',   label:'FÁCIL',   sub:'12s · 4 opc',
              iconHtml:'<svg viewBox="0 0 16 16" width="16" height="16"><path d="M8 1C4.136 1 1 4.136 1 8s3.136 7 7 7 7-3.136 7-7-3.136-7-7-7z" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.7"/><path d="M1 8a7 7 0 0 1 14 0" fill="#ee1515"/><path d="M1 8h14" stroke="#1a1a2e" stroke-width="0.7"/><circle cx="8" cy="8" r="2.4" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.7"/><circle cx="8" cy="8" r="1.2" fill="#ccc" stroke="#1a1a2e" stroke-width="0.4"/></svg>' },
            { value:'medium',  label:'MEDIO',   sub:'8s · 4 opc',
              iconHtml:'<svg viewBox="0 0 16 16" width="16" height="16"><path d="M8 1C4.136 1 1 4.136 1 8s3.136 7 7 7 7-3.136 7-7-3.136-7-7-7z" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.7"/><path d="M1 8a7 7 0 0 1 14 0" fill="#1565C0"/><path d="M1 8h14" stroke="#1a1a2e" stroke-width="0.7"/><circle cx="8" cy="8" r="2.4" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.7"/><circle cx="8" cy="8" r="1.2" fill="#ccc" stroke="#1a1a2e" stroke-width="0.4"/></svg>' },
            { value:'hard',    label:'DIFÍCIL', sub:'6s · 6 opc',
              iconHtml:'<svg viewBox="0 0 16 16" width="16" height="16"><path d="M8 1C4.136 1 1 4.136 1 8s3.136 7 7 7 7-3.136 7-7-3.136-7-7-7z" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.7"/><path d="M1 8a7 7 0 0 1 14 0" fill="#222"/><path d="M1 8h14" stroke="#1a1a2e" stroke-width="0.7"/><circle cx="8" cy="8" r="2.4" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.7"/><circle cx="8" cy="8" r="1.2" fill="#ccc" stroke="#1a1a2e" stroke-width="0.4"/></svg>' },
        ],

        generations: [
            { num:1, roman:'I',   region:'Kanto'  },
            { num:2, roman:'II',  region:'Johto'  },
            { num:3, roman:'III', region:'Hoenn'  },
            { num:4, roman:'IV',  region:'Sinnoh' },
            { num:5, roman:'V',   region:'Unova'  },
            { num:6, roman:'VI',  region:'Kalos'  },
            { num:7, roman:'VII', region:'Alola'  },
            { num:8, roman:'VIII',region:'Galar'  },
            { num:9, roman:'IX',  region:'Paldea' },
        ],

        pokemonCounts: { 1:151, 2:251, 3:386, 4:493, 5:649, 6:721, 7:809, 8:905, 9:1025 },

        get genInfo() {
            const counts = this.pokemonCounts;
            const g = this.maxGen;
            const total = counts[g] || 0;
            const region = this.generations[g - 1]?.region ?? '';
            return `${region} · ${total} Pokémon`;
        },

        selectSubmode(value) {
            this.submode = value;
            if (value === 'medium') { this.maxGen = 5; this.questionCount = 15; }
            else if (value === 'hard') { this.maxGen = 9; this.questionCount = 25; }
            else { this.maxGen = 2; this.questionCount = 10; }
        },

        startLeague() {
            window.location.href = '/league';
        },

        startClassic() {
            const p = new URLSearchParams({
                difficulty:     this.submode,
                max_generation: this.maxGen,
                question_count: this.questionCount,
            });
            window.location.href = `/game?${p}`;
        },
    }
}
</script>
@endsection
