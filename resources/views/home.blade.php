@extends('layouts.app')

@section('title', 'PokéTrivia — ¿Quién es ese Pokémon?')

@section('content')
<div class="min-h-[calc(100vh-56px)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md" x-data="homeForm()">

        {{-- Header --}}
        <div class="text-center mb-10">
            {{-- Pokeball grande decorativa --}}
            <div class="relative inline-block mb-6">
                <svg width="90" height="90" viewBox="0 0 100 100" class="drop-shadow-[0_0_30px_rgba(249,85,135,0.4)]">
                    <circle cx="50" cy="50" r="46" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="6"/>
                    <path d="M4 50 A46 46 0 0 1 96 50" fill="rgba(249,85,135,0.15)" stroke="rgba(249,85,135,0.4)" stroke-width="2"/>
                    <path d="M4 50 A46 46 0 0 0 96 50" fill="rgba(99,144,240,0.1)"/>
                    <line x1="4" y1="50" x2="96" y2="50" stroke="rgba(255,255,255,0.15)" stroke-width="3"/>
                    <circle cx="50" cy="50" r="13" fill="#0a0a1a" stroke="rgba(255,255,255,0.2)" stroke-width="4"/>
                    <circle cx="50" cy="50" r="7" fill="rgba(255,255,255,0.15)"/>
                </svg>
            </div>

            <h1 class="text-4xl font-black tracking-tight mb-2">
                ¿Quién es ese<br>
                <span class="bg-gradient-to-r from-indigo-400 to-pink-400 bg-clip-text text-transparent">Pokémon?</span>
            </h1>
            <p class="text-white/40 text-sm">251 Pokémon · Generaciones I y II</p>
        </div>

        {{-- Form --}}
        <div class="glass rounded-2xl p-6 space-y-5">

            {{-- Nombre --}}
            <div>
                <label class="block text-xs font-semibold text-white/50 uppercase tracking-widest mb-2">Tu nombre</label>
                <input
                    x-model="playerName"
                    type="text"
                    maxlength="20"
                    placeholder="Ash Ketchum..."
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/60 focus:ring-1 focus:ring-indigo-500/30 transition-all text-sm"
                >
            </div>

            {{-- Dificultad --}}
            <div>
                <label class="block text-xs font-semibold text-white/50 uppercase tracking-widest mb-3">Dificultad</label>
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="d in difficulties" :key="d.value">
                        <button
                            @click="difficulty = d.value"
                            :class="difficulty === d.value
                                ? 'border-opacity-100 bg-white/10 ' + d.activeClass
                                : 'border-white/10 text-white/50 hover:border-white/20 hover:text-white/80'"
                            class="relative border rounded-xl p-3 text-center transition-all duration-200 group"
                        >
                            <div class="text-lg mb-0.5" x-text="d.icon"></div>
                            <div class="text-xs font-bold" x-text="d.label"></div>
                            <div class="text-[10px] opacity-60 mt-0.5" x-text="d.hint"></div>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Info por dificultad --}}
            <div class="rounded-xl bg-white/[0.03] border border-white/5 p-3 text-xs text-white/40 space-y-1">
                <template x-if="difficulty === 'easy'">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2"><span class="text-emerald-400">●</span> Solo Gen I (151 Pokémon)</div>
                        <div class="flex items-center gap-2"><span class="text-emerald-400">●</span> 4 opciones · 12 segundos por pregunta</div>
                        <div class="flex items-center gap-2"><span class="text-emerald-400">●</span> Imagen a color</div>
                    </div>
                </template>
                <template x-if="difficulty === 'medium'">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2"><span class="text-amber-400">●</span> Gen I + II (251 Pokémon)</div>
                        <div class="flex items-center gap-2"><span class="text-amber-400">●</span> 4 opciones · 8 segundos por pregunta</div>
                        <div class="flex items-center gap-2"><span class="text-amber-400">●</span> Imagen a color</div>
                    </div>
                </template>
                <template x-if="difficulty === 'hard'">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2"><span class="text-red-400">●</span> Gen I + II (251 Pokémon)</div>
                        <div class="flex items-center gap-2"><span class="text-red-400">●</span> 6 opciones · 6 segundos por pregunta</div>
                        <div class="flex items-center gap-2"><span class="text-red-400">●</span> ¡Silueta negra hasta responder!</div>
                    </div>
                </template>
            </div>

            {{-- Botón jugar --}}
            <button
                @click="startGame()"
                :disabled="!playerName.trim()"
                class="w-full py-3.5 rounded-xl font-bold text-sm tracking-wide transition-all duration-200
                       bg-gradient-to-r from-indigo-600 to-indigo-500
                       hover:from-indigo-500 hover:to-indigo-400
                       disabled:opacity-30 disabled:cursor-not-allowed
                       shadow-[0_0_20px_rgba(99,102,241,0.3)] hover:shadow-[0_0_30px_rgba(99,102,241,0.5)]"
            >
                ¡Empezar!
            </button>
        </div>

        {{-- Enlace ranking --}}
        <div class="text-center mt-5">
            <a href="{{ route('ranking') }}" class="text-sm text-white/30 hover:text-white/60 transition-colors">
                Ver ranking global →
            </a>
        </div>
    </div>
</div>

<script>
function homeForm() {
    return {
        playerName: '',
        difficulty: 'medium',
        difficulties: [
            { value: 'easy',   label: 'Fácil',   icon: '😊', hint: 'Gen I',      activeClass: 'border-emerald-500/60 text-emerald-400' },
            { value: 'medium', label: 'Medio',   icon: '🔥', hint: 'Gen I+II',   activeClass: 'border-amber-500/60 text-amber-400' },
            { value: 'hard',   label: 'Difícil', icon: '💀', hint: 'Silueta',    activeClass: 'border-red-500/60 text-red-400' },
        ],
        startGame() {
            if (!this.playerName.trim()) return;
            const params = new URLSearchParams({
                player: this.playerName.trim(),
                difficulty: this.difficulty
            });
            window.location.href = `/game?${params.toString()}`;
        }
    }
}
</script>
@endsection
