<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'LOI',
            'PENSION ACCOUNT',
            'SPA',
            'ORDERS/AMENDMENT',
            'OMBUDSMAN',
            'COURT ORDER',
            'CENOMAR',
        ];

        foreach ($types as $type) {
            DocumentType::firstOrCreate(['name' => $type]);
        }
    }
}