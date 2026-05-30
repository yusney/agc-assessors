<?php

declare(strict_types=1);

namespace Database\Seeders;

use AGC\Infrastructure\Persistence\Eloquent\Models\HomeSection;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'slug' => 'home-hero',
                'type' => 'hero',
                'title' => [
                    'ca' => 'La teva <span class="text-[#00346f]">assessoria</span> de confiança',
                    'es' => 'Tu <span class="text-[#00346f]">asesoría</span> de confianza',
                    'en' => 'Your trusted <span class="text-[#00346f]">advisory</span> firm',
                ],
                'subtitle' => [
                    'ca' => 'Experts en gestió fiscal, laboral i comptable. T\'acompanyem en cada pas del teu negoci.',
                    'es' => 'Expertos en gestión fiscal, laboral y contable. Te acompañamos en cada paso de tu negocio.',
                    'en' => 'Experts in tax, labour and accounting management. We support you at every step of your business.',
                ],
                'cta_label' => [
                    'ca' => 'Sol·licitar consulta',
                    'es' => 'Solicitar consulta',
                    'en' => 'Request consultation',
                ],
                'cta_url' => '/contacte',
                'secondary_cta_label' => [
                    'ca' => 'Conèixer els serveis',
                    'es' => 'Ver los servicios',
                    'en' => 'Our services',
                ],
                'secondary_cta_url' => '/serveis',
                'image_url' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=900&auto=format&fit=crop',
                'settings' => ['image_alt' => ['ca' => 'Professionals en reunió', 'es' => 'Profesionales reunidos', 'en' => 'Professionals in a meeting']],
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'slug' => 'home-news',
                'type' => 'news_highlight',
                'title' => ['ca' => 'Actualitat', 'es' => 'Actualidad', 'en' => 'Latest news'],
                'subtitle' => [
                    'ca' => 'Novetats fiscals, laborals i empresarials.',
                    'es' => 'Novedades fiscales, laborales y empresariales.',
                    'en' => 'Tax, labour and business updates.',
                ],
                'cta_label' => ['ca' => 'Veure tot', 'es' => 'Ver todo', 'en' => 'View all'],
                'cta_url' => '/actualitat',
                'settings' => ['limit' => 3],
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'slug' => 'home-services',
                'type' => 'services_highlight',
                'title' => ['ca' => 'Els nostres serveis', 'es' => 'Nuestros servicios', 'en' => 'Our services'],
                'subtitle' => [
                    'ca' => 'Solucions integrals per a empreses i autònoms.',
                    'es' => 'Soluciones integrales para empresas y autónomos.',
                    'en' => 'Comprehensive solutions for businesses and freelancers.',
                ],
                'settings' => [
                    'icons' => ['balance', 'work_outline', 'monitoring', 'gavel', 'real_estate_agent', 'groups'],
                ],
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'slug' => 'home-carousel',
                'type' => 'carousel',
                'title' => [
                    'ca' => 'Assessorament amb mirada llarga',
                    'es' => 'Asesoramiento con visión de futuro',
                    'en' => 'Advice with a long-term view',
                ],
                'subtitle' => [
                    'ca' => 'Un espai visual per destacar campanyes, serveis o missatges estratègics des de Filament.',
                    'es' => 'Un espacio visual para destacar campañas, servicios o mensajes estratégicos desde Filament.',
                    'en' => 'A visual space to highlight campaigns, services or strategic messages from Filament.',
                ],
                'carousel_items' => [
                    [
                        'image_url' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1400&auto=format&fit=crop',
                        'eyebrow' => ['ca' => 'Fiscal', 'es' => 'Fiscal', 'en' => 'Tax'],
                        'title' => ['ca' => 'Planificació fiscal sense improvisar', 'es' => 'Planificación fiscal sin improvisar', 'en' => 'Tax planning without improvisation'],
                        'body' => ['ca' => 'Anticipem obligacions i oportunitats perquè cada decisió tingui context.', 'es' => 'Anticipamos obligaciones y oportunidades para que cada decisión tenga contexto.', 'en' => 'We anticipate obligations and opportunities so every decision has context.'],
                        'cta_label' => ['ca' => 'Parlem-ne', 'es' => 'Hablemos', 'en' => 'Let\'s talk'],
                        'cta_url' => '/contacte',
                    ],
                    [
                        'image_url' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=1400&auto=format&fit=crop',
                        'eyebrow' => ['ca' => 'Empresa', 'es' => 'Empresa', 'en' => 'Business'],
                        'title' => ['ca' => 'Decisions empresarials amb dades clares', 'es' => 'Decisiones empresariales con datos claros', 'en' => 'Business decisions with clear data'],
                        'body' => ['ca' => 'Comptabilitat, fiscalitat i gestió connectades per entendre millor el negoci.', 'es' => 'Contabilidad, fiscalidad y gestión conectadas para entender mejor el negocio.', 'en' => 'Accounting, tax and management connected to understand the business better.'],
                        'cta_label' => ['ca' => 'Veure serveis', 'es' => 'Ver servicios', 'en' => 'View services'],
                        'cta_url' => '/serveis',
                    ],
                ],
                'sort_order' => 40,
                'is_active' => true,
            ],
            [
                'slug' => 'home-newsletter',
                'type' => 'contact_cta',
                'title' => ['ca' => 'Mantén-te informat', 'es' => 'Mantente informado', 'en' => 'Stay informed'],
                'subtitle' => [
                    'ca' => 'Rep les novetats fiscals i laborals que afecten el teu negoci directament al correu.',
                    'es' => 'Recibe las novedades fiscales y laborales que afectan a tu negocio directamente en tu correo.',
                    'en' => 'Receive tax and labour updates that affect your business directly in your inbox.',
                ],
                'cta_label' => ['ca' => 'Subscriure\'m', 'es' => 'Suscribirme', 'en' => 'Subscribe'],
                'settings' => [
                    'mode' => 'newsletter',
                    'newsletter_placeholder' => ['ca' => 'El teu correu electrònic', 'es' => 'Tu correo electrónico', 'en' => 'Your email address'],
                    'newsletter_legal' => ['ca' => 'Sense spam. Pots cancel·lar en qualsevol moment.', 'es' => 'Sin spam. Puedes cancelar en cualquier momento.', 'en' => 'No spam. You can unsubscribe at any time.'],
                ],
                'sort_order' => 50,
                'is_active' => true,
            ],
        ];

        foreach ($sections as $data) {
            HomeSection::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
