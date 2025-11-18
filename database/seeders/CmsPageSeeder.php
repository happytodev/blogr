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
                'meta_title' => 'Blogr - Modern Multilingual Blog Platform',
                'meta_description' => 'Discover Blogr, a powerful FilamentPHP plugin for creating stunning multilingual blog content.',
                'meta_keywords' => 'blog, CMS, multilingual, Laravel, FilamentPHP',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'blocks' => $this->getHomePageBlocksEN(),
            ]
        );

        // French translation
        $page->translations()->updateOrCreate(
            ['locale' => 'fr'],
            [
                'slug' => 'accueil',
                'title' => 'Bienvenue sur Blogr',
                'meta_title' => 'Blogr - Plateforme de Blog Multilingue Moderne',
                'meta_description' => 'Découvrez Blogr, un plugin FilamentPHP puissant pour créer du contenu blog multilingue époustouflant.',
                'meta_keywords' => 'blog, CMS, multilingue, Laravel, FilamentPHP',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
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
                'meta_title' => 'Contactez-nous - Blogr',
                'meta_description' => 'Des questions ? Contactez notre équipe. Nous aimerions beaucoup vous entendre !',
                'meta_keywords' => 'contact, support, aide',
                'content' => '# Contactez Blogr

Nous sommes toujours ravis de vous entendre. Que vous ayez une question sur les fonctionnalités, la tarification ou autre chose, notre équipe est prête à répondre à toutes vos questions.',
                'blocks' => $this->getContactPageBlocksFR(),
            ]
        );
    }

    /**
     * Get homepage blocks structure (English)
     */
    private function getHomePageBlocksEN(): array
    {
        return [
            // Hero Section
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Blogr: Modern Multilingual Blog Platform',
                    'subtitle' => 'Create stunning, SEO-friendly blog content in multiple languages with ease',
                    'cta_text' => 'Start Blogging',
                    'cta_link_type' => 'external',
                    'cta_url' => '/blog',
                    'cta_category_id' => null,
                    'cta_cms_page_id' => null,
                    'alignment' => 'center',
                    'background_type' => 'color',
                    'background_color' => '#667eea',
                    'text_shadow' => true,
                    'shadow_intensity' => 'medium',
                ],
            ],

            // Transition: Wavy with solid colors
            [
                'type' => 'transition-diagonal',
                'data' => [
                    'shape' => 'wavy',
                    'amplitude' => 40,
                ],
            ],

            // Stats Section
            [
                'type' => 'stats',
                'data' => [
                    'heading' => 'Trusted by Content Creators',
                    'stats' => [
                        ['number' => 5000, 'label' => 'Active Bloggers'],
                        ['number' => 50000, 'label' => 'Published Posts'],
                        ['number' => 25, 'label' => 'Languages Supported'],
                        ['number' => 99, 'label' => 'Uptime %'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f093fb',
                ],
            ],

            // Features Section
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Powerful Features for Modern Bloggers',
                    'subtitle' => 'Everything you need to create, manage, and grow your blog',
                    'columns' => '3',
                    'items' => [
                        [
                            'title' => '✍️ Intuitive Editor',
                            'description' => 'Block-based editor with live preview, markdown support, and rich formatting options',
                        ],
                        [
                            'title' => '🌍 Global Reach',
                            'description' => 'Write in 25+ languages with automatic SEO optimization for each locale',
                        ],
                        [
                            'title' => '⚡ Lightning Fast',
                            'description' => 'Optimized performance with caching, CDN ready, and zero-lag loading',
                        ],
                        [
                            'title' => '📈 SEO Mastery',
                            'description' => 'Built-in SEO tools, meta tags, structured data, and sitemap generation',
                        ],
                        [
                            'title' => '🔐 Enterprise Security',
                            'description' => 'Advanced permissions, role-based access, audit logs, and automated backups',
                        ],
                        [
                            'title' => '🎨 Design Freedom',
                            'description' => 'Customize colors, fonts, layouts - no coding required',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#667eea',
                ],
            ],

            // Content Section
            [
                'type' => 'content',
                'data' => [
                    'content' => '## Why Leading Content Creators Choose Blogr

Blogr is built on **FilamentPHP** and **Laravel 12**, giving you enterprise-grade performance with a focus on content creation. Whether you\'re a solo blogger or managing a team of writers, Blogr scales with your needs.

### What Makes Blogr Different
- **Multilingual by Design**: Not an afterthought - multilingual support is baked into every feature
- **SEO First**: Every post is optimized for search engines with automatic sitemaps and structured data
- **Performance Obsessed**: Avg load time under 1s, even with thousands of posts
- **Team Collaboration**: Roles, permissions, and workflows built for teams
- **Content Portability**: Export your content anytime in standard formats',
                    'max_width' => 'prose',
                    'background_type' => 'color',
                    'background_color' => '#f5f7fa',
                    'text_shadow' => true,
                    'shadow_intensity' => 'medium',
                ],
            ],

            // Transition: Zigzag with solid colors
            [
                'type' => 'transition-diagonal',
                'data' => [
                    'shape' => 'zigzag',
                    'amplitude' => 35,
                ],
            ],

            // Gallery Section
            [
                'type' => 'gallery',
                'data' => [
                    'heading' => 'Showcase Your Visual Stories',
                    'description' => 'Stunning gallery layouts to display your best content',
                    'layout' => 'grid',
                    'columns' => '3',
                    'images' => [],
                    'background_type' => 'color',
                    'background_color' => '#f5576c',
                ],
            ],

            // Transition: Smooth with solid colors
            [
                'type' => 'transition-diagonal',
                'data' => [
                    'shape' => 'smooth',
                    'amplitude' => 30,
                ],
            ],

            // Features Grid
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Advanced Capabilities',
                    'columns' => '2',
                    'items' => [
                        [
                            'title' => '📱 Responsive Design',
                            'description' => 'Perfect on mobile, tablet, desktop. Your blog looks amazing everywhere.',
                        ],
                        [
                            'title' => '🔗 SEO Optimization',
                            'description' => 'Automatic meta tags, canonical URLs, XML sitemaps, and structured data.',
                        ],
                        [
                            'title' => '🔄 Content Scheduling',
                            'description' => 'Schedule posts to publish automatically for global audiences.',
                        ],
                        [
                            'title' => '📊 Advanced Analytics',
                            'description' => 'Track views, engagement, bounce rates. Integrate with analytics tools.',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#4facfe',
                ],
            ],

            // CTA Section
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Join the Blogr Revolution',
                    'subheading' => 'Start creating amazing multilingual content today. No credit card required.',
                    'button_text' => 'Begin Your Journey',
                    'button_link_type' => 'external',
                    'button_url' => 'https://example.com/join',
                    'button_category_id' => null,
                    'button_cms_page_id' => null,
                    'button_style' => 'primary',
                    'background_type' => 'color',
                    'background_color' => '#f5576c',
                ],
            ],
        ];
    }

    /**
     * Get homepage blocks structure (French)
     */
    private function getHomePageBlocksFR(): array
    {
        return [
            // Hero Section
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Blogr : Plateforme de Blog Multilingue Moderne',
                    'subtitle' => 'Créez un contenu de blog magnifique et optimisé pour le SEO en plusieurs langues facilement',
                    'cta_text' => 'Commencer à bloguer',
                    'cta_link_type' => 'external',
                    'cta_url' => '/blog',
                    'cta_category_id' => null,
                    'cta_cms_page_id' => null,
                    'alignment' => 'center',
                    'background_type' => 'color',
                    'background_color' => '#667eea',
                    'text_shadow' => true,
                    'shadow_intensity' => 'medium',
                ],
            ],

            // Transition: Wavy with solid colors
            [
                'type' => 'transition-diagonal',
                'data' => [
                    'shape' => 'wavy',
                    'amplitude' => 40,
                ],
            ],

            // Stats Section
            [
                'type' => 'stats',
                'data' => [
                    'heading' => 'Approuvé par les Créateurs de Contenu',
                    'stats' => [
                        ['number' => 5000, 'label' => 'Blogueurs Actifs'],
                        ['number' => 50000, 'label' => 'Articles Publiés'],
                        ['number' => 25, 'label' => 'Langues Supportées'],
                        ['number' => 99, 'label' => 'Disponibilité %'],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f093fb',
                ],
            ],

            // Features Section
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Fonctionnalités Puissantes pour les Blogueurs Modernes',
                    'subtitle' => 'Tout ce dont vous avez besoin pour créer, gérer et développer votre blog',
                    'columns' => '3',
                    'items' => [
                        [
                            'title' => '✍️ Éditeur Intuitif',
                            'description' => 'Éditeur basé sur des blocs avec aperçu en direct, support Markdown et options de formatage avancées',
                        ],
                        [
                            'title' => '🌍 Portée Mondiale',
                            'description' => 'Écrivez en 25+ langues avec optimisation SEO automatique pour chaque locale',
                        ],
                        [
                            'title' => '⚡ Ultra Rapide',
                            'description' => 'Performance optimisée avec mise en cache, prêt pour CDN et zéro latence',
                        ],
                        [
                            'title' => '📈 Maîtrise du SEO',
                            'description' => 'Outils SEO intégrés, balises meta, données structurées et génération de sitemap',
                        ],
                        [
                            'title' => '🔐 Sécurité Entreprise',
                            'description' => 'Permissions avancées, accès basé sur les rôles, journaux d\'audit et sauvegardes automatisées',
                        ],
                        [
                            'title' => '🎨 Liberté de Conception',
                            'description' => 'Personnalisez les couleurs, les polices, les mises en page - aucune programmation requise',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#667eea',
                ],
            ],

            // Content Section
            [
                'type' => 'content',
                'data' => [
                    'content' => '## Pourquoi les Meilleurs Créateurs de Contenu Choisissent Blogr

Blogr est construit sur **FilamentPHP** et **Laravel 12**, vous offrant une performance de qualité entreprise avec un focus sur la création de contenu. Que vous soyez un blogueur solo ou que vous gériez une équipe d\'écrivains, Blogr évolue selon vos besoins.

### Ce qui Rend Blogr Différent
- **Multilingue par Conception** : Pas un ajout secondaire - le support multilingue est intégré dans chaque fonctionnalité
- **SEO en Premier** : Chaque article est optimisé pour les moteurs de recherche avec sitemaps automatiques et données structurées
- **Obsédé par la Performance** : Temps de chargement moyen inférieur à 1s, même avec des milliers d\'articles
- **Collaboration d\'Équipe** : Rôles, permissions et workflows construits pour les équipes
- **Portabilité du Contenu** : Exportez votre contenu à tout moment dans des formats standards',
                    'max_width' => 'prose',
                    'background_type' => 'color',
                    'background_color' => '#f5f7fa',
                    'text_shadow' => true,
                    'shadow_intensity' => 'medium',
                ],
            ],

            // Transition: Zigzag with solid colors
            [
                'type' => 'transition-diagonal',
                'data' => [
                    'shape' => 'zigzag',
                    'amplitude' => 35,
                ],
            ],

            // Gallery Section
            [
                'type' => 'gallery',
                'data' => [
                    'heading' => 'Mettez en Valeur Vos Histoires Visuelles',
                    'description' => 'Des mises en page galerie magnifiques pour afficher votre meilleur contenu',
                    'layout' => 'grid',
                    'columns' => '3',
                    'images' => [],
                    'background_type' => 'color',
                    'background_color' => '#f5576c',
                ],
            ],

            // Transition: Smooth with solid colors
            [
                'type' => 'transition-diagonal',
                'data' => [
                    'shape' => 'smooth',
                    'amplitude' => 30,
                ],
            ],

            // Features Grid
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Capacités Avancées',
                    'columns' => '2',
                    'items' => [
                        [
                            'title' => '📱 Design Responsif',
                            'description' => 'Parfait sur mobile, tablette, desktop. Votre blog est magnifique partout.',
                        ],
                        [
                            'title' => '🔗 Optimisation SEO',
                            'description' => 'Balises meta automatiques, URLs canoniques, sitemaps XML et données structurées.',
                        ],
                        [
                            'title' => '🔄 Planification de Contenu',
                            'description' => 'Programmez les articles pour publier automatiquement pour un audience mondial.',
                        ],
                        [
                            'title' => '📊 Analyse Avancée',
                            'description' => 'Suivi des vues, engagement, taux de rebond. Intégrez avec les outils d\'analyse.',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#4facfe',
                ],
            ],

            // CTA Section
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Rejoignez la Révolution Blogr',
                    'subheading' => 'Commencez à créer un contenu multilingue incroyable dès aujourd\'hui. Aucune carte de crédit requise.',
                    'button_text' => 'Commencer Votre Voyage',
                    'button_link_type' => 'external',
                    'button_url' => 'https://example.com/rejoindre',
                    'button_category_id' => null,
                    'button_cms_page_id' => null,
                    'button_style' => 'primary',
                    'background_type' => 'color',
                    'background_color' => '#f5576c',
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
            // Contact CTA Section
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Let\'s Connect',
                    'subheading' => 'Send us a message and we\'ll respond as soon as possible',
                    'button_text' => 'Send Message',
                    'button_link_type' => 'external',
                    'button_url' => '#contact-form',
                    'button_category_id' => null,
                    'button_cms_page_id' => null,
                    'button_style' => 'primary',
                    'background_type' => 'color',
                    'background_color' => '#667eea',
                ],
            ],

            // Contact Info Features
            [
                'type' => 'features',
                'data' => [
                    'title' => 'How to Reach Us',
                    'subtitle' => 'Multiple ways to get in touch with our team',
                    'columns' => '3',
                    'items' => [
                        [
                            'title' => '📧 Email',
                            'description' => 'Send us an email and we\'ll get back to you within 24 hours',
                        ],
                        [
                            'title' => '💬 Live Chat',
                            'description' => 'Chat with our support team in real-time during business hours',
                        ],
                        [
                            'title' => '📱 Social Media',
                            'description' => 'Follow us on social media for updates and announcements',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f093fb',
                ],
            ],
        ];
    }

    /**
     * Get contact page blocks (French)
     */
    private function getContactPageBlocksFR(): array
    {
        return [
            // Contact CTA Section
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Connectons-nous',
                    'subheading' => 'Envoyez-nous un message et nous répondrons dès que possible',
                    'button_text' => 'Envoyer un Message',
                    'button_link_type' => 'external',
                    'button_url' => '#contact-form',
                    'button_category_id' => null,
                    'button_cms_page_id' => null,
                    'button_style' => 'primary',
                    'background_type' => 'color',
                    'background_color' => '#667eea',
                ],
            ],

            // Contact Info Features
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Comment Nous Joindre',
                    'subtitle' => 'Plusieurs façons de nous contacter',
                    'columns' => '3',
                    'items' => [
                        [
                            'title' => '📧 Email',
                            'description' => 'Envoyez-nous un email et nous répondrons dans les 24 heures',
                        ],
                        [
                            'title' => '💬 Chat en Direct',
                            'description' => 'Chattez avec notre équipe d\'assistance en temps réel pendant les heures de bureau',
                        ],
                        [
                            'title' => '📱 Réseaux Sociaux',
                            'description' => 'Suivez-nous sur les réseaux sociaux pour les mises à jour et annonces',
                        ],
                    ],
                    'background_type' => 'color',
                    'background_color' => '#f093fb',
                ],
            ],
        ];
    }
}
