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
                'slug' => 'home-intro',
                'type' => 'intro',
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'slug' => 'home-stats',
                'type' => 'stats',
                'sort_order' => 30,
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
                    'service_items' => [
                        ['service_slug' => 'assessoria-fiscal', 'icon' => 'balance'],
                        ['service_slug' => 'assessoria-laboral', 'icon' => 'work_outline'],
                        ['service_slug' => 'assessoria-comptable', 'icon' => 'monitoring'],
                        ['service_slug' => 'dret-mercantil', 'icon' => 'gavel'],
                        ['service_slug' => 'gestio-patrimonial', 'icon' => 'real_estate_agent'],
                        ['service_slug' => 'assessoria-internacional', 'icon' => 'groups'],
                    ],
                ],
                'sort_order' => 40,
                'is_active' => true,
            ],
            [
                'slug' => 'home-testimonials',
                'type' => 'testimonials',
                'title' => [
                    'ca' => 'Què diuen els nostres clients',
                    'es' => 'Qué dicen nuestros clientes',
                    'en' => 'What our clients say',
                ],
                'sort_order' => 50,
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
                'sort_order' => 60,
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
                'sort_order' => 70,
                'is_active' => true,
            ],
            [
                'slug' => 'offices-map',
                'type' => 'offices_map',
                'sort_order' => 80,
                'is_active' => true,
            ],
            // Keep carousel in DB but deactivated / at end — or just leave it as-is.
            // The carousel slug is updated below to sort_order 90 so it doesn't interfere.
            [
                'slug' => 'home-carousel',
                'type' => 'carousel',
                'sort_order' => 90,
                'is_active' => false,
            ],
        ];

        foreach ($sections as $data) {
            HomeSection::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
