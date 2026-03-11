<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta completa</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Source+Sans+3:wght@400;600;700&display=swap');

        @page {
            size: A4;
            margin: 12mm 14mm;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Source Sans 3', 'DejaVu Sans', sans-serif;
            font-size: 10.5px;
            color: #1a1a1a;
            background: #fff;
        }

        .sheet {
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            padding: 0;
        }

        /* ── HEADER ────────────────────────────────────────── */
        .header-band {
            background: #1a1a1a;
            color: #fff;
            padding: 14px 18px 12px;
            display: table;
            width: 100%;
        }
        .header-band-left {
            display: table-cell;
            vertical-align: middle;
            width: 200px;
        }
        .header-band-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding: 0 16px;
        }
        .header-band-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 200px;
        }

        .logo-wrap {
            width: 160px;
            height: 52px;
        }
        .logo {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            display: block;
            filter: brightness(0) invert(1);
        }
        .no-logo-placeholder {
            width: 160px;
            height: 52px;
        }

        .product-title {
            font-family: 'Abril Fatface', 'DejaVu Sans', Georgia, serif;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 0.5px;
            line-height: 1.1;
            text-transform: uppercase;
            color: #fff;
        }
        .product-subtitle {
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #aaa;
            margin-top: 3px;
        }
        .lotto-badge {
            display: inline-block;
            background: #fff;
            color: #1a1a1a;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 3px 10px;
            text-transform: uppercase;
        }

        /* ── META BAR ───────────────────────────────────────── */
        .meta-bar {
            background: #f4f4f2;
            border-bottom: 2px solid #1a1a1a;
            padding: 8px 18px;
            display: table;
            width: 100%;
        }
        .meta-item {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            padding: 0 8px;
            border-right: 1px solid #d0d0cc;
        }
        .meta-item:last-child { border-right: none; }
        .meta-item-label {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #888;
            display: block;
            margin-bottom: 2px;
        }
        .meta-item-value {
            font-size: 12px;
            font-weight: 700;
            color: #1a1a1a;
            display: block;
            white-space: nowrap;
        }
        .meta-item-value.highlight {
            font-size: 13px;
            color: #b5722a;
        }

        /* ── DESCRIZIONE ────────────────────────────────────── */
        .desc-section {
            padding: 10px 18px;
            border-bottom: 1px solid #e5e5e3;
        }
        .section-label {
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 5px;
        }
        .desc-text {
            font-size: 10px;
            line-height: 1.55;
            color: #333;
        }

        /* ── ALLERGENI ──────────────────────────────────────── */
        .allergen-section {
            margin: 0 18px;
            margin-top: 10px;
            border: 2.5px solid #1a1a1a;
        }
        .allergen-header {
            background: #1a1a1a;
            color: #fff;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .allergen-body {
            padding: 10px 12px;
        }
        .allergen-names {
            font-size: 11.5px;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1.4;
        }
        .allergen-names.none {
            color: #666;
            font-weight: 400;
            font-style: italic;
        }
        .allergen-icons {
            margin-top: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .icon-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #ccc;
            padding: 3px 8px 3px 5px;
            background: #fafafa;
        }
        .icon-chip img {
            width: 18px;
            height: 18px;
            object-fit: contain;
        }
        .icon-chip-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tracce-row {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #ccc;
        }
        .tracce-label {
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 3px;
        }
        .tracce-names {
            font-size: 10px;
            font-weight: 600;
            color: #555;
        }

        /* ── SPLIT SECTION ──────────────────────────────────── */
        .split-section {
            display: table;
            width: 100%;
            margin-top: 10px;
            padding: 0 18px;
            table-layout: fixed;
        }
        .split-left {
            display: table-cell;
            vertical-align: top;
            width: 62%;
            padding-right: 14px;
        }
        .split-right {
            display: table-cell;
            vertical-align: top;
            width: 38%;
        }

        /* ── TABLES ─────────────────────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 9.5px;
        }
        .data-table thead tr {
            background: #1a1a1a;
            color: #fff;
        }
        .data-table th {
            padding: 5px 7px;
            text-align: left;
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .data-table th.num,
        .data-table td.num {
            text-align: right;
        }
        .data-table tbody tr:nth-child(even) {
            background: #f7f7f5;
        }
        .data-table tbody tr:nth-child(odd) {
            background: #fff;
        }
        .data-table td {
            padding: 5px 7px;
            border-bottom: 1px solid #e8e8e6;
            font-size: 9.5px;
            color: #1a1a1a;
        }
        .data-table.nutri thead tr {
            background: #b5722a;
        }
        .data-table.nutri tbody tr:nth-child(even) {
            background: #fdf6ef;
        }

        .table-gap {
            margin-top: 12px;
        }

        /* ── FOOTER ─────────────────────────────────────────── */
        .footer-band {
            margin-top: 12px;
            background: #f4f4f2;
            border-top: 2px solid #1a1a1a;
            padding: 6px 18px;
            text-align: center;
            font-size: 8px;
            color: #999;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<div class="sheet">

    {{-- ═══ HEADER ═══ --}}
    <div class="header-band">
        <div class="header-band-left">
            @if (! empty($logo_path))
                <div class="logo-wrap">
                    <img class="logo" src="{{ $logo_path }}" alt="Logo">
                </div>
            @else
                <div class="no-logo-placeholder"></div>
            @endif
        </div>
        <div class="header-band-center">
            <div class="product-title">{{ $nome_prodotto }}</div>
            @if (! empty($categoria))
                <div class="product-subtitle">{{ $categoria }}</div>
            @endif
        </div>
        <div class="header-band-right">
            <div class="lotto-badge">LOTTO: {{ $lotto }}</div>
        </div>
    </div>

    {{-- ═══ META BAR ═══ --}}
    <div class="meta-bar">
        <div class="meta-item">
            <span class="meta-item-label">Prodotto il</span>
            <span class="meta-item-value">{{ $prodotto_il ?? 'N/D' }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-item-label">Consumare entro</span>
            <span class="meta-item-value">{{ $da_consumare_entro ?? 'N/D' }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-item-label">Peso prodotto</span>
            <span class="meta-item-value">{{ number_format((float) $peso_prodotto, 2, ',', '.') }} g</span>
        </div>
        <div class="meta-item">
            <span class="meta-item-label">Costo totale</span>
            <span class="meta-item-value highlight">{{ number_format((float) $costo_totale, 2, ',', '.') }} {{ $valuta }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-item-label">Costo / kg</span>
            <span class="meta-item-value highlight">{{ number_format((float) $costo_kg, 2, ',', '.') }} {{ $valuta }}</span>
        </div>
    </div>

    {{-- ═══ DESCRIZIONE ═══ --}}
    @if (! empty($descrizione))
    <div class="desc-section">
        <div class="section-label">Descrizione prodotto</div>
        <div class="desc-text">{!! nl2br(e($descrizione)) !!}</div>
    </div>
    @endif

    {{-- ═══ ALLERGENI ═══ --}}
    <div class="allergen-section">
        <div class="allergen-header">⚠ Informazioni sugli allergeni</div>
        <div class="allergen-body">
            <div class="section-label">Contiene</div>
            @if (! empty($allergeni_contiene))
                <div class="allergen-names">{{ implode(' · ', $allergeni_contiene) }}</div>
            @else
                <div class="allergen-names none">Nessun allergene rilevante</div>
            @endif

            @if (! empty($allergeni_contiene_items))
                <div class="allergen-icons">
                    @foreach ($allergeni_contiene_items as $item)
                        <div class="icon-chip">
                            @if (! empty($item['icon_path']))
                                <img src="{{ $item['icon_path'] }}" alt="{{ $item['name'] }}">
                            @endif
                            <span class="icon-chip-label">{{ $item['code'] !== '' ? $item['code'] : $item['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (! empty($allergeni_puo_contenere))
                <div class="tracce-row">
                    <div class="tracce-label">Può contenere tracce di</div>
                    <div class="tracce-names">{{ implode(' · ', $allergeni_puo_contenere) }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ INGREDIENTI + VALORI NUTRIZIONALI ═══ --}}
    <div class="split-section">
        <div class="split-left">
            <div class="section-label">Ingredienti e tracciabilità</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ingrediente</th>
                        <th class="num">Quantità</th>
                        <th>Unità</th>
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

        <div class="split-right">
            <div class="section-label">Valori nutrizionali medi per 100 g</div>
            <table class="data-table nutri">
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
                <div class="table-gap">
                    <div class="section-label">Per porzione</div>
                    <table class="data-table nutri">
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
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ FOOTER ═══ --}}
    <div class="footer-band">
        Documento generato automaticamente — conservare per tracciabilità interna
    </div>

</div>
</body>
</html>