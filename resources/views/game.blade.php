@extends('layouts.app')

@section('title', 'PokéTrivia — Jugando')

@push('styles')
<style>
    .pokemon-card-glow {
        transition: box-shadow 0.4s ease, border-color 0.4s ease;
    }
    .silhouette {
        filter: brightness(0) contrast(1);
    }
    .silhouette-reveal {
        animation: reveal-glow 0.6s ease-out forwards;
    }
    .timer-ring {
        transition: stroke-dashoffset 0.1s linear;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }
    .answer-correct {
        background: rgba(34,197,94,0.15) !important;
        border-color: rgba(34,197,94,0.6) !important;
        color: #4ade80 !important;
    }
    .answer-wrong {
        background: rgba(239,68,68,0.1) !important;
        border-color: rgba(239,68,68,0.3) !important;
        color: rgba(255,255,255,0.3) !important;
    }
    .answer-reveal {
        background: rgba(34,197,94,0.08) !important;
        border-color: rgba(34,197,94,0.4) !important;
        color: rgba(74,222,128,0.7) !important;
    }
    .streak-badge {
        animation: pop 0.3s ease-out;
    }
</style>
@endpush

@section('content')
<div
    class="min-h-[calc(100vh-56px)] flex flex-col items-center justify-start px-4 py-6"
    x-data="pokeGame({
        playerName: @js($playerName),
        difficulty: @js($difficulty),
        timePerQuestion: {{ $timePerQuestion }},
        optionCount: {{ $optionCount }}
    })"
    x-init="init()"
