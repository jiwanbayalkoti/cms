<?php

namespace Database\Seeders;

use App\Models\ConstructionMaterial;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ConstructionMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $units = ['bag', 'kg', 'ton', 'piece', 'm³'];
        $paymentStatuses = ['Paid', 'Unpaid', 'Partial'];
        $statuses = ['Received', 'Pending', 'Returned', 'Damaged'];

        for ($i = 0; $i < 20; $i++) {
            $quantityReceived = $faker->randomFloat(2, 10, 500);
            $ratePerUnit = $faker->randomFloat(2, 10, 200);
            $quantityUsed = $faker->randomFloat(2, 0, $quantityReceived);

            $totalCost = $quantityReceived * $ratePerUnit;
            $quantityRemaining = max($quantityReceived - $quantityUsed, 0);

            ConstructionMaterial::create([
                'material_name' => $faker->randomElement(['Cement', 'Sand', 'Steel Rod', 'Bricks', 'Concrete', 'Tiles']),
                'material_category' => $faker->randomElement(['Structural', 'Finishing', 'Foundation']),
                'unit' => $faker->randomElement($units),
                'quantity_received' => $quantityReceived,
                'rate_per_unit' => $ratePerUnit,
                'total_cost' => $totalCost,
                'quantity_used' => $quantityUsed,
                'quantity_remaining' => $quantityRemaining,
                'wastage_quantity' => $faker->randomFloat(2, 0, 10),
                'supplier_name' => $faker->company,
                'supplier_contact' => $faker->phoneNumber,
                'bill_number' => $faker->bothify('BILL-####'),
                'bill_date' => $faker->date(),
                'payment_status' => $faker->randomElement($paymentStatuses),
                'payment_mode' => $faker->randomElement(['Cash', 'Bank Transfer', 'Cheque']),
                'delivery_date' => $faker->date(),
                'delivery_site' => $faker->streetAddress,
                'delivered_by' => $faker->name,
                'received_by' => $faker->name,
                'project_name' => $faker->sentence(3),
                'work_type' => $faker->randomElement(['Foundation', 'Masonry', 'Slab', 'Finishing']),
                'usage_purpose' => $faker->sentence(8),
                'status' => $faker->randomElement($statuses),
                'approved_by' => $faker->name,
                'approval_date' => $faker->date(),
            ]);
        }
    }
}


