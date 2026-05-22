@extends('layouts.app')
@section('title', 'PokéTrivia — Jugando')

@push('styles')
<style>
/* ── Game-specific styles ──────────────────────────────────────────── */

/* silhouette */
.poke-silhouette { filter: brightness(0) contrast(1.1); }
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
.opt-reveal  { background:rgba(46,213,115,.07)!important; border-color:rgba(46,213,115,.35)!important; color:rgba(46,213,115,.65)!important; }

/* HP bar */
.hp-bar-fill { transition: width .08s linear, background-color .5s ease; }
.hp-blink    { animation: hp-blink .5s ease-in-out infinite; }

/* corner markers */
.corner { position:absolute; width:14px; height:14px; border-color:var(--yellow); border-style:solid; opacity:.5; }
.corner-tl { top:0; left:0;  border-width:2px 0 0 2px; }
.corner-tr { top:0; right:0; border-width:2px 2px 0 0; }
.corner-bl { bottom:0; left:0;  border-width:0 0 2px 2px; }
.corner-br { bottom:0; right:0; border-width:0 2px 2px 0; }

/* points popup */
.pts-popup {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
    font-family:var(--font-display); font-weight:900; font-size:28px;
    color:var(--yellow); text-shadow:0 0 20px rgba(255,203,5,.6);
    pointer-events:none; white-space:nowrap;
    animation:points-float .9s ease-out forwards;
    z-index:10;
}

/* result screen */
.stat-card {
    background:var(--surface-2); border:1px solid var(--border-mid);
    border-radius:12px; padding:20px 16px; text-align:center;
}
.stat-val {
    font-family:var(--font-mono); font-weight:700; font-size:28px; line-height:1;
}
.stat-lbl {
    font-family:var(--font-mono); font-size:9px; font-weight:700;
    letter-spacing:.15em; color:var(--text-muted); margin-top:5px;
    text-transform:uppercase;
}

/* score bump */
.score-bump { animation:score-bump .45s ease-out; }

/* question slide animation */
.q-enter { animation:slide-right .3s ease-out; }
</style>
@endpush

@section('content')
<div
    class="game-root"
    x-data="pokeGame({
        playerName: @js($playerName),
        difficulty: @js($difficulty),
        timePerQuestion: {{ $timePerQuestion }},
        optionCount: {{ $optionCount }},
        maxGen: {{ $maxGen ?? 2 }},
        questionCount: {{ $questionCount ?? 10 }}
    })"
>

{{-- ── LOADING ────────────────────────────────────────────────────── --}}
<div x-show="phase === 'loading'" class="state-center">
    <svg width="52" height="52" viewBox="0 0 100 100" style="animation:spin 1s linear infinite;opacity:.5">
        <circle cx="50" cy="50" r="44" fill="none" stroke="rgba(255,203,5,.4)" stroke-width="7"/>
        <path d="M6 50 A44 44 0 0 1 94 50" fill="rgba(255,203,5,.15)"/>
        <line x1="6" y1="50" x2="94" y2="50" stroke="rgba(255,255,255,.2)" stroke-width="5"/>
        <circle cx="50" cy="50" r="10" fill="#07080f" stroke="rgba(255,203,5,.4)" stroke-width="4"/>
    </svg>
    <p style="font-family:var(--font-mono);font-size:12px;color:var(--text-muted);letter-spacing:.1em;">
        CARGANDO POKÉMON...
    </p>
</div>

{{-- ── ERROR ──────────────────────────────────────────────────────── --}}
<div x-show="phase === 'error'" class="state-center">
    <div style="font-size:40px;margin-bottom:12px;">⚠️</div>
    <p style="font-family:var(--font-display);font-size:22px;font-weight:800;margin-bottom:6px;">Error de carga</p>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:18px;" x-text="errorMsg"></p>
    <div style="display:flex;gap:10px;">
        <button @click="retryLoad()" class="btn-yellow">Reintentar</button>
        <a href="/" class="btn-ghost">Inicio</a>
    </div>
</div>

