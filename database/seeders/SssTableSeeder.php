<?php
// database/seeders/SssTableSeeder.php
namespace Database\Seeders;

use App\Models\SssTable;
use Illuminate\Database\Seeder;

class SssTableSeeder extends Seeder
{
    private const ER_RATE = 0.10; // employer share of MSC
    private const EE_RATE = 0.05; // employee share of MSC

    private const MSC_MIN = 5000;
    private const MSC_MAX = 35000;
    private const MSC_STEP = 500;

    public function run(): void
    {
        SssTable::truncate();

        $rows = [];

        for ($msc = self::MSC_MIN; $msc <= self::MSC_MAX; $msc += self::MSC_STEP) {
            $from = $msc === self::MSC_MIN ? 0 : $msc - 250;
            $to   = $msc === self::MSC_MAX ? 999999.99 : $msc + 249.99; // last row = "and above"

            $rows[] = [
                'SalaryFrom'   => $from,
                'SalaryTo'     => $to,
                'ER'           => round($msc * self::ER_RATE, 2),
                'EE'           => round($msc * self::EE_RATE, 2),
                'RecordStatus' => 'Active',
            ];
        }

        SssTable::insert($rows);
    }
}