<?php

namespace Happytodev\Blogr\Database\Seeders;

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Enums\CmsPageTemplate;
use Illuminate\Database\Seeder;

class CmsPageSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Create Home Page
        $this->createHomePage();

        // Create Contact Page
        $this->createContactPage();
    }

    /**
     * Create a modern homepage showcasing Blogr features
     */
    private function createHomePage(): void
    {
        $page = CmsPage::updateOrCreate(
            ['slug' => 'home-page'],
            [
                'template' => CmsPageTemplate::LANDING->value,
                'is_published' => true,
                'published_at' => now(),
                'is_homepage' => true,
                'default_locale' => 'en',
                'blocks' => $this->getHomePageBlocksEN(),
            ]
        );

        // English translation
        $page->translations()->updateOrCreate(
            ['locale' => 'en'],
            [
                'slug' => 'home',
                'title' => 'Welcome to Blogr',
                'meta_title' => 'Blogr — Multilingual CMS for Laravel & FilamentPHP',
                'meta_description' => 'A translation-first CMS built on Laravel 12 and FilamentPHP v4. Create, manage, and scale multilingual content with 24+ block types.',
                'meta_keywords' => 'blog, CMS, multilingual, Laravel, FilamentPHP, translation-first',
                'content' => '',
                'blocks' => $this->getHomePageBlocksEN(),
            ]
        );

        // French translation
        $page->translations()->updateOrCreate(
            ['locale' => 'fr'],
            [
                'slug' => 'accueil',
                'title' => 'Bienvenue sur Blogr',
                'meta_title' => 'Blogr — CMS Multilingue pour Laravel & FilamentPHP',
                'meta_description' => 'Un CMS traduction-first construit sur Laravel 12 et FilamentPHP v4. Créez, gérez et passez à l\'échelle du contenu multilingue avec 24+ types de blocs.',
                'meta_keywords' => 'blog, CMS, multilingue, Laravel, FilamentPHP, traduction-first',
                'content' => '',
                'blocks' => $this->getHomePageBlocksFR(),
            ]
        );
    }

    /**
     * Create a contact page
     */
    private function createContactPage(): void
    {
        $page = CmsPage::updateOrCreate(
            ['slug' => 'contact'],
            [
                'template' => CmsPageTemplate::CONTACT->value,
                'is_published' => true,
                'published_at' => now(),
                'is_homepage' => false,
                'default_locale' => 'en',
                'blocks' => $this->getContactPageBlocksEN(),
            ]
        );

        // English translation
        $page->translations()->updateOrCreate(
            ['locale' => 'en'],
            [
                'slug' => 'contact',
                'title' => 'Get in Touch',
                'meta_title' => 'Contact Us - Blogr',
                'meta_description' => 'Have questions? Get in touch with our team. We\'d love to hear from you!',
                'meta_keywords' => 'contact, support, help',
                'content' => '# Contact Blogr

We are always excited to hear from you. Whether you have a question about features, pricing, or anything else, our team is ready to answer all your questions.',
                'blocks' => $this->getContactPageBlocksEN(),
            ]
        );

        // French translation
        $page->translations()->updateOrCreate(
            ['locale' => 'fr'],
            [
                'slug' => 'contact',
                'title' => 'Nous Contacter',
                'meta_title' => 'Contactez-nous — Blogr',
                'meta_description' => 'Conçu avec soin à Grasse, la capitale mondiale du parfum. Contactez notre équipe.',
                'meta_keywords' => 'contact, support, aide, grasse, parfum',
                'content' => '# Contactez Blogr

Nous sommes toujours ravis de vous entendre. Que vous ayez une question sur les fonctionnalités, la tarification ou autre chose, notre équipe est prête à répondre à toutes vos questions.',
                'blocks' => $this->getContactPageBlocksFR(),
            ]
        );

        // Spanish translation
        $page->translations()->updateOrCreate(
            ['locale' => 'es'],
            [
                'slug' => 'contacto',
                'title' => 'Contáctenos',
                'meta_title' => 'Contáctenos — Blogr',
                'meta_description' => 'Hecho con amor en Grasse, la capital mundial del perfume. Póngase en contacto con nuestro equipo.',
                'meta_keywords' => 'contacto, soporte, ayuda, grasse, perfume',
                'content' => '# Contacte a Blogr

Siempre estamos encantados de saber de usted. Si tiene una pregunta sobre las funciones, los precios o cualquier otra cosa, nuestro equipo está listo para responder a todas sus preguntas.',
                'blocks' => $this->getContactPageBlocksES(),
            ]
        );

        // Polish translation
        $page->translations()->updateOrCreate(
            ['locale' => 'pl'],
            [
                'slug' => 'kontakt',
                'title' => 'Skontaktuj się z nami',
                'meta_title' => 'Kontakt — Blogr',
                'meta_description' => 'Wykonane z miłością w Grasse, światowej stolicy perfum. Skontaktuj się z naszym zespołem.',
                'meta_keywords' => 'kontakt, wsparcie, pomoc, grasse, perfumy',
                'content' => '# Skontaktuj się z Blogr

Zawsze chętnie słyszymy od Ciebie. Niezależnie od tego, czy masz pytanie dotyczące funkcji, cen, czy czegokolwiek innego, nasz zespół jest gotowy odpowiedzieć na wszystkie Twoje pytania.',
                'blocks' => $this->getContactPageBlocksPL(),
            ]
        );
    }

    private function getHomePageBlocksEN(): array
    {
        return [
            // Hero — dark enough for white text in both modes
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Blogr',
                    'subtitle' => 'A multilingual CMS for Laravel & FilamentPHP — natively designed for global content.',
                    'cta_text' => 'Read the Blog',
                    'cta_link_type' => 'blog',
                    'cta_url' => null,
                    'cta_category_id' => null,
                    'cta_cms_page_id' => null,
                    'alignment' => 'center',
                    'background_type' => 'gradient',
                    'gradient_from' => '#4f46e5',
                    'gradient_to' => '#7c3aed',
                    'gradient_direction' => 'to-br',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#0f0c29',
                    'gradient_to_dark' => '#302b63',
                    'gradient_direction_dark' => 'to-br',
                    'text_shadow' => true,
                    'shadow_intensity' => 'medium',
                ],
            ],

            // Stats — light bg in light mode, dark bg in dark mode
            [
                'type' => 'stats',
                'data' => [
                    'heading' => 'Built for content, engineered for scale',
                    'stats' => [
                        ['number' => 24, 'suffix' => '+', 'label' => 'Block Types'],
                        ['number' => 25, 'suffix' => '+', 'label' => 'Supported Languages'],
                        ['number' => 100, 'suffix' => '%', 'label' => 'Open Source'],
                        ['number' => 12, 'suffix' => '.x', 'label' => 'Laravel Native'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                ],
            ],

            // Features — light bg in light mode, dark bg in dark mode
            [
                'type' => 'features',
                'data' => [
                    'title' => "Everything you need, nothing you don't",
                    'subtitle' => 'Translation-first architecture, FilamentPHP v4 admin, and a modern Laravel 12 stack.',
                    'columns' => '3',
                    'items' => [
                        [
                            'icon' => 'heroicon-o-globe-alt',
                            'title' => 'Translation-First',
                            'description' => 'Every entity — posts, pages, categories, tags, series — is multilingual by design, not as an afterthought.',
                        ],
                        [
                            'icon' => 'heroicon-o-squares-2x2',
                            'title' => '24+ Content Blocks',
                            'description' => 'Hero, features, testimonials, pricing, gallery, timeline, FAQ, team, video, newsletter, maps, and more.',
                        ],
                        [
                            'icon' => 'heroicon-o-shield-check',
                            'title' => 'FilamentPHP v4 Admin',
                            'description' => 'Full-featured admin panel with role-based access, audit logs, and a beautiful block-based page builder.',
                        ],
                        [
                            'icon' => 'heroicon-o-magnifying-glass',
                            'title' => 'SEO Optimized',
                            'description' => 'Automatic XML sitemaps, canonical URLs, structured data, meta tags, and locale-aware routing.',
                        ],
                        [
                            'icon' => 'heroicon-o-cube',
                            'title' => 'Custom Templates',
                            'description' => '7 page templates: landing, about, pricing, FAQ, contact, custom, and default — each with tailored blocks.',
                        ],
                        [
                            'icon' => 'heroicon-o-arrow-path',
                            'title' => 'Import / Export',
                            'description' => 'Full content portability with JSON export/import. Backup, restore, and migrate your content freely.',
                        ],
                        [
                            'icon' => 'heroicon-o-clock',
                            'title' => 'Content Scheduling',
                            'description' => 'Schedule posts for automatic publication across time zones. Draft, review, and publish workflows.',
                        ],
                        [
                            'icon' => 'heroicon-o-code-bracket',
                            'title' => 'Developer Friendly',
                            'description' => 'PHP 8.3+, Laravel 12, Tailwind CSS 4, Vite, Pest tests, and Playwright E2E — fully open source.',
                        ],
                        [
                            'icon' => 'heroicon-o-tag',
                            'title' => 'Categories & Tags',
                            'description' => 'Multi-level categorization with per-locale slugs. Tags auto-sort alphabetically. Full pivot relationships.',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f8fafc',
                    'heading_color' => '#0f172a',
                    'text_color' => '#334155',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#0f172a',
                    'heading_color_dark' => '#f1f5f9',
                    'text_color_dark' => '#cbd5e1',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],

            // Blog Posts — light bg in light mode, dark bg in dark mode
            [
                'type' => 'blog_posts',
                'data' => [
                    'heading' => 'Latest from the blog',
                    'limit' => 3,
                    'layout' => 'grid',
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#f1f5f9',
                    'text_color_dark' => '#94a3b8',
                ],
            ],

            // Timeline — light bg in light mode, dark bg in dark mode
            [
                'type' => 'timeline',
                'data' => [
                    'heading' => 'Roadmap',
                    'events' => [
                        [
                            'date' => 'v0.20 — 2026',
                            'title' => 'CMS Pages & Blocks',
                            'description' => 'Full page builder with 24+ block types, 7 templates, transitions, and dark mode support.',
                        ],
                        [
                            'date' => 'v0.15 — 2025',
                            'title' => 'Multilingual Everywhere',
                            'description' => 'Translation-first architecture: all entities support per-locale slugs, SEO, and content.',
                        ],
                        [
                            'date' => 'v0.10 — 2025',
                            'title' => 'FilamentPHP v4 Upgrade',
                            'description' => 'Migrated to Filament v4 with new Schema system, improved navigation, and panel structure.',
                        ],
                        [
                            'date' => 'v0.1 — 2024',
                            'title' => 'Initial Release',
                            'description' => 'First public release — Laravel blog plugin with basic posts, categories, and tags.',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f8fafc',
                    'heading_color' => '#0f172a',
                    'text_color' => '#334155',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#0f172a',
                    'heading_color_dark' => '#f1f5f9',
                    'text_color_dark' => '#cbd5e1',
                ],
            ],

            // CTA — dark enough for white button text in both modes
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Start building in multiple languages today.',
                    'subheading' => 'Open source and MIT licensed. No credit card required.',
                    'button_text' => 'Get Started',
                    'button_link_type' => 'blog',
                    'button_url' => null,
                    'button_category_id' => null,
                    'button_cms_page_id' => null,
                    'button_style' => 'primary',
                    'background_type' => 'gradient',
                    'gradient_from' => '#4f46e5',
                    'gradient_to' => '#7c3aed',
                    'gradient_direction' => 'to-l',
                    'heading_color' => '#ffffff',
                    'subtitle_color' => '#e0e7ff',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#24243e',
                    'gradient_to_dark' => '#0f0c29',
                    'gradient_direction_dark' => 'to-l',
                    'heading_color_dark' => '#f1f5f9',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
        ];
    }

    private function getHomePageBlocksFR(): array
    {
        return [
            // Hero
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Blogr',
                    'subtitle' => 'Un CMS multilingue pour Laravel & FilamentPHP — conçu nativement pour le contenu global.',
                    'cta_text' => 'Lire le Blog',
                    'cta_link_type' => 'blog',
                    'cta_url' => null,
                    'cta_category_id' => null,
                    'cta_cms_page_id' => null,
                    'alignment' => 'center',
                    'background_type' => 'gradient',
                    'gradient_from' => '#4f46e5',
                    'gradient_to' => '#7c3aed',
                    'gradient_direction' => 'to-br',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#0f0c29',
                    'gradient_to_dark' => '#302b63',
                    'gradient_direction_dark' => 'to-br',
                    'text_shadow' => true,
                    'shadow_intensity' => 'medium',
                ],
            ],

            // Stats
            [
                'type' => 'stats',
                'data' => [
                    'heading' => "Conçu pour le contenu, architecturé pour l'échelle",
                    'stats' => [
                        ['number' => 24, 'suffix' => '+', 'label' => 'Types de Blocs'],
                        ['number' => 25, 'suffix' => '+', 'label' => 'Langues Supportées'],
                        ['number' => 100, 'suffix' => '%', 'label' => 'Open Source'],
                        ['number' => 12, 'suffix' => '.x', 'label' => 'Laravel Natif'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                ],
            ],

            // Features
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Tout ce dont vous avez besoin, rien de superflu',
                    'subtitle' => 'Architecture traduction-first, admin FilamentPHP v4, et une stack Laravel 12 moderne.',
                    'columns' => '3',
                    'items' => [
                        [
                            'icon' => 'heroicon-o-globe-alt',
                            'title' => 'Traduction-First',
                            'description' => 'Chaque entité — posts, pages, catégories, tags, séries — est multilingue par conception, pas par ajout.',
                        ],
                        [
                            'icon' => 'heroicon-o-squares-2x2',
                            'title' => '24+ Blocs de Contenu',
                            'description' => 'Hero, fonctionnalités, témoignages, tarifs, galerie, timeline, FAQ, équipe, vidéo, newsletter, cartes et plus.',
                        ],
                        [
                            'icon' => 'heroicon-o-shield-check',
                            'title' => 'Admin FilamentPHP v4',
                            'description' => "Panneau d'administration complet avec accès basé sur les rôles, journaux d'audit et constructeur de pages par blocs.",
                        ],
                        [
                            'icon' => 'heroicon-o-magnifying-glass',
                            'title' => 'Optimisé SEO',
                            'description' => 'Sitemaps XML automatiques, URLs canoniques, données structurées, balises meta et routage par locale.',
                        ],
                        [
                            'icon' => 'heroicon-o-cube',
                            'title' => 'Templates Personnalisés',
                            'description' => '7 templates : landing, about, pricing, FAQ, contact, custom et default — chacun avec ses blocs dédiés.',
                        ],
                        [
                            'icon' => 'heroicon-o-arrow-path',
                            'title' => 'Import / Export',
                            'description' => 'Portabilité totale du contenu avec export/import JSON. Sauvegardez, restaurez et migrez librement.',
                        ],
                        [
                            'icon' => 'heroicon-o-clock',
                            'title' => 'Planification de Contenu',
                            'description' => 'Programmez les publications automatiquement par fuseau horaire. Workflows de brouillon, relecture et publication.',
                        ],
                        [
                            'icon' => 'heroicon-o-code-bracket',
                            'title' => 'Développeur Friendly',
                            'description' => 'PHP 8.3+, Laravel 12, Tailwind CSS 4, Vite, tests Pest et Playwright E2E — 100% open source.',
                        ],
                        [
                            'icon' => 'heroicon-o-tag',
                            'title' => 'Catégories & Tags',
                            'description' => 'Catégorisation multi-niveaux avec slugs par locale. Tri alphabétique automatique des tags. Relations pivot complètes.',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f8fafc',
                    'heading_color' => '#0f172a',
                    'text_color' => '#334155',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#0f172a',
                    'heading_color_dark' => '#f1f5f9',
                    'text_color_dark' => '#cbd5e1',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],

            // Blog Posts
            [
                'type' => 'blog_posts',
                'data' => [
                    'heading' => 'Derniers articles',
                    'limit' => 3,
                    'layout' => 'grid',
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#f1f5f9',
                    'text_color_dark' => '#94a3b8',
                ],
            ],

            // Timeline
            [
                'type' => 'timeline',
                'data' => [
                    'heading' => 'Feuille de route',
                    'events' => [
                        [
                            'date' => 'v0.20 — 2026',
                            'title' => 'Pages CMS & Blocs',
                            'description' => 'Constructeur de page complet avec 24+ types de blocs, 7 templates, transitions et support dark mode.',
                        ],
                        [
                            'date' => 'v0.15 — 2025',
                            'title' => 'Multilingue Partout',
                            'description' => 'Architecture traduction-first : toutes les entités supportent les slugs, le SEO et le contenu par locale.',
                        ],
                        [
                            'date' => 'v0.10 — 2025',
                            'title' => 'Migration FilamentPHP v4',
                            'description' => 'Migration vers Filament v4 avec le nouveau système Schema, navigation améliorée et structure du panneau.',
                        ],
                        [
                            'date' => 'v0.1 — 2024',
                            'title' => 'Première Release',
                            'description' => 'Première publication publique — plugin blog Laravel avec posts, catégories et tags de base.',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f8fafc',
                    'heading_color' => '#0f172a',
                    'text_color' => '#334155',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#0f172a',
                    'heading_color_dark' => '#f1f5f9',
                    'text_color_dark' => '#cbd5e1',
                ],
            ],

            // CTA
            [
                'type' => 'cta',
                'data' => [
                    'heading' => "Construisez en plusieurs langues dès aujourd'hui.",
                    'subheading' => 'Open source sous licence MIT. Aucune carte de crédit requise.',
                    'button_text' => 'Commencer',
                    'button_link_type' => 'blog',
                    'button_url' => null,
                    'button_category_id' => null,
                    'button_cms_page_id' => null,
                    'button_style' => 'primary',
                    'background_type' => 'gradient',
                    'gradient_from' => '#4f46e5',
                    'gradient_to' => '#7c3aed',
                    'gradient_direction' => 'to-l',
                    'heading_color' => '#ffffff',
                    'subtitle_color' => '#e0e7ff',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#24243e',
                    'gradient_to_dark' => '#0f0c29',
                    'gradient_direction_dark' => 'to-l',
                    'heading_color_dark' => '#f1f5f9',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
        ];
    }

    /**
     * Get contact page blocks (English)
     */
    private function getContactPageBlocksEN(): array
    {
        return [
            // Hero
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Get in Touch',
                    'subtitle' => 'Built with care in Grasse — the world capital of perfume. We\'d love to hear from you.',
                    'cta_text' => 'Send a Message',
                    'cta_link_type' => 'external',
                    'cta_url' => '#contact-form',
                    'cta_category_id' => null,
                    'cta_cms_page_id' => null,
                    'alignment' => 'center',
                    'background_type' => 'gradient',
                    'gradient_from' => '#0f0c29',
                    'gradient_to' => '#302b63',
                    'gradient_direction' => 'to-br',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#0f0c29',
                    'gradient_to_dark' => '#24243e',
                    'gradient_direction_dark' => 'to-br',
                    'text_shadow' => true,
                    'shadow_intensity' => 'medium',
                ],
            ],

            // Stats
            [
                'type' => 'stats',
                'data' => [
                    'heading' => 'Built with passion in the South of France',
                    'stats' => [
                        ['number' => 25, 'suffix' => '+', 'label' => 'Languages Supported'],
                        ['number' => 24, 'suffix' => '+', 'label' => 'Content Blocks'],
                        ['number' => 100, 'suffix' => '%', 'label' => 'Open Source'],
                        ['number' => 12, 'suffix' => '.x', 'label' => 'Laravel Native'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                ],
            ],

            // Interactive Map — Grasse, France
            [
                'type' => 'map',
                'data' => [
                    'heading' => 'Find Us in Grasse',
                    'subtitle' => 'Nestled in the hills of the French Riviera, where perfume has been an art for centuries.',
                    'tagline' => 'Made with love in the world capital of perfume',
                    'tagline_position' => 'bottom',
                    'center_lat' => 43.6589,
                    'center_lng' => 6.9252,
                    'zoom' => 15,
                    'height' => 480,
                    'markers' => [
                        [
                            'lat' => 43.6589,
                            'lng' => 6.9252,
                            'popup_text' => '📍 Grasse — World Capital of Perfume',
                        ],
                        [
                            'lat' => 43.6580,
                            'lng' => 6.9200,
                            'popup_text' => '🏭 Fragonard Perfume Factory',
                        ],
                        [
                            'lat' => 43.6600,
                            'lng' => 6.9300,
                            'popup_text' => '🌿 Molinard Perfume House (est. 1849)',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f8fafc',
                    'heading_color' => '#0f172a',
                    'text_color' => '#334155',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#0f172a',
                    'heading_color_dark' => '#f1f5f9',
                    'text_color_dark' => '#cbd5e1',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],

            // Contact Form
            [
                'type' => 'contact_form',
                'data' => [
                    'heading' => 'Send Us a Message',
                    'subtitle' => "We'll get back to you within 24 hours.",
                    'submit_text' => 'Send Message',
                    'success_message' => 'Thank you! Your message has been sent.',
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],

            // Contact Info Features
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Find Us Online',
                    'subtitle' => 'We respond faster than a Grasse perfumer can name a fragrance note.',
                    'columns' => '3',
                    'items' => [
                        [
                            'icon' => 'heroicon-o-envelope',
                            'title' => 'Email Us',
                            'description' => 'Send an email and we\'ll get back to you within 24 hours — even on weekends.',
                        ],
                        [
                            'icon' => 'heroicon-o-x-mark',
                            'title' => 'X (formerly Twitter)',
                            'description' => 'Follow us @blogr for the latest news, releases, and behind-the-scenes updates.',
                        ],
                        [
                            'icon' => 'heroicon-o-chat-bubble-oval-left-ellipsis',
                            'title' => 'Bluesky',
                            'description' => 'Find us on Bluesky for community discussions, tips, and support.',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],

            // CTA
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Let\'s build something beautiful together.',
                    'subheading' => 'Open source and MIT licensed. No credit card required.',
                    'button_text' => 'Get Started',
                    'button_link_type' => 'blog',
                    'button_url' => null,
                    'button_category_id' => null,
                    'button_cms_page_id' => null,
                    'button_style' => 'primary',
                    'background_type' => 'gradient',
                    'gradient_from' => '#4f46e5',
                    'gradient_to' => '#7c3aed',
                    'gradient_direction' => 'to-l',
                    'heading_color' => '#ffffff',
                    'subtitle_color' => '#e0e7ff',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#24243e',
                    'gradient_to_dark' => '#0f0c29',
                    'gradient_direction_dark' => 'to-l',
                    'heading_color_dark' => '#f1f5f9',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
        ];
    }

    private function getContactPageBlocksFR(): array
    {
        return [
            // Hero
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Contactez-Nous',
                    'subtitle' => 'Conçu avec soin à Grasse — la capitale mondiale du parfum. Nous serions ravis de vous entendre.',
                    'cta_text' => 'Envoyer un Message',
                    'cta_link_type' => 'external',
                    'cta_url' => '#contact-form',
                    'cta_category_id' => null,
                    'cta_cms_page_id' => null,
                    'alignment' => 'center',
                    'background_type' => 'gradient',
                    'gradient_from' => '#0f0c29',
                    'gradient_to' => '#302b63',
                    'gradient_direction' => 'to-br',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#0f0c29',
                    'gradient_to_dark' => '#24243e',
                    'gradient_direction_dark' => 'to-br',
                    'text_shadow' => true,
                    'shadow_intensity' => 'medium',
                ],
            ],

            // Stats
            [
                'type' => 'stats',
                'data' => [
                    'heading' => 'Développé avec passion dans le Sud de la France',
                    'stats' => [
                        ['number' => 25, 'suffix' => '+', 'label' => 'Langues Supportées'],
                        ['number' => 24, 'suffix' => '+', 'label' => 'Blocs de Contenu'],
                        ['number' => 100, 'suffix' => '%', 'label' => 'Open Source'],
                        ['number' => 12, 'suffix' => '.x', 'label' => 'Laravel Natif'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                ],
            ],

            // Interactive Map — Grasse, France
            [
                'type' => 'map',
                'data' => [
                    'heading' => 'Retrouvez-nous à Grasse',
                    'subtitle' => 'Nichée dans les collines de la Riviera française, où le parfum est un art depuis des siècles.',
                    'tagline' => 'Fabriqué avec amour dans la capitale mondiale du parfum',
                    'tagline_position' => 'bottom',
                    'center_lat' => 43.6589,
                    'center_lng' => 6.9252,
                    'zoom' => 15,
                    'height' => 480,
                    'markers' => [
                        [
                            'lat' => 43.6589,
                            'lng' => 6.9252,
                            'popup_text' => '📍 Grasse — Capitale Mondiale du Parfum',
                        ],
                        [
                            'lat' => 43.6580,
                            'lng' => 6.9200,
                            'popup_text' => '🏭 Parfumerie Fragonard',
                        ],
                        [
                            'lat' => 43.6600,
                            'lng' => 6.9300,
                            'popup_text' => '🌿 Maison Molinard (fondée en 1849)',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f8fafc',
                    'heading_color' => '#0f172a',
                    'text_color' => '#334155',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#0f172a',
                    'heading_color_dark' => '#f1f5f9',
                    'text_color_dark' => '#cbd5e1',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],

            // Contact Form
            [
                'type' => 'contact_form',
                'data' => [
                    'heading' => 'Envoyez-nous un Message',
                    'subtitle' => 'Nous répondons sous 24 heures.',
                    'submit_text' => 'Envoyer le Message',
                    'success_message' => 'Merci ! Votre message a été envoyé.',
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],

            // Contact Info Features
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Retrouvez-nous en Ligne',
                    'subtitle' => 'Nous répondons plus vite qu\'un parfumeur grassois ne nomme une note.',
                    'columns' => '3',
                    'items' => [
                        [
                            'icon' => 'heroicon-o-envelope',
                            'title' => 'Email',
                            'description' => 'Envoyez-nous un email et nous répondrons sous 24 heures — même le week-end.',
                        ],
                        [
                            'icon' => 'heroicon-o-x-mark',
                            'title' => 'X (anciennement Twitter)',
                            'description' => 'Suivez @blogr pour les dernières nouvelles et mises à jour.',
                        ],
                        [
                            'icon' => 'heroicon-o-chat-bubble-oval-left-ellipsis',
                            'title' => 'Bluesky',
                            'description' => 'Retrouvez-nous sur Bluesky pour des discussions, astuces et support.',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],

            // CTA
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Construisons ensemble quelque chose de beau.',
                    'subheading' => 'Open source sous licence MIT. Aucune carte de crédit requise.',
                    'button_text' => 'Commencer',
                    'button_link_type' => 'blog',
                    'button_url' => null,
                    'button_category_id' => null,
                    'button_cms_page_id' => null,
                    'button_style' => 'primary',
                    'background_type' => 'gradient',
                    'gradient_from' => '#4f46e5',
                    'gradient_to' => '#7c3aed',
                    'gradient_direction' => 'to-l',
                    'heading_color' => '#ffffff',
                    'subtitle_color' => '#e0e7ff',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#24243e',
                    'gradient_to_dark' => '#0f0c29',
                    'gradient_direction_dark' => 'to-l',
                    'heading_color_dark' => '#f1f5f9',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
        ];
    }

    private function getContactPageBlocksES(): array
    {
        return [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Contáctenos',
                    'subtitle' => 'Hecho con amor en Grasse — la capital mundial del perfume. Nos encantaría saber de usted.',
                    'cta_text' => 'Enviar Mensaje',
                    'cta_link_type' => 'external',
                    'cta_url' => '#contact-form',
                    'cta_category_id' => null,
                    'cta_cms_page_id' => null,
                    'alignment' => 'center',
                    'background_type' => 'gradient',
                    'gradient_from' => '#0f0c29',
                    'gradient_to' => '#302b63',
                    'gradient_direction' => 'to-br',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#0f0c29',
                    'gradient_to_dark' => '#24243e',
                    'gradient_direction_dark' => 'to-br',
                    'text_shadow' => true,
                    'shadow_intensity' => 'medium',
                ],
            ],
            [
                'type' => 'stats',
                'data' => [
                    'heading' => 'Desarrollado con pasión en el Sur de Francia',
                    'stats' => [
                        ['number' => 25, 'suffix' => '+', 'label' => 'Idiomas Compatibles'],
                        ['number' => 24, 'suffix' => '+', 'label' => 'Bloques de Contenido'],
                        ['number' => 100, 'suffix' => '%', 'label' => 'Código Abierto'],
                        ['number' => 12, 'suffix' => '.x', 'label' => 'Laravel Nativo'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                ],
            ],
            [
                'type' => 'map',
                'data' => [
                    'heading' => 'Encuéntranos en Grasse',
                    'subtitle' => 'Ubicado en las colinas de la Riviera francesa, donde el perfume ha sido un arte durante siglos.',
                    'tagline' => 'Hecho con amor en la capital mundial del perfume',
                    'tagline_position' => 'bottom',
                    'center_lat' => 43.6589,
                    'center_lng' => 6.9252,
                    'zoom' => 15,
                    'height' => 480,
                    'markers' => [
                        ['lat' => 43.6589, 'lng' => 6.9252, 'popup_text' => '📍 Grasse — Capital Mundial del Perfume'],
                        ['lat' => 43.6580, 'lng' => 6.9200, 'popup_text' => '🏭 Fragonard Perfume Factory'],
                        ['lat' => 43.6600, 'lng' => 6.9300, 'popup_text' => '🌿 Molinard Perfume House (est. 1849)'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f8fafc',
                    'heading_color' => '#0f172a',
                    'text_color' => '#334155',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#0f172a',
                    'heading_color_dark' => '#f1f5f9',
                    'text_color_dark' => '#cbd5e1',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
            [
                'type' => 'contact_form',
                'data' => [
                    'heading' => 'Envíenos un Mensaje',
                    'subtitle' => 'Le responderemos en un plazo de 24 horas.',
                    'submit_text' => 'Enviar Mensaje',
                    'success_message' => '¡Gracias! Su mensaje ha sido enviado.',
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Encuéntranos en Línea',
                    'subtitle' => 'Respondemos más rápido de lo que un perfumero de Grasse nombra una nota.',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'heroicon-o-envelope', 'title' => 'Correo Electrónico', 'description' => 'Envíenos un correo y le responderemos en 24 horas — incluso los fines de semana.'],
                        ['icon' => 'heroicon-o-x-mark', 'title' => 'X (antes Twitter)', 'description' => 'Siga a @blogr para las últimas noticias y novedades.'],
                        ['icon' => 'heroicon-o-chat-bubble-oval-left-ellipsis', 'title' => 'Bluesky', 'description' => 'Encuéntrenos en Bluesky para debates, consejos y soporte.'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Construyamos algo hermoso juntos.',
                    'subheading' => 'Código abierto con licencia MIT. Sin tarjeta de crédito.',
                    'button_text' => 'Comenzar',
                    'button_link_type' => 'blog',
                    'button_url' => null,
                    'button_category_id' => null,
                    'button_cms_page_id' => null,
                    'button_style' => 'primary',
                    'background_type' => 'gradient',
                    'gradient_from' => '#4f46e5',
                    'gradient_to' => '#7c3aed',
                    'gradient_direction' => 'to-l',
                    'heading_color' => '#ffffff',
                    'subtitle_color' => '#e0e7ff',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#24243e',
                    'gradient_to_dark' => '#0f0c29',
                    'gradient_direction_dark' => 'to-l',
                    'heading_color_dark' => '#f1f5f9',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
        ];
    }

    private function getContactPageBlocksPL(): array
    {
        return [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Skontaktuj się z nami',
                    'subtitle' => 'Wykonane z miłością w Grasse — światowej stolicy perfum. Chętnie Cię usłyszymy.',
                    'cta_text' => 'Wyślij Wiadomość',
                    'cta_link_type' => 'external',
                    'cta_url' => '#contact-form',
                    'cta_category_id' => null,
                    'cta_cms_page_id' => null,
                    'alignment' => 'center',
                    'background_type' => 'gradient',
                    'gradient_from' => '#0f0c29',
                    'gradient_to' => '#302b63',
                    'gradient_direction' => 'to-br',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#0f0c29',
                    'gradient_to_dark' => '#24243e',
                    'gradient_direction_dark' => 'to-br',
                    'text_shadow' => true,
                    'shadow_intensity' => 'medium',
                ],
            ],
            [
                'type' => 'stats',
                'data' => [
                    'heading' => 'Stworzone z pasją na Południu Francji',
                    'stats' => [
                        ['number' => 25, 'suffix' => '+', 'label' => 'Obsługiwane Języki'],
                        ['number' => 24, 'suffix' => '+', 'label' => 'Bloki Treści'],
                        ['number' => 100, 'suffix' => '%', 'label' => 'Open Source'],
                        ['number' => 12, 'suffix' => '.x', 'label' => 'Laravel Natywny'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                ],
            ],
            [
                'type' => 'map',
                'data' => [
                    'heading' => 'Znajdź nas w Grasse',
                    'subtitle' => 'Położone na wzgórzach Riwiery Francuskiej, gdzie perfumy są sztuką od wieków.',
                    'tagline' => 'Wykonane z miłością w światowej stolicy perfum',
                    'tagline_position' => 'bottom',
                    'center_lat' => 43.6589,
                    'center_lng' => 6.9252,
                    'zoom' => 15,
                    'height' => 480,
                    'markers' => [
                        ['lat' => 43.6589, 'lng' => 6.9252, 'popup_text' => '📍 Grasse — Stolica Perfum Świata'],
                        ['lat' => 43.6580, 'lng' => 6.9200, 'popup_text' => '🏭 Fabryka Perfum Fragonard'],
                        ['lat' => 43.6600, 'lng' => 6.9300, 'popup_text' => '🌿 Dom Perfum Molinard (zał. 1849)'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f8fafc',
                    'heading_color' => '#0f172a',
                    'text_color' => '#334155',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#0f172a',
                    'heading_color_dark' => '#f1f5f9',
                    'text_color_dark' => '#cbd5e1',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
            [
                'type' => 'contact_form',
                'data' => [
                    'heading' => 'Wyślij Nam Wiadomość',
                    'subtitle' => 'Odpowiemy w ciągu 24 godzin.',
                    'submit_text' => 'Wyślij Wiadomość',
                    'success_message' => 'Dziękujemy! Twoja wiadomość została wysłana.',
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Znajdź nas w Sieci',
                    'subtitle' => 'Odpowiadamy szybciej, niż perfumiarz z Grasse nazwie nutę zapachową.',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'heroicon-o-envelope', 'title' => 'Email', 'description' => 'Wyślij email, a odpowiemy w ciągu 24 godzin — nawet w weekendy.'],
                        ['icon' => 'heroicon-o-x-mark', 'title' => 'X (dawniej Twitter)', 'description' => 'Obserwuj @blogr, aby być na bieżąco z nowościami.'],
                        ['icon' => 'heroicon-o-chat-bubble-oval-left-ellipsis', 'title' => 'Bluesky', 'description' => 'Znajdź nas na Bluesky, aby dyskutować i uzyskać pomoc.'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#ffffff',
                    'heading_color' => '#1e293b',
                    'text_color' => '#475569',
                    'subtitle_color' => '#64748b',
                    'background_type_dark' => 'color',
                    'background_color_dark' => '#111827',
                    'heading_color_dark' => '#e2e8f0',
                    'text_color_dark' => '#94a3b8',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Zbudujmy razem coś pięknego.',
                    'subheading' => 'Open source na licencji MIT. Bez karty kredytowej.',
                    'button_text' => 'Rozpocznij',
                    'button_link_type' => 'blog',
                    'button_url' => null,
                    'button_category_id' => null,
                    'button_cms_page_id' => null,
                    'button_style' => 'primary',
                    'background_type' => 'gradient',
                    'gradient_from' => '#4f46e5',
                    'gradient_to' => '#7c3aed',
                    'gradient_direction' => 'to-l',
                    'heading_color' => '#ffffff',
                    'subtitle_color' => '#e0e7ff',
                    'background_type_dark' => 'gradient',
                    'gradient_from_dark' => '#24243e',
                    'gradient_to_dark' => '#0f0c29',
                    'gradient_direction_dark' => 'to-l',
                    'heading_color_dark' => '#f1f5f9',
                    'subtitle_color_dark' => '#94a3b8',
                ],
            ],
        ];
    }
}
