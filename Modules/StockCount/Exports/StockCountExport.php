<?php

namespace Modules\StockCount\Exports;

use Modules\StockCount\Entities\StockCountLine;
use Modules\StockCount\Entities\StockCountSession;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockCountExport implements FromCollection, WithHeadings
{
    protected $session_id;

    public function __construct($session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * Fetch the variance lines.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $lines = StockCountLine::with(['product', 'variation', 'counter'])
            ->where('stock_count_session_id', $this->session_id)
            ->get();

        return $lines->map(function ($line, $index) {
            $variance = $line->counted_quantity - $line->book_quantity;
            $financial_diff = $variance * $line->unit_price;

            return [
                'index' => $index + 1,
                'product_name' => ($line->product?->name ?? '') . ' (' . ($line->variation?->name ?? '') . ')',
                'sku' => $line->variation?->sub_sku ?? '',
                'book_qty' => number_format($line->book_quantity, 2),
                'counted_qty' => number_format($line->counted_quantity, 2),
                'variance' => ($variance > 0 ? '+' : '') . number_format($variance, 2),
                'unit_price' => number_format($line->unit_price, 2),
                'financial_impact' => number_format($financial_diff, 2),
                'counted_by' => $line->counter?->user_full_name ?? '',
                'note' => $line->note ?? '',
            ];
        });
    }

    /**
     * Headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            '#',
            'Product Name',
            'SKU',
            'Book Qty',
            'Counted Qty',
            'Variance',
            'Cost Price',
            'Financial Impact',
            'Counted By',
            'Notes'
        ];
    }
}