{{-- ── JUEGO ──────────────────────────────────────────────────────── --}}
<div x-show="phase !== 'loading' && phase !== 'finished' && phase !== 'error'"
     class="game-layout q-enter" :key="currentIndex">

    {{-- HUD ── progress + score --}}
    <div class="hud">
        <div class="hud-left">
            <div class="hud-q-label">
                PREGUNTA
                <span class="hud-q-num" x-text="currentIndex + 1"></span>
                <span style="color:var(--text-faint)"> / </span>
                <span x-text="questions.length"></span>
            </div>
            <div class="hud-bar-track">
                <div class="hud-bar-fill" :style="`width:${progress}%`"></div>
            </div>
        </div>
        <div class="hud-right">
            {{-- Streak combo --}}
            <div x-show="streak >= 2" class="combo-badge" :class="streak >= 5 ? 'combo-hot' : ''">
                <span x-show="streak >= 5" style="font-size:14px;">🔥</span>
                <span style="font-family:var(--font-mono);font-weight:700;">×<span x-text="streak"></span></span>
                <span style="font-size:9px;letter-spacing:.1em;opacity:.7;">COMBO</span>
            </div>
            {{-- Score --}}
            <div class="score-block">
                <div class="score-label">SCORE</div>
                <div class="score-value" :class="scoreFlash ? 'score-bump' : ''" x-text="score.toLocaleString()"></div>
            </div>
        </div>
    </div>

    {{-- Battle card --}}
    <div class="battle-card" :style="typeAccentStyle">
        {{-- Corner HUD markers --}}
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

        {{-- Points popup --}}
        <div x-show="pointsPopup.show" class="pts-popup">
            +<span x-text="pointsPopup.amount"></span>
            <span x-show="pointsPopup.streakBonus > 0" style="font-size:16px;color:#ffda45;"> (+<span x-text="pointsPopup.streakBonus"></span>)</span>
            <span x-show="pointsPopup.genMult > 1" style="font-size:14px;color:#a78bfa;"> (×<span x-text="pointsPopup.genMult.toFixed(2)"></span>🌍)</span>
        </div>

        {{-- Card header: gen badge + type badges --}}
        <div class="card-header">
            <span class="gen-pill" x-show="current" x-text="'GEN ' + (current?.generation ?? '?')"></span>
            <div class="type-row" x-show="revealed || difficulty !== 'hard'">
                <template x-for="t in (current ? current.types : [])" :key="t">
                    <span class="type-badge" :class="`type-${t}`" x-text="t"></span>
                </template>
            </div>
            <div x-show="!revealed && difficulty === 'hard'" style="height:22px;"></div>
        </div>

        {{-- Pokemon image --}}
        <div class="poke-stage">
            <img
                :key="currentIndex"
                :src="current ? current.artwork_url : ''"
                :alt="revealed ? (current ? current.answer : '') : '???'"
                :class="['poke-img', difficulty === 'hard' && !revealed ? 'poke-silhouette' : '', difficulty === 'hard' && revealed ? 'poke-reveal' : '']"
                :style="`opacity:${imgLoaded ? 1 : 0}`"
                @@load="imgLoaded = true"
                @@error="imgLoaded = true"
            >
        </div>

        {{-- Pokemon name --}}
        <div class="poke-name-row">
            <span x-show="revealed" class="poke-name" x-text="current ? current.answer : ''"></span>
            <span x-show="!revealed" class="poke-unknown">? ? ?</span>
        </div>

        {{-- HP bar timer ── the signature element --}}
        <div class="hp-bar-section">
            <span class="hp-label">HP</span>
            <div class="hp-track">
                <div
                    class="hp-bar-fill"
                    :class="timeLeft <= 3 ? 'hp-blink' : ''"
                    :style="`width:${Math.max(0, timeLeft/timePerQuestion*100)}%; background:${hpBarColor};`"
                ></div>
            </div>
            <span class="hp-num" :style="`color:${hpBarColor}`" x-text="Math.ceil(timeLeft)"></span>
        </div>
    </div>

    {{-- Answer options --}}
    <div class="options-grid" :class="optionCount > 4 ? 'options-grid--6' : ''">
        <template x-for="(option, idx) in (current ? current.options : [])" :key="idx">
            <button
                @click="selectAnswer(option)"
                :disabled="selectedAnswer !== null"
                :class="['opt-btn', getOptionClass(option)]"
            >
                <span class="opt-key" x-text="'ABCDEF'[idx]"></span>
                <span class="opt-text" x-text="option"></span>
            </button>
        </template>
    </div>

