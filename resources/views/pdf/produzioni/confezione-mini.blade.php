<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta mini</title>
    <style>
        @page { size: 60mm 60mm; margin: 2mm; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #111; font-size: 7.2px; line-height: 1.08; }
        .box { border: 1.4px solid #111; padding: 1.8mm; box-sizing: border-box; height: 46mm; }
        .name { font-size: 11.5px; font-weight: 900; text-transform: uppercase; line-height: 1.05; }
        .label { margin-top: 0.6mm; font-size: 7.2px; font-weight: 800; text-transform: uppercase; }
        .text { margin-top: 0.2mm; font-size: 7px; font-weight: 700; line-height: 1.1; }
        .line { margin-top: 0.6mm; font-size: 7.2px; font-weight: 800; }
    </style>
</head>
<body>
@php
    $ingredientiTesto = $ingredienti_testo ?? '';
    $allergeniContieneCompatti = $allergeni_contiene !== [] ? implode(', ', $allergeni_contiene) : 'Nessuno';
    $allergeniPuoContenereCompatti = $allergeni_puo_contenere !== [] ? implode(', ', $allergeni_puo_contenere) : null;
@endphp
<div class="box">
    <div class="name">{{ $nome_prodotto }}</div>
    <div class="label">Ingredienti</div>
    <div class="text">{{ $ingredientiTesto !== '' ? $ingredientiTesto : 'N/D' }}</div>
    <div class="label">Contiene allergeni</div>
    <div class="text">{{ $allergeniContieneCompatti }}</div>
    @if ($allergeniPuoContenereCompatti !== null)
        <div class="label">Può contenere tracce di</div>
        <div class="text">{{ $allergeniPuoContenereCompatti }}</div>
    @endif
    <div class="line"><strong>Lotto:</strong> {{ $lotto }}</div>
    <div class="line"><strong>Scadenza:</strong> {{ $da_consumare_entro ?? 'N/D' }}</div>
</div>
</body>
</html>
