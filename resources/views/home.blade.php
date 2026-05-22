@extends('layouts.app')
@section('title', 'PokéTrivia — ¿Quién es ese Pokémon?')

@section('content')
<div class="home-root" x-data="homeForm()">

    {{-- Decorative rings --}}
    <div class="deco-ring" style="width:700px;height:700px;top:-280px;right:-220px;border-width:1.5px;opacity:.045;"></div>
    <div class="deco-ring" style="width:380px;height:380px;bottom:-120px;left:-130px;border-width:1px;opacity:.035;"></div>
    <div class="deco-ring" style="width:180px;height:180px;top:38%;right:8%;border-width:1px;opacity:.06;"></div>

    <div class="home-card" style="animation:fade-up .4s ease-out">
        <div class="home-card-accent"></div>

        {{-- Title --}}
        <div class="home-title-area">
            <p class="home-eyebrow">¿QUIÉN ES ESE</p>
            <h1 class="home-headline">POKÉMON?</h1>
        </div>

        {{-- Name --}}
        <div class="form-field">
            <label class="form-label">ENTRENADOR</label>
            <input
                x-model="playerName" type="text" maxlength="20"
                placeholder="Tu nombre..."
                class="form-input"
                @keydown.enter="startGame()"
            >
        </div>

        {{-- Difficulty --}}
        <div class="form-field">
            <label class="form-label">DIFICULTAD</label>
            <div class="diff-row">
                <template x-for="d in difficulties" :key="d.value">
                    <button type="button" @click="difficulty = d.value"
                        :class="['diff-btn', difficulty === d.value ? 'diff-btn--on diff-btn--' + d.value : '']">
                        <span class="diff-icon" x-html="d.iconHtml"></span>
                        <span class="diff-name" x-text="d.label"></span>
                        <span class="diff-sub" x-text="d.sub"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Generation selector --}}
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

        {{-- Question count --}}
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

        {{-- Info strip --}}
        <div class="info-strip">
            <template x-if="difficulty === 'easy'">
                <div class="info-strip-inner">
                    <span class="chip chip--green">Hasta Gen <span x-text="maxGen"></span></span>
                    <span class="chip">4 opciones</span>
                    <span class="chip">12 seg</span>
                    <span class="chip">Imagen completa</span>
                </div>
            </template>
            <template x-if="difficulty === 'medium'">
                <div class="info-strip-inner">
                    <span class="chip chip--yellow">Hasta Gen <span x-text="maxGen"></span></span>
                    <span class="chip">4 opciones</span>
                    <span class="chip">8 seg</span>
                    <span class="chip">Imagen completa</span>
                </div>
            </template>
            <template x-if="difficulty === 'hard'">
                <div class="info-strip-inner">
                    <span class="chip chip--red">Hasta Gen <span x-text="maxGen"></span></span>
                    <span class="chip">6 opciones</span>
                    <span class="chip">6 seg</span>
                    <span class="chip chip--red">¡SILUETA!</span>
                </div>
            </template>
        </div>

        {{-- CTA --}}
        <button type="button" @click="startGame()" :disabled="!playerName.trim()" class="start-btn">
            EMPEZAR
        </button>
        <a href="{{ route('ranking') }}" class="ranking-cta">Ver ranking global →</a>
    </div>
</div>

<style>
.home-root {
    min-height: calc(100vh - 54px);
    display: flex; align-items: center; justify-content: center;
    padding: 28px 16px; position: relative; overflow: hidden;
}
.deco-ring {
    position: absolute; border-radius: 50%;
    border-style: solid; border-color: var(--yellow);
    pointer-events: none;
}

/* ── Card ──────────────────────────────────────────────────────────── */
.home-card {
    background: var(--surface);
    border: 1px solid var(--border-mid);
    border-radius: 16px;
    padding: 32px 28px 24px;
    width: 100%; max-width: 460px;
    position: relative;
}
.home-card-accent {
    position: absolute; top: 0; left: 40px; right: 40px; height: 2px;
    background: linear-gradient(90deg, transparent, var(--yellow) 30%, var(--yellow) 70%, transparent);
    border-radius: 0 0 4px 4px;
}

/* ── Title ─────────────────────────────────────────────────────────── */
.home-title-area { text-align: center; margin-bottom: 26px; }
.home-eyebrow {
    font-family: var(--font-display); font-weight: 700;
    font-size: 13px; letter-spacing: .25em; color: var(--text-muted);
}
.home-headline {
    font-family: var(--font-display); font-weight: 900;
    font-size: clamp(48px, 11vw, 64px); letter-spacing: .02em; line-height: 1;
    color: var(--yellow);
    text-shadow: 0 0 50px rgba(255,203,5,.35), 0 0 100px rgba(255,203,5,.12);
    margin-top: 2px;
}

