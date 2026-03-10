<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta spedizione</title>
    <style>
        @page { size: 100mm 70mm; margin: 2mm; }
        body { margin: 0; font-size: 0; }
        .sheet { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 5.3px; line-height: 1.08; border: 1.2px solid #111; padding: 1.2mm; box-sizing: border-box; }
        .top { width: 100%; border-collapse: collapse; }
        .top td { vertical-align: top; }
        .logo-wrap { width: 30mm; height: 8mm; }
        .logo { display: block; max-width: 100%; max-height: 100%; width: auto; height: auto; }
        .title { text-align: right; font-size: 7.2px; font-weight: 700; text-transform: uppercase; line-height: 1.03; }
        .meta { margin-top: 0.7mm; line-height: 1.08; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .meta strong { font-weight: 700; }
        .desc { margin-top: 0.8mm; border: 1px solid #d1d5db; background: #f9fafb; padding: 0.8mm; line-height: 1.1; max-height: 7.6mm; overflow: hidden; }
        .allergen-box { margin-top: 0.8mm; border: 1.6px solid #111; background: #fff7ed; padding: 0.8mm; }
        .allergen-title { font-size: 5.1px; font-weight: 700; text-transform: uppercase; }
        .allergen-list { margin-top: 0.2mm; font-size: 5.2px; font-weight: 700; line-height: 1.1; white-space: normal; word-break: break-word; }
        .icons { margin-top: 0.4mm; max-height: 4mm; overflow: hidden; }
        .icon { display: inline-block; width: 3.8mm; height: 3.8mm; margin-right: 0.7mm; margin-bottom: 0.2mm; vertical-align: middle; }
        .icon img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .ingredients { margin-top: 0.8mm; border: 1px solid #111; padding: 0.8mm; max-height: 13.5mm; overflow: hidden; }
        .ingredients-title { font-weight: 700; text-transform: uppercase; }
        .ingredients-text { margin-top: 0.2mm; line-height: 1.1; }
    </style>
</head>
<body>
@php
    $descrizioneCompatta = trim((string) preg_replace('/\s+/', ' ', (string) $descrizione));
    $descrizioneCompatta = \Illuminate\Support\Str::limit($descrizioneCompatta !== '' ? $descrizioneCompatta : 'N/D', 120);
    $allergeniContieneCompatti = $allergeni_contiene !== [] ? implode(', ', $allergeni_contiene) : 'NESSUNO';
    $allergeniPuoContenereCompatti = $allergeni_puo_contenere !== [] ? implode(', ', $allergeni_puo_contenere) : null;
    $iconeAllergeni = $allergeni_contiene_items ?? [];
    $ingredientiSpedizione = array_slice($ingredienti ?? [], 0, 5);
    $ingredientiSpedizioneTesto = collect($ingredientiSpedizione)
        ->map(fn (array $item): string => sprintf(
            '%s (%s %s, lotto %s, scad %s)',
            (string) ($item['nome'] ?? ''),
            number_format((float) ($item['quantita'] ?? 0), 3, ',', '.'),
            (string) ($item['unita'] ?? ''),
            (string) ($item['lotto'] ?? 'N/D'),
            (string) ($item['scadenza'] ?? 'N/D'),
        ))
        ->filter()
        ->implode('; ');
    $ingredientiSpedizioneTesto = \Illuminate\Support\Str::limit($ingredientiSpedizioneTesto !== '' ? $ingredientiSpedizioneTesto : 'N/D', 170);
    $ingredientiRestanti = max(0, count($ingredienti ?? []) - count($ingredientiSpedizione));
@endphp
<div class="sheet">
    <table class="top">
        <tr>
            <td>
                @if (! empty($logo_path))
                    <div class="logo-wrap">
                        <img class="logo" src="{{ $logo_path }}" alt="Logo">
                    </div>
                @endif
            </td>
            <td class="title">{{ $nome_prodotto }}</td>
        </tr>
    </table>

    <div class="meta">
        <strong>Lotto:</strong> {{ $lotto }} |
        <strong>Prodotto il:</strong> {{ $prodotto_il ?? 'N/D' }} |
        <strong>Scadenza:</strong> {{ $da_consumare_entro ?? 'N/D' }} |
        <strong>Peso:</strong> {{ number_format((float) $peso_prodotto, 2, ',', '.') }} g |
        <strong>Prezzo pubblico/kg:</strong> {{ number_format((float) $prezzo_pubblico_kg, 2, ',', '.') }} {{ $valuta }}
    </div>

    <div class="desc">
        <strong>DESCRIZIONE:</strong>
        {{ $descrizioneCompatta }}
    </div>

    <div class="allergen-box">
        <div class="allergen-title">CONTIENE ALLERGENI</div>
        <div class="allergen-list">{{ $allergeniContieneCompatti }}</div>
        @if ($iconeAllergeni !== [])
            <div class="icons">
                @foreach ($iconeAllergeni as $item)
                    <div class="icon">
                        @if (! empty($item['icon_path']))
                            <img src="{{ $item['icon_path'] }}" alt="{{ $item['name'] }}">
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
        @if ($allergeniPuoContenereCompatti !== null)
            <div class="allergen-title" style="margin-top: 0.4mm;">PUO CONTENERE TRACCE DI</div>
            <div class="allergen-list">{{ $allergeniPuoContenereCompatti }}</div>
        @endif
    </div>

    <div class="ingredients">
        <div class="ingredients-title">Ingredienti e tracciabilita</div>
        <div class="ingredients-text">{{ $ingredientiSpedizioneTesto }}</div>
        @if ($ingredientiRestanti > 0)
            <div class="ingredients-text">+{{ $ingredientiRestanti }} ingredienti aggiuntivi</div>
        @endif
    </div>
</div>
</body>
</html>
