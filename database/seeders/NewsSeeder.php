<?php

declare(strict_types=1);

namespace Database\Seeders;

use AGC\Infrastructure\Persistence\Eloquent\Models\NewsModel;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'slug' => 'novetats-fiscals-2026',
                'title' => [
                    'ca' => 'Principals novetats fiscals per al 2026',
                    'es' => 'Principales novedades fiscales para 2026',
                    'en' => 'Key tax changes for 2026',
                ],
                'excerpt' => [
                    'ca' => 'Repassem les principals modificacions tributàries que entraran en vigor al llarg del 2026 i com afectaran les empreses i autònoms.',
                    'es' => 'Repasamos las principales modificaciones tributarias que entrarán en vigor a lo largo del 2026 y cómo afectarán a las empresas y autónomos.',
                    'en' => 'We review the main tax changes coming into force in 2026 and how they will affect businesses and freelancers.',
                ],
                'body' => [
                    'ca' => '<p>El nou exercici trae amb si un conjunt de canvis normatius en matèria tributària que convé tenir presents. A continuació, detallem els més rellevants:</p><h2>Modificacions en l\'IRPF</h2><p>S\'amplien els trams de la base imposable i es revisen els mínims personals i familiars. Els contribuents amb rendes del treball per sota de 22.000 euros anualment veuran reduïda la seva càrrega impositiva.</p><h2>Impost sobre Societats</h2><p>S\'introdueix un tipus mínim del 15% per a grans empreses amb facturació superior a 750 milions d\'euros. Les PIMES mantenen el tipus general del 25%.</p><h2>IVA i operacions intracomunitàries</h2><p>Es reforcen els controls sobre les operacions intracomunitàries i s\'actualitzen els procediments de declaració del IVA en el marc de l\'OSS europeu.</p>',
                    'es' => '<p>El nuevo ejercicio trae consigo un conjunto de cambios normativos en materia tributaria que conviene tener presentes. A continuación, detallamos los más relevantes:</p><h2>Modificaciones en el IRPF</h2><p>Se amplían los tramos de la base imponible y se revisan los mínimos personales y familiares.</p><h2>Impuesto sobre Sociedades</h2><p>Se introduce un tipo mínimo del 15% para grandes empresas con facturación superior a 750 millones de euros. Las PYMES mantienen el tipo general del 25%.</p><h2>IVA y operaciones intracomunitarias</h2><p>Se refuerzan los controles sobre las operaciones intracomunitarias.</p>',
                    'en' => '<p>The new financial year brings a set of regulatory changes in tax matters that are worth keeping in mind. Below we detail the most relevant ones.</p><h2>Income tax changes</h2><p>The tax brackets have been widened and personal and family allowances revised.</p><h2>Corporate tax</h2><p>A minimum rate of 15% is introduced for large companies with turnover above €750 million. SMEs maintain the general rate of 25%.</p>',
                ],
                'published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'slug' => 'reforma-laboral-novetats',
                'title' => [
                    'ca' => 'Reforma laboral: Allò que cal saber el 2026',
                    'es' => 'Reforma laboral: Lo que hay que saber en 2026',
                    'en' => 'Labour reform: What you need to know in 2026',
                ],
                'excerpt' => [
                    'ca' => 'La reforma laboral consolida nous models de contractació i canvia les regles del joc en matèria d\'acomiadament i negociació col·lectiva.',
                    'es' => 'La reforma laboral consolida nuevos modelos de contratación y cambia las reglas del juego en materia de despido y negociación colectiva.',
                    'en' => 'The labour reform consolidates new hiring models and changes the rules on dismissal and collective bargaining.',
                ],
                'body' => [
                    'ca' => '<p>La reforma laboral ha transformat de manera significativa el mercat de treball. Analitzem els aspectes clau que afecten les empreses.</p><h2>Contractació temporal</h2><p>La limitació de la contractació temporal al 25% de la plantilla és ara d\'estricte compliment. Les empreses que la superin s\'enfronten a sancions creixents.</p><h2>Acomiadament i indemnitzacions</h2><p>S\'han revisat els criteris de l\'acomiadament objectiu i disciplinari. Les indemnitzacions s\'actualitzen anualment amb l\'IPC.</p>',
                    'es' => '<p>La reforma laboral ha transformado de manera significativa el mercado de trabajo. Analizamos los aspectos clave que afectan a las empresas.</p><h2>Contratación temporal</h2><p>La limitación de la contratación temporal al 25% de la plantilla es ahora de estricto cumplimiento.</p>',
                    'en' => '<p>The labour reform has significantly transformed the labour market. We analyse the key aspects that affect businesses.</p><h2>Temporary hiring</h2><p>The limitation on temporary contracts to 25% of the workforce is now strictly enforced.</p>',
                ],
                'published' => true,
                'published_at' => now()->subDays(12),
            ],
            [
                'slug' => 'digitalitzacio-pime-ajuts',
                'title' => [
                    'ca' => 'Digitalització a la PIME: Ajuts i bonificacions disponibles',
                    'es' => 'Digitalización en la PYME: Ayudas y bonificaciones disponibles',
                    'en' => 'SME digitalisation: Available grants and incentives',
                ],
                'excerpt' => [
                    'ca' => 'El Govern posa a disposició de les empreses un paquet d\'ajuts per a la transformació digital. Descobreix com accedir-hi.',
                    'es' => 'El Gobierno pone a disposición de las empresas un paquete de ayudas para la transformación digital. Descubre cómo acceder.',
                    'en' => 'The Government is making a package of digital transformation grants available to businesses. Find out how to access them.',
                ],
                'body' => [
                    'ca' => '<p>La transformació digital ja no és opcional per a les empreses. El Govern ha articulat un conjunt d\'instruments de finançament per facilitar aquest procés.</p><h2>Kit Digital</h2><p>El programa Kit Digital ofereix xecs digitals de fins a 12.000€ per a PIMES de menys de 50 treballadors. Les categories elegibles inclouen presència web, comerç electrònic, gestió de clients i ciberseguretat.</p><h2>Deduccions fiscals per R+D+i</h2><p>Les inversions en digitalització poden acollir-se a deduccions de fins al 25% en l\'Impost sobre Societats si es vinculen a projectes d\'innovació.</p>',
                    'es' => '<p>La transformación digital ya no es opcional para las empresas. El Gobierno ha articulado un conjunto de instrumentos de financiación para facilitar este proceso.</p><h2>Kit Digital</h2><p>El programa Kit Digital ofrece cheques digitales de hasta 12.000€ para PYMES de menos de 50 trabajadores.</p>',
                    'en' => '<p>Digital transformation is no longer optional for businesses. The Government has put together a set of financing instruments to facilitate this process.</p><h2>Digital Kit</h2><p>The Digital Kit programme offers digital vouchers of up to €12,000 for SMEs with fewer than 50 employees.</p>',
                ],
                'published' => true,
                'published_at' => now()->subDays(20),
            ],
            [
                'slug' => 'societat-limitada-vs-autonomia',
                'title' => [
                    'ca' => 'SL vs Autònom: Quin règim t\'interessa el 2026?',
                    'es' => 'SL vs Autónomo: ¿Qué régimen te conviene en 2026?',
                    'en' => 'Limited company vs sole trader: Which suits you in 2026?',
                ],
                'excerpt' => [
                    'ca' => 'Analitzem els avantatges i inconvenients de cada forma jurídica tenint en compte la fiscalitat i els costos de seguretat social actualitzats.',
                    'es' => 'Analizamos las ventajas e inconvenientes de cada forma jurídica teniendo en cuenta la fiscalidad y los costes de seguridad social actualizados.',
                    'en' => 'We analyse the pros and cons of each legal structure taking into account updated taxation and social security costs.',
                ],
                'body' => [
                    'ca' => '<p>Una de les decisions més importants per a un professional o empresari és triar la forma jurídica adequada. Aquesta decisió té implicacions fiscals, laborals i patrimonials rellevants.</p><h2>Avantatges de l\'autònom</h2><p>La simplicitat administrativa, el menor cost de constitució i la flexibilitat operativa fan de l\'autònom una opció atractiva per a qui comença.</p><h2>Avantatges de la Societat Limitada</h2><p>La limitació de responsabilitat, l\'optimització fiscal a partir de certs nivells de benefici i la imatge professional justifiquen la constitució d\'una SL.</p>',
                    'es' => '<p>Una de las decisiones más importantes para un profesional o empresario es elegir la forma jurídica adecuada.</p><h2>Ventajas del autónomo</h2><p>La simplicidad administrativa y la flexibilidad operativa hacen del autónomo una opción atractiva para quien empieza.</p>',
                    'en' => '<p>One of the most important decisions for a professional or business owner is choosing the right legal structure.</p><h2>Sole trader advantages</h2><p>Administrative simplicity and operational flexibility make the sole trader an attractive option for those starting out.</p>',
                ],
                'published' => true,
                'published_at' => now()->subDays(30),
            ],
            [
                'slug' => 'irpf-declaracio-renda-2025',
                'title' => [
                    'ca' => 'Declaració de la Renda 2025: Dates i novetats',
                    'es' => 'Declaración de la Renta 2025: Fechas y novedades',
                    'en' => 'Income Tax Return 2025: Dates and changes',
                ],
                'excerpt' => [
                    'ca' => 'S\'acosta la campanya de la Renda 2025. Repasem les dates clau, les novetats i els aspectes a tenir en compte per optimitzar la declaració.',
                    'es' => 'Se acerca la campaña de la Renta 2025. Repasamos las fechas clave, las novedades y los aspectos a tener en cuenta para optimizar la declaración.',
                    'en' => 'The 2025 tax return campaign is approaching. We review the key dates, changes and points to consider to optimise your return.',
                ],
                'body' => [
                    'ca' => '<p>La campanya de la declaració de l\'IRPF 2025 arrenca el proper mes d\'abril. Prepara\'t amb antelació per evitar sorpreses.</p><h2>Calendari clau</h2><ul><li>Abril: Inici de la campanya, confirmació de l\'esborrany</li><li>Juny: Data límit per a domiciliació bancària</li><li>Juliol: Fi de la campanya presencial</li></ul><h2>Novetats per al 2025</h2><p>S\'amplia la deducció per habitatge habitual per a joves de menys de 35 anys. S\'actualitzen els límits de reducció per aportacions a plans de pensions.</p>',
                    'es' => '<p>La campaña de la declaración del IRPF 2025 arranca el próximo mes de abril.</p><h2>Calendario clave</h2><ul><li>Abril: Inicio de la campaña</li><li>Junio: Fecha límite para domiciliación bancaria</li><li>Julio: Fin de la campaña presencial</li></ul>',
                    'en' => '<p>The 2025 income tax return campaign starts next April.</p><h2>Key calendar</h2><ul><li>April: Campaign start</li><li>June: Direct debit deadline</li><li>July: End of in-person campaign</li></ul>',
                ],
                'published' => true,
                'published_at' => now()->subDays(45),
            ],
            [
                'slug' => 'factura-electronica-obligatoria',
                'title' => [
                    'ca' => 'Factura electrònica obligatòria: Quan i com afecta l\'empresa',
                    'es' => 'Factura electrónica obligatoria: Cuándo y cómo afecta a la empresa',
                    'en' => 'Mandatory e-invoicing: When and how it affects your business',
                ],
                'excerpt' => [
                    'ca' => 'La implantació de la factura electrònica obligatòria avança. Coneix els terminis i els requisits tècnics per al teu negoci.',
                    'es' => 'La implantación de la factura electrónica obligatoria avanza. Conoce los plazos y los requisitos técnicos para tu negocio.',
                    'en' => 'Mandatory e-invoicing is rolling out. Find out the deadlines and technical requirements for your business.',
                ],
                'body' => [
                    'ca' => '<p>La Llei Crea i Creix estableix l\'obligatorietat de la facturació electrònica entre empreses. La seva implantació es farà de forma progressiva.</p><h2>Terminis</h2><p>Les empreses amb facturació superior a 8 milions d\'euros han d\'adaptar-se en un termini de 12 mesos des de l\'aprovació del Reglament tècnic. La resta tindran 24 mesos.</p><h2>Requisits tècnics</h2><p>Les factures hauran d\'emetre\'s en format estructurat (XML/Facturae) i complir amb els requisits de segell electrònic i registre.</p>',
                    'es' => '<p>La Ley Crea y Crece establece la obligatoriedad de la facturación electrónica entre empresas.</p><h2>Plazos</h2><p>Las empresas con facturación superior a 8 millones de euros deben adaptarse en un plazo de 12 meses.</p>',
                    'en' => '<p>The Crea y Crece Law establishes mandatory e-invoicing between businesses.</p><h2>Deadlines</h2><p>Companies with turnover above €8 million must adapt within 12 months.</p>',
                ],
                'published' => true,
                'published_at' => now()->subDays(60),
            ],
        ];

        foreach ($articles as $data) {
            NewsModel::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
