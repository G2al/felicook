<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<title>Etichetta spedizione</title>

<style>

@page {
    size: 103mm 164mm;
    margin: 2mm;
}

body{
    font-family: "Helvetica Neue", Arial, sans-serif;
    margin:0;
    color:#111;
    font-size:11px;
}

/* CARD */

.sheet{
    border:2px solid #111;
    background:#fff;
    padding:3mm;
    box-sizing:border-box;
}

/* HEADER */

.top{
    width:100%;
    border-collapse:collapse;
}

.top td{
    vertical-align:top;
}

.logo-wrap{
    width:20mm;
    height:10mm;
}

.logo{
    width:100%;
    height:auto;
    margin-top: -15px;
}

.title{
    text-align:right;
    font-size:20px;
    font-weight:900;
    text-transform:uppercase;
    line-height:1.05;
}

/* META */

.meta{
    margin-top:12px;
    line-height:1.35;
}

/* DESCRIZIONE */

.desc{
    margin-top:16px;
    border:1px solid #d1d5db;
    background:#f9fafb;
    padding:7px;
    line-height:1.38;
    font-size:11px;
}

/* ALLERGENI */

.allergen-box{
    margin-top:12px;
    border:2px solid #111;
    padding:6px;
    background:#fff7ed;
}

.allergen-title{
    font-size:11px;
    font-weight:900;
    text-transform:uppercase;
}

.allergen-list{
    margin-top:4px;
    font-size:10px;
    font-weight:850;
}

/* ICONS */

.icons{
    margin-top:8px;
}

.icon{
    display:inline-block;
    width:34px;
    margin-right:6px;
}

.icon img{
    width:18px;
    height:18px;
}

/* TABELLA NUTRIZIONALE */

.nutritional{
    margin-top:16px;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th,
.table td{
    border:1px solid #111;
    padding:4px;
    font-size:10px;
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
</div>

@endforeach
</div>

@endif

@if ($allergeni_puo_contenere !== [])

<div class="allergen-title" style="margin-top:6px;">
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