</div>

{{-- ── RESULTADO ──────────────────────────────────────────────────── --}}
<div x-show="phase === 'finished'" class="result-root" style="animation:fade-up .4s ease-out">

    <div class="result-hero">
        <div class="result-emoji" x-html="resultEmoji"></div>
        <h2 class="result-title" x-text="resultTitle"></h2>
        <p class="result-player" x-text="playerName"></p>
    </div>

    <div class="result-stats">
        <div class="stat-card">
            <div class="stat-val" style="color:var(--yellow)" x-text="score.toLocaleString()"></div>
            <div class="stat-lbl">Puntuación</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color:var(--green)">
                <span x-text="correctCount"></span><span style="color:var(--text-faint);font-size:18px;">/</span><span x-text="questions.length" style="font-size:18px;"></span>
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
            <div class="stat-lbl">Posición</div>
        </div>
    </div>

    <div class="result-diff-row">
        <span class="chip" style="display:inline-flex;align-items:center;gap:5px" x-html="difficultyBadge"></span>
        <span class="chip">🌍 Gen 1–<span x-text="maxGen"></span> (×<span x-text="genMultiplier.toFixed(2)"></span>)</span>
        <span x-show="maxStreak >= 3" class="chip chip--yellow">🔥 Racha <span x-text="maxStreak"></span></span>
    </div>

    <div class="result-actions">
        <button @click="restartGame()" class="btn-yellow">Jugar de nuevo</button>
        <a href="{{ route('ranking') }}" class="btn-ghost">Ver ranking →</a>
    </div>
</div>

</div>{{-- /game-root --}}

<style>
.game-root {
    min-height: calc(100vh - 54px);
    display: flex; flex-direction: column; align-items: center;
    justify-content: flex-start;
    padding: 20px 16px 32px; gap: 0;
}
.state-center {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 14px; padding: 60px 0;
}

/* ── HUD ─────────────────────────────────────────────────────────── */
.hud {
    width: 100%; max-width: 520px;
    display: flex; align-items: flex-end; gap: 14px;
    margin-bottom: 14px;
}
.hud-left { flex: 1; }
.hud-q-label {
    font-family: var(--font-mono); font-size: 10px; font-weight: 700;
    letter-spacing: .1em; color: var(--text-muted);
    margin-bottom: 7px;
}
.hud-q-num {
    font-size: 13px; font-weight: 700; color: var(--text);
}
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
.hud-right {
    display: flex; align-items: center; gap: 12px; flex-shrink: 0;
}
.combo-badge {
    display: flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 6px;
    background: rgba(255,203,5,.1); border: 1px solid rgba(255,203,5,.3);
    color: var(--yellow);
    font-family: var(--font-display); font-size: 14px; font-weight: 800;
    letter-spacing: .03em;
}
.combo-hot { animation: hp-blink .6s ease-in-out infinite alternate; }
.score-block { text-align: right; }
.score-label {
    font-family: var(--font-mono); font-size: 9px; font-weight: 700;
    letter-spacing: .15em; color: var(--text-faint); margin-bottom: 1px;
}
.score-value {
    font-family: var(--font-mono); font-size: 22px; font-weight: 700;
    color: var(--text); letter-spacing: -.01em;
}

