<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<title>Etichetta mini</title>

<style>

@page {
    size: 60mm 60mm;
    margin: 0;
}

body{
    font-family: DejaVu Sans, sans-serif;
    margin:0;
    padding:0;
    color:#111;
    font-size:7.6px;
    line-height:1.15;
}

.box{
    border:1.4px solid #111;
    padding:2mm;
    box-sizing:border-box;
}

.name{
    font-size:13.5px;
    font-weight:900;
    text-transform:uppercase;
    line-height:1.05;
}

.label{
    margin-top:1mm;
    font-size:8px;
    font-weight:900;
    text-transform:uppercase;
}

.text{
    margin-top:0.5mm;
    font-size:7.6px;
    font-weight:700;
    line-height:1.15;
}

.line{
    margin-top:1.2mm;
    font-size:8.4px;
    font-weight:900;
}

</style>

</head>

<body>

@php
$ingredientiTesto = $ingredienti_testo ?? '';

$allergeniContieneCompatti =
    !empty($allergeni_contiene)
    ? implode(', ', $allergeni_contiene)
    : 'Nessuno';

$allergeniPuoContenereCompatti =
    !empty($allergeni_puo_contenere)
    ? implode(', ', $allergeni_puo_contenere)
    : null;
@endphp

<div class="box">

<div class="name">
{{ $nome_prodotto }}
</div>

<div class="label">
Ingredienti
</div>

<div class="text">
{{ $ingredientiTesto !== '' ? $ingredientiTesto : 'N/D' }}
</div>

<div class="label">
Contiene allergeni
</div>

<div class="text">
{{ $allergeniContieneCompatti }}
</div>

@if ($allergeniPuoContenereCompatti !== null)

<div class="label">
Può contenere tracce di
</div>

<div class="text">
{{ $allergeniPuoContenereCompatti }}
</div>

@endif

<div class="line">
<strong>Lotto:</strong> {{ $lotto }}
</div>

<div class="line">
<strong>Scadenza:</strong> {{ $da_consumare_entro ?? 'N/D' }}
</div>

</div>

</body>
</html>