<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<title>Etichetta bancone</title>

<style>

@page {
    size: 103mm 164mm;
    margin: 2mm;
}

body {
    font-family: DejaVu Sans, sans-serif;
    margin:0;
    color:#111;
    font-size:9px;
}

/* CARD */

.card {

    border:2px solid #111;

    background:#fff;

    padding:3mm;

    max-height:158mm;

    box-sizing:border-box;

    overflow:hidden;

    page-break-inside:avoid;

}

/* HEADER */

.top {
    width:100%;
    border-collapse:collapse;
}

.top td {
    vertical-align:top;
}

.logo-wrap {
    width:48mm;
    height:14mm;
}

.logo {
    max-width:110%;
    max-height:110%;
}

.name {

    text-align:right;

    font-size:26px;

    font-weight:900;

    text-transform:uppercase;

    line-height:1.05;

}

/* PREZZO */

.price-bar {

    margin-top:12px;

    border:2px solid #111;

    background:#f59e0b;

    padding:7px 9px;

    font-size:23px;

    font-weight:900;

}

/* DESCRIZIONE */

.desc {

    margin-top:16px;

    border:1px solid #d1d5db;

    background:#f9fafb;

    padding:8px;

    line-height:1.35;

    font-size:9px;

}

/* ALLERGENI */

.allergen-box {

    margin-top:16px;

    border:2px solid #111;

    padding:8px;

    background:#fff7ed;

}

.allergen-title {

    font-size:10px;

    font-weight:900;

    text-transform:uppercase;

}

.allergen-list {

    margin-top:4px;

    font-size:9.5px;

    font-weight:850;

    line-height:1.25;

}

/* ICONS */

.icons {

    margin-top:8px;

}

.icon {

    display:inline-block;

    width:36px;

    margin-right:6px;

    text-align:center;

}

.icon img {

    width:18px;

    height:18px;

}

.icon-code {

    font-size:8px;

    font-weight:750;

}

/* INGREDIENTI */

.ingredients-box {

    margin-top:16px;

    border:1px solid #d1d5db;

    background:#f9fafb;

    padding:8px;

    font-size:8.8px;

    line-height:1.3;

}

.ingredients-title {

    font-weight:900;

    text-transform:uppercase;

    font-size:9px;

}

.ingredients-text {

    margin-top:4px;

}

/* FOOTER */

.meta {

    margin-top:16px;

    font-size:9px;

}

</style>

</head>

<body>

<div class="card">

<table class="top">

<tr>

<td>

@if (! empty($logo_path))

<div class="logo-wrap">
<img class="logo" src="{{ $logo_path }}">
</div>

@endif

</td>

<td class="name">

{{ $nome_prodotto }}

</td>

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

<div class="allergen-list">

{{ $allergeni_contiene !== [] ? implode(', ', $allergeni_contiene) : 'NESSUNO' }}

</div>

@if ($allergeni_contiene_items !== [])

<div class="icons">

@foreach ($allergeni_contiene_items as $item)

<div class="icon">

@if (! empty($item['icon_path']))
<img src="{{ $item['icon_path'] }}">
@endif

<div class="icon-code">
{{ $item['code'] !== '' ? $item['code'] : $item['name'] }}
</div>

</div>

@endforeach

</div>

@endif

@if ($allergeni_puo_contenere !== [])

<div class="allergen-title" style="margin-top:6px;">
PUO CONTENERE TRACCE DI
</div>

<div class="allergen-list">

{{ implode(', ', $allergeni_puo_contenere) }}

</div>

@endif

</div>

<div class="ingredients-box">

<div class="ingredients-title">

INGREDIENTI

</div>

<div class="ingredients-text">

{{ $ingredienti_testo ?? 'N/D' }}

</div>

</div>

<div class="meta">

<strong>Lotto:</strong> {{ $lotto }}

|

<strong>Scadenza:</strong> {{ $da_consumare_entro ?? 'N/D' }}

</div>

</div>

</body>
</html>