/* ── Battle card ─────────────────────────────────────────────────── */
.battle-card {
    width: 100%; max-width: 520px;
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
@keyframes poke-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}
.poke-name-row {
    text-align: center; min-height: 36px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 16px 10px;
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
    width: 100%; max-width: 520px;
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
.opt-text {
    font-family: var(--font-ui); font-size: 13px; font-weight: 600;
    line-height: 1.3;
}

/* ── Result screen ───────────────────────────────────────────────── */
.result-root {
    width: 100%; max-width: 520px;
    display: flex; flex-direction: column; gap: 16px;
    padding-top: 16px;
}
.result-hero { text-align: center; margin-bottom: 4px; }
.result-emoji { line-height: 1; margin-bottom: 10px; display:flex; justify-content:center; filter: drop-shadow(0 4px 20px rgba(0,0,0,.4)); }
.result-title {
    font-family: var(--font-display); font-size: 36px; font-weight: 900;
    letter-spacing: .03em; color: var(--text); margin-bottom: 4px;
}
.result-player {
    font-family: var(--font-mono); font-size: 12px; color: var(--text-muted);
    letter-spacing: .1em;
}
.result-stats {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.result-diff-row {
    display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;
}
.result-actions {
    display: flex; flex-direction: column; gap: 8px;
}
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
.btn-yellow:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 0 rgba(0,0,0,.4), 0 8px 28px rgba(255,203,5,.4);
}
.btn-ghost {
    display: block; width: 100%; padding: 13px;
    background: var(--surface-2); border: 1px solid var(--border-mid);
    border-radius: 8px;
    font-family: var(--font-ui); font-size: 14px; font-weight: 600;
    color: var(--text-muted); text-align: center; text-decoration: none;
    transition: all .12s; cursor: pointer;
}
.btn-ghost:hover { color: var(--text); border-color: var(--border-hi); background: var(--surface-3); }
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
    stellar:  { color:'#4FDBD6', glow:'rgba(79,219,214,0.22)' },
};

const AUDIO_CTX = typeof AudioContext !== 'undefined' ? new (window.AudioContext || window.webkitAudioContext)() : null;
function playSound(type) {
    if (!AUDIO_CTX) return;
    try {
        const now = AUDIO_CTX.currentTime;
        if (type === 'correct') {
            [523,659,784].forEach((f,i)=>{
                const o=AUDIO_CTX.createOscillator(), g=AUDIO_CTX.createGain();
                o.connect(g); g.connect(AUDIO_CTX.destination);
                o.type='sine'; o.frequency.setValueAtTime(f, now+i*.1);
                g.gain.setValueAtTime(.13, now+i*.1);
                g.gain.exponentialRampToValueAtTime(.001, now+i*.1+.3);
                o.start(now+i*.1); o.stop(now+i*.1+.3);
            });
        } else if (type === 'wrong') {
            const o=AUDIO_CTX.createOscillator(), g=AUDIO_CTX.createGain();
            o.connect(g); g.connect(AUDIO_CTX.destination);
            o.type='sawtooth'; o.frequency.setValueAtTime(200,now); o.frequency.setValueAtTime(150,now+.15);
            g.gain.setValueAtTime(.09,now); g.gain.exponentialRampToValueAtTime(.001,now+.3);
            o.start(now); o.stop(now+.3);
        } else if (type === 'tick') {
            const o=AUDIO_CTX.createOscillator(), g=AUDIO_CTX.createGain();
            o.connect(g); g.connect(AUDIO_CTX.destination);
            o.frequency.setValueAtTime(800,now); g.gain.setValueAtTime(.04,now);
            g.gain.exponentialRampToValueAtTime(.001,now+.05);
            o.start(now); o.stop(now+.05);
        } else if (type === 'finish') {
            [523,659,784,1047].forEach((f,i)=>{
                const o=AUDIO_CTX.createOscillator(), g=AUDIO_CTX.createGain();
                o.connect(g); g.connect(AUDIO_CTX.destination);
                o.type='sine'; o.frequency.setValueAtTime(f, now+i*.15);
                g.gain.setValueAtTime(.11, now+i*.15);
                g.gain.exponentialRampToValueAtTime(.001, now+i*.15+.3);
                o.start(now+i*.15); o.stop(now+i*.15+.3);
            });
        } else if (type === 'timeout' || type === 'wrong') {
            const o=AUDIO_CTX.createOscillator(), g=AUDIO_CTX.createGain();
            o.connect(g); g.connect(AUDIO_CTX.destination);
            o.type='square'; o.frequency.setValueAtTime(250,now); o.frequency.setValueAtTime(180,now+.2);
            g.gain.setValueAtTime(.07,now); g.gain.exponentialRampToValueAtTime(.001,now+.35);
            o.start(now); o.stop(now+.35);
        }
    } catch(e) {}
}

