@extends('layouts.app')
@section('title', 'PokéTrivia — Ranking')

@section('content')
<div class="rank-root">

    {{-- Header --}}
    <div class="rank-header">
        <p class="rank-eyebrow">HALL OF FAME</p>
        <h1 class="rank-headline">RANKING</h1>
        <p class="rank-sub">Los mejores entrenadores Pokémon del mundo</p>
    </div>

    {{-- Filters ─────────────────────────────────────────────────────── --}}
    <div class="filters-section">

        {{-- Difficulty --}}
        <div class="filter-row">
            @foreach(['all' => 'Todos', 'easy' => '😊 Fácil', 'medium' => '🔥 Medio', 'hard' => '💀 Difícil'] as $val => $label)
                <a href="{{ route('ranking', ['difficulty' => $val, 'max_generation' => $maxGen]) }}"
                   class="filter-btn {{ $difficulty === $val ? 'filter-btn--on' : '' }}"
                >{{ $label }}</a>
            @endforeach
        </div>

        {{-- Generation ─ "hasta Gen X" --}}
        <div class="filter-row filter-row--gen">
            @php
                $gens = [
                    1 => ['roman'=>'I',    'region'=>'Kanto'],
                    2 => ['roman'=>'II',   'region'=>'Johto'],
                    3 => ['roman'=>'III',  'region'=>'Hoenn'],
                    4 => ['roman'=>'IV',   'region'=>'Sinnoh'],
                    5 => ['roman'=>'V',    'region'=>'Unova'],
                    6 => ['roman'=>'VI',   'region'=>'Kalos'],
                    7 => ['roman'=>'VII',  'region'=>'Alola'],
                    8 => ['roman'=>'VIII', 'region'=>'Galar'],
                    9 => ['roman'=>'IX',   'region'=>'Paldea'],
                ];
            @endphp

            {{-- Todas las generaciones --}}
            <a href="{{ route('ranking', ['difficulty' => $difficulty, 'max_generation' => 'all']) }}"
               class="filter-btn filter-btn--gen {{ $maxGen === 'all' ? 'filter-btn--on-gen' : '' }}"
            >
                <span class="gen-roman">Todas</span>
                <span class="gen-region-sm">1–1025</span>
            </a>

            @foreach($gens as $num => $g)
                <a href="{{ route('ranking', ['difficulty' => $difficulty, 'max_generation' => $num]) }}"
                   class="filter-btn filter-btn--gen {{ $maxGen === $num ? 'filter-btn--on-gen' : '' }}"
                   title="{{ $g['region'] }}"
                >
                    <span class="gen-roman">Gen {{ $g['roman'] }}</span>
                    <span class="gen-region-sm">{{ $g['region'] }}</span>
                </a>
            @endforeach
        </div>

    </div>

    {{-- Empty state --}}
    @if($scores->isEmpty())
    <div class="empty-state">
        <div style="font-size:56px;margin-bottom:16px;">🎮</div>
        <p style="font-family:var(--font-display);font-size:24px;font-weight:800;margin-bottom:6px;">Sin puntuaciones aún</p>
        <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">Sé el primero en aparecer aquí</p>
        <a href="{{ route('home') }}" class="btn-yellow-sm">Jugar ahora</a>
    </div>

    @else

    {{-- ── TOP 3 PODIUM ─────────────────────────────────────────── --}}
    @if($scores->count() >= 3)
    @php $top3 = $scores->take(3); @endphp
    <div class="podium">

        {{-- 2nd --}}
        <div class="podium-slot podium-slot--2">
            <div class="podium-card">
                <div class="podium-medal">🥈</div>
                <div class="podium-name">{{ $top3[1]->player_name }}</div>
                <div class="podium-score">{{ number_format($top3[1]->score) }}</div>
                <div class="podium-sub">{{ $top3[1]->correct_answers }}/{{ $top3[1]->total_questions }} aciertos</div>
                @if(($top3[1]->max_streak ?? 0) >= 3)
                    <div class="podium-streak">🔥 ×{{ $top3[1]->max_streak }}</div>
                @endif
            </div>
            <div class="podium-block podium-block--2">2</div>
        </div>

        {{-- 1st --}}
        <div class="podium-slot podium-slot--1">
            <div class="podium-card podium-card--1">
                <div class="podium-medal" style="font-size:32px">🏆</div>
                <div class="podium-name" style="font-size:16px">{{ $top3[0]->player_name }}</div>
                <div class="podium-score podium-score--1">{{ number_format($top3[0]->score) }}</div>
                <div class="podium-sub">{{ $top3[0]->correct_answers }}/{{ $top3[0]->total_questions }} aciertos</div>
                @if(($top3[0]->max_streak ?? 0) >= 3)
                    <div class="podium-streak" style="color:var(--yellow)">🔥 ×{{ $top3[0]->max_streak }}</div>
                @endif
            </div>
            <div class="podium-block podium-block--1">1</div>
        </div>

        {{-- 3rd --}}
        <div class="podium-slot podium-slot--3">
            <div class="podium-card">
                <div class="podium-medal">🥉</div>
                <div class="podium-name">{{ $top3[2]->player_name }}</div>
                <div class="podium-score">{{ number_format($top3[2]->score) }}</div>
                <div class="podium-sub">{{ $top3[2]->correct_answers }}/{{ $top3[2]->total_questions }} aciertos</div>
                @if(($top3[2]->max_streak ?? 0) >= 3)
                    <div class="podium-streak">🔥 ×{{ $top3[2]->max_streak }}</div>
                @endif
            </div>
            <div class="podium-block podium-block--3">3</div>
        </div>
    </div>
    @endif

    {{-- ── TABLE ────────────────────────────────────────────────── --}}
    <div class="rank-table-wrap">
        <table class="rank-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Jugador</th>
                    <th class="text-right">Puntos</th>
                    <th class="text-center hide-sm">Aciertos</th>
                    <th class="text-center hide-sm">Tiempo</th>
                    <th class="text-center hide-sm">Racha</th>
                    <th class="text-center">Modo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scores as $idx => $s)
                <tr class="{{ $idx === 0 ? 'tr--gold' : ($idx === 1 ? 'tr--silver' : ($idx === 2 ? 'tr--bronze' : '')) }}">
                    <td class="td-rank">
                        @if($idx === 0)<span class="rank-num rank-num--1">1</span>
                        @elseif($idx === 1)<span class="rank-num rank-num--2">2</span>
                        @elseif($idx === 2)<span class="rank-num rank-num--3">3</span>
                        @else<span class="rank-num">{{ $idx + 1 }}</span>
                        @endif
                    </td>
                    <td class="td-name">{{ $s->player_name }}</td>
                    <td class="td-score {{ $idx === 0 ? 'td-score--1' : '' }}">{{ number_format($s->score) }}</td>
                    <td class="text-center hide-sm td-acc">
                        <span class="acc-num">{{ $s->correct_answers }}</span>
                        <span class="acc-total">/{{ $s->total_questions }}</span>
                    </td>
                    <td class="text-center hide-sm td-time">
                        @php $m=floor($s->time_seconds/60); $sec=$s->time_seconds%60; @endphp
                        {{ str_pad($m,2,'0',STR_PAD_LEFT) }}:{{ str_pad($sec,2,'0',STR_PAD_LEFT) }}
                    </td>
                    <td class="text-center hide-sm td-streak">
                        @if(($s->max_streak ?? 0) >= 3)
                            <span class="streak-pill">🔥 ×{{ $s->max_streak }}</span>
                        @else
                            <span style="color:var(--text-faint)">—</span>
                        @endif
                    </td>
                    <td class="text-center td-diff">
                        @php
                            $icons = ['easy' => '😊', 'medium' => '🔥', 'hard' => '💀'];
                            $labels = ['easy' => 'F', 'medium' => 'M', 'hard' => 'D'];
                        @endphp
                        <span class="diff-dot diff-dot--{{ $s->difficulty }}" title="{{ $s->difficulty }}">
                            {{ $icons[$s->difficulty] ?? '?' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="rank-cta-row">
        <a href="{{ route('home') }}" class="btn-yellow-sm">← Jugar ahora</a>
    </div>

</div>

<style>
.rank-root {
    max-width: 820px; margin: 0 auto;
    padding: 40px 16px 60px;
    display: flex; flex-direction: column; gap: 28px;
}

/* ── Header ─────────────────────────────────────────────────────── */
.rank-header { text-align: center; }
.rank-eyebrow {
    font-family: var(--font-mono); font-size: 11px; font-weight: 700;
    letter-spacing: .25em; color: var(--text-muted); margin-bottom: 4px;
}
.rank-headline {
    font-family: var(--font-display); font-size: clamp(52px,10vw,80px); font-weight: 900;
    letter-spacing: .04em; line-height: 1;
    color: var(--yellow);
    text-shadow: 0 0 60px rgba(255,203,5,.3);
    margin-bottom: 8px;
}
.rank-sub { font-size: 13px; color: var(--text-muted); }

/* ── Filters ─────────────────────────────────────────────────────── */
.filters-section { display: flex; flex-direction: column; gap: 8px; align-items: center; }
.filter-row { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; }
.filter-btn {
    padding: 7px 16px; border-radius: 6px;
    background: var(--surface-2); border: 1px solid var(--border-mid);
    font-family: var(--font-ui); font-size: 13px; font-weight: 600;
    color: var(--text-muted); text-decoration: none;
    transition: all .15s;
}
.filter-btn:hover { color: var(--text); border-color: var(--border-hi); }
.filter-btn--on {
    background: var(--yellow); color: #06070d;
    border-color: var(--yellow);
    box-shadow: 0 0 16px rgba(255,203,5,.3);
}

/* ── Podium ──────────────────────────────────────────────────────── */
.podium {
    display: grid; grid-template-columns: 1fr 1fr 1fr;
    gap: 8px; align-items: flex-end;
    max-width: 600px; margin: 0 auto; width: 100%;
}
.podium-slot { display: flex; flex-direction: column; align-items: center; }
.podium-card {
    background: var(--surface-2); border: 1px solid var(--border-mid);
    border-radius: 12px; padding: 16px 12px 12px;
    text-align: center; width: 100%; margin-bottom: 0;
}
.podium-card--1 {
    border-color: rgba(255,203,5,.4);
    box-shadow: 0 0 28px rgba(255,203,5,.15);
}
.podium-medal { font-size: 24px; margin-bottom: 6px; }
.podium-name {
    font-family: var(--font-ui); font-weight: 700; font-size: 13px;
    color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 4px;
}
.podium-score {
    font-family: var(--font-mono); font-size: 18px; font-weight: 700;
    color: var(--text-muted);
}
.podium-score--1 {
    font-size: 24px; color: var(--yellow);
    text-shadow: 0 0 16px rgba(255,203,5,.4);
}
.podium-sub  { font-size: 10px; color: var(--text-faint); margin-top: 3px; font-family: var(--font-mono); }
.podium-streak { font-size: 10px; color: rgba(255,203,5,.6); margin-top: 2px; font-family: var(--font-mono); }
.podium-block {
    width: 100%; padding: 8px 0;
    border-radius: 0 0 8px 8px;
    font-family: var(--font-display); font-weight: 900; font-size: 20px;
    text-align: center; color: rgba(255,255,255,.3);
    margin-top: 4px;
}
.podium-block--1 { background: rgba(255,203,5,.18); height: 52px; border-radius: 8px; }
.podium-block--2 { background: rgba(255,255,255,.06); height: 38px; border-radius: 8px; }
.podium-block--3 { background: rgba(255,255,255,.04); height: 28px; border-radius: 8px; }

/* ── Table ───────────────────────────────────────────────────────── */
.rank-table-wrap {
    background: var(--surface);
    border: 1px solid var(--border-mid);
    border-radius: 14px; overflow: hidden;
}
.rank-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.rank-table thead tr {
    border-bottom: 1px solid var(--border-mid);
}
.rank-table th {
    padding: 12px 16px;
    font-family: var(--font-mono); font-size: 9px; font-weight: 700;
    letter-spacing: .15em; color: var(--text-faint);
    text-align: left;
}
.rank-table tbody tr {
    border-bottom: 1px solid rgba(255,255,255,.04);
    transition: background .12s;
}
.rank-table tbody tr:last-child { border-bottom: none; }
.rank-table tbody tr:hover { background: rgba(255,255,255,.02); }
.tr--gold   { background: rgba(255,203,5,.04); }
.tr--silver { background: rgba(255,255,255,.02); }

.td-rank { padding: 14px 16px; width: 48px; }
.rank-num {
    font-family: var(--font-mono); font-weight: 700; font-size: 14px;
    color: var(--text-faint);
}
.rank-num--1 { color: var(--yellow); font-size: 16px; }
.rank-num--2 { color: #c0c0c0; }
.rank-num--3 { color: #cd7f32; }

.td-name {
    padding: 14px 16px; font-weight: 600; font-size: 14px; color: var(--text);
}
.td-score {
    padding: 14px 16px; text-align: right;
    font-family: var(--font-mono); font-weight: 700; font-size: 15px;
    color: var(--text-muted);
}
.td-score--1 { color: var(--yellow); }
.td-acc { padding: 14px 16px; }
.acc-num  { font-weight: 700; color: var(--green); font-family: var(--font-mono); }
.acc-total { font-family: var(--font-mono); font-size: 12px; color: var(--text-faint); }
.td-time  { padding: 14px 16px; font-family: var(--font-mono); font-size: 12px; color: var(--text-muted); }
.td-streak { padding: 14px 16px; }
.streak-pill {
    font-family: var(--font-mono); font-size: 11px; font-weight: 700;
    color: rgba(255,203,5,.7);
    background: rgba(255,203,5,.08); border: 1px solid rgba(255,203,5,.2);
    padding: 2px 8px; border-radius: 4px;
}
.td-diff { padding: 14px 16px; }

@media (max-width: 600px) { .hide-sm { display: none !important; } }
.text-right  { text-align: right; }
.text-center { text-align: center; }

/* ── Empty / CTA ─────────────────────────────────────────────────── */
.empty-state {
    text-align: center; padding: 60px 20px;
    background: var(--surface); border: 1px solid var(--border-mid);
    border-radius: 14px;
}
.btn-yellow-sm {
    display: inline-block; padding: 12px 28px;
    background: var(--yellow); color: #06070d;
    border: none; border-radius: 8px;
    font-family: var(--font-display); font-size: 18px; font-weight: 900;
    letter-spacing: .08em; cursor: pointer; text-decoration: none;
    box-shadow: 0 2px 0 rgba(0,0,0,.3), 0 4px 20px rgba(255,203,5,.3);
    transition: all .12s;
}
.btn-yellow-sm:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 0 rgba(0,0,0,.3), 0 8px 28px rgba(255,203,5,.4);
}
.rank-cta-row { text-align: center; }

/* ── Gen filter row ──────────────────────────────────────────────── */
.filter-row--gen { margin-top: 6px; }
.filter-btn--gen {
    padding: 5px 10px;
    display: flex; flex-direction: column; align-items: center; gap: 1px;
    min-width: 56px;
}
.gen-roman  { font-family:var(--font-display); font-weight:800; font-size:12px; letter-spacing:.03em; }
.gen-region-sm { font-family:var(--font-mono); font-size:8px; color:var(--text-faint); }
.filter-btn--on-gen {
    background: rgba(255,203,5,.15);
    border-color: var(--yellow);
    color: var(--yellow);
    box-shadow: 0 0 14px rgba(255,203,5,.25);
}
.filter-btn--on-gen .gen-region-sm { color:rgba(255,203,5,.6); }
</style>
@endsection
