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
    font-family: "Helvetica Neue", Arial, sans-serif;
    margin:0;
    color:#111;
    font-size:11.5px;
}

/* CARD */

.card {

    border:2px solid #111;

    background:#fff;

    padding:3mm;

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
    width:30mm;
    height:5mm;
}

.logo {
    width:100%;
    height:auto;
}

.name {

    text-align:right;

    font-size:29px;

    font-weight:900;

    text-transform:uppercase;

    line-height:1.05;

}

/* PREZZO */

.price-bar {

    margin-top:35px;

    border:2px solid #111;

    background:#f59e0b;

    padding:7px 9px;

    font-size:25px;

    font-weight:900;

}

/* DESCRIZIONE */

.desc {

    margin-top:16px;

    border:1px solid #d1d5db;

    background:#f9fafb;

    padding:7px;

    line-height:1.38;

    font-size:11px;

}

/* INGREDIENTI */

.ingredients-box {

    margin-top:16px;

    border:1px solid #d1d5db;

    background:#f9fafb;

    padding:7px;

    font-size:11px;

    line-height:1.3;

}

.ingredients-title {

    font-weight:900;

    text-transform:uppercase;

    font-size:12px;

}

.ingredients-text {

    margin-top:16px;

}

/* ALLERGENI */

.allergen-box {

    margin-top:8px;

    border:2px solid #111;

    padding:6px;

    background:#fff7ed;

}

.allergen-title {

    font-size:11px;

    font-weight:900;

    text-transform:uppercase;

}

.allergen-list {

    margin-top:4px;

    font-size:10.2px;

    font-weight:850;

    line-height:1.25;

}

/* ICONS */

.icons {

    margin-top:8px;

}

.icon {

    display:inline-block;

    width:34px;

    margin-right:6px;

    text-align:center;

}

.icon img {

    width:18px;
    height:18px;

}

.icon-code {

    font-size:8.8px;

    font-weight:750;

}

/* FOOTER */

.meta {

    margin-top:16px;

    font-size:8.4px;

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

<div class="ingredients-box">

<div class="ingredients-title">

INGREDIENTI

</div>

<div class="ingredients-text">

{{ $ingredienti_testo ?? 'N/D' }}

</div>

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

</div>

</body>
</html>