>

    {{-- LOADING --}}
    <div x-show="phase === 'loading'" class="flex-1 flex flex-col items-center justify-center gap-4 py-20">
        <svg width="60" height="60" viewBox="0 0 100 100" class="pokeball-spin opacity-60">
            <circle cx="50" cy="50" r="46" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="8"/>
            <path d="M4 50 A46 46 0 0 1 96 50" fill="rgba(99,144,240,0.3)"/>
            <line x1="4" y1="50" x2="96" y2="50" stroke="rgba(255,255,255,0.2)" stroke-width="5"/>
            <circle cx="50" cy="50" r="12" fill="#0a0a1a" stroke="rgba(255,255,255,0.2)" stroke-width="4"/>
        </svg>
        <p class="text-white/40 text-sm">Cargando Pokémon...</p>
    </div>

    {{-- ERROR --}}
    <div x-show="phase === 'error'" class="flex-1 flex flex-col items-center justify-center gap-3 py-20 text-center">
        <div class="text-4xl">⚠️</div>
        <p class="text-white/60 font-semibold">Error cargando las preguntas</p>
        <p class="text-white/30 text-sm">Comprueba la consola del servidor</p>
        <a href="/" class="mt-2 px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 transition-colors text-sm font-semibold">Volver al inicio</a>
    </div>

    {{-- JUEGO --}}
    <div x-show="phase !== 'loading' && phase !== 'finished'" class="w-full max-w-lg slide-up">

        {{-- HUD top --}}
        <div class="flex items-center gap-3 mb-5">
            {{-- Progreso --}}
            <div class="flex-1">
                <div class="flex justify-between text-xs text-white/40 mb-1.5">
                    <span>Pregunta <span class="text-white/70 font-semibold" x-text="currentIndex + 1"></span> de 10</span>
                    <span x-show="streak >= 2" class="streak-badge text-amber-400 font-bold">
                        🔥 ×<span x-text="streak"></span>
                    </span>
                </div>
                <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                    <div
                        class="h-full bg-gradient-to-r from-indigo-500 to-indigo-400 rounded-full transition-all duration-500"
                        :style="`width: ${progress}%`"
                    ></div>
                </div>
            </div>

            {{-- Score --}}
            <div class="text-right flex-shrink-0">
                <div class="text-[10px] text-white/30 uppercase tracking-widest">Puntos</div>
                <div class="text-xl font-black tabular-nums" :class="scoreFlash ? 'score-flash' : ''" x-text="score.toLocaleString()"></div>
            </div>
        </div>

        {{-- Pokemon Card --}}
        <div
            class="pokemon-card-glow glass rounded-2xl p-6 mb-4 text-center relative overflow-hidden"
            :style="cardStyle"
        >
            {{-- Timer circular --}}
            <div class="absolute top-4 right-4">
                <svg width="44" height="44" viewBox="0 0 44 44">
                    <circle cx="22" cy="22" r="18" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="3"/>
                    <circle
                        cx="22" cy="22" r="18"
                        fill="none"
                        :stroke="timerColor"
                        stroke-width="3"
                        stroke-linecap="round"
                        :stroke-dasharray="113"
                        :stroke-dashoffset="timerDash"
                        class="timer-ring"
                        style="transition: stroke-dashoffset 0.1s linear, stroke 0.3s ease;"
                    />
                    <text x="22" y="27" text-anchor="middle" font-size="11" font-weight="700" :fill="timerColor" x-text="Math.ceil(timeLeft)"></text>
                </svg>
            </div>

            {{-- Tipo badges --}}
            <div class="flex justify-center gap-1.5 mb-4 h-5" x-show="revealed || difficulty !== 'hard'">
                <template x-for="type in (current ? current.types : [])" :key="type">
                    <span class="type-badge" :class="`type-${type}`" x-text="type"></span>
                </template>
            </div>
            <div class="h-5 mb-4" x-show="!revealed && difficulty === 'hard'"></div>

            {{-- Imagen Pokémon --}}
            <div class="relative inline-block">
                <img
                    x-show="current"
                    :src="current ? current.artwork_url : ''"
                    :alt="revealed ? current.answer : '???'"
                    :class="[
                        'w-44 h-44 object-contain mx-auto',
                        difficulty === 'hard' && !revealed ? 'silhouette' : '',
                        difficulty === 'hard' && revealed ? 'silhouette-reveal' : '',
                    ]"
                    style="image-rendering: auto;"
                    @@error="$el.src = 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/0.png'"
                >
            </div>

            {{-- Nombre o interrogantes --}}
            <div class="mt-3 h-7 flex items-center justify-center">
                <span
                    x-show="revealed"
                    class="text-lg font-black tracking-wide slide-up"
                    x-text="current ? current.answer : ''"
                ></span>
                <span x-show="!revealed" class="text-white/20 text-lg font-black tracking-[0.3em]">???</span>
            </div>
        </div>

        {{-- Opciones --}}
        <div
            class="grid gap-2"
            :class="optionCount === 6 ? 'grid-cols-2' : 'grid-cols-2'"
        >
            <template x-for="(option, idx) in (current ? current.options : [])" :key="idx">
                <button
                    @click="selectAnswer(option)"
                    :disabled="selectedAnswer !== null"
                    :class="getOptionClass(option)"
                    class="answer-btn glass border rounded-xl px-3 py-3 text-sm font-semibold text-left transition-all duration-200 disabled:cursor-default"
                >
                    <span class="text-white/20 text-xs mr-2 font-mono" x-text="String.fromCharCode(65 + idx)"></span>
                    <span x-text="option"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- PANTALLA FINAL --}}
    <div x-show="phase === 'finished'" class="w-full max-w-lg slide-up py-6">

        {{-- Título resultado --}}
        <div class="text-center mb-8">
            <div class="text-5xl mb-3" x-text="resultEmoji"></div>
            <h2 class="text-3xl font-black mb-1" x-text="resultTitle"></h2>
            <p class="text-white/40 text-sm" x-text="playerName"></p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-3 mb-5">
            <div class="glass rounded-2xl p-5 text-center">
                <div class="text-3xl font-black bg-gradient-to-r from-indigo-400 to-pink-400 bg-clip-text text-transparent" x-text="score.toLocaleString()"></div>
                <div class="text-xs text-white/40 mt-1 uppercase tracking-widest">Puntuación</div>
            </div>
            <div class="glass rounded-2xl p-5 text-center">
                <div class="text-3xl font-black text-emerald-400">
                    <span x-text="correctCount"></span><span class="text-white/20">/10</span>
                </div>
                <div class="text-xs text-white/40 mt-1 uppercase tracking-widest">Aciertos</div>
            </div>
            <div class="glass rounded-2xl p-5 text-center">
                <div class="text-3xl font-black text-amber-400" x-text="formatTime(totalTime)"></div>
                <div class="text-xs text-white/40 mt-1 uppercase tracking-widest">Tiempo</div>
            </div>
            <div class="glass rounded-2xl p-5 text-center">
                <div class="text-3xl font-black text-purple-400">
                    #<span x-text="rank ?? '—'"></span>
                </div>
                <div class="text-xs text-white/40 mt-1 uppercase tracking-widest">Posición</div>
            </div>
        </div>

        {{-- Difficulty badge --}}
        <div class="text-center mb-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs text-white/40">
                <span x-text="difficulty === 'easy' ? '😊 Fácil' : difficulty === 'medium' ? '🔥 Medio' : '💀 Difícil'"></span>
            </span>
        </div>

        {{-- Acciones --}}
        <div class="space-y-2">
            <button
                @click="restartGame()"
                class="w-full py-3.5 rounded-xl font-bold text-sm bg-gradient-to-r from-indigo-600 to-indigo-500
                       hover:from-indigo-500 hover:to-indigo-400 transition-all
                       shadow-[0_0_20px_rgba(99,102,241,0.3)] hover:shadow-[0_0_30px_rgba(99,102,241,0.5)]"
            >
                Jugar de nuevo
            </button>
            <a
                href="/ranking"
                class="block w-full py-3.5 rounded-xl font-bold text-sm text-center glass border border-white/10
                       hover:border-white/20 hover:bg-white/5 transition-all text-white/70 hover:text-white"
            >
                Ver ranking completo →
            </a>
        </div>
    </div>

