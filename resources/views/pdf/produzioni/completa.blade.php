<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta completa</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; margin: 0; padding: 0; }
        .sheet { padding: 8mm; box-sizing: border-box; width: 100%; max-width: 100%; page-break-inside: avoid; }
        .header { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .logo-wrap { width: 220px; height: 60px; }
        .logo { display: block; max-width: 100%; max-height: 100%; width: auto; height: auto; }
        .title { font-size: 24px; font-weight: 900; text-align: right; text-transform: uppercase; line-height: 1.05; word-break: break-word; }
        .meta { margin-top: 2px; line-height: 1.3; }
        .meta strong { font-weight: 700; }
        .section { margin-top: 12px; }
        .section-title { margin-bottom: 4px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .25px; }
        .desc { border: 1px solid #d1d5db; background: #f9fafb; padding: 9px; line-height: 1.4; font-size: 9.5px; }
        .allergen-box { border: 2px solid #111; padding: 9px; margin-top: 6px; page-break-inside: avoid; }
        .allergen-row { margin-top: 6px; }
        .allergen-label { font-weight: 800; font-size: 11px; text-transform: uppercase; }
        .allergen-list { margin-top: 2px; font-weight: 700; font-size: 10px; line-height: 1.3; }
        .icons { margin-top: 5px; }
        .icon { display: inline-block; text-align: center; margin-right: 7px; margin-bottom: 7px; vertical-align: top; width: 46px; }
        .icon img { width: 22px; height: 22px; object-fit: contain; display: block; margin: 0 auto 2px; }
        .icon-code { font-size: 8px; font-weight: 700; }
        .split { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 12px; }
        .split td { vertical-align: top; }
        .split-left { width: 63%; padding-right: 12px; }
        .split-right { width: 37%; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #1f2937; padding: 5px 5px; font-size: 9.2px; }
        .table th { text-align: left; font-weight: 800; background: #f3f4f6; }
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
                @if (! empty($tabella_nutrizionale_porzione))
                    <div class="section-title" style="margin-top: 10px;">Valori nutrizionali per porzione</div>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Nutriente</th>
                            <th class="num">Valore</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($tabella_nutrizionale_porzione as $riga)
                            <tr>
                                <td>{{ $riga['label'] }}</td>
                                <td class="num">{{ number_format((float) $riga['value'], 2, ',', '.') }} {{ $riga['unit'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </td>
        </tr>
    </table>
</div>
</body>
</html>
