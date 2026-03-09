<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta mini</title>
    <style>
        @page { margin: 1.8mm; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #111; font-size: 6px; }
        .box { border: 1px solid #111; padding: 2px; }
        .name { font-size: 8px; font-weight: 700; text-transform: uppercase; line-height: 1.1; }
        .line { margin-top: 1px; line-height: 1.25; }
        .allergen { margin-top: 2px; font-weight: 700; text-transform: uppercase; }
        .desc { margin-top: 2px; line-height: 1.2; }
    </style>
</head>
<body>
<div class="box">
    <div class="name">{{ $nome_prodotto }}</div>
    <div class="line"><strong>Lotto:</strong> {{ $lotto }}</div>
    <div class="line"><strong>Scadenza:</strong> {{ $da_consumare_entro ?? 'N/D' }}</div>
    <div class="allergen">CONTIENE ALLERGENI: {{ $allergeni_contiene !== [] ? implode(', ', $allergeni_contiene) : 'NESSUNO' }}</div>
    <div class="desc"><strong>Descrizione:</strong> {!! nl2br(e($descrizione !== '' ? $descrizione : 'N/D')) !!}</div>
</div>
</body>
</html>
