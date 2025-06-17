<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductImport implements ToModel, WithHeadingRow, WithMultipleSheets, SkipsEmptyRows, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function sheets(): array
    {
        return [
            0 => $this
        ];
    }
    public function model(array $row)
    {   
        // dd($row);
        return new Product([
            'name' => $row['name'],
            'slug' => Product::generateUniqueSlug($row['name']),
            'category_id' => $row['category_id'],
            'stock' => $row['stock'],
            'price' => $row['price'],
            'is_active' => $row['is_active'] ?? true,
            'barcode' => $row['barcode'] ?? null,
            'image' => $row['image'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.name' => 'required|string|max:255',
            '*.category_id' => 'required|exists:categories,id',
            '*.stock' => 'required|integer|min:0',
            '*.price' => 'required|numeric|min:0',
            '*.is_active' => 'boolean',
            '*.barcode' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'The product name is required.',
            'category_id.required' => 'The category ID is required.',
            'stock.required' => 'The stock quantity is required.',
            'price.required' => 'The price is required.',
            'is_active.boolean' => 'The active status must be true or false.',
            'barcode.max' => 'The barcode may not be greater than 255 characters.',
        ];
    }
}
