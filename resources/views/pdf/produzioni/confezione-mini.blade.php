<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta mini</title>
    <style>
        @page { size: 62mm 29mm; margin: 1.2mm; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #111; font-size: 7px; line-height: 1.15; }
        .box { border: 1.4px solid #111; padding: 1.2mm; box-sizing: border-box; height: 22mm; }
        .name { font-size: 11px; font-weight: 900; text-transform: uppercase; line-height: 1.05; }
        .line { margin-top: 0.6mm; font-size: 7.6px; font-weight: 700; }
        .allergen-title { margin-top: 1mm; font-weight: 800; font-size: 8.4px; text-transform: uppercase; }
        .allergen-text { font-size: 7.2px; font-weight: 700; line-height: 1.15; }
        .icons { margin-top: 0.8mm; }
        .icon { display: inline-block; width: 10px; height: 10px; margin-right: 2px; vertical-align: middle; }
        .icon img { width: 100%; height: 100%; object-fit: contain; }
    </style>
</head>
<body>
@php
    $allergeniContieneCompatti = $allergeni_contiene !== [] ? implode(', ', $allergeni_contiene) : 'NESSUNO';
    $iconeAllergeni = $allergeni_contiene_items ?? [];
@endphp
<div class="box">
    <div class="name">{{ $nome_prodotto }}</div>
    <div class="line"><strong>Lotto:</strong> {{ $lotto }}</div>
    <div class="line"><strong>Scadenza:</strong> {{ $da_consumare_entro ?? 'N/D' }}</div>
    <div class="allergen-title">CONTIENE ALLERGENI</div>
    <div class="allergen-text">{{ $allergeniContieneCompatti }}</div>
    @if ($iconeAllergeni !== [])
        <div class="icons">
            @foreach ($iconeAllergeni as $item)
                @if (! empty($item['icon_path']))
                    <span class="icon"><img src="{{ $item['icon_path'] }}" alt="{{ $item['name'] }}"></span>
                @endif
            @endforeach
        </div>
    @endif
</div>
</body>
</html>
