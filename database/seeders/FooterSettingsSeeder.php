<?php

declare(strict_types=1);

namespace Database\Seeders;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Illuminate\Database\Seeder;

class FooterSettingsSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::set('footer', [
            'description'          => 'Assessoria fiscal, laboral i comptable. Més de 30 anys acompanyant empreses i autònoms.',
            'phone'                => '+34 93 862 61 00',
            'email'                => 'agcassessors@agc.cat',
            'address'              => 'Av. Pi i Margall 114 · 08140 · Caldes de Montbui',
            'copyright'            => '© 2025 AGC Assessors. Tots els drets reservats.',
            'institutional_logos'  => [],
            'extra_links'          => [],
        ]);
    }
}