/* ── Form fields ───────────────────────────────────────────────────── */
.form-field { margin-bottom: 18px; }
.form-label-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.form-label {
    display: block; margin-bottom: 8px;
    font-family: var(--font-mono); font-size: 10px; font-weight: 700;
    letter-spacing: .18em; color: var(--text-muted);
}
.form-input {
    width: 100%; background: transparent;
    border: none; border-bottom: 2px solid var(--border-mid);
    padding: 9px 0; outline: none;
    color: var(--text); font-family: var(--font-ui); font-size: 15px; font-weight: 500;
    transition: border-color .2s;
}
.form-input::placeholder { color: var(--text-faint); }
.form-input:focus { border-bottom-color: var(--yellow); }

/* ── Difficulty ────────────────────────────────────────────────────── */
.diff-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; }
.diff-btn {
    background: var(--bg); border: 2px solid var(--border);
    border-radius: 10px; padding: 12px 6px 10px;
    cursor: pointer; transition: all .15s;
    display: flex; flex-direction: column; align-items: center; gap: 3px;
}
.diff-btn:hover { border-color: var(--border-hi); background: var(--surface-2); }
.diff-btn--on   { background: var(--surface-2); }
.diff-btn--easy.diff-btn--on   { border-color: #2ed573; box-shadow: 0 0 10px rgba(46,213,115,.15); }
.diff-btn--medium.diff-btn--on { border-color: var(--yellow); box-shadow: 0 0 10px rgba(255,203,5,.18); }
.diff-btn--hard.diff-btn--on   { border-color: #ff4757; box-shadow: 0 0 10px rgba(255,71,87,.15); }
.diff-icon { display:flex; align-items:center; justify-content:center; line-height:1; }
.diff-btn--easy.diff-btn--on .diff-icon { color: #2ed573; }
.diff-btn--medium.diff-btn--on .diff-icon { color: var(--yellow); }
.diff-btn--hard.diff-btn--on .diff-icon { color: #ff4757; }
.diff-name { font-family: var(--font-display); font-weight: 800; font-size: 13px; letter-spacing: .04em; color: var(--text); }
.diff-sub  { font-family: var(--font-mono); font-size: 9px; color: var(--text-faint); }

/* ── Generation selector ───────────────────────────────────────────── */
.gen-info-badge {
    font-family: var(--font-mono); font-size: 10px; font-weight: 700;
    color: var(--yellow); letter-spacing: .05em;
}
.gen-row {
    display: grid; grid-template-columns: repeat(9, 1fr); gap: 4px;
}
.gen-btn {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 6px; padding: 6px 2px;
    cursor: pointer; transition: all .15s;
    display: flex; flex-direction: column; align-items: center; gap: 1px;
}
.gen-btn:hover { border-color: var(--border-hi); background: var(--surface-2); }
.gen-btn--reached { border-color: rgba(255,203,5,.2); background: rgba(255,203,5,.04); }
.gen-btn--on {
    background: rgba(255,203,5,.15) !important;
    border-color: var(--yellow) !important;
    box-shadow: 0 0 8px rgba(255,203,5,.2);
}
.gen-num {
    font-family: var(--font-display); font-weight: 800; font-size: 11px;
    letter-spacing: .02em; color: var(--text);
}
.gen-region {
    font-family: var(--font-mono); font-size: 7px; color: var(--text-faint);
    white-space: nowrap; overflow: hidden; width: 100%; text-align: center;
    text-overflow: ellipsis;
}
@media (max-width: 400px) { .gen-region { display: none; } }

/* ── Question count ────────────────────────────────────────────────── */
.qcount-row { display: flex; gap: 6px; }
.qcount-btn {
    flex: 1; padding: 8px 4px;
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 7px;
    font-family: var(--font-mono); font-size: 13px; font-weight: 700;
    color: var(--text-muted); cursor: pointer; transition: all .15s;
    text-align: center;
}
.qcount-btn:hover { border-color: var(--border-hi); color: var(--text); background: var(--surface-2); }
.qcount-btn--on {
    background: rgba(255,203,5,.15);
    border-color: var(--yellow);
    color: var(--yellow);
    box-shadow: 0 0 8px rgba(255,203,5,.18);
}

/* ── Info strip ────────────────────────────────────────────────────── */
.info-strip { margin-bottom: 18px; min-height: 24px; }
.info-strip-inner { display: flex; flex-wrap: wrap; gap: 5px; }
.chip {
    padding: 3px 9px; border-radius: 4px;
    background: var(--surface-2); border: 1px solid var(--border);
    font-family: var(--font-mono); font-size: 10px; font-weight: 600;
    color: var(--text-muted);
}
.chip--green  { background:rgba(46,213,115,.1); border-color:rgba(46,213,115,.3); color:#2ed573; }
.chip--yellow { background:rgba(255,203,5,.1); border-color:rgba(255,203,5,.3); color:var(--yellow); }
.chip--red    { background:rgba(255,71,87,.1); border-color:rgba(255,71,87,.3); color:#ff4757; }

/* ── CTA ───────────────────────────────────────────────────────────── */
.start-btn {
    width: 100%; padding: 15px;
    background: var(--yellow); color: #06070d;
    border: none; border-radius: 8px;
    font-family: var(--font-display); font-size: 23px; font-weight: 900;
    letter-spacing: .1em; cursor: pointer;
    box-shadow: 0 2px 0 rgba(0,0,0,.4), 0 4px 22px rgba(255,203,5,.3);
    transition: all .12s; margin-bottom: 12px;
}
.start-btn:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 4px 0 rgba(0,0,0,.4), 0 8px 30px rgba(255,203,5,.42); }
.start-btn:active:not(:disabled) { transform:translateY(1px); }
.start-btn:disabled { opacity:.25; cursor:not-allowed; }
.ranking-cta {
    display: block; text-align: center;
    font-family: var(--font-mono); font-size: 11px;
    color: var(--text-faint); text-decoration: none;
    letter-spacing: .05em; transition: color .15s;
}
.ranking-cta:hover { color: var(--text-muted); }
</style>

<script>
function homeForm() {
    return {
        playerName:    '',
        difficulty:    'medium',
        maxGen:        2,
        questionCount: 10,

        difficulties: [
            { value:'easy',   label:'FÁCIL',   sub:'12s · 4 opc',
              iconHtml:'<svg viewBox="0 0 18 18" width="18" height="18"><path d="M9 1C4.582 1 1 4.582 1 9s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8z" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><path d="M1 9a8 8 0 0 1 16 0" fill="#ee1515"/><path d="M1 9h16" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="2.8" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="1.4" fill="#ccc" stroke="#1a1a2e" stroke-width="0.5"/><path d="M6 5a2 2 0 0 1 1.5-.6" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="1.2" stroke-linecap="round"/></svg>' },
            { value:'medium', label:'MEDIO',   sub:'8s · 4 opc',
              iconHtml:'<svg viewBox="0 0 18 18" width="18" height="18"><path d="M9 1C4.582 1 1 4.582 1 9s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8z" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><path d="M1 9a8 8 0 0 1 16 0" fill="#1565C0"/><path d="M7 9h4" transform="rotate(180 9 9)" fill="none" stroke="#C62828" stroke-width="1.8"/><path d="M1 9h16" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="2.8" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="1.4" fill="#ccc" stroke="#1a1a2e" stroke-width="0.5"/><path d="M6 5a2 2 0 0 1 1.5-.6" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="1.2" stroke-linecap="round"/></svg>' },
            { value:'hard',   label:'DIFÍCIL', sub:'6s · 6 opc · 🌑',
              iconHtml:'<svg viewBox="0 0 18 18" width="18" height="18"><path d="M9 1C4.582 1 1 4.582 1 9s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8z" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><path d="M1 9a8 8 0 0 1 16 0" fill="#222"/><path d="M5 7.5h8M7 9h4M5 10.5h6" stroke="#FFD600" stroke-width="1.2" stroke-linecap="round"/><path d="M1 9h16" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="2.8" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="1.4" fill="#ccc" stroke="#1a1a2e" stroke-width="0.5"/><path d="M6 5a2 2 0 0 1 1.5-.6" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="1.2" stroke-linecap="round"/></svg>' },
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
            const prev = g > 1 ? counts[g - 1] : 0;
            const total = counts[g] || 0;
            const region = this.generations[g - 1]?.region ?? '';
            return `${region} · ${total} Pokémon`;
        },

        startGame() {
            if (!this.playerName.trim()) return;
            const p = new URLSearchParams({
                player:         this.playerName.trim(),
                difficulty:     this.difficulty,
                max_generation: this.maxGen,
                question_count: this.questionCount,
            });
            window.location.href = `/game?${p}`;
        }
    }
}
</script>
@endsection
