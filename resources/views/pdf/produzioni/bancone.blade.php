<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta bancone</title>
    <style>
        @page { margin: 6mm; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #111; font-size: 11px; }
        .card { border: 2px solid #111; background: #fff; padding: 12px; }
        .top { width: 100%; border-collapse: collapse; }
        .top td { vertical-align: top; }
        .logo { width: 150px; max-height: 55px; object-fit: contain; }
        .name { text-align: right; font-size: 28px; font-weight: 700; text-transform: uppercase; line-height: 1.05; }
        .price-bar { margin-top: 8px; border: 2px solid #111; background: #f59e0b; color: #111; padding: 6px 8px; font-size: 28px; font-weight: 700; }
        .desc { margin-top: 8px; border: 1px solid #d1d5db; background: #f9fafb; padding: 8px; line-height: 1.35; font-size: 10.8px; }
        .allergen-box { margin-top: 9px; border: 2px solid #111; padding: 8px; background: #fff7ed; }
        .allergen-title { font-size: 14px; font-weight: 700; text-transform: uppercase; }
        .allergen-list { margin-top: 4px; font-size: 13px; font-weight: 700; line-height: 1.35; }
        .icons { margin-top: 5px; }
        .icon { display: inline-block; width: 52px; margin-right: 6px; margin-bottom: 6px; text-align: center; vertical-align: top; }
        .icon img { width: 24px; height: 24px; object-fit: contain; display: block; margin: 0 auto 2px; }
        .icon-code { font-size: 7px; font-weight: 700; }
        .meta { margin-top: 8px; font-size: 11px; line-height: 1.35; }
        .meta strong { font-weight: 700; }
    </style>
</head>
<body>
<div class="card">
    <table class="top">
        <tr>
            <td>
                @if (! empty($logo_path))
                    <img class="logo" src="{{ $logo_path }}" alt="Logo">
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
        <div class="allergen-title" style="margin-top: 4px;">PUO CONTENERE TRACCE DI</div>
        <div class="allergen-list">{{ $allergeni_puo_contenere !== [] ? implode(', ', $allergeni_puo_contenere) : 'NESSUNO' }}</div>
    </div>

    <div class="meta">
        <strong>Lotto:</strong> {{ $lotto }} |
        <strong>Scadenza:</strong> {{ $da_consumare_entro ?? 'N/D' }}
    </div>
</div>
</body>
</html>
