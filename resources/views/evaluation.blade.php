@extends('layouts.app')

@section('title', 'CafeBlend — Evaluasi Sistem')

@section('content')
    <header class="mb-8">
        <a href="{{ route('search') }}" class="text-xs font-medium text-primary hover:text-primary-dark">&larr; Kembali ke Pencarian</a>
        <h1 class="font-serif text-4xl text-primary leading-tight mt-3">Evaluasi Sistem</h1>
        <p class="text-muted mt-2 text-sm">
            {{ $summary['queries'] }} query uji dibandingkan dengan ground truth ·
            Precision{{ '@'.$summary['k'] }}, Recall, dan Average Precision.
        </p>
    </header>

    {{-- Ringkasan metrik utama --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        @php
            $cards = [
                ['Precision@'.$summary['k'], $summary['avg_precision']],
                ['Recall', $summary['avg_recall']],
                ['MAP', $summary['map']],
            ];
        @endphp
        @foreach($cards as [$label, $val])
            <div class="bg-cream rounded-xl neu-raised p-6 text-center">
                <div class="text-[11px] uppercase tracking-wide text-muted/80">{{ $label }}</div>
                <div class="font-mono text-3xl font-medium text-primary mt-2">{{ number_format($val, 3) }}</div>
            </div>
        @endforeach
    </div>

    {{-- Tabel rincian per query --}}
    <div class="bg-cream rounded-xl neu-raised p-3 sm:p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-separate border-spacing-y-1">
                <thead class="text-muted/80 text-left text-[11px] uppercase tracking-wide">
                    <tr>
                        <th class="px-3 py-2 font-medium">#</th>
                        <th class="px-3 py-2 font-medium">Query</th>
                        <th class="px-3 py-2 font-medium text-center">Relevan</th>
                        <th class="px-3 py-2 font-medium text-center">Ditemukan</th>
                        <th class="px-3 py-2 font-medium text-center">P{{ '@'.$summary['k'] }}</th>
                        <th class="px-3 py-2 font-medium text-center">Recall</th>
                        <th class="px-3 py-2 font-medium text-center">AP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                        <tr class="bg-surface/60">
                            <td class="px-3 py-3 text-muted/70 font-mono rounded-l-lg">{{ $i + 1 }}</td>
                            <td class="px-3 py-3 font-medium text-coffee">{{ $row['query'] }}</td>
                            <td class="px-3 py-3 text-center text-muted font-mono">{{ $row['relevant_total'] }}</td>
                            <td class="px-3 py-3 text-center text-muted font-mono">{{ $row['retrieved'] }}</td>
                            <td class="px-3 py-3 text-center font-mono font-medium text-primary">{{ number_format($row['precision'], 3) }}</td>
                            <td class="px-3 py-3 text-center font-mono text-coffee">{{ number_format($row['recall'], 3) }}</td>
                            <td class="px-3 py-3 text-center font-mono text-coffee rounded-r-lg">{{ number_format($row['ap'], 3) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-muted/80 mt-5 leading-relaxed px-1">
        Ground truth disusun dari keberadaan kata kunci konsep pada teks review asli tiap cafe.
        Precision{{ '@'.$summary['k'] }} menilai ketepatan {{ $summary['k'] }} hasil teratas;
        Recall menilai cakupan; AP/MAP menghargai sistem yang menempatkan dokumen relevan di peringkat atas.
    </p>
@endsection