</div>

<script>
const TYPE_COLORS = {
    fire:     { color: '#FF9C54', glow: 'rgba(255,156,84,0.25)' },
    water:    { color: '#6390F0', glow: 'rgba(99,144,240,0.25)' },
    grass:    { color: '#7AC74C', glow: 'rgba(122,199,76,0.25)' },
    electric: { color: '#F7D02C', glow: 'rgba(247,208,44,0.25)' },
    psychic:  { color: '#F95587', glow: 'rgba(249,85,135,0.25)' },
    ice:      { color: '#96D9D6', glow: 'rgba(150,217,214,0.25)' },
    dragon:   { color: '#6F35FC', glow: 'rgba(111,53,252,0.25)' },
    dark:     { color: '#9e8878', glow: 'rgba(112,87,70,0.25)' },
    fairy:    { color: '#D685AD', glow: 'rgba(214,133,173,0.25)' },
    normal:   { color: '#A8A77A', glow: 'rgba(168,167,122,0.2)' },
    fighting: { color: '#C22E28', glow: 'rgba(194,46,40,0.25)' },
    flying:   { color: '#A98FF3', glow: 'rgba(169,143,243,0.25)' },
    poison:   { color: '#A33EA1', glow: 'rgba(163,62,161,0.25)' },
    ground:   { color: '#E2BF65', glow: 'rgba(226,191,101,0.25)' },
    rock:     { color: '#B6A136', glow: 'rgba(182,161,54,0.25)' },
    bug:      { color: '#A6B91A', glow: 'rgba(166,185,26,0.25)' },
    ghost:    { color: '#735797', glow: 'rgba(115,87,151,0.25)' },
    steel:    { color: '#B7B7CE', glow: 'rgba(183,183,206,0.2)' },
};

