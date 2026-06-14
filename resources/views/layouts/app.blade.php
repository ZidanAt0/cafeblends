<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CafeBlend — STKI')</title>

    {{-- Fonts: DM Serif Display (headline), Work Sans (body), IBM Plex Mono (angka) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:      '#8B5E3C',
                        'primary-dark':'#6B4226',
                        secondary:    '#C4956A',
                        cream:        '#FDF6EC',
                        surface:      '#FFFFFF',
                        inset:        '#F0E6D6',
                        coffee:       '#3E2723',
                        muted:        '#6D4C41',
                        success:      '#059669',
                        warning:      '#D97706',
                        danger:       '#EF4444',
                        info:         '#6366F1',
                    },
                    fontFamily: {
                        serif: ['"DM Serif Display"', 'serif'],
                        sans:  ['"Work Sans"', 'sans-serif'],
                        mono:  ['"IBM Plex Mono"', 'monospace'],
                    },
                },
            },
        };
    </script>

    {{-- Neumorphism: shadow timbul & cekung di atas latar cream --}}
    <style>
        body { background: #FDF6EC; }
        .neu-raised { box-shadow: 6px 6px 12px #D5C9B8, -6px -6px 12px #FFFFFF; }
        .neu-raised-sm { box-shadow: 4px 4px 8px #D9CEBD, -4px -4px 8px #FFFFFF; }
        .neu-inset { box-shadow: inset 4px 4px 8px #D5C9B8, inset -4px -4px 8px #FFFFFF; }
        .neu-press { transition: all .12s ease; }
        .neu-press:hover { background: #6B4226; }
        .neu-press:active { box-shadow: inset 3px 3px 7px #5A3820; transform: translateY(1px); }
        .neu-input:focus { outline: none; box-shadow: inset 4px 4px 8px #D5C9B8, inset -4px -4px 8px #FFFFFF, 0 0 0 3px rgba(139,94,60,.25); }
    </style>
</head>
<body class="font-sans text-coffee antialiased">
    <div class="max-w-3xl mx-auto px-4 py-10 sm:py-14">
        @yield('content')

        <footer class="mt-12 text-center text-xs text-muted/70 font-sans">
            CafeBlend · Find your best cafe here
        </footer>
    </div>
</body>
</html>