function pokeGame({ playerName, difficulty, timePerQuestion, optionCount, maxGen, questionCount }) {
    return {
        playerName, difficulty, timePerQuestion, optionCount, maxGen, questionCount,

        phase: 'loading',
        questions: [], currentIndex: 0,
        selectedAnswer: null,
        score: 0, streak: 0, maxStreak: 0, correctCount: 0,
        revealed: false, scoreFlash: false, rank: null,
        gameToken: '', errorMsg: '',
        timeLeft: timePerQuestion, timerInterval: null, timerStart: 0,
        gameStartTime: null, totalTime: 0,
        pointsPopup: { show: false, amount: 0, streakBonus: 0 },
        imgLoaded: false,

        get current()     { return this.questions[this.currentIndex] ?? null; },
        get progress()    { return this.questions.length > 0 ? (this.currentIndex / this.questions.length) * 100 : 0; },
        get typeAccentStyle() {
            if (!this.current) return '';
            const t = this.current.types[0];
            const c = TYPE_COLORS[t] ?? TYPE_COLORS.normal;
            return `border-color:${c.color}40; box-shadow:0 0 32px ${c.glow}, 0 0 0 1px ${c.color}18 inset;`;
        },
        get hpBarColor() {
            const p = this.timeLeft / this.timePerQuestion;
            if (p > .5) return '#2ed573';
            if (p > .25) return '#ffcb05';
            return '#ff4757';
        },
        get resultEmoji() {
            const p = this.questions.length > 0 ? this.correctCount / this.questions.length : 0;
            const pika = (eyes, mouth, extra='') => `<svg width="80" height="80" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 43L10 5L36 21Z" fill="#FFCB05" stroke="#c8a000" stroke-width="1"/>
              <path d="M10 5L24 22L36 21Z" fill="#2a1a0a"/>
              <path d="M82 43L90 5L64 21Z" fill="#FFCB05" stroke="#c8a000" stroke-width="1"/>
              <path d="M90 5L76 22L64 21Z" fill="#2a1a0a"/>
              <ellipse cx="50" cy="57" rx="40" ry="38" fill="#FFCB05" stroke="#c8a000" stroke-width="1.5"/>
              <ellipse cx="21" cy="67" rx="10" ry="8" fill="#E8544A" opacity="0.9"/>
              <ellipse cx="79" cy="67" rx="10" ry="8" fill="#E8544A" opacity="0.9"/>
              <ellipse cx="50" cy="58" rx="2.5" ry="1.8" fill="#b89000"/>
              ${eyes}
              ${mouth}
              ${extra}
            </svg>`;
            // ≥90% Pikachu eufórico: ojos guiñados (∪), sonrisa enorme con lengua, destellos eléctricos
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
            // ≥70% Pikachu contento: ojos abiertos con brillo, sonrisa
            const happy = pika(
                `<ellipse cx="38" cy="50" rx="5.5" ry="6" fill="#2a1a0a"/>
                 <ellipse cx="62" cy="50" rx="5.5" ry="6" fill="#2a1a0a"/>
                 <circle cx="40" cy="48" r="2" fill="white"/>
                 <circle cx="64" cy="48" r="2" fill="white"/>`,
                `<path d="M34 66Q50 79 66 66" fill="none" stroke="#2a1a0a" stroke-width="3" stroke-linecap="round"/>`
            );
            // ≥50% Pikachu preocupado: cejas internas arriba, ojos neutros, boca plana
            const worried = pika(
                `<path d="M33 43Q38 39 43 42" fill="none" stroke="#2a1a0a" stroke-width="2" stroke-linecap="round"/>
                 <path d="M57 42Q62 39 67 43" fill="none" stroke="#2a1a0a" stroke-width="2" stroke-linecap="round"/>
                 <ellipse cx="38" cy="51" rx="5.5" ry="5.5" fill="#2a1a0a"/>
                 <ellipse cx="62" cy="51" rx="5.5" ry="5.5" fill="#2a1a0a"/>
                 <circle cx="40" cy="49" r="2" fill="white"/>
                 <circle cx="64" cy="49" r="2" fill="white"/>`,
                `<path d="M38 67Q50 72 62 67" fill="none" stroke="#2a1a0a" stroke-width="2.5" stroke-linecap="round"/>`
            );
            // <50% Pikachu triste: cejas tristes, párpados caídos, frunce, lágrima
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
            const p = this.questions.length > 0 ? this.correctCount / this.questions.length : 0;
            return p >= .9 ? '¡Maestro Pokémon!' : p >= .7 ? '¡Muy bien!' : p >= .5 ? 'Nada mal' : 'Necesitas entrenar más';
        },
        get genMultiplier() {
            return 1 + (this.maxGen - 1) * 0.15;
        },
        get difficultyBadge() {
            const pokeball = `<svg width="15" height="15" viewBox="0 0 20 20"><circle cx="10" cy="10" r="9.5" fill="#fff"/><path d="M.5 10A9.5 9.5 0 0 1 19.5 10Z" fill="#e63232"/><line x1=".5" y1="10" x2="19.5" y2="10" stroke="#1a1a1a" stroke-width="1.3"/><circle cx="10" cy="10" r="9.5" fill="none" stroke="#1a1a1a" stroke-width="1.3"/><circle cx="10" cy="10" r="3.1" fill="#fff" stroke="#1a1a1a" stroke-width="1.3"/><circle cx="10" cy="10" r="1.3" fill="#1a1a1a"/></svg>`;
            const greatball = `<svg width="15" height="15" viewBox="0 0 20 20"><circle cx="10" cy="10" r="9.5" fill="#fff"/><path d="M.5 10A9.5 9.5 0 0 1 19.5 10Z" fill="#2563eb"/><path d="M2.2 8.8L5.8 4.2L5.8 8.8Z" fill="#e63232"/><path d="M17.8 8.8L14.2 4.2L14.2 8.8Z" fill="#e63232"/><line x1=".5" y1="10" x2="19.5" y2="10" stroke="#1a1a1a" stroke-width="1.3"/><circle cx="10" cy="10" r="9.5" fill="none" stroke="#1a1a1a" stroke-width="1.3"/><circle cx="10" cy="10" r="3.1" fill="#fff" stroke="#1a1a1a" stroke-width="1.3"/><circle cx="10" cy="10" r="1.3" fill="#1a1a1a"/></svg>`;
            const ultraball = `<svg width="15" height="15" viewBox="0 0 20 20"><circle cx="10" cy="10" r="9.5" fill="#fff"/><path d="M.5 10A9.5 9.5 0 0 1 19.5 10Z" fill="#1a1a1a"/><path d="M1 7A9.5 9.5 0 0 1 19 7L19 9A9.5 9.5 0 0 0 1 9Z" fill="#f7c823"/><line x1=".5" y1="10" x2="19.5" y2="10" stroke="#1a1a1a" stroke-width="1.3"/><circle cx="10" cy="10" r="9.5" fill="none" stroke="#1a1a1a" stroke-width="1.3"/><circle cx="10" cy="10" r="3.1" fill="#fff" stroke="#1a1a1a" stroke-width="1.3"/><circle cx="10" cy="10" r="1.3" fill="#f7c823"/></svg>`;
            const labels = { easy: [pokeball,'Fácil'], medium: [greatball,'Medio'], hard: [ultraball,'Difícil'] };
            const [icon, text] = labels[this.difficulty] ?? ['',''];
            return icon + text;
        },

        async init() { await this.loadQuestions(); },

        async loadQuestions() {
            this.phase = 'loading'; this.errorMsg = '';
            try {
                const res = await fetch('/api/game/questions', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ difficulty: this.difficulty, max_generation: this.maxGen, question_count: this.questionCount }),
                });
                const data = await res.json();
                const qs = data.questions ?? (Array.isArray(data) ? data : null);
                if (!qs || qs.length === 0) { this.errorMsg = data.error || 'Sin preguntas'; this.phase = 'error'; return; }
                this.questions = qs;
                this.gameToken = data.token ?? '';
                this.gameStartTime = Date.now();
                this.imgLoaded = false;
                this.phase = 'playing';
                this.startTimer();
            } catch(e) { this.errorMsg = 'Error de conexión'; this.phase = 'error'; }
        },

        async retryLoad() { await this.loadQuestions(); },

        startTimer() {
            clearInterval(this.timerInterval);
            this.timerStart = Date.now();
            this.timeLeft = this.timePerQuestion;
            this.timerInterval = setInterval(() => {
                const elapsed = (Date.now() - this.timerStart) / 1000;
                this.timeLeft = Math.max(0, this.timePerQuestion - elapsed);
                if (this.timeLeft <= 3 && this.timeLeft > 0 && Math.ceil(this.timeLeft) !== Math.ceil(this.timeLeft + .1)) playSound('tick');
                if (this.timeLeft <= 0) { clearInterval(this.timerInterval); this.handleTimeout(); }
            }, 50);
        },

        selectAnswer(option) {
            if (this.selectedAnswer !== null) return;
            clearInterval(this.timerInterval);
            this.selectedAnswer = option;
            this.revealed = true;
            const ok = option === this.current.answer;
            if (ok) {
                this.streak++;
                if (this.streak > this.maxStreak) this.maxStreak = this.streak;
                const tb = Math.floor(this.timeLeft * 10), sb = Math.max(0, this.streak - 1) * 25;
                const base = 100 + tb + sb;
                const gained = Math.round(base * this.genMultiplier);
                this.score += gained; this.correctCount++;
                this.phase = 'correct';
                this.triggerScoreFlash();
                this.pointsPopup = { show: true, amount: gained, streakBonus: sb, genMult: this.genMultiplier };
                setTimeout(() => { this.pointsPopup.show = false; }, 950);
                playSound('correct');
            } else {
                this.streak = 0; this.phase = 'wrong';
                playSound('wrong');
            }
            setTimeout(() => this.nextQuestion(), 1600);
        },

        handleTimeout() {
            if (this.selectedAnswer !== null) return;
            this.selectedAnswer = '__timeout__'; this.streak = 0;
            this.revealed = true; this.phase = 'wrong';
            playSound('timeout');
            setTimeout(() => this.nextQuestion(), 1600);
        },

        nextQuestion() {
            if (this.currentIndex >= this.questions.length - 1) { this.finishGame(); return; }
            this.imgLoaded = false;
            this.$nextTick(() => {
                this.currentIndex++;
                this.selectedAnswer = null;
                this.revealed = false;
                this.phase = 'playing';
                this.startTimer();
            });
        },

        async finishGame() {
            clearInterval(this.timerInterval);
            this.totalTime = Math.floor((Date.now() - this.gameStartTime) / 1000);
            this.phase = 'finished';
            playSound('finish');
            try {
                const res = await fetch('/api/game/score', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ player_name:this.playerName, score:this.score, correct_answers:this.correctCount, total_questions:this.questions.length, time_seconds:this.totalTime, difficulty:this.difficulty, max_generation:this.maxGen, max_streak:this.maxStreak, token:this.gameToken }),
                });
                const data = await res.json();
                this.rank = data.rank;
            } catch(e) {}
        },

        restartGame() {
            const p = new URLSearchParams({ player:this.playerName, difficulty:this.difficulty, max_generation:this.maxGen, question_count:this.questionCount });
            window.location.href = `/game?${p}`;
        },

        getOptionClass(option) {
            if (this.selectedAnswer === null) return 'opt-idle';
            if (option === this.current.answer) return 'opt-correct';
            if (option === this.selectedAnswer) return 'opt-wrong';
            return 'opt-wrong';
        },

        triggerScoreFlash() {
            this.scoreFlash = true;
            setTimeout(() => { this.scoreFlash = false; }, 500);
        },

        formatTime(s) {
            return `${Math.floor(s/60).toString().padStart(2,'0')}:${(s%60).toString().padStart(2,'0')}`;
        },
    };
}
</script>
@endsection