function pokeGame({ playerName, difficulty, timePerQuestion, optionCount }) {
    return {
        playerName,
        difficulty,
        timePerQuestion,
        optionCount,

        phase: 'loading',
        questions: [],
        currentIndex: 0,
        selectedAnswer: null,
        score: 0,
        streak: 0,
        correctCount: 0,
        revealed: false,
        scoreFlash: false,
        rank: null,

        timeLeft: timePerQuestion,
        timerInterval: null,

        gameStartTime: null,
        questionStartTime: null,
        totalTime: 0,

        get current() {
            return this.questions[this.currentIndex] ?? null;
        },
        get progress() {
            return ((this.currentIndex) / 10) * 100;
        },
        get cardStyle() {
            if (!this.current) return '';
            const type = this.current.types[0];
            const c = TYPE_COLORS[type] ?? TYPE_COLORS.normal;
            return `box-shadow: 0 0 40px ${c.glow}, inset 0 0 40px ${c.glow}; border-color: ${c.color}30;`;
        },
        get timerColor() {
            const pct = this.timeLeft / this.timePerQuestion;
            if (pct > 0.5) return '#6390F0';
            if (pct > 0.25) return '#F7D02C';
            return '#F95587';
        },
        get timerDash() {
            const circumference = 113;
            const pct = Math.max(0, this.timeLeft / this.timePerQuestion);
            return circumference * (1 - pct);
        },
        get resultEmoji() {
            const pct = this.correctCount / 10;
            if (pct >= 0.9) return '🏆';
            if (pct >= 0.7) return '⭐';
            if (pct >= 0.5) return '👍';
            return '💀';
        },
        get resultTitle() {
            const pct = this.correctCount / 10;
            if (pct >= 0.9) return '¡Maestro Pokémon!';
            if (pct >= 0.7) return '¡Muy bien!';
            if (pct >= 0.5) return 'Nada mal';
            return 'Necesitas entrenar más';
        },

        async init() {
            await this.loadQuestions();
        },

        async loadQuestions() {
            this.phase = 'loading';
            try {
                const res = await fetch('/api/game/questions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ difficulty }),
                });
                const data = await res.json();
                if (!Array.isArray(data) || data.length === 0) {
                    this.phase = 'error';
                    return;
                }
                this.questions = data;
                this.gameStartTime = Date.now();
                this.phase = 'playing';
                this.startTimer();
            } catch (e) {
                console.error(e);
                this.phase = 'error';
            }
        },

        startTimer() {
            clearInterval(this.timerInterval);
            this.timeLeft = this.timePerQuestion;
            this.questionStartTime = Date.now();
            this.timerInterval = setInterval(() => {
                this.timeLeft = Math.max(0, this.timeLeft - 0.1);
                if (this.timeLeft <= 0) {
                    clearInterval(this.timerInterval);
                    this.handleTimeout();
                }
            }, 100);
        },

        selectAnswer(option) {
            if (this.selectedAnswer !== null) return;
            clearInterval(this.timerInterval);
            this.selectedAnswer = option;
            this.revealed = true;

            const isCorrect = option === this.current.answer;

            if (isCorrect) {
                this.streak++;
                const timeBonus = Math.floor(this.timeLeft * 10);
                const streakBonus = Math.max(0, this.streak - 1) * 25;
                const gained = 100 + timeBonus + streakBonus;
                this.score += gained;
                this.correctCount++;
                this.phase = 'correct';
                this.triggerScoreFlash();
            } else {
                this.streak = 0;
                this.phase = 'wrong';
            }

            setTimeout(() => this.nextQuestion(), 1600);
        },

        handleTimeout() {
            if (this.selectedAnswer !== null) return;
            this.selectedAnswer = '__timeout__';
            this.streak = 0;
            this.revealed = true;
            this.phase = 'wrong';
            setTimeout(() => this.nextQuestion(), 1600);
        },

        nextQuestion() {
            if (this.currentIndex >= this.questions.length - 1) {
                this.finishGame();
                return;
            }
            this.currentIndex++;
            this.selectedAnswer = null;
            this.revealed = false;
            this.phase = 'playing';
            this.startTimer();
        },

        async finishGame() {
            clearInterval(this.timerInterval);
            this.totalTime = Math.floor((Date.now() - this.gameStartTime) / 1000);
            this.phase = 'finished';

            try {
                const res = await fetch('/api/game/score', {
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
                        time_seconds:    this.totalTime,
                        difficulty:      this.difficulty,
                    }),
                });
                const data = await res.json();
                this.rank = data.rank;
            } catch (e) {
                console.error(e);
            }
        },

        restartGame() {
            const params = new URLSearchParams({ player: this.playerName, difficulty: this.difficulty });
            window.location.href = `/game?${params}`;
        },

        getOptionClass(option) {
            if (this.selectedAnswer === null) return 'border-white/10 text-white hover:border-white/25';
            if (option === this.current.answer) return 'answer-correct border';
            if (option === this.selectedAnswer) return 'answer-wrong border';
            return 'answer-wrong border opacity-40';
        },

        triggerScoreFlash() {
            this.scoreFlash = true;
            setTimeout(() => { this.scoreFlash = false; }, 500);
        },

        formatTime(seconds) {
            const m = Math.floor(seconds / 60).toString().padStart(2, '0');
            const s = (seconds % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        },
    };
}
</script>
@endsection
