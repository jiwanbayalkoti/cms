<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentType;

class PaymentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Vehicle Rent', 'code' => 'vehicle_rent'],
            ['name' => 'Material Payment', 'code' => 'material_payment'],
        ];

        foreach ($types as $type) {
            PaymentType::firstOrCreate(
                ['code' => $type['code']],
                ['name' => $type['name']]
            );
        }
    }
}
