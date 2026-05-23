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
            @php
                $ballPoke = '<svg viewBox="0 0 18 18" width="16" height="16"><path d="M9 1C4.582 1 1 4.582 1 9s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8z" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><path d="M1 9a8 8 0 0 1 16 0" fill="#ee1515"/><path d="M1 9h16" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="2.8" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="1.4" fill="#ccc" stroke="#1a1a2e" stroke-width="0.5"/></svg>';
                $ballGreat = '<svg viewBox="0 0 18 18" width="16" height="16"><path d="M9 1C4.582 1 1 4.582 1 9s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8z" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><path d="M1 9a8 8 0 0 1 16 0" fill="#1565C0"/><path d="M7 9h4" transform="rotate(180 9 9)" fill="none" stroke="#C62828" stroke-width="1.8"/><path d="M1 9h16" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="2.8" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="1.4" fill="#ccc" stroke="#1a1a2e" stroke-width="0.5"/></svg>';
                $ballUltra = '<svg viewBox="0 0 18 18" width="16" height="16"><path d="M9 1C4.582 1 1 4.582 1 9s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8z" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><path d="M1 9a8 8 0 0 1 16 0" fill="#222"/><path d="M5 7.5h8M7 9h4M5 10.5h6" stroke="#FFD600" stroke-width="1.2" stroke-linecap="round"/><path d="M1 9h16" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="2.8" fill="#f5f5f5" stroke="#1a1a2e" stroke-width="0.8"/><circle cx="9" cy="9" r="1.4" fill="#ccc" stroke="#1a1a2e" stroke-width="0.5"/></svg>';
                $swordIcon = '<svg viewBox="0 0 18 18" width="16" height="16"><path d="M3 3l9 12M15 3L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M5 12h3M10 12h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M3 3h.01M15 3h.01M12 15h.01M6 15h.01" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>';
                $ballMap = ['easy' => $ballPoke, 'medium' => $ballGreat, 'hard' => $ballUltra, 'league' => $swordIcon];
                $filterLabels = ['all' => ['Todos'], 'easy' => ['Fácil', $ballPoke], 'medium' => ['Medio', $ballGreat], 'hard' => ['Difícil', $ballUltra], 'league' => ['Liga', $swordIcon]];
            @endphp
            @foreach($filterLabels as $val => $item)
                <a href="{{ route('ranking', ['difficulty' => $val, 'max_generation' => $maxGen, 'page' => null]) }}"
                   class="filter-btn {{ $difficulty === $val ? 'filter-btn--on' : '' }}{{ $val === 'league' && $difficulty === 'league' ? ' filter-btn--league-on' : '' }}"
                >
                    @if (count($item) > 1)<span class="filter-ball">{!! $item[1] !!}</span>@endif
                    <span>{{ $item[0] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Generation ─ "hasta Gen X" (oculto en Liga) --}}
        @if($difficulty !== 'league')
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
            <a href="{{ route('ranking', ['difficulty' => $difficulty, 'max_generation' => 'all', 'page' => null]) }}"
               class="filter-btn filter-btn--gen {{ $maxGen === 'all' ? 'filter-btn--on-gen' : '' }}"
            >
                <span class="gen-roman">Todas</span>
                <span class="gen-region-sm">1–1025</span>
            </a>

            @foreach($gens as $num => $g)
                <a href="{{ route('ranking', ['difficulty' => $difficulty, 'max_generation' => $num, 'page' => null]) }}"
                   class="filter-btn filter-btn--gen {{ $maxGen === $num ? 'filter-btn--on-gen' : '' }}"
                   title="{{ $g['region'] }}"
                >
                    <span class="gen-roman">Gen {{ $g['roman'] }}</span>
                    <span class="gen-region-sm">{{ $g['region'] }}</span>
                </a>
            @endforeach
        </div>
        @endif

    </div>

    {{-- ── EMPTY STATE ───────────────────────────────────────────────── --}}
    @if($scores->isEmpty())
    <div class="empty-state">
        <div style="font-size:56px;margin-bottom:16px;">
            @if($difficulty === 'league')
            ⚔️
            @else
            🎮
            @endif
        </div>
        <p style="font-family:var(--font-display);font-size:24px;font-weight:800;margin-bottom:6px;">
            @if($difficulty === 'league')
            Sin entrenadores aún
            @else
            Sin puntuaciones aún
            @endif
        </p>
        <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">
            @if($difficulty === 'league')
            Supera la Liga para aparecer aquí
            @else
            Sé el primero en aparecer aquí
            @endif
        </p>
        @if($difficulty === 'league')
            <a href="{{ route('home') }}" class="btn-yellow-sm">Jugar ahora</a>
        @else
            <a href="{{ route('home') }}" class="btn-yellow-sm">Jugar ahora</a>
        @endif
    </div>

    @else

    {{-- ── TOP 3 PODIUM ─────────────────────────────────────────── --}}
    @php
        $trophySvg = fn($color, $light, $dark) => '<svg viewBox="0 0 28 32" width="32" height="36" style="filter:drop-shadow(0 2px 6px rgba(0,0,0,.3))">'
            .'<path d="M5 9C1 9 1 14 5 14" fill="none" stroke="'.$color.'" stroke-width="2.5" stroke-linecap="round"/>'
            .'<path d="M23 9C27 9 27 14 23 14" fill="none" stroke="'.$color.'" stroke-width="2.5" stroke-linecap="round"/>'
            .'<path d="M4 6C4 1 24 1 24 6L23 14Q14 17 5 14Z" fill="'.$color.'" stroke="'.$dark.'" stroke-width="0.8"/>'
            .'<circle cx="14" cy="8" r="3.5" fill="#f5f5f5" stroke="'.$dark.'" stroke-width="0.5"/>'
            .'<path d="M10.5 8a3.5 3.5 0 0 1 7 0" fill="'.$dark.'" opacity="0.5"/>'
            .'<path d="M10.5 8h7" stroke="'.$dark.'" stroke-width="0.5"/>'
            .'<circle cx="14" cy="8" r="1" fill="#f5f5f5" stroke="'.$dark.'" stroke-width="0.4"/>'
            .'<rect x="11.5" y="15" width="5" height="6" fill="'.$color.'" stroke="'.$dark.'" stroke-width="0.8"/>'
            .'<rect x="8" y="21" width="12" height="3" rx="1" fill="'.$color.'" stroke="'.$dark.'" stroke-width="0.8"/>'
            .'<path d="M8 4a6 6 0 0 1 4-1" fill="none" stroke="'.$light.'" stroke-width="1.5" stroke-linecap="round" opacity="0.5"/>'
            .'</svg>';
        $trophyGold   = $trophySvg('#FFD700', '#FFF8DC', '#B8860B');
        $trophySilver = $trophySvg('#C0C0C0', '#F5F5F5', '#808080');
        $trophyBronze = $trophySvg('#CD7F32', '#F5DEB3', '#8B4513');
    @endphp

    @if($top3->count() >= 3)
    <div class="podium">

        {{-- 2nd --}}
        <div class="podium-slot podium-slot--2">
            <div class="podium-card">
                <div class="podium-medal">{!! $trophySilver !!}</div>
                <div class="podium-name" title="{{ $top3[1]->player_name }}">{{ $top3[1]->player_name }}</div>
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
                <div class="podium-medal">{!! $trophyGold !!}</div>
                <div class="podium-name" style="font-size:16px" title="{{ $top3[0]->player_name }}">{{ $top3[0]->player_name }}</div>
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
                <div class="podium-medal">{!! $trophyBronze !!}</div>
                <div class="podium-name" title="{{ $top3[2]->player_name }}">{{ $top3[2]->player_name }}</div>
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
                @php $globalOffset = ($scores->currentPage() - 1) * $scores->perPage(); @endphp
                @foreach($scores as $idx => $s)
                @php $globalRank = $globalOffset + $idx + 1; @endphp
                <tr class="{{ $globalRank === 1 ? 'tr--gold' : ($globalRank === 2 ? 'tr--silver' : ($globalRank === 3 ? 'tr--bronze' : '')) }}">
                    <td class="td-rank">
                        @if($globalRank === 1)<span class="rank-num rank-num--1">1</span>
                        @elseif($globalRank === 2)<span class="rank-num rank-num--2">2</span>
                        @elseif($globalRank === 3)<span class="rank-num rank-num--3">3</span>
                        @else<span class="rank-num">{{ $globalRank }}</span>
                        @endif
                    </td>
                    <td class="td-name" title="{{ $s->player_name }}">{{ $s->player_name }}</td>
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
                        <span class="diff-dot" title="{{ $s->difficulty }}">
                            {!! $ballMap[$s->difficulty] ?? $ballPoke !!}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── PAGINATION ─────────────────────────────────────────────── --}}
    @if($scores->hasPages())
    <div class="pagination-wrap">
        @php
            $current = $scores->currentPage();
            $last    = $scores->lastPage();
            $prev    = $current > 1 ? $current - 1 : null;
            $next    = $current < $last ? $current + 1 : null;
            $qs      = request()->except('page');
        @endphp
        <div class="pagination">
            @if($prev)
                <a href="{{ route('ranking', array_merge($qs, ['page' => $prev])) }}" class="page-link page-prev">‹</a>
            @else
                <span class="page-link page-disabled">‹</span>
            @endif
            @for($i = 1; $i <= $last; $i++)
                @if($i === $current)
                    <span class="page-link page-on">{{ $i }}</span>
                @else
                    <a href="{{ route('ranking', array_merge($qs, ['page' => $i])) }}" class="page-link">{{ $i }}</a>
                @endif
            @endfor
            @if($next)
                <a href="{{ route('ranking', array_merge($qs, ['page' => $next])) }}" class="page-link page-next">›</a>
            @else
                <span class="page-link page-disabled">›</span>
            @endif
        </div>
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
    display: inline-flex; align-items: center; gap: 6px;
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
.podium-medal { display:flex; align-items:center; justify-content:center; margin-bottom: 6px; filter: drop-shadow(0 2px 6px rgba(0,0,0,.3)); }
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
.diff-dot { display: inline-flex; align-items: center; vertical-align: middle; }

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

/* ── Pagination ──────────────────────────────────────────────────── */
.pagination-wrap { display: flex; justify-content: center; }
.pagination { display: flex; gap: 4px; }
.page-link {
    display: flex; align-items: center; justify-content: center;
    min-width: 36px; height: 36px; padding: 0 8px;
    font-family: var(--font-mono); font-size: 13px; font-weight: 700;
    color: var(--text-muted); background: var(--surface-2);
    border: 1px solid var(--border-mid); border-radius: 6px;
    text-decoration: none; transition: all .12s;
}
.page-link:hover { border-color: var(--yellow); color: var(--yellow); }
.page-on { background: var(--yellow); color: #06070d; border-color: var(--yellow); }
.page-disabled { opacity: .3; pointer-events: none; }
.page-prev, .page-next { font-size: 20px; line-height: 1; }

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

/* ── Stage badge ─────────────────────────────────────────────────── */
.stage-badge {
    font-family: var(--font-mono); font-size: 10px; font-weight: 700;
    padding: 3px 8px; border-radius: 4px; border: 1px solid;
    white-space: nowrap;
}

/* ── Hearts ──────────────────────────────────────────────────────── */
.hearts-display { font-size: 12px; display: inline-flex; gap: 2px; }
</style>
@endsection
