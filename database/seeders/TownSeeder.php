<?php

namespace Database\Seeders;

use App\Models\LocalGovernmentArea;
use App\Models\Town;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TownSeeder extends Seeder
{
    public function run(): void
    {
        $townsByLga = [

            'Aiyedaade' => [
                'Gbongan',
                'Ode-Omu',
                'Orile-Owu',
                'Ogbagba',
                'Wakajaiye',
            ],

            'Aiyedire' => [
                'Ile-Ogbo',
                'Kuta',
                'Olupona',
                'Oke-Osun',
                'Alabata',
            ],

            'Atakunmosa East' => [
                'Iperindo',
                'Iwara',
                'Ipole',
                'Eti-Oni',
                'Ajebamidele',
                'Faforiji',
            ],

            'Atakunmosa West' => [
                'Osu',
                'Ibodi',
                'Ifewara',
                'Isaobi',
                'Itagunmodi',
                'Muroko',
                'Okebode',
            ],

            'Boluwaduro' => [
                'Otan-Ayegbaju',
                'Eripa',
                'Igbajo',
                'Iresi',
            ],

            'Boripe' => [
                'Iragbiji',
                'Iree',
                'Ada',
                'Aagba',
                'Ororuwo',
            ],

            'Ede North' => [
                'Ede',
            ],

            'Ede South' => [
                'Ede',
                'Sekona',
                'Alajue',
                'Owode-Ede',
            ],

            'Egbedore' => [
                'Awo',
                'Ara',
                'Ido-Osun',
                'Ikotun',
                'Iragberi',
                'Okinni',
            ],

            'Ejigbo' => [
                'Ejigbo',
                'Ife-Odan',
                'Ilawo',
                'Olla',
                'Isundunrin',
            ],

            'Ife Central' => [
                'Ile-Ife',
                'Ajebamidele',
                'Elefon-Owaa',
                'Ita-Oosa',
            ],

            'Ife East' => [
                'Oke-Ogbo',
                'Modakeke',
            ],

            'Ife North' => [
                'Ipetumodu',
                'Edunabon',
                'Ashipa',
                'Famia',
                'Moro',
                'Yakoyo',
            ],

            'Ife South' => [
                'Ifetedo',
                'Awolowo Town',
                'Omifunfun',
                'Olode',
                'Aye-Amodo',
            ],

            'Ifedayo' => [
                'Oke-Ila Orangun',
                'Ora',
            ],

            'Ifelodun' => [
                'Ikirun',
                'Eko-Ende',
                'Iba',
                'Obagun',
            ],

            'Ila' => [
                'Ila-Orangun',
                'Edemosi',
                'Ajaba',
            ],

            'Ilesa East' => [
                'Ilesa',
                'Iyemogun',
                'Ilerin',
                'Ayeso',
            ],

            'Ilesa West' => [
                'Ilesa',
                'Ereja',
                'Omi-Aladiye',
                'Imo',
                'Isokun',
            ],

            'Irepodun' => [
                'Ilobu',
                'Erin-Osun',
            ],

            'Irewole' => [
                'Ikire',
                'Wasinmi',
            ],

            'Isokan' => [
                'Apomu',
                'Ikoyi',
            ],

            'Iwo' => [
                'Iwo',
                'Agberire',
                'Olomowewe',
            ],

            'Obokun' => [
                'Ibokun',
                'Esa-Oke',
                'Imesi-Ile',
                'Esa-Odo',
                'Ilare',
                'Ipetu-Ile',
            ],

            'Odo Otin' => [
                'Okuku',
                'Oyan',
                'Inisha',
                'Igbaye',
                'Ekosin',
                'Asaba',
            ],

            'Ola Oluwa' => [
                'Bode-Osi',
                'Ilemowu',
                'Telemu',
                'Ogbaagba',
                'Asamu',
                'Ikonifin',
            ],

            'Olorunda' => [
                'Igbona',
                'Ota-Efun',
                'Oba-Ile',
                'Oba-Oke',
            ],

            'Oriade' => [
                'Ijebu-Jesa',
                'Ipetu-Ijesa',
                'Erinmo',
                'Erin-Ijesa',
                'Ikeji-Arakeji',
                'Ikeji-Ile',
                'Ijeda',
                'Iloko',
            ],

            'Orolu' => [
                'Ifon-Osun',
            ],

            'Osogbo' => [
                'Osogbo',
                'Isale-Osun',
                'Oke-Arungbo',
            ],
        ];

        foreach ($townsByLga as $lgaName => $townNames) {
            $lga = LocalGovernmentArea::where('name', $lgaName)
                ->firstOrFail();

            foreach ($townNames as $townName) {
                Town::firstOrCreate(
                    [
                        'local_government_area_id' => $lga->id,
                        'name' => $townName,
                    ],
                    [
                        'slug' => Str::slug($townName),
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
