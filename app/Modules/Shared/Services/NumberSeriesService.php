<?php

namespace App\Modules\Shared\Services;

use App\Modules\Shared\Models\NumberSeries;
use Illuminate\Support\Facades\DB;

class NumberSeriesService
{
    public function next(string $organizationId, string $entityType, array $defaults = []): string
    {
        return DB::transaction(function () use ($organizationId, $entityType, $defaults) {
            $series = NumberSeries::query()
                ->where('organization_id', $organizationId)
                ->where('entity_type', $entityType)
                ->lockForUpdate()
                ->first();

            if (!$series) {
                $series = NumberSeries::create([
                    'organization_id' => $organizationId,
                    'entity_type' => $entityType,
                    'prefix' => $defaults['prefix'] ?? strtoupper(substr($entityType, 0, 3)) . '-',
                    'suffix' => $defaults['suffix'] ?? null,
                    'format' => $defaults['format'] ?? '{PREFIX}{DATE}-{NUMBER}',
                    'current_number' => 0,
                    'padding' => $defaults['padding'] ?? 6,
                    'include_date' => $defaults['include_date'] ?? true,
                    'date_format' => $defaults['date_format'] ?? 'ymd',
                    'reset_on_date_change' => $defaults['reset_on_date_change'] ?? false,
                    'last_reset_date' => now()->toDateString(),
                ]);
            }

            if ($series->reset_on_date_change && $series->last_reset_date !== now()->toDateString()) {
                $series->current_number = 0;
                $series->last_reset_date = now()->toDateString();
            }

            $series->current_number = (int) $series->current_number + 1;
            $series->save();

            $number = str_pad((string) $series->current_number, (int) ($series->padding ?? 6), '0', STR_PAD_LEFT);
            $datePart = $series->include_date ? now()->format($this->toPhpDateFormat((string) $series->date_format)) : '';

            return ($series->prefix ?? '') . ($datePart !== '' ? $datePart . '-' : '') . $number . ($series->suffix ?? '');
        });
    }

    private function toPhpDateFormat(string $format): string
    {
        return match (strtolower($format)) {
            'yymmdd', 'ymd' => 'ymd',
            'yyyymmdd' => 'Ymd',
            'yy' => 'y',
            'yyyy' => 'Y',
            default => 'ymd',
        };
    }
}
