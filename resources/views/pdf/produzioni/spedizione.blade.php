<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<title>Etichetta spedizione</title>

<style>

@page {
    size: 100mm 95mm;
    margin: 2mm;
}

body{
    margin:0;
    font-family: DejaVu Sans, sans-serif;
    font-size:6px;
    color:#111;
}

/* CONTENITORE */

.sheet{
    border:1.5px solid #111;
    padding:1.5mm;
}

/* HEADER */

.top{
    width:100%;
    border-collapse:collapse;
}

.top td{
    vertical-align:top;
}

.logo-wrap {
    width:9mm;
    height:7mm;
}

.logo {
    width:100%;
    height:auto;
}

.title{
    text-align:right;
    font-size:8px;
    font-weight:900;
    text-transform:uppercase;
}

/* META */

.meta{
    margin-top:1mm;
    line-height:1.2;
}

.meta strong{
    font-weight:700;
}

/* DESCRIZIONE */

.desc{
    margin-top:1mm;
    border:1px solid #d1d5db;
    background:#f9fafb;
    padding:1mm;
    line-height:1.2;
}

/* ALLERGENI */

.allergen-box{
    margin-top:1mm;
    border:1.6px solid #111;
    background:#fff7ed;
    padding:1mm;
}

.allergen-title{
    font-weight:800;
    text-transform:uppercase;
}

.allergen-list{
    margin-top:0.3mm;
    font-weight:700;
}

/* ICONS */

.icons{
    margin-top:2mm;
}

.icon{
    display:inline-block;
    width:4mm;
    height:4mm;
    margin-right:0.6mm;
}

.icon img{
    width:100%;
    height:100%;
}

/* NUTRIZIONALI */

.nutritional{
    margin-top:1mm;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th,
.table td{
    border:1px solid #111;
    padding:0.6mm;
    font-size:5.6px;
}

.table th{
    background:#f3f4f6;
    font-weight:800;
}

.num{
    text-align:right;
}

</style>

</head>

<body>

<div class="sheet">

<table class="top">
<tr>

<td>

@if (!empty($logo_path))
<div class="logo-wrap">
<img class="logo" src="{{ $logo_path }}">
</div>
@endif

</td>

<td class="title">
{{ $nome_prodotto }}
</td>

</tr>
</table>

<div class="meta">
<strong>Lotto:</strong> {{ $lotto }} |
<strong>Prodotto il:</strong> {{ $prodotto_il ?? 'N/D' }} |
<strong>Scadenza:</strong> {{ $da_consumare_entro ?? 'N/D' }} |
<strong>Peso:</strong> {{ number_format((float) $peso_prodotto,2,',','.') }} g |
<strong>Prezzo pubblico/kg:</strong> {{ number_format((float) $prezzo_pubblico_kg,2,',','.') }} {{ $valuta }}
</div>

<div class="desc">
<strong>DESCRIZIONE:</strong><br>
{!! nl2br(e($descrizione !== '' ? $descrizione : 'N/D')) !!}
</div>

<div class="allergen-box">

<div class="allergen-title">
CONTIENE ALLERGENI
</div>

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

</div>

@endforeach

</div>

@endif

@if ($allergeni_puo_contenere !== [])

<div class="allergen-title" style="margin-top:0.6mm;">
PUÒ CONTENERE TRACCE DI
</div>

<div class="allergen-list">
{{ implode(', ', $allergeni_puo_contenere) }}
</div>

@endif

</div>

<div class="nutritional">

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
<td class="num">{{ number_format((float) $riga['value'],2,',','.') }} {{ $riga['unit'] }}</td>
</tr>

@endforeach

</tbody>

</table>

</div>

</div>

</body>
</html>