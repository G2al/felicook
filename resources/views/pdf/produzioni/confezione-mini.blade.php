<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Etichetta mini</title>
    <style>
        @page { size: 62mm 30.48mm; margin: 1mm; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #111; font-size: 4.8px; line-height: 1.08; }
        .box { border: 1px solid #111; padding: 0.9mm; box-sizing: border-box; }
        .name { font-size: 6.2px; font-weight: 700; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .line { margin-top: 0.3mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .allergen-title { margin-top: 0.6mm; font-weight: 700; text-transform: uppercase; }
        .allergen-text { font-size: 4.5px; font-weight: 700; line-height: 1.1; white-space: normal; word-break: break-word; }
        .icons { margin-top: 0.2mm; max-height: 4.2mm; overflow: hidden; }
        .icon { display: inline-block; width: 3mm; height: 3mm; margin-right: 0.35mm; vertical-align: middle; }
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
