<?php

declare(strict_types=1);

namespace Database\Seeders;

use AGC\Infrastructure\Persistence\Eloquent\Models\ServiceModel;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'slug' => 'assessoria-fiscal',
                'name' => ['ca' => 'Assessoria Fiscal', 'es' => 'Asesoría Fiscal', 'en' => 'Tax Advisory'],
                'description' => [
                    'ca' => '<p>Oferim un servei complet d\'assessoria fiscal per a empreses i autònoms. Gestionem les vostres obligacions tributàries amb rigor i anticipem els canvis normatius per minimitzar la càrrega fiscal.</p><ul><li>Declaració de l\'IRPF i Impost sobre Societats</li><li>IVA trimestral i anual</li><li>Planificació fiscal estratègica</li><li>Atenció a requeriments de l\'Agència Tributària</li></ul>',
                    'es' => '<p>Ofrecemos un servicio completo de asesoría fiscal para empresas y autónomos. Gestionamos sus obligaciones tributarias con rigor y anticipamos los cambios normativos para minimizar la carga fiscal.</p><ul><li>Declaración del IRPF e Impuesto sobre Sociedades</li><li>IVA trimestral y anual</li><li>Planificación fiscal estratégica</li><li>Atención a requerimientos de la Agencia Tributaria</li></ul>',
                    'en' => '<p>We offer a complete tax advisory service for businesses and freelancers. We manage your tax obligations with rigour and anticipate regulatory changes to minimise the tax burden.</p><ul><li>Personal and corporate income tax returns</li><li>Quarterly and annual VAT</li><li>Strategic tax planning</li><li>HMRC/Tax authority enquiry management</li></ul>',
                ],
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'slug' => 'assessoria-laboral',
                'name' => ['ca' => 'Assessoria Laboral', 'es' => 'Asesoría Laboral', 'en' => 'Labour Advisory'],
                'description' => [
                    'ca' => '<p>Gestionem tots els aspectes de les relacions laborals de la teva empresa. Des de la contractació fins a les nòmines, passant per expedients disciplinaris i acomiadaments.</p><ul><li>Confecció de nòmines i TC\'s</li><li>Contractes de treball i modificacions</li><li>Gestió de baixes i incapacitats</li><li>Assessorament en ERTOs i EREs</li></ul>',
                    'es' => '<p>Gestionamos todos los aspectos de las relaciones laborales de tu empresa. Desde la contratación hasta las nóminas, pasando por expedientes disciplinarios y despidos.</p><ul><li>Elaboración de nóminas y TCs</li><li>Contratos de trabajo y modificaciones</li><li>Gestión de bajas e incapacidades</li><li>Asesoramiento en ERTEs y EREs</li></ul>',
                    'en' => '<p>We manage all aspects of your company\'s employment relationships. From hiring to payroll, through disciplinary procedures and dismissals.</p><ul><li>Payroll processing</li><li>Employment contracts and amendments</li><li>Sick leave and disability management</li><li>Redundancy and restructuring advice</li></ul>',
                ],
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'slug' => 'assessoria-comptable',
                'name' => ['ca' => 'Assessoria Comptable', 'es' => 'Asesoría Contable', 'en' => 'Accounting Advisory'],
                'description' => [
                    'ca' => '<p>Portem la comptabilitat de la teva empresa de forma rigorosa i actualitzada. Els nostres serveis comptables garanteixen el compliment normatiu i ofereixen una visió clara de la situació financera.</p><ul><li>Comptabilitat general i analítica</li><li>Elaboració de comptes anuals</li><li>Conciliació bancària</li><li>Reporting mensual i indicadors clau</li></ul>',
                    'es' => '<p>Llevamos la contabilidad de tu empresa de forma rigurosa y actualizada. Nuestros servicios contables garantizan el cumplimiento normativo y ofrecen una visión clara de la situación financiera.</p><ul><li>Contabilidad general y analítica</li><li>Elaboración de cuentas anuales</li><li>Conciliación bancaria</li><li>Reporting mensual e indicadores clave</li></ul>',
                    'en' => '<p>We handle your company\'s accounting rigorously and up to date. Our accounting services guarantee regulatory compliance and offer a clear picture of the financial situation.</p><ul><li>General and analytical accounting</li><li>Annual accounts preparation</li><li>Bank reconciliation</li><li>Monthly reporting and KPIs</li></ul>',
                ],
                'sort_order' => 3,
                'active' => true,
            ],
            [
                'slug' => 'dret-mercantil',
                'name' => ['ca' => 'Dret Mercantil', 'es' => 'Derecho Mercantil', 'en' => 'Corporate Law'],
                'description' => [
                    'ca' => '<p>Assessorem en tots els aspectes jurídics de la teva empresa. Constitució de societats, contractes mercantils, fusions i adquisicions.</p><ul><li>Constitució i dissolució de societats</li><li>Redacció i revisió de contractes</li><li>Fusions, escissions i adquisicions</li><li>Pactes d\'accionistes</li></ul>',
                    'es' => '<p>Asesoramos en todos los aspectos jurídicos de tu empresa. Constitución de sociedades, contratos mercantiles, fusiones y adquisiciones.</p><ul><li>Constitución y disolución de sociedades</li><li>Redacción y revisión de contratos</li><li>Fusiones, escisiones y adquisiciones</li><li>Pactos de accionistas</li></ul>',
                    'en' => '<p>We advise on all legal aspects of your business. Company formation, commercial contracts, mergers and acquisitions.</p><ul><li>Company formation and dissolution</li><li>Contract drafting and review</li><li>Mergers, demergers and acquisitions</li><li>Shareholder agreements</li></ul>',
                ],
                'sort_order' => 4,
                'active' => true,
            ],
            [
                'slug' => 'gestio-patrimonial',
                'name' => ['ca' => 'Gestió Patrimonial', 'es' => 'Gestión Patrimonial', 'en' => 'Wealth Management'],
                'description' => [
                    'ca' => '<p>Optimitzem la gestió del teu patrimoni personal i empresarial, planificant la successió i minimitzant l\'impacte fiscal.</p><ul><li>Planificació successòria</li><li>Impost sobre el patrimoni</li><li>Impost de successions i donacions</li><li>Estructures patrimonials eficients</li></ul>',
                    'es' => '<p>Optimizamos la gestión de tu patrimonio personal y empresarial, planificando la sucesión y minimizando el impacto fiscal.</p><ul><li>Planificación sucesoria</li><li>Impuesto sobre el patrimonio</li><li>Impuesto de sucesiones y donaciones</li><li>Estructuras patrimoniales eficientes</li></ul>',
                    'en' => '<p>We optimise the management of your personal and business wealth, planning succession and minimising the tax impact.</p><ul><li>Succession planning</li><li>Wealth tax</li><li>Inheritance and gift tax</li><li>Efficient wealth structures</li></ul>',
                ],
                'sort_order' => 5,
                'active' => true,
            ],
            [
                'slug' => 'assessoria-internacional',
                'name' => ['ca' => 'Assessoria Internacional', 'es' => 'Asesoría Internacional', 'en' => 'International Advisory'],
                'description' => [
                    'ca' => '<p>Acompanyem empreses en la seva expansió internacional. Fiscalitat transfronterera, preus de transferència i estructures internacionals eficients.</p><ul><li>Fiscalitat de no residents</li><li>Preus de transferència</li><li>Convenis de doble imposició</li><li>Estructures internacionals</li></ul>',
                    'es' => '<p>Acompañamos a empresas en su expansión internacional. Fiscalidad transfronteriza, precios de transferencia y estructuras internacionales eficientes.</p><ul><li>Fiscalidad de no residentes</li><li>Precios de transferencia</li><li>Convenios de doble imposición</li><li>Estructuras internacionales</li></ul>',
                    'en' => '<p>We support companies in their international expansion. Cross-border taxation, transfer pricing and efficient international structures.</p><ul><li>Non-resident taxation</li><li>Transfer pricing</li><li>Double taxation treaties</li><li>International structures</li></ul>',
                ],
                'sort_order' => 6,
                'active' => true,
            ],
        ];

        foreach ($services as $data) {
            ServiceModel::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
