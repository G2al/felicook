<?php

declare(strict_types=1);

namespace App\Services\Units;

use App\Models\UnitConversion;
use RuntimeException;
use SplQueue;

class UnitConversionService
{
    protected ?array $graph = null;

    public function convert(float $quantity, string $fromUnitCode, string $toUnitCode): float
    {
        return $quantity * $this->multiplier($fromUnitCode, $toUnitCode);
    }

    public function tryConvert(float $quantity, string $fromUnitCode, string $toUnitCode): ?float
    {
        try {
            return $this->convert($quantity, $fromUnitCode, $toUnitCode);
        } catch (RuntimeException) {
            return null;
        }
    }

    public function multiplier(string $fromUnitCode, string $toUnitCode): float
    {
        $normalizedFrom = $this->normalizeCode($fromUnitCode);
        $normalizedTo = $this->normalizeCode($toUnitCode);

        if ($normalizedFrom === $normalizedTo) {
            return 1.0;
        }

        $this->bootGraph();

        if (isset($this->graph[$normalizedFrom][$normalizedTo])) {
            return (float) $this->graph[$normalizedFrom][$normalizedTo];
        }

        $queue = new SplQueue();
        $queue->enqueue([$normalizedFrom, 1.0]);
        $visited = [$normalizedFrom => true];

        while (! $queue->isEmpty()) {
            [$currentUnit, $currentMultiplier] = $queue->dequeue();

            foreach ($this->graph[$currentUnit] ?? [] as $nextUnit => $edgeMultiplier) {
                if (isset($visited[$nextUnit])) {
                    continue;
                }

                $resolvedMultiplier = $currentMultiplier * (float) $edgeMultiplier;

                if ($nextUnit === $normalizedTo) {
                    return $resolvedMultiplier;
                }

                $visited[$nextUnit] = true;
                $queue->enqueue([$nextUnit, $resolvedMultiplier]);
            }
        }

        throw new RuntimeException("Percorso di conversione non trovato da [{$fromUnitCode}] a [{$toUnitCode}].");
    }

    public function clearCache(): void
    {
        $this->graph = null;
    }

    protected function bootGraph(): void
    {
        if ($this->graph !== null) {
            return;
        }

        $graph = [];

        UnitConversion::query()
            ->get(['from_unit_code', 'to_unit_code', 'multiplier'])
            ->each(function (UnitConversion $conversion) use (&$graph): void {
                $fromUnitCode = $this->normalizeCode((string) $conversion->from_unit_code);
                $toUnitCode = $this->normalizeCode((string) $conversion->to_unit_code);
                $multiplier = (float) $conversion->multiplier;

                if ($multiplier <= 0) {
                    return;
                }

                $graph[$fromUnitCode][$toUnitCode] = $multiplier;
                $graph[$toUnitCode][$fromUnitCode] = 1 / $multiplier;
            });

        $this->graph = $graph;
    }

    protected function normalizeCode(string $unitCode): string
    {
        return mb_strtolower(trim($unitCode));
    }
}
