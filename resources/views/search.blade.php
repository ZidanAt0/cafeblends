@extends('layouts.app')

@section('title', 'CafeBlend — Pencarian Review Cafe')

@section('content')
    {{-- Header --}}
    <header class="mb-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-cream neu-raised mb-4 text-3xl">
            ☕
        </div>
        <h1 class="font-serif text-4xl text-primary leading-tight">CafeBlend</h1>
        <p class="text-muted mt-2 text-sm">
            Temukan cafe dari isi review-nya
        </p>

        {{-- TODO (integrasi): aktifkan link evaluasi setelah modul Anggota 4 jadi --}}
        {{-- <a href="{{ route('evaluation') }}" class="inline-block mt-3 text-xs font-medium text-primary hover:text-primary-dark">
            Lihat halaman Evaluasi &rarr;
        </a> --}}
    </header>

    {{-- Form pencarian (neumorphic inset) --}}
    <form method="GET" action="{{ route('search') }}" class="flex gap-3 mb-8">
        <input
            type="text" name="q" value="{{ $query }}"
            placeholder="cth: cozy cafe with good coffee and dessert"
            autofocus
            class="neu-input flex-1 rounded-xl bg-inset px-5 h-12 text-coffee placeholder:text-muted/60 neu-inset border-0">
        <button type="submit"
            class="neu-press rounded-xl bg-primary px-7 h-12 font-semibold text-white neu-raised-sm">
            Cari
        </button>
    </form>

    {{-- Ringkasan hasil (aktif setelah modul pencarian diintegrasikan) --}}
    @if($query !== '' && count($results) > 0)
        <p class="text-xs text-muted mb-5 px-1">
            Hasil untuk <span class="font-semibold text-primary">"{{ $query }}"</span>
            — {{ count($results) }} dokumen
            <span class="font-mono">({{ number_format($elapsed, 1) }} ms)</span>
        </p>
    @endif

    {{-- Daftar hasil --}}
    @forelse($results as $i => $r)
        @php
            $cafe = $r['cafe'];
            $pct  = round($r['score'] * 100, 1);
            $snippet = \Illuminate\Support\Str::limit($cafe->review_text, 230);
        @endphp
        <article class="bg-cream rounded-xl neu-raised p-6 mb-5">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-inset neu-inset font-mono text-xs text-primary shrink-0">
                            {{ $i + 1 }}
                        </span>
                        <h2 class="font-serif text-xl text-coffee truncate">{{ $cafe->name }}</h2>
                    </div>
                    <div class="flex flex-wrap gap-1.5 mt-3">
                        <span class="text-[11px] px-2.5 py-1 rounded-full bg-[#FAF0E1] text-primary neu-raised-sm">
                            📍 {{ ucfirst($cafe->city) }}
                        </span>
                        @if($cafe->overall_rating)
                            <span class="text-[11px] px-2.5 py-1 rounded-full bg-[#FAF0E1] text-primary neu-raised-sm">
                                ⭐ {{ $cafe->overall_rating }}
                            </span>
                        @endif
                        @if($cafe->cuisine)
                            <span class="text-[11px] px-2.5 py-1 rounded-full bg-[#FAF0E1] text-secondary neu-raised-sm">
                                {{ \Illuminate\Support\Str::limit($cafe->cuisine, 32) }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Skor relevansi (mono) --}}
                <div class="text-right shrink-0">
                    <div class="text-[10px] uppercase tracking-wide text-muted/70">relevansi</div>
                    <div class="font-mono text-lg font-medium text-primary">{{ number_format($r['score'], 4) }}</div>
                </div>
            </div>

            {{-- Bar skor (inset track + raised fill) --}}
            <div class="w-full bg-inset rounded-full h-2 mt-4 neu-inset">
                <div class="bg-primary h-2 rounded-full" style="width: {{ min(max($pct, 3), 100) }}%"></div>
            </div>

            <p class="text-sm text-muted mt-4 leading-relaxed">{{ $snippet }}</p>
        </article>
    @empty
        @if($query !== '')
            <div class="bg-cream rounded-xl neu-raised p-10 text-center text-muted">
                Fitur pencarian belum aktif.<br>
            </div>
        @else
            <div class="text-center text-muted/70 py-12 text-sm">
                Ketik sesuatu untuk mulai mencari cafe berdasarkan isi review-nya.
            </div>
        @endif
    @endforelse
@endsection
