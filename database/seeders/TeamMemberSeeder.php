<?php

declare(strict_types=1);

namespace Database\Seeders;

use AGC\Infrastructure\Persistence\Eloquent\Models\TeamMemberModel;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name'       => 'Anna Garcia Castells',
                'email'      => 'anna.garcia@agcassessors.com',
                'role'       => ['ca' => 'Sòcia fundadora – Fiscal', 'es' => 'Socia fundadora – Fiscal', 'en' => 'Founding partner – Tax'],
                'bio'        => [
                    'ca' => 'Llicenciada en Dret per la UB i Màster en Fiscalitat Internacional. Més de 20 anys d\'experiència en assessoria fiscal d\'empreses i grans patrimonis.',
                    'es' => 'Licenciada en Derecho por la UB y Máster en Fiscalidad Internacional. Más de 20 años de experiencia en asesoría fiscal de empresas y grandes patrimonios.',
                    'en' => 'Law degree from UB and Master\'s in International Taxation. Over 20 years of experience in tax advisory for businesses and high-net-worth individuals.',
                ],
                'sort_order' => 1,
                'active'     => true,
            ],
            [
                'name'       => 'Jordi Bosch Puig',
                'email'      => 'jordi.bosch@agcassessors.com',
                'role'       => ['ca' => 'Soci – Laboral', 'es' => 'Socio – Laboral', 'en' => 'Partner – Labour'],
                'bio'        => [
                    'ca' => 'Graduat en Relacions Laborals i diplomat en Gestió i Administració Pública. Especialista en negociació col·lectiva i ERTOs.',
                    'es' => 'Graduado en Relaciones Laborales y diplomado en Gestión y Administración Pública. Especialista en negociación colectiva y ERTEs.',
                    'en' => 'Graduate in Labour Relations and qualified in Public Management and Administration. Specialist in collective bargaining and redundancy procedures.',
                ],
                'sort_order' => 2,
                'active'     => true,
            ],
            [
                'name'       => 'Marta Vidal Riu',
                'email'      => 'marta.vidal@agcassessors.com',
                'role'       => ['ca' => 'Responsable Comptable', 'es' => 'Responsable Contable', 'en' => 'Head of Accounting'],
                'bio'        => [
                    'ca' => 'Economista per la UAB i auditora de comptes. Gestiona la comptabilitat de més de 80 empreses clients amb especial atenció al sector industrial.',
                    'es' => 'Economista por la UAB y auditora de cuentas. Gestiona la contabilidad de más de 80 empresas clientes con especial atención al sector industrial.',
                    'en' => 'Economist from UAB and statutory auditor. Manages the accounting for over 80 client businesses with a special focus on the industrial sector.',
                ],
                'sort_order' => 3,
                'active'     => true,
            ],
            [
                'name'       => 'Pere Mas Domènech',
                'email'      => 'pere.mas@agcassessors.com',
                'role'       => ['ca' => 'Assessor Mercantil', 'es' => 'Asesor Mercantil', 'en' => 'Corporate Adviser'],
                'bio'        => [
                    'ca' => 'Advocat col·legiat al ICAB. Especialitzat en dret societari, fusions i adquisicions i reestructuracions empresarials.',
                    'es' => 'Abogado colegiado en el ICAB. Especializado en derecho societario, fusiones y adquisiciones y reestructuraciones empresariales.',
                    'en' => 'Registered solicitor with the ICAB. Specialised in company law, mergers and acquisitions, and business restructuring.',
                ],
                'sort_order' => 4,
                'active'     => true,
            ],
        ];

        foreach ($members as $data) {
            TeamMemberModel::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}
