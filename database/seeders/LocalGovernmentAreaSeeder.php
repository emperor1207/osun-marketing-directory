<?php

namespace Database\Seeders;

use App\Models\LocalGovernmentArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocalGovernmentAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['name' => 'Aiyedaade', 'headquarters' => 'Gbongan'],
            ['name' => 'Aiyedire', 'headquarters' => 'Ile-Ogbo'],
            ['name' => 'Atakunmosa East', 'headquarters' => 'Iperindo'],
            ['name' => 'Atakunmosa West', 'headquarters' => 'Osu'],
            ['name' => 'Boluwaduro', 'headquarters' => 'Otan-Ayegbaju'],
            ['name' => 'Boripe', 'headquarters' => 'Iragbiji'],
            ['name' => 'Ede North', 'headquarters' => 'Oja-Timi, Ede'],
            ['name' => 'Ede South', 'headquarters' => 'Oke-Iresi, Ede'],
            ['name' => 'Egbedore', 'headquarters' => 'Awo'],
            ['name' => 'Ejigbo', 'headquarters' => 'Ejigbo'],
            ['name' => 'Ife Central', 'headquarters' => 'Ile-Ife'],
            ['name' => 'Ife East', 'headquarters' => 'Oke-Ogbo'],
            ['name' => 'Ife North', 'headquarters' => 'Ipetumodu'],
            ['name' => 'Ife South', 'headquarters' => 'Ifetedo'],
            ['name' => 'Ifedayo', 'headquarters' => 'Oke-Ila Orangun'],
            ['name' => 'Ifelodun', 'headquarters' => 'Ikirun'],
            ['name' => 'Ila', 'headquarters' => 'Ila-Orangun'],
            ['name' => 'Ilesa East', 'headquarters' => 'Ilesa'],
            ['name' => 'Ilesa West', 'headquarters' => 'Ereja Square'],
            ['name' => 'Irepodun', 'headquarters' => 'Ilobu'],
            ['name' => 'Irewole', 'headquarters' => 'Ikire'],
            ['name' => 'Isokan', 'headquarters' => 'Apomu'],
            ['name' => 'Iwo', 'headquarters' => 'Iwo'],
            ['name' => 'Obokun', 'headquarters' => 'Ibokun'],
            ['name' => 'Odo Otin', 'headquarters' => 'Okuku'],
            ['name' => 'Ola Oluwa', 'headquarters' => 'Bode Osi'],
            ['name' => 'Olorunda', 'headquarters' => 'Igbona'],
            ['name' => 'Oriade', 'headquarters' => 'Ijebu-Jesa'],
            ['name' => 'Orolu', 'headquarters' => 'Ifon-Osun'],
            ['name' => 'Osogbo', 'headquarters' => 'Osogbo'],
        ];

        foreach ($areas as $area) {
            LocalGovernmentArea::firstOrCreate(
                ['name' => $area['name']],
                [
                    'slug' => Str::slug($area['name']),
                    'headquarters' => $area['headquarters'],
                    'is_active' => true,
                ]
            );
        }
    }
}
