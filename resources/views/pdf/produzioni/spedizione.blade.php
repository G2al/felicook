<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta spedizione</title>
    <style>
        @page { margin: 5mm; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #111; font-size: 10px; }
        .sheet { border: 2px solid #111; padding: 8px; }
        .top { width: 100%; border-collapse: collapse; }
        .top td { vertical-align: top; }
        .logo { width: 130px; max-height: 42px; object-fit: contain; }
        .title { text-align: right; font-size: 18px; font-weight: 700; text-transform: uppercase; line-height: 1.1; }
        .meta { margin-top: 6px; line-height: 1.35; }
        .meta strong { font-weight: 700; }
        .desc { margin-top: 7px; border: 1px solid #d1d5db; background: #f9fafb; padding: 6px; font-size: 9px; line-height: 1.35; }
        .allergen-box { margin-top: 7px; border: 2px solid #111; background: #fff7ed; padding: 6px; }
        .allergen-title { font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .allergen-list { margin-top: 3px; font-size: 11px; font-weight: 700; line-height: 1.35; }
        .icons { margin-top: 4px; }
        .icon { display: inline-block; width: 46px; margin-right: 5px; margin-bottom: 5px; text-align: center; vertical-align: top; }
        .icon img { width: 22px; height: 22px; object-fit: contain; display: block; margin: 0 auto 2px; }
        .icon-code { font-size: 7px; font-weight: 700; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { border-bottom: 1px solid #111; padding: 3px 2px; font-size: 8.4px; }
        .table th { text-align: left; font-weight: 700; }
        .num { text-align: right; white-space: nowrap; }
    </style>
</head>
<body>
<div class="sheet">
    <table class="top">
        <tr>
            <td>
                @if (! empty($logo_path))
                    <img class="logo" src="{{ $logo_path }}" alt="Logo">
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
        <div class="allergen-title" style="margin-top: 3px;">PUO CONTENERE TRACCE DI</div>
        <div class="allergen-list">{{ $allergeni_puo_contenere !== [] ? implode(', ', $allergeni_puo_contenere) : 'NESSUNO' }}</div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>Ingrediente</th>
            <th class="num">Qta</th>
            <th>Unita</th>
            <th>Lotto</th>
            <th>Scadenza</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($ingredienti as $ingrediente)
            <tr>
                <td>{{ $ingrediente['nome'] }}</td>
                <td class="num">{{ number_format((float) $ingrediente['quantita'], 4, ',', '.') }}</td>
                <td>{{ $ingrediente['unita'] }}</td>
                <td>{{ $ingrediente['lotto'] }}</td>
                <td>{{ $ingrediente['scadenza'] ?? 'N/D' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
