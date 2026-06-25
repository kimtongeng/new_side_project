<?php

namespace Modules\StockCount\Exports;

use Modules\StockCount\Entities\StockCountLine;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockCountTemplateExport implements FromCollection, WithHeadings
{
    protected $session_id;

    public function __construct($session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * Fetch the worksheet template lines.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $lines = StockCountLine::with(['product', 'variation'])
            ->where('stock_count_session_id', $this->session_id)
            ->get();

        return $lines->map(function ($line) {
            return [
                'product_name' => ($line->product?->name ?? '') . ' (' . ($line->variation?->name ?? '') . ')',
                'sku' => $line->variation?->sub_sku ?? '',
                'counted_qty' => $line->counted_quantity ?? 0,
                'note' => $line->note ?? '',
            ];
        });
    }

    /**
     * Headings for the template.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Counted Qty',
            'Note'
        ];
    }
}
