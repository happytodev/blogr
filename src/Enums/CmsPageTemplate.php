<?php

namespace Happytodev\Blogr\Enums;

enum CmsPageTemplate: string
{
    case DEFAULT = 'default';
    case LANDING = 'landing';
    case CONTACT = 'contact';
    case ABOUT = 'about';
    case PRICING = 'pricing';
    case FAQ = 'faq';
    case CUSTOM = 'custom';

    /**
     * Get human-readable label for the template
     */
    public function label(): string
    {
        return match($this) {
            self::DEFAULT => 'Simple Page',
            self::LANDING => 'Landing Page',
            self::CONTACT => 'Contact Page',
            self::ABOUT => 'About Page',
            self::PRICING => 'Pricing Page',
            self::FAQ => 'FAQ Page',
            self::CUSTOM => 'Custom Page',
        };
    }

    /**
     * Get description for the template
     */
    public function description(): string
    {
        return match($this) {
            self::DEFAULT => 'Simple page with markdown content',
            self::LANDING => 'Landing page with block builder (hero, features, CTA, etc.)',
            self::CONTACT => 'Contact form with map integration',
            self::ABOUT => 'About page with team and timeline blocks',
            self::PRICING => 'Pricing tables with plans comparison',
            self::FAQ => 'Frequently Asked Questions with accordion',
            self::CUSTOM => 'Full custom page with all available blocks',
        };
    }

    /**
     * Get available blocks for this template
     */
    public function availableBlocks(): array
    {
        return match($this) {
            self::DEFAULT => ['markdown'],
            self::LANDING => ['hero', 'features', 'cta', 'testimonials', 'pricing', 'statistics'],
            self::CONTACT => ['hero', 'cta', 'features', 'stats', 'map', 'contact_form', 'faq', 'markdown'],
            self::ABOUT => ['hero', 'markdown', 'team', 'timeline', 'statistics'],
            self::PRICING => ['hero', 'pricing', 'faq', 'testimonials', 'cta'],
            self::FAQ => ['hero', 'faq', 'contact_form', 'markdown'],
            self::CUSTOM => ['hero', 'features', 'cta', 'testimonials', 'faq', 'contact_form', 'markdown', 'team', 'gallery', 'pricing', 'statistics', 'timeline', 'map', 'html'],
        };
    }

    /**
     * Check if this template supports block builder
     */
    public function supportsBlockBuilder(): bool
    {
        return $this !== self::DEFAULT;
    }

    /**
     * Get default pre-populated blocks for this template
     *
     * @return array<int, array{type: string, data: array}>
     */
    public function defaultBlocks(): array
    {
        return match($this) {
            self::DEFAULT => [],
            self::CUSTOM => [],
            self::LANDING => $this->landingDefaultBlocks(),
            self::CONTACT => $this->contactDefaultBlocks(),
            self::ABOUT => $this->aboutDefaultBlocks(),
            self::PRICING => $this->pricingDefaultBlocks(),
            self::FAQ => $this->faqDefaultBlocks(),
        };
    }

