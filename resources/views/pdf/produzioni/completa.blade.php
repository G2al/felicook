<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta completa</title>
    <style>
        @page { margin: 7mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; margin: 0; }
        .sheet { border: 1px solid #9ca3af; padding: 7px; page-break-inside: avoid; }
        .header { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .logo-wrap { width: 185px; height: 46px; }
        .logo { display: block; max-width: 100%; max-height: 100%; width: auto; height: auto; }
        .title { font-size: 19px; font-weight: 700; text-align: right; text-transform: uppercase; line-height: 1.05; }
        .meta { margin-top: 1px; line-height: 1.25; }
        .meta strong { font-weight: 700; }
        .section { margin-top: 6px; }
        .section-title { margin-bottom: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .25px; }
        .desc { border: 1px solid #e5e7eb; background: #f9fafb; padding: 5px; line-height: 1.25; font-size: 8px; }
        .allergen-box { border: 2px solid #111; padding: 5px; margin-top: 2px; page-break-inside: avoid; }
        .allergen-row { margin-top: 4px; }
        .allergen-label { font-weight: 700; font-size: 10px; text-transform: uppercase; }
        .allergen-list { margin-top: 1px; font-weight: 700; font-size: 9.4px; line-height: 1.25; }
        .icons { margin-top: 3px; }
        .icon { display: inline-block; text-align: center; margin-right: 4px; margin-bottom: 4px; vertical-align: top; width: 38px; }
        .icon img { width: 18px; height: 18px; object-fit: contain; display: block; margin: 0 auto 1px; }
        .icon-code { font-size: 7px; font-weight: 700; }
        .split { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 6px; }
        .split td { vertical-align: top; }
        .split-left { width: 63%; padding-right: 6px; }
        .split-right { width: 37%; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border-bottom: 1px solid #1f2937; padding: 2px 2px; font-size: 8px; }
        .table th { text-align: left; font-weight: 700; }
        .num { text-align: right; white-space: nowrap; }
    </style>
</head>
<body>
<div class="sheet">
    <table class="header">
        <tr>
            <td>
                @if (! empty($logo_path))
                    <div class="logo-wrap">
                        <img class="logo" src="{{ $logo_path }}" alt="Logo">
                    </div>
                @endif
                <div class="meta"><strong>Categoria:</strong> {{ $categoria !== '' ? $categoria : 'N/D' }}</div>
                <div class="meta"><strong>Lotto:</strong> {{ $lotto }}</div>
                <div class="meta"><strong>Prodotto il:</strong> {{ $prodotto_il ?? 'N/D' }}</div>
                <div class="meta"><strong>Da consumare entro:</strong> {{ $da_consumare_entro ?? 'N/D' }}</div>
                <div class="meta"><strong>Peso prodotto:</strong> {{ number_format((float) $peso_prodotto, 2, ',', '.') }} g</div>
                <div class="meta"><strong>Costo totale:</strong> {{ number_format((float) $costo_totale, 2, ',', '.') }} {{ $valuta }}</div>
                <div class="meta"><strong>Costo/kg:</strong> {{ number_format((float) $costo_kg, 2, ',', '.') }} {{ $valuta }}</div>
            </td>
            <td class="title">{{ $nome_prodotto }}</td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Descrizione completa prodotto</div>
        <div class="desc">{!! nl2br(e($descrizione !== '' ? $descrizione : 'N/D')) !!}</div>
    </div>

    <div class="section">
        <div class="allergen-box">
            <div class="allergen-label">CONTIENE ALLERGENI</div>
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
                <div class="allergen-row">
                    <div class="allergen-label">PUO CONTENERE TRACCE DI</div>
                    <div class="allergen-list">{{ implode(', ', $allergeni_puo_contenere) }}</div>
                </div>
            @endif
        </div>
    </div>

    <table class="split">
        <tr>
            <td class="split-left">
                <div class="section-title">Ingredienti e tracciabilita</div>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Ingrediente</th>
                        <th class="num">Quantita</th>
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
            </td>
            <td class="split-right">
                <div class="section-title">Valori nutrizionali medi per 100 g</div>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Nutriente</th>
                        <th class="num">Valore</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($tabella_nutrizionale as $riga)
                        <tr>
                            <td>{{ $riga['label'] }}</td>
                            <td class="num">{{ number_format((float) $riga['value'], 2, ',', '.') }} {{ $riga['unit'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
