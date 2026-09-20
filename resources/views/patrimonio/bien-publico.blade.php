<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>{{ $ficha['folio_sicam'] }} · SICAM</title><link rel="icon" type="image/png" href="{{ asset('assets/icons/logo-sin-fondo.png') }}"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">@vite('resources/css/app.css')</head>
<body class="bg-canvas font-sans text-ink">
    <main class="mx-auto max-w-3xl p-4 sm:p-8">
        <header class="mb-6 border-b border-line pb-5"><img src="{{ asset('assets/icons/sicam-logo.png') }}" alt="SICAM" class="h-20 w-56 object-contain"><p class="mt-3 text-sm text-muted">Inventario municipal · Nezahualcóyotl</p></header>
        <article class="form-card">
            <p class="text-sm font-semibold text-brand">{{ $ficha['folio_sicam'] }}</p><h1 class="mt-2 text-2xl font-bold">{{ $ficha['nombre'] }}</h1>
            @if($ficha['fotografia'])<img src="{{ route('bienes.publico.fotografia', $ficha['public_id']) }}" alt="Fotografía del bien" class="my-5 max-h-80 w-full rounded-lg object-contain">@endif
            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                @foreach(['numero_patrimonial' => 'Número patrimonial', 'categoria' => 'Categoría', 'marca' => 'Marca', 'modelo' => 'Modelo', 'estado' => 'Estado', 'dependencia_id_accesos' => 'Dependencia', 'area_id_accesos' => 'Área'] as $key => $label)
                    <div><dt class="text-xs text-muted">{{ $label }}</dt><dd class="mt-1 break-words text-sm font-semibold">{{ $key === 'estado' ? str_replace('_', ' ', $ficha[$key]) : ($ficha[$key] ?: 'No registrado') }}</dd></div>
                @endforeach
            </dl>
        </article>
    </main>
</body>
</html>
