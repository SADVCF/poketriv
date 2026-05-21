@extends('layouts.app')

@section('title', 'PokéTrivia — Ranking')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-4xl font-black mb-2">
            <span class="bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text text-transparent">Ranking</span>
            Global
        </h1>
        <p class="text-white/40 text-sm">Los mejores entrenadores Pokémon</p>
    </div>

    {{-- Filtros --}}
    <div class="flex items-center justify-center gap-2 mb-8">
        @foreach(['all' => 'Todos', 'easy' => '😊 Fácil', 'medium' => '🔥 Medio', 'hard' => '💀 Difícil'] as $val => $label)
            <a
                href="{{ route('ranking', ['difficulty' => $val]) }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200
                    {{ $difficulty === $val
                        ? 'bg-indigo-600 text-white shadow-[0_0_15px_rgba(99,102,241,0.4)]'
                        : 'glass border border-white/10 text-white/50 hover:text-white hover:border-white/20' }}"
            >{{ $label }}</a>
        @endforeach
    </div>

    @if($scores->isEmpty())
        <div class="text-center py-20">
            <div class="text-5xl mb-4">🎮</div>
            <p class="text-white/40">Nadie ha jugado todavía. ¡Sé el primero!</p>
            <a href="{{ route('home') }}" class="inline-block mt-4 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 transition-colors text-sm font-semibold">
                Jugar ahora
            </a>
        </div>
    @else
        {{-- Top 3 Podio --}}
        @if($scores->count() >= 3)
        <div class="grid grid-cols-3 gap-3 mb-8">
            @php $top3 = $scores->take(3); @endphp

            {{-- 2nd --}}
            <div class="glass rounded-2xl p-4 text-center border border-white/10 mt-4">
                <div class="text-2xl mb-1">🥈</div>
                <div class="font-bold text-sm truncate">{{ $top3[1]->player_name }}</div>
                <div class="text-lg font-black text-white/80 tabular-nums">{{ number_format($top3[1]->score) }}</div>
                <div class="text-[10px] text-white/30 mt-1">{{ $top3[1]->correct_answers }}/{{ $top3[1]->total_questions }} aciertos</div>
            </div>

            {{-- 1st --}}
            <div class="glass rounded-2xl p-5 text-center border border-amber-500/30 shadow-[0_0_30px_rgba(245,158,11,0.15)] -mt-2">
                <div class="text-3xl mb-1">🏆</div>
                <div class="font-black text-sm truncate">{{ $top3[0]->player_name }}</div>
                <div class="text-2xl font-black bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text text-transparent tabular-nums">
                    {{ number_format($top3[0]->score) }}
                </div>
                <div class="text-[10px] text-white/30 mt-1">{{ $top3[0]->correct_answers }}/{{ $top3[0]->total_questions }} aciertos</div>
            </div>

            {{-- 3rd --}}
            <div class="glass rounded-2xl p-4 text-center border border-white/10 mt-4">
                <div class="text-2xl mb-1">🥉</div>
                <div class="font-bold text-sm truncate">{{ $top3[2]->player_name }}</div>
                <div class="text-lg font-black text-white/80 tabular-nums">{{ number_format($top3[2]->score) }}</div>
                <div class="text-[10px] text-white/30 mt-1">{{ $top3[2]->correct_answers }}/{{ $top3[2]->total_questions }} aciertos</div>
            </div>
        </div>
        @endif

        {{-- Tabla completa --}}
        <div class="glass rounded-2xl overflow-hidden border border-white/8">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/8">
                        <th class="px-4 py-3 text-left text-[10px] text-white/30 uppercase tracking-widest font-semibold">#</th>
                        <th class="px-4 py-3 text-left text-[10px] text-white/30 uppercase tracking-widest font-semibold">Jugador</th>
                        <th class="px-4 py-3 text-right text-[10px] text-white/30 uppercase tracking-widest font-semibold">Puntos</th>
                        <th class="px-4 py-3 text-center text-[10px] text-white/30 uppercase tracking-widest font-semibold hidden sm:table-cell">Aciertos</th>
                        <th class="px-4 py-3 text-center text-[10px] text-white/30 uppercase tracking-widest font-semibold hidden sm:table-cell">Tiempo</th>
                        <th class="px-4 py-3 text-center text-[10px] text-white/30 uppercase tracking-widest font-semibold">Modo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scores as $idx => $s)
                    <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors {{ $idx < 3 ? 'bg-white/[0.02]' : '' }}">
                        <td class="px-4 py-3.5 font-mono text-white/30 text-sm">
                            @if($idx === 0) <span class="text-amber-400">1</span>
                            @elseif($idx === 1) <span class="text-slate-300">2</span>
                            @elseif($idx === 2) <span class="text-amber-700">3</span>
                            @else {{ $idx + 1 }}
                            @endif
                        </td>
                        <td class="px-4 py-3.5 font-semibold text-white/90">{{ $s->player_name }}</td>
                        <td class="px-4 py-3.5 text-right font-black tabular-nums
                            {{ $idx === 0 ? 'text-amber-400' : ($idx < 3 ? 'text-white/80' : 'text-white/60') }}">
                            {{ number_format($s->score) }}
                        </td>
                        <td class="px-4 py-3.5 text-center hidden sm:table-cell">
                            <span class="text-emerald-400 font-semibold">{{ $s->correct_answers }}</span>
                            <span class="text-white/20">/{{ $s->total_questions }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-center text-white/40 hidden sm:table-cell font-mono text-xs">
                            @php
                                $m = floor($s->time_seconds / 60);
                                $sec = $s->time_seconds % 60;
                            @endphp
                            {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($sec, 2, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @php
                                $labels = ['easy' => ['😊','text-emerald-400/70'], 'medium' => ['🔥','text-amber-400/70'], 'hard' => ['💀','text-red-400/70']];
                                [$icon, $cls] = $labels[$s->difficulty] ?? ['?','text-white/30'];
                            @endphp
                            <span class="text-xs {{ $cls }}">{{ $icon }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="text-center mt-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 transition-all font-semibold text-sm shadow-[0_0_20px_rgba(99,102,241,0.3)] hover:shadow-[0_0_30px_rgba(99,102,241,0.5)]">
            ← Jugar ahora
        </a>
    </div>
</div>
@endsection
