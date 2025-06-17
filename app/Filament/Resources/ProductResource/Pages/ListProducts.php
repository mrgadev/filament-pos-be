<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Imports\ProductImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importProducts')->label('Import Product')->icon('heroicon-s-arrow-down-tray')->color('danger')->form([
                FileUpload::make('attachment')
                    ->label('Upload Excel File')
                    ->required()
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                    ->maxSize(1024 * 5) // 5MB
                    ->helperText('Upload an Excel file to import products.')
                    
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/'. $data['attachment']);

                    try {
                        Excel::import(new ProductImport(), $file);
                        Notification::make()
                            ->title('Import Successful')
                            ->body('Products have been successfully imported.')
                            ->success()
                            ->send();
                    } catch (\Throwable $th) {
                        Notification::make()
                            ->title('Import Failed')
                            ->body('There was an error importing the products: ' . $th->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make("Download Template")->url(route('download.template'))->color('success'),
            Actions\CreateAction::make(),
        ];
    }
}
