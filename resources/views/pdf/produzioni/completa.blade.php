<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta completa</title>
    <style>
        @page { margin: 11mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; margin: 0; }
        .sheet { border: 1px solid #9ca3af; padding: 10px; }
        .header { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .logo { width: 160px; max-height: 58px; object-fit: contain; }
        .title { font-size: 23px; font-weight: 700; text-align: right; text-transform: uppercase; line-height: 1.1; }
        .meta { margin-top: 2px; line-height: 1.35; }
        .meta strong { font-weight: 700; }
        .section { margin-top: 10px; }
        .section-title { margin-bottom: 5px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .35px; }
        .desc { border: 1px solid #e5e7eb; background: #f9fafb; padding: 8px; line-height: 1.4; font-size: 9.8px; }
        .allergen-box { border: 2px solid #111; padding: 7px; margin-top: 4px; }
        .allergen-row { margin-top: 6px; }
        .allergen-label { font-weight: 700; font-size: 11px; text-transform: uppercase; }
        .allergen-list { margin-top: 2px; font-weight: 700; font-size: 11px; line-height: 1.35; }
        .icons { margin-top: 4px; }
        .icon { display: inline-block; text-align: center; margin-right: 6px; margin-bottom: 6px; vertical-align: top; width: 56px; }
        .icon img { width: 26px; height: 26px; object-fit: contain; display: block; margin: 0 auto 2px; }
        .icon-code { font-size: 7px; font-weight: 700; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border-bottom: 1px solid #1f2937; padding: 4px 3px; }
        .table th { text-align: left; font-size: 10px; font-weight: 700; }
        .num { text-align: right; white-space: nowrap; }
    </style>
</head>
<body>
<div class="sheet">
    <table class="header">
        <tr>
            <td>
                @if (! empty($logo_path))
                    <img class="logo" src="{{ $logo_path }}" alt="Logo">
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
            <div class="allergen-row">
                <div class="allergen-label">PUO CONTENERE TRACCE DI</div>
                <div class="allergen-list">{{ $allergeni_puo_contenere !== [] ? implode(', ', $allergeni_puo_contenere) : 'NESSUNO' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
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
    </div>

    <div class="section">
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
    </div>
</div>
</body>
</html>
