<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // Check if all required fields are present in the $data array
    //     if (isset($data['product_total_weight'], $data['stone_weight'])) {
    //         // Perform the calculation
    //         $data['product_net_weight'] = $data['product_total_weight'] - $data['stone_weight'];
    //     } else {
    //         // Handle the case where required fields are missing
    //         // You can throw an exception, log an error, or handle it in any other appropriate way
    //         // For simplicity, we'll set the net weight to null
    //         $data['product_net_weight'] = null;
    //     }

    //     return $data;
    // }
}
