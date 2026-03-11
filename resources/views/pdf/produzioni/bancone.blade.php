<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta bancone</title>
    <style>
        @page { size: 103mm 164mm; margin: 2mm; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #111; font-size: 8.5px; }
        .card { border: 2px solid #111; background: #fff; padding: 6mm 6mm 5mm 6mm; height: 120mm; overflow: hidden; page-break-inside: avoid; box-sizing: border-box; }
        .top { width: 100%; border-collapse: collapse; }
        .top td { vertical-align: top; }
        .logo-wrap { width: 46mm; height: 14mm; }
        .logo { display: block; max-width: 100%; max-height: 100%; width: auto; height: auto; }
        .name { text-align: right; font-size: 22px; font-weight: 800; text-transform: uppercase; line-height: 1.04; word-break: break-word; }
        .price-bar { margin-top: 4px; border: 2px solid #111; background: #f59e0b; color: #111; padding: 3px 6px; font-size: 20px; font-weight: 800; }
        .desc { margin-top: 4px; border: 1px solid #d1d5db; background: #f9fafb; padding: 5px; line-height: 1.2; font-size: 8px; max-height: 30mm; overflow: hidden; }
        .allergen-box { margin-top: 6px; border: 2px solid #111; padding: 5px; background: #fff7ed; max-height: 36mm; overflow: hidden; }
        .allergen-title { font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .allergen-list { margin-top: 2px; font-size: 9px; font-weight: 700; line-height: 1.2; }
        .icons { margin-top: 3px; max-height: 18mm; overflow: hidden; }
        .icon { display: inline-block; width: 30px; margin-right: 4px; margin-bottom: 3px; text-align: center; vertical-align: top; }
        .icon img { width: 14px; height: 14px; object-fit: contain; display: block; margin: 0 auto 1px; }
        .icon-code { font-size: 7.4px; font-weight: 700; }
        .meta { margin-top: 5px; font-size: 8.2px; line-height: 1.2; }
        .meta strong { font-weight: 700; }
    </style>
</head>
<body>
<div class="card">
    <table class="top">
        <tr>
            <td>
                @if (! empty($logo_path))
                    <div class="logo-wrap">
                        <img class="logo" src="{{ $logo_path }}" alt="Logo">
                    </div>
                @endif
            </td>
            <td class="name">{{ $nome_prodotto }}</td>
        </tr>
    </table>

    <div class="price-bar">
        {{ number_format((float) $prezzo_pubblico_kg, 2, ',', '.') }} {{ $valuta }}/kg
    </div>

    <div class="desc">
        <strong>DESCRIZIONE:</strong><br>
        {!! nl2br(e($descrizione !== '' ? $descrizione : 'N/D')) !!}
    </div>

    <div class="allergen-box">
        <div class="allergen-title">CONTIENE ALLERGENI</div>
        <div class="allergen-list">{{ $allergeni_contiene !== [] ? implode(', ', $allergeni_contiene) : 'NESSUNO' }}</div>
        @if ($allergeni_contiene_items !== [])
            <div class="icons">
                @foreach ($allergeni_contiene_items as $item)
                    <div class="icon">
                        @if (! empty($item['icon_path']))
                            <img src="{{ $item['icon_path'] }}" alt="{{ $item['name'] }}">
                        @endif
                        <div class="icon-code">{{ $item['code'] !== '' ? $item['code'] : $item['name'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
        @if ($allergeni_puo_contenere !== [])
            <div class="allergen-title" style="margin-top: 3px;">PUO CONTENERE TRACCE DI</div>
            <div class="allergen-list">{{ implode(', ', $allergeni_puo_contenere) }}</div>
        @endif
    </div>

    <div class="meta">
        <strong>Lotto:</strong> {{ $lotto }} |
        <strong>Scadenza:</strong> {{ $da_consumare_entro ?? 'N/D' }}
    </div>
</div>
</body>
</html>