    /**
     * Get default blocks for the Landing template
     */
    private function landingDefaultBlocks(): array
    {
        return [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Welcome to Our Platform',
                    'subtitle' => 'We build solutions that help your business grow.',
                    'cta_text' => 'Learn More',
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
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Why Choose Us',
                    'subtitle' => 'We deliver quality and performance at every step.',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'heroicon-o-bolt', 'title' => 'Lightning Fast', 'description' => 'Optimized for speed and performance. Your content loads in milliseconds.'],
                        ['icon' => 'heroicon-o-shield-check', 'title' => 'Secure by Default', 'description' => 'Enterprise-grade security built-in. Your data stays safe with us.'],
                        ['icon' => 'heroicon-o-globe-alt', 'title' => 'Multilingual', 'description' => 'Built for a global audience with full translation-first architecture.'],
                        ['icon' => 'heroicon-o-cube', 'title' => 'Modular & Extensible', 'description' => 'Plugin-based architecture that grows with your needs.'],
                        ['icon' => 'heroicon-o-magnifying-glass', 'title' => 'SEO Optimized', 'description' => 'Sitemaps, structured data, meta tags, and clean URLs out of the box.'],
                        ['icon' => 'heroicon-o-arrow-path', 'title' => 'Always Up to Date', 'description' => 'Regular updates, security patches, and new features shipped frequently.'],
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
                'type' => 'stats',
                'data' => [
                    'heading' => 'Our Impact in Numbers',
                    'stats' => [
                        ['number' => 10, 'suffix' => '+', 'label' => 'Years of Experience'],
                        ['number' => 500, 'suffix' => '+', 'label' => 'Projects Delivered'],
                        ['number' => 99, 'suffix' => '%', 'label' => 'Client Satisfaction'],
                        ['number' => 50, 'suffix' => '+', 'label' => 'Team Members'],
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
                'type' => 'blog_posts',
                'data' => [
                    'heading' => 'Latest from Our Blog',
                    'limit' => 3,
                    'layout' => 'grid',
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
            [
                'type' => 'timeline',
                'data' => [
                    'heading' => 'Our Journey',
                    'events' => [
                        ['date' => '2024', 'title' => 'Founded', 'description' => 'Our journey began with a vision to transform the industry.'],
                        ['date' => '2025', 'title' => 'First Major Release', 'description' => 'Launched our flagship product to critical acclaim.'],
                        ['date' => '2026', 'title' => 'Global Expansion', 'description' => 'Opened offices in three continents, serving clients worldwide.'],
                        ['date' => '2027', 'title' => 'Next Chapter', 'description' => 'Continuing to innovate and shape the future of our industry.'],
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
                'type' => 'cta',
                'data' => [
                    'heading' => 'Ready to Get Started?',
                    'subheading' => 'Join thousands of satisfied customers. No credit card required.',
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

    /**
     * Get default blocks for the Contact template
     */
    private function contactDefaultBlocks(): array
    {
        return [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Get in Touch',
                    'subtitle' => "We'd love to hear from you. Reach out and we'll get back to you within 24 hours.",
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
            [
                'type' => 'stats',
                'data' => [
                    'heading' => 'Built with Passion',
                    'stats' => [
                        ['number' => 10, 'suffix' => '+', 'label' => 'Years in Business'],
                        ['number' => 500, 'suffix' => '+', 'label' => 'Happy Clients'],
                        ['number' => 50, 'suffix' => '+', 'label' => 'Team Members'],
                        ['number' => 24, 'suffix' => '/7', 'label' => 'Support Available'],
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
                    'heading' => 'Find Us',
                    'subtitle' => 'Visit our office — coffee is always on us.',
                    'tagline' => 'We are here to help',
                    'tagline_position' => 'bottom',
                    'center_lat' => 48.8566,
                    'center_lng' => 2.3522,
                    'zoom' => 13,
                    'height' => 400,
                    'markers' => [
                        ['lat' => 48.8566, 'lng' => 2.3522, 'popup_text' => '📍 Our Headquarters'],
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
                    'heading' => 'Send Us a Message',
                    'subtitle' => "Fill out the form below and we'll get back to you shortly.",
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
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Other Ways to Reach Us',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'heroicon-o-envelope', 'title' => 'Email Us', 'description' => 'hello@example.com — we respond within 24 hours.'],
                        ['icon' => 'heroicon-o-phone', 'title' => 'Call Us', 'description' => '+1 (555) 123-4567 — Monday to Friday, 9 AM to 6 PM.'],
                        ['icon' => 'heroicon-o-chat-bubble-oval-left-ellipsis', 'title' => 'Live Chat', 'description' => 'Chat with our team directly on our website.'],
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
                'type' => 'cta',
                'data' => [
                    'heading' => "Let's Build Something Together",
                    'subheading' => 'Open source and built for everyone. No commitment required.',
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

    /**
     * Get default blocks for the About template
     */
    private function aboutDefaultBlocks(): array
    {
        return [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'About Us',
                    'subtitle' => 'We are a team of passionate creators dedicated to building exceptional digital experiences.',
                    'cta_text' => 'Meet the Team',
                    'cta_link_type' => 'external',
                    'cta_url' => '#team',
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
            [
                'type' => 'stats',
                'data' => [
                    'heading' => 'Who We Are',
                    'stats' => [
                        ['number' => 10, 'suffix' => '+', 'label' => 'Years Together'],
                        ['number' => 500, 'suffix' => '+', 'label' => 'Projects Completed'],
                        ['number' => 50, 'suffix' => '+', 'label' => 'Team Members'],
                        ['number' => 25, 'suffix' => '+', 'label' => 'Countries Served'],
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
                'type' => 'timeline',
                'data' => [
                    'heading' => 'Our Story',
                    'events' => [
                        ['date' => '2019', 'title' => 'The Beginning', 'description' => 'A small team with a big dream. Started in a garage with nothing but passion.'],
                        ['date' => '2021', 'title' => 'First Milestone', 'description' => 'Reached 100 clients and expanded the team to 15 people.'],
                        ['date' => '2023', 'title' => 'Going Global', 'description' => 'Opened offices in Europe, Asia, and North America.'],
                        ['date' => '2025', 'title' => 'Industry Leaders', 'description' => 'Recognized as a top innovator in our space.'],
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
            [
                'type' => 'team',
                'data' => [
                    'heading' => 'Meet Our Team',
                    'description' => 'The people behind the product.',
                    'columns' => '4',
                    'members' => [
                        ['name' => 'Alex Johnson', 'role' => 'CEO & Founder', 'bio' => 'Visionary leader with 15+ years of industry experience.'],
                        ['name' => 'Sarah Chen', 'role' => 'CTO', 'bio' => 'Tech lead passionate about building scalable systems.'],
                        ['name' => 'Marcus Williams', 'role' => 'Head of Design', 'bio' => 'Creating beautiful, user-centered experiences since 2012.'],
                        ['name' => 'Elena Rodriguez', 'role' => 'Head of Marketing', 'bio' => 'Growth strategist with a knack for storytelling.'],
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
                'type' => 'cta',
                'data' => [
                    'heading' => 'Want to Be Part of Our Story?',
                    'subheading' => "We're always looking for talented people to join us.",
                    'button_text' => 'View Careers',
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
     * Get default blocks for the Pricing template
     */
    private function pricingDefaultBlocks(): array
    {
        return [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Our Pricing',
                    'subtitle' => 'Choose the plan that fits your needs. No hidden fees, no surprises.',
                    'cta_text' => 'Compare Plans',
                    'cta_link_type' => 'external',
                    'cta_url' => '#pricing',
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
            [
                'type' => 'pricing',
                'data' => [
                    'heading' => 'Simple, Transparent Pricing',
                    'description' => 'Start free, upgrade when you grow.',
                    'show_yearly_toggle' => false,
                    'columns' => '3',
                    'plans' => [
                        [
                            'name' => 'Starter',
                            'price' => 'Free',
                            'period' => 'forever',
                            'description' => 'Perfect for getting started.',
                            'features' => [
                                'Up to 3 projects',
                                'Basic analytics',
                                'Community support',
                                '1 GB storage',
                            ],
                            'cta_text' => 'Get Started',
                            'cta_url' => '/signup',
                            'highlighted' => false,
                        ],
                        [
                            'name' => 'Pro',
                            'price' => '$29',
                            'period' => '/month',
                            'description' => 'Best for growing teams.',
                            'features' => [
                                'Unlimited projects',
                                'Advanced analytics',
                                'Priority support',
                                '50 GB storage',
                                'Custom domains',
                                'Team collaboration',
                            ],
                            'cta_text' => 'Start Free Trial',
                            'cta_url' => '/signup',
                            'highlighted' => true,
                        ],
                        [
                            'name' => 'Enterprise',
                            'price' => '$99',
                            'period' => '/month',
                            'description' => 'For large organizations.',
                            'features' => [
                                'Everything in Pro',
                                'Dedicated support',
                                '500 GB storage',
                                'SLA guarantee',
                                'Custom integrations',
                                'On-premise deployment',
                            ],
                            'cta_text' => 'Contact Sales',
                            'cta_url' => '/contact',
                            'highlighted' => false,
                        ],
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
                'type' => 'features',
                'data' => [
                    'title' => 'Compare Features',
                    'subtitle' => 'Everything you need to make an informed decision.',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'heroicon-o-cloud', 'title' => 'Cloud Hosted', 'description' => 'Fully managed cloud infrastructure with 99.9% uptime guarantee.'],
                        ['icon' => 'heroicon-o-lock-closed', 'title' => 'Enterprise Security', 'description' => 'SOC 2 compliant, end-to-end encryption, and regular audits.'],
                        ['icon' => 'heroicon-o-arrow-path', 'title' => 'Automatic Updates', 'description' => 'Always running the latest version with zero downtime.'],
                        ['icon' => 'heroicon-o-chart-bar', 'title' => 'Detailed Analytics', 'description' => 'Comprehensive dashboards and reports for data-driven decisions.'],
                        ['icon' => 'heroicon-o-users', 'title' => 'Team Management', 'description' => 'Role-based access, audit logs, and collaboration tools.'],
                        ['icon' => 'heroicon-o-cog-6-tooth', 'title' => 'API Access', 'description' => 'Full REST API with rate limiting and webhook support.'],
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
                'type' => 'faq',
                'data' => [
                    'title' => 'Frequently Asked Questions',
                    'items' => [
                        ['question' => 'Can I switch plans later?', 'answer' => 'Yes, you can upgrade or downgrade at any time. Changes take effect immediately.'],
                        ['question' => 'Is there a free trial?', 'answer' => 'Yes, all paid plans come with a 14-day free trial. No credit card required.'],
                        ['question' => 'What payment methods do you accept?', 'answer' => 'We accept all major credit cards, PayPal, and bank transfers for annual plans.'],
                        ['question' => 'Can I cancel anytime?', 'answer' => 'Absolutely. No cancellation fees, no hidden charges. Your data stays yours.'],
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
                'type' => 'cta',
                'data' => [
                    'heading' => 'Still Have Questions?',
                    'subheading' => "We're here to help you find the perfect plan.",
                    'button_text' => 'Contact Us',
                    'button_link_type' => 'external',
                    'button_url' => '#contact-form',
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
     * Get default blocks for the FAQ template
     */
    private function faqDefaultBlocks(): array
    {
        return [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Frequently Asked Questions',
                    'subtitle' => "Find answers to the most common questions. Can't find what you're looking for? Reach out to us.",
                    'cta_text' => 'Contact Us',
                    'cta_link_type' => 'external',
                    'cta_url' => '#contact-form',
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
            [
                'type' => 'faq',
                'data' => [
                    'title' => 'Common Questions',
                    'items' => [
                        ['question' => 'How do I get started?', 'answer' => 'Simply sign up for an account and follow our quick start guide. You can be up and running in minutes.'],
                        ['question' => 'Is there a mobile app?', 'answer' => 'Yes, we offer iOS and Android apps with full feature parity to our web platform.'],
                        ['question' => 'Can I integrate with other tools?', 'answer' => 'Absolutely. We offer native integrations with popular tools and a REST API for custom integrations.'],
                        ['question' => 'What kind of support do you offer?', 'answer' => 'All plans include email support. Pro and Enterprise plans include priority support and dedicated account management.'],
                        ['question' => 'How secure is my data?', 'answer' => 'We take security seriously. All data is encrypted at rest and in transit. We are SOC 2 compliant.'],
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
                'type' => 'contact_form',
                'data' => [
                    'heading' => "Still Have Questions?",
                    'subtitle' => "We're here to help. Send us a message and we'll get back to you.",
                    'submit_text' => 'Send Message',
                    'success_message' => 'Thank you! We will get back to you shortly.',
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
        ];
    }

    /**
     * Get Blade view path for this template
     */
    public function viewPath(): string
    {
        return "blogr::cms.templates.{$this->value}";
    }

    /**
     * Get all templates as options array for Filament Select
     */
    public static function toSelectOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}
