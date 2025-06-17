<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class TemplateExport implements WithMultipleSheets
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function sheets(): array
    {
        return [
            new ProductExport(),
            new CategoriesExport(),
        ];
    }
}


class ProductExport implements FromCollection, WithHeadings, WithTitle {
    public function collection()
    {
        return collect([]);
    }

    public function headings(): array
    {
        return [
            'name',
            'category_id',
            'stock',
            'price',
            'is_active',
            'barcode',
            'image'
        ];
    }

    public function title(): string
    {
        return 'Products';
    }
}

class CategoriesExport implements FromCollection, WithHeadings, WithTitle {
    public function collection()
    {
        return Category::select('id', 'name')->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'name'
        ];
    }
    public function title(): string
    {
        return 'Categories';
    }
}