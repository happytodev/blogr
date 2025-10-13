<?php<?php



namespace Happytodev\Blogr\Database\Seeders;namespace Happytodev\Blogr\Database\Seeders;



use Illuminate\Database\Seeder;class SeriesWithMultipleAuthorsSeeder

use Workbench\App\Models\User;{

use Happytodev\Blogr\Models\BlogSeries;}

use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Illuminate\Support\Str;

class SeriesWithMultipleAuthorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create authors if they don't exist
        $authors = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@example.com',
                'slug' => 'sarah-johnson',
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael@example.com',
                'slug' => 'michael-chen',
            ],
            [
                'name' => 'Emma Martinez',
                'email' => 'emma@example.com',
                'slug' => 'emma-martinez',
            ],
            [
                'name' => 'David Kim',
                'email' => 'david@example.com',
                'slug' => 'david-kim',
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa@example.com',
                'slug' => 'lisa-anderson',
            ],
        ];

        $userModels = [];
        foreach ($authors as $authorData) {
            $userModels[] = User::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'slug' => $authorData['slug'],
                    'password' => bcrypt('password'),
                ]
            );
        }

        // Series 1: Laravel for Beginners (3 authors)
        $laravel = BlogSeries::firstOrCreate(
            ['slug' => 'laravel-for-beginners'],
            [
                'is_featured' => true,
                'published_at' => now()->subMonths(6),
            ]
        );

        BlogSeriesTranslation::firstOrCreate(
            ['blog_series_id' => $laravel->id, 'locale' => 'en'],
            [
                'title' => 'Laravel for Beginners',
                'description' => 'A comprehensive guide to learning Laravel from scratch. Perfect for developers new to the framework.',
            ]
        );

        BlogSeriesTranslation::firstOrCreate(
            ['blog_series_id' => $laravel->id, 'locale' => 'fr'],
            [
                'title' => 'Laravel pour débutants',
                'description' => 'Un guide complet pour apprendre Laravel depuis zéro. Parfait pour les développeurs débutant avec ce framework.',
            ]
        );

        // Laravel articles (distributed among 3 authors: Sarah-5, Michael-3, Emma-2)
        $laravelArticles = [
            [
                'author' => $userModels[0], // Sarah - 5 articles
                'slug_en' => 'getting-started-with-laravel',
                'title_en' => 'Getting Started with Laravel',
                'tldr_en' => 'Learn how to install Laravel, understand its directory structure, and create your first route.',
                'content_en' => 'Laravel is a powerful PHP framework that makes web development a breeze. In this article, we\'ll walk through the installation process using Composer, explore the directory structure, and create our first route and view.',
                'slug_fr' => 'debuter-avec-laravel',
                'title_fr' => 'Débuter avec Laravel',
                'tldr_fr' => 'Apprenez à installer Laravel, comprendre sa structure de répertoires et créer votre première route.',
                'content_fr' => 'Laravel est un framework PHP puissant qui facilite le développement web. Dans cet article, nous verrons le processus d\'installation avec Composer, explorerons la structure des répertoires et créerons notre première route et vue.',
                'published_at' => now()->subMonths(6),
            ],
            [
                'author' => $userModels[0], // Sarah
                'slug_en' => 'understanding-laravel-routing',
                'title_en' => 'Understanding Laravel Routing',
                'tldr_en' => 'Master Laravel routing patterns, route parameters, named routes, and route groups.',
                'content_en' => 'Routing is the backbone of any web application. Learn how to define routes, work with parameters, use named routes for cleaner code, and organize your routes with groups.',
                'slug_fr' => 'comprendre-le-routing-laravel',
                'title_fr' => 'Comprendre le routing Laravel',
                'tldr_fr' => 'Maîtrisez les patterns de routing, les paramètres de route, les routes nommées et les groupes de routes.',
                'content_fr' => 'Le routing est la colonne vertébrale de toute application web. Apprenez à définir des routes, travailler avec les paramètres, utiliser les routes nommées pour un code plus propre et organiser vos routes avec des groupes.',
                'published_at' => now()->subMonths(5)->subDays(15),
            ],
            [
                'author' => $userModels[1], // Michael - 3 articles
                'slug_en' => 'eloquent-orm-basics',
                'title_en' => 'Eloquent ORM Basics',
                'tldr_en' => 'Discover Laravel\'s Eloquent ORM to interact with your database using elegant model syntax.',
                'content_en' => 'Eloquent makes database interactions simple and intuitive. Learn how to define models, perform CRUD operations, and use relationships to connect your data.',
                'slug_fr' => 'bases-de-eloquent-orm',
                'title_fr' => 'Bases d\'Eloquent ORM',
                'tldr_fr' => 'Découvrez l\'ORM Eloquent de Laravel pour interagir avec votre base de données via une syntaxe élégante.',
                'content_fr' => 'Eloquent rend les interactions avec la base de données simples et intuitives. Apprenez à définir des modèles, effectuer des opérations CRUD et utiliser les relations pour connecter vos données.',
                'published_at' => now()->subMonths(5),
            ],
            [
                'author' => $userModels[1], // Michael
                'slug_en' => 'blade-templating-engine',
                'title_en' => 'Blade Templating Engine',
                'tldr_en' => 'Learn to use Blade directives, layouts, and components to build dynamic views efficiently.',
                'content_en' => 'Blade is Laravel\'s powerful templating engine. Master directives like @if, @foreach, @extends, and create reusable components for cleaner views.',
                'slug_fr' => 'moteur-de-templates-blade',
                'title_fr' => 'Moteur de templates Blade',
                'tldr_fr' => 'Apprenez à utiliser les directives Blade, layouts et composants pour construire des vues dynamiques efficacement.',
                'content_fr' => 'Blade est le puissant moteur de templates de Laravel. Maîtrisez les directives comme @if, @foreach, @extends et créez des composants réutilisables pour des vues plus propres.',
                'published_at' => now()->subMonths(4)->subDays(20),
            ],
            [
                'author' => $userModels[2], // Emma - 2 articles
                'slug_en' => 'form-validation-in-laravel',
                'title_en' => 'Form Validation in Laravel',
                'tldr_en' => 'Implement robust form validation with Laravel\'s built-in validation rules and custom validators.',
                'content_en' => 'Laravel provides powerful validation out of the box. Learn to validate user input, create custom rules, and display validation errors elegantly.',
                'slug_fr' => 'validation-de-formulaires-laravel',
                'title_fr' => 'Validation de formulaires dans Laravel',
                'tldr_fr' => 'Implémentez une validation robuste avec les règles intégrées de Laravel et des validateurs personnalisés.',
                'content_fr' => 'Laravel fournit une validation puissante dès le départ. Apprenez à valider les entrées utilisateur, créer des règles personnalisées et afficher les erreurs élégamment.',
                'published_at' => now()->subMonths(4),
            ],
            [
                'author' => $userModels[2], // Emma
                'slug_en' => 'laravel-middleware-explained',
                'title_en' => 'Laravel Middleware Explained',
                'tldr_en' => 'Understand how middleware filters HTTP requests and implement authentication and authorization.',
                'content_en' => 'Middleware provides a convenient mechanism for filtering HTTP requests. Learn to create custom middleware, apply it to routes, and secure your application.',
                'slug_fr' => 'middleware-laravel-explique',
                'title_fr' => 'Les middleware Laravel expliqués',
                'tldr_fr' => 'Comprenez comment les middleware filtrent les requêtes HTTP et implémentez l\'authentification et l\'autorisation.',
                'content_fr' => 'Les middleware fournissent un mécanisme pratique pour filtrer les requêtes HTTP. Apprenez à créer des middleware personnalisés, les appliquer aux routes et sécuriser votre application.',
                'published_at' => now()->subMonths(3)->subDays(25),
            ],
            [
                'author' => $userModels[0], // Sarah (additional)
                'slug_en' => 'database-migrations-seeders',
                'title_en' => 'Database Migrations & Seeders',
                'tldr_en' => 'Manage your database schema with migrations and populate it with test data using seeders.',
                'content_en' => 'Migrations allow you to version control your database schema. Learn to create tables, modify columns, and use seeders to populate your database with sample data for testing.',
                'slug_fr' => 'migrations-et-seeders-database',
                'title_fr' => 'Migrations & Seeders de base de données',
                'tldr_fr' => 'Gérez votre schéma de base de données avec les migrations et remplissez-le avec des données de test via les seeders.',
                'content_fr' => 'Les migrations permettent de versionner votre schéma de base de données. Apprenez à créer des tables, modifier des colonnes et utiliser les seeders pour peupler votre base avec des données de test.',
                'published_at' => now()->subMonths(3),
            ],
            [
                'author' => $userModels[0], // Sarah (additional)
                'slug_en' => 'laravel-authentication-basics',
                'title_en' => 'Laravel Authentication Basics',
                'tldr_en' => 'Implement secure user authentication with Laravel\'s built-in authentication scaffolding.',
                'content_en' => 'Laravel makes authentication simple. Discover how to set up user registration, login, password reset, and protect routes with authentication middleware.',
                'slug_fr' => 'bases-authentification-laravel',
                'title_fr' => 'Bases de l\'authentification Laravel',
                'tldr_fr' => 'Implémentez une authentification sécurisée avec le scaffolding d\'authentification intégré de Laravel.',
                'content_fr' => 'Laravel simplifie l\'authentification. Découvrez comment configurer l\'inscription, la connexion, la réinitialisation de mot de passe et protéger les routes avec le middleware d\'authentification.',
                'published_at' => now()->subMonths(2)->subDays(15),
            ],
            [
                'author' => $userModels[1], // Michael (additional)
                'slug_en' => 'working-with-laravel-collections',
                'title_en' => 'Working with Laravel Collections',
                'tldr_en' => 'Master Laravel collections to manipulate arrays with powerful methods like map, filter, and reduce.',
                'content_en' => 'Collections are one of Laravel\'s most powerful features. Learn to chain methods, transform data, and write more expressive code with collection helpers.',
                'slug_fr' => 'travailler-avec-collections-laravel',
                'title_fr' => 'Travailler avec les collections Laravel',
                'tldr_fr' => 'Maîtrisez les collections Laravel pour manipuler des tableaux avec des méthodes puissantes comme map, filter et reduce.',
                'content_fr' => 'Les collections sont l\'une des fonctionnalités les plus puissantes de Laravel. Apprenez à chaîner les méthodes, transformer les données et écrire du code plus expressif avec les helpers de collection.',
                'published_at' => now()->subMonths(2),
            ],
            [
                'author' => $userModels[0], // Sarah (final - 5 total)
                'slug_en' => 'laravel-artisan-commands',
                'title_en' => 'Laravel Artisan Commands',
                'tldr_en' => 'Discover essential Artisan commands and learn to create custom commands for your application.',
                'content_en' => 'Artisan is Laravel\'s command-line interface. Explore built-in commands for common tasks and create your own custom commands to automate repetitive workflows.',
                'slug_fr' => 'commandes-artisan-laravel',
                'title_fr' => 'Commandes Artisan de Laravel',
                'tldr_fr' => 'Découvrez les commandes Artisan essentielles et apprenez à créer des commandes personnalisées pour votre application.',
                'content_fr' => 'Artisan est l\'interface en ligne de commande de Laravel. Explorez les commandes intégrées pour les tâches courantes et créez vos propres commandes pour automatiser les workflows répétitifs.',
                'published_at' => now()->subMonths(1)->subDays(20),
            ],
        ];

        foreach ($laravelArticles as $index => $article) {
            $post = BlogPost::firstOrCreate(
                ['slug' => $article['slug_en']],
                [
                    'blog_series_id' => $laravel->id,
                    'user_id' => $article['author']->id,
                    'is_published' => true,
                    'published_at' => $article['published_at'],
                    'order' => $index + 1,
                ]
            );

            BlogPostTranslation::firstOrCreate(
                ['blog_post_id' => $post->id, 'locale' => 'en'],
                [
                    'title' => $article['title_en'],
                    'tldr' => $article['tldr_en'],
                    'content' => $article['content_en'],
                ]
            );

            BlogPostTranslation::firstOrCreate(
                ['blog_post_id' => $post->id, 'locale' => 'fr'],
                [
                    'title' => $article['title_fr'],
                    'tldr' => $article['tldr_fr'],
                    'content' => $article['content_fr'],
                ]
            );
        }

        // Series 2: Vue.js Best Practices (2 authors)
        $vue = BlogSeries::firstOrCreate(
            ['slug' => 'vuejs-best-practices'],
            [
                'is_featured' => true,
                'published_at' => now()->subMonths(5),
            ]
        );

        BlogSeriesTranslation::firstOrCreate(
            ['blog_series_id' => $vue->id, 'locale' => 'en'],
            [
                'title' => 'Vue.js Best Practices',
                'description' => 'Master Vue.js with proven patterns and best practices for building scalable applications.',
            ]
        );

        BlogSeriesTranslation::firstOrCreate(
            ['blog_series_id' => $vue->id, 'locale' => 'fr'],
            [
                'title' => 'Bonnes pratiques Vue.js',
                'description' => 'Maîtrisez Vue.js avec des patterns éprouvés et des bonnes pratiques pour construire des applications évolutives.',
            ]
        );

        // Vue.js articles (distributed among 2 authors: David-6, Lisa-4)
        $vueArticles = [
            [
                'author' => $userModels[3], // David - 6 articles
                'slug_en' => 'vue3-composition-api-guide',
                'title_en' => 'Vue 3 Composition API Guide',
                'tldr_en' => 'Learn the Composition API in Vue 3 for better code organization and reusability.',
                'content_en' => 'The Composition API is a game-changer in Vue 3. Discover how to organize your logic with setup(), use reactive refs, and create composable functions.',
                'slug_fr' => 'guide-api-composition-vue3',
                'title_fr' => 'Guide de l\'API Composition Vue 3',
                'tldr_fr' => 'Apprenez l\'API Composition de Vue 3 pour une meilleure organisation et réutilisabilité du code.',
                'content_fr' => 'L\'API Composition est une révolution dans Vue 3. Découvrez comment organiser votre logique avec setup(), utiliser les refs réactives et créer des fonctions composables.',
                'published_at' => now()->subMonths(5),
            ],
            [
                'author' => $userModels[3], // David
                'slug_en' => 'reactive-state-management-vue',
                'title_en' => 'Reactive State Management in Vue',
                'tldr_en' => 'Master Vue\'s reactivity system and implement efficient state management patterns.',
                'content_en' => 'Understanding Vue\'s reactivity is crucial. Learn about reactive(), ref(), computed(), and when to use Pinia for application-wide state.',
                'slug_fr' => 'gestion-etat-reactif-vue',
                'title_fr' => 'Gestion d\'état réactif dans Vue',
                'tldr_fr' => 'Maîtrisez le système de réactivité de Vue et implémentez des patterns efficaces de gestion d\'état.',
                'content_fr' => 'Comprendre la réactivité de Vue est crucial. Apprenez reactive(), ref(), computed() et quand utiliser Pinia pour l\'état global.',
                'published_at' => now()->subMonths(4)->subDays(25),
            ],
            [
                'author' => $userModels[4], // Lisa - 4 articles
                'slug_en' => 'component-design-patterns',
                'title_en' => 'Component Design Patterns',
                'tldr_en' => 'Build maintainable Vue components using proven design patterns and best practices.',
                'content_en' => 'Well-designed components are the foundation of scalable Vue apps. Explore patterns like renderless components, slots, provide/inject, and prop drilling solutions.',
                'slug_fr' => 'patterns-conception-composants',
                'title_fr' => 'Patterns de conception de composants',
                'tldr_fr' => 'Construisez des composants Vue maintenables en utilisant des patterns éprouvés et des bonnes pratiques.',
                'content_fr' => 'Des composants bien conçus sont la base d\'applications Vue évolutives. Explorez les patterns comme les composants renderless, slots, provide/inject et solutions au prop drilling.',
                'published_at' => now()->subMonths(4)->subDays(10),
            ],
            [
                'author' => $userModels[4], // Lisa
                'slug_en' => 'vue-router-advanced-techniques',
                'title_en' => 'Vue Router Advanced Techniques',
                'tldr_en' => 'Implement advanced routing with navigation guards, lazy loading, and nested routes.',
                'content_en' => 'Take your Vue Router skills to the next level. Master navigation guards for authentication, optimize with lazy loading, and organize complex routes.',
                'slug_fr' => 'techniques-avancees-vue-router',
                'title_fr' => 'Techniques avancées Vue Router',
                'tldr_fr' => 'Implémentez du routing avancé avec les guards de navigation, lazy loading et routes imbriquées.',
                'content_fr' => 'Passez au niveau supérieur avec Vue Router. Maîtrisez les guards de navigation pour l\'authentification, optimisez avec le lazy loading et organisez les routes complexes.',
                'published_at' => now()->subMonths(3)->subDays(20),
            ],
            [
                'author' => $userModels[3], // David (additional)
                'slug_en' => 'vue-performance-optimization',
                'title_en' => 'Vue Performance Optimization',
                'tldr_en' => 'Optimize your Vue applications with code splitting, virtual scrolling, and memoization.',
                'content_en' => 'Performance matters. Learn techniques like v-once, v-memo, computed caching, component lazy loading, and virtual scrolling for large lists.',
                'slug_fr' => 'optimisation-performance-vue',
                'title_fr' => 'Optimisation des performances Vue',
                'tldr_fr' => 'Optimisez vos applications Vue avec le code splitting, le virtual scrolling et la mémoization.',
                'content_fr' => 'La performance compte. Apprenez des techniques comme v-once, v-memo, le cache computed, le lazy loading de composants et le virtual scrolling pour les grandes listes.',
                'published_at' => now()->subMonths(3),
            ],
            [
                'author' => $userModels[3], // David (additional)
                'slug_en' => 'testing-vue-components',
                'title_en' => 'Testing Vue Components',
                'tldr_en' => 'Write comprehensive tests for Vue components using Vitest and Vue Test Utils.',
                'content_en' => 'Testing ensures code quality. Master unit testing, integration testing, and E2E testing for Vue components with modern tools.',
                'slug_fr' => 'tester-composants-vue',
                'title_fr' => 'Tester les composants Vue',
                'tldr_fr' => 'Écrivez des tests complets pour vos composants Vue avec Vitest et Vue Test Utils.',
                'content_fr' => 'Les tests assurent la qualité du code. Maîtrisez les tests unitaires, d\'intégration et E2E pour les composants Vue avec des outils modernes.',
                'published_at' => now()->subMonths(2)->subDays(15),
            ],
            [
                'author' => $userModels[4], // Lisa (additional)
                'slug_en' => 'vue-typescript-integration',
                'title_en' => 'Vue & TypeScript Integration',
                'tldr_en' => 'Build type-safe Vue applications by integrating TypeScript with Vue 3.',
                'content_en' => 'TypeScript adds type safety to Vue. Learn to define prop types, create typed composables, and leverage IDE autocomplete for better DX.',
                'slug_fr' => 'integration-vue-typescript',
                'title_fr' => 'Intégration Vue & TypeScript',
                'tldr_fr' => 'Construisez des applications Vue type-safe en intégrant TypeScript avec Vue 3.',
                'content_fr' => 'TypeScript ajoute la sécurité des types à Vue. Apprenez à définir les types de props, créer des composables typés et exploiter l\'autocomplete IDE pour une meilleure DX.',
                'published_at' => now()->subMonths(2),
            ],
            [
                'author' => $userModels[4], // Lisa (final - 4 total)
                'slug_en' => 'vue-ssr-with-nuxt',
                'title_en' => 'Vue SSR with Nuxt',
                'tldr_en' => 'Implement server-side rendering for Vue applications using the Nuxt framework.',
                'content_en' => 'SSR improves SEO and initial load times. Discover how Nuxt simplifies Vue SSR with file-based routing, auto-imports, and hybrid rendering.',
                'slug_fr' => 'ssr-vue-avec-nuxt',
                'title_fr' => 'SSR Vue avec Nuxt',
                'tldr_fr' => 'Implémentez le server-side rendering pour les applications Vue avec le framework Nuxt.',
                'content_fr' => 'Le SSR améliore le SEO et les temps de chargement initiaux. Découvrez comment Nuxt simplifie le SSR Vue avec le routing basé sur les fichiers, les auto-imports et le rendu hybride.',
                'published_at' => now()->subMonths(1)->subDays(10),
            ],
            [
                'author' => $userModels[3], // David (additional - 6 total)
                'slug_en' => 'vue-animation-techniques',
                'title_en' => 'Vue Animation Techniques',
                'tldr_en' => 'Create smooth animations and transitions in Vue using built-in features and third-party libraries.',
                'content_en' => 'Animations enhance user experience. Master Vue\'s <Transition> and <TransitionGroup> components, and integrate libraries like GSAP for complex animations.',
                'slug_fr' => 'techniques-animation-vue',
                'title_fr' => 'Techniques d\'animation Vue',
                'tldr_fr' => 'Créez des animations et transitions fluides dans Vue avec les fonctionnalités intégrées et les bibliothèques tierces.',
                'content_fr' => 'Les animations améliorent l\'expérience utilisateur. Maîtrisez les composants <Transition> et <TransitionGroup> de Vue, et intégrez des bibliothèques comme GSAP pour des animations complexes.',
                'published_at' => now()->subMonths(1),
            ],
            [
                'author' => $userModels[3], // David (final - 6 total)
                'slug_en' => 'vue-custom-directives',
                'title_en' => 'Vue Custom Directives',
                'tldr_en' => 'Extend Vue\'s functionality by creating custom directives for DOM manipulation.',
                'content_en' => 'Custom directives provide reusable DOM manipulation logic. Learn to create directives for common tasks like click-outside, lazy-load, and tooltips.',
                'slug_fr' => 'directives-personnalisees-vue',
                'title_fr' => 'Directives personnalisées Vue',
                'tldr_fr' => 'Étendez les fonctionnalités de Vue en créant des directives personnalisées pour la manipulation du DOM.',
                'content_fr' => 'Les directives personnalisées fournissent une logique réutilisable de manipulation du DOM. Apprenez à créer des directives pour des tâches courantes comme click-outside, lazy-load et tooltips.',
                'published_at' => now()->subDays(25),
            ],
        ];

        foreach ($vueArticles as $index => $article) {
            $post = BlogPost::firstOrCreate(
                ['slug' => $article['slug_en']],
                [
                    'blog_series_id' => $vue->id,
                    'user_id' => $article['author']->id,
                    'is_published' => true,
                    'published_at' => $article['published_at'],
                    'order' => $index + 1,
                ]
            );

            BlogPostTranslation::firstOrCreate(
                ['blog_post_id' => $post->id, 'locale' => 'en'],
                [
                    'title' => $article['title_en'],
                    'tldr' => $article['tldr_en'],
                    'content' => $article['content_en'],
                ]
            );

            BlogPostTranslation::firstOrCreate(
                ['blog_post_id' => $post->id, 'locale' => 'fr'],
                [
                    'title' => $article['title_fr'],
                    'tldr' => $article['tldr_fr'],
                    'content' => $article['content_fr'],
                ]
            );
        }

        // Series 3: Golang Essentials (NEW - 3 authors)
        $golang = BlogSeries::firstOrCreate(
            ['slug' => 'golang-essentials'],
            [
                'is_featured' => true,
                'published_at' => now()->subMonths(4),
            ]
        );

        BlogSeriesTranslation::firstOrCreate(
            ['blog_series_id' => $golang->id, 'locale' => 'en'],
            [
                'title' => 'Golang Essentials',
                'description' => 'Master Go programming with this essential guide covering fundamentals to advanced concepts.',
            ]
        );

        BlogSeriesTranslation::firstOrCreate(
            ['blog_series_id' => $golang->id, 'locale' => 'fr'],
            [
                'title' => 'Essentiels de Golang',
                'description' => 'Maîtrisez la programmation Go avec ce guide essentiel couvrant les fondamentaux aux concepts avancés.',
            ]
        );

        // Golang articles (distributed among 3 authors: Emma-4, Sarah-3, Michael-3)
        $golangArticles = [
            [
                'author' => $userModels[2], // Emma - 4 articles
                'slug_en' => 'introduction-to-golang',
                'title_en' => 'Introduction to Golang',
                'tldr_en' => 'Get started with Go by understanding its philosophy, syntax, and unique features.',
                'content_en' => 'Go is a statically typed, compiled language designed for simplicity and efficiency. Learn about its concurrency model, fast compilation, and why companies like Google and Uber use it.',
                'slug_fr' => 'introduction-golang',
                'title_fr' => 'Introduction à Golang',
                'tldr_fr' => 'Débutez avec Go en comprenant sa philosophie, sa syntaxe et ses fonctionnalités uniques.',
                'content_fr' => 'Go est un langage compilé et typé statiquement conçu pour la simplicité et l\'efficacité. Découvrez son modèle de concurrence, sa compilation rapide et pourquoi des entreprises comme Google et Uber l\'utilisent.',
                'published_at' => now()->subMonths(4),
            ],
            [
                'author' => $userModels[2], // Emma
                'slug_en' => 'go-data-types-variables',
                'title_en' => 'Go Data Types & Variables',
                'tldr_en' => 'Master Go\'s type system including primitives, structs, pointers, and type inference.',
                'content_en' => 'Understanding Go\'s type system is fundamental. Explore basic types, custom types with structs, pointer mechanics, and how := short declaration works.',
                'slug_fr' => 'types-variables-go',
                'title_fr' => 'Types de données & variables Go',
                'tldr_fr' => 'Maîtrisez le système de types de Go incluant les primitives, structs, pointeurs et l\'inférence de types.',
                'content_fr' => 'Comprendre le système de types de Go est fondamental. Explorez les types de base, les types personnalisés avec structs, la mécanique des pointeurs et comment fonctionne la déclaration courte :=.',
                'published_at' => now()->subMonths(3)->subDays(25),
            ],
            [
                'author' => $userModels[0], // Sarah - 3 articles
                'slug_en' => 'goroutines-concurrency',
                'title_en' => 'Goroutines & Concurrency',
                'tldr_en' => 'Learn Go\'s powerful concurrency model with goroutines and channels for parallel processing.',
                'content_en' => 'Goroutines are lightweight threads managed by the Go runtime. Discover how to spawn concurrent tasks, communicate with channels, and avoid race conditions.',
                'slug_fr' => 'goroutines-concurrence',
                'title_fr' => 'Goroutines & Concurrence',
                'tldr_fr' => 'Apprenez le puissant modèle de concurrence de Go avec les goroutines et channels pour le traitement parallèle.',
                'content_fr' => 'Les goroutines sont des threads légers gérés par le runtime Go. Découvrez comment lancer des tâches concurrentes, communiquer avec les channels et éviter les race conditions.',
                'published_at' => now()->subMonths(3)->subDays(10),
            ],
            [
                'author' => $userModels[0], // Sarah
                'slug_en' => 'error-handling-go',
                'title_en' => 'Error Handling in Go',
                'tldr_en' => 'Implement robust error handling with Go\'s explicit error returns and error wrapping.',
                'content_en' => 'Go uses explicit error handling instead of exceptions. Learn idiomatic patterns, error wrapping with fmt.Errorf, and custom error types.',
                'slug_fr' => 'gestion-erreurs-go',
                'title_fr' => 'Gestion des erreurs en Go',
                'tldr_fr' => 'Implémentez une gestion d\'erreurs robuste avec les retours d\'erreur explicites et l\'encapsulation d\'erreurs de Go.',
                'content_fr' => 'Go utilise la gestion explicite des erreurs plutôt que les exceptions. Apprenez les patterns idiomatiques, l\'encapsulation d\'erreurs avec fmt.Errorf et les types d\'erreurs personnalisés.',
                'published_at' => now()->subMonths(2)->subDays(20),
            ],
            [
                'author' => $userModels[1], // Michael - 3 articles
                'slug_en' => 'go-interfaces-polymorphism',
                'title_en' => 'Go Interfaces & Polymorphism',
                'tldr_en' => 'Achieve polymorphism in Go with interfaces and understand implicit interface implementation.',
                'content_en' => 'Interfaces define behavior in Go. Learn how to create flexible, testable code with interface types and discover Go\'s implicit interface satisfaction.',
                'slug_fr' => 'interfaces-polymorphisme-go',
                'title_fr' => 'Interfaces & Polymorphisme Go',
                'tldr_fr' => 'Réalisez le polymorphisme en Go avec les interfaces et comprenez l\'implémentation implicite d\'interface.',
                'content_fr' => 'Les interfaces définissent le comportement en Go. Apprenez à créer du code flexible et testable avec les types d\'interface et découvrez la satisfaction implicite d\'interface de Go.',
                'published_at' => now()->subMonths(2),
            ],
            [
                'author' => $userModels[1], // Michael
                'slug_en' => 'go-testing-practices',
                'title_en' => 'Go Testing Best Practices',
                'tldr_en' => 'Write effective unit tests, benchmarks, and table-driven tests in Go.',
                'content_en' => 'Testing is built into Go. Master the testing package, write table-driven tests, use subtests, and measure performance with benchmarks.',
                'slug_fr' => 'pratiques-tests-go',
                'title_fr' => 'Bonnes pratiques de tests Go',
                'tldr_fr' => 'Écrivez des tests unitaires efficaces, benchmarks et tests pilotés par des tables en Go.',
                'content_fr' => 'Les tests sont intégrés à Go. Maîtrisez le package testing, écrivez des tests pilotés par des tables, utilisez les sous-tests et mesurez les performances avec les benchmarks.',
                'published_at' => now()->subMonths(1)->subDays(15),
            ],
            [
                'author' => $userModels[2], // Emma (additional)
                'slug_en' => 'go-modules-dependencies',
                'title_en' => 'Go Modules & Dependencies',
                'tldr_en' => 'Manage project dependencies efficiently with Go modules and semantic versioning.',
                'content_en' => 'Go modules revolutionized dependency management. Learn to initialize modules, add dependencies, vendor packages, and understand go.mod and go.sum files.',
                'slug_fr' => 'modules-dependances-go',
                'title_fr' => 'Modules & Dépendances Go',
                'tldr_fr' => 'Gérez les dépendances de projet efficacement avec les modules Go et le versionnage sémantique.',
                'content_fr' => 'Les modules Go ont révolutionné la gestion des dépendances. Apprenez à initialiser des modules, ajouter des dépendances, vendor des packages et comprendre les fichiers go.mod et go.sum.',
                'published_at' => now()->subMonths(1),
            ],
            [
                'author' => $userModels[2], // Emma (final - 4 total)
                'slug_en' => 'go-web-development',
                'title_en' => 'Go Web Development',
                'tldr_en' => 'Build web applications and REST APIs using Go\'s standard library and popular frameworks.',
                'content_en' => 'Go excels at web development. Create HTTP servers with net/http, build REST APIs, handle routing, and explore frameworks like Gin and Echo.',
                'slug_fr' => 'developpement-web-go',
                'title_fr' => 'Développement web Go',
                'tldr_fr' => 'Construisez des applications web et des API REST en utilisant la bibliothèque standard de Go et les frameworks populaires.',
                'content_fr' => 'Go excelle dans le développement web. Créez des serveurs HTTP avec net/http, construisez des API REST, gérez le routing et explorez les frameworks comme Gin et Echo.',
                'published_at' => now()->subDays(20),
            ],
            [
                'author' => $userModels[0], // Sarah (final - 3 total)
                'slug_en' => 'go-database-integration',
                'title_en' => 'Go Database Integration',
                'tldr_en' => 'Connect Go applications to databases using database/sql and popular ORMs.',
                'content_en' => 'Database integration is straightforward in Go. Use database/sql for raw queries, implement connection pooling, and explore ORMs like GORM.',
                'slug_fr' => 'integration-base-donnees-go',
                'title_fr' => 'Intégration de base de données Go',
                'tldr_fr' => 'Connectez les applications Go aux bases de données avec database/sql et les ORM populaires.',
                'content_fr' => 'L\'intégration de base de données est simple en Go. Utilisez database/sql pour les requêtes brutes, implémentez le pooling de connexions et explorez les ORM comme GORM.',
                'published_at' => now()->subDays(10),
            ],
            [
                'author' => $userModels[1], // Michael (final - 3 total)
                'slug_en' => 'go-microservices-architecture',
                'title_en' => 'Go Microservices Architecture',
                'tldr_en' => 'Design and build scalable microservices with Go, gRPC, and container orchestration.',
                'content_en' => 'Go is perfect for microservices. Learn service decomposition, implement gRPC communication, use Docker for containerization, and deploy with Kubernetes.',
                'slug_fr' => 'architecture-microservices-go',
                'title_fr' => 'Architecture microservices Go',
                'tldr_fr' => 'Concevez et construisez des microservices évolutifs avec Go, gRPC et l\'orchestration de conteneurs.',
                'content_fr' => 'Go est parfait pour les microservices. Apprenez la décomposition de services, implémentez la communication gRPC, utilisez Docker pour la conteneurisation et déployez avec Kubernetes.',
                'published_at' => now()->subDays(5),
            ],
        ];

        foreach ($golangArticles as $index => $article) {
            $post = BlogPost::firstOrCreate(
                ['slug' => $article['slug_en']],
                [
                    'blog_series_id' => $golang->id,
                    'user_id' => $article['author']->id,
                    'is_published' => true,
                    'published_at' => $article['published_at'],
                    'order' => $index + 1,
                ]
            );

            BlogPostTranslation::firstOrCreate(
                ['blog_post_id' => $post->id, 'locale' => 'en'],
                [
                    'title' => $article['title_en'],
                    'tldr' => $article['tldr_en'],
                    'content' => $article['content_en'],
                ]
            );

            BlogPostTranslation::firstOrCreate(
                ['blog_post_id' => $post->id, 'locale' => 'fr'],
                [
                    'title' => $article['title_fr'],
                    'tldr' => $article['tldr_fr'],
                    'content' => $article['content_fr'],
                ]
            );
        }

        $this->command->info('✅ Series with multiple authors created successfully!');
        $this->command->info('   - Laravel for Beginners: 10 articles by Sarah (5), Michael (3), Emma (2)');
        $this->command->info('   - Vue.js Best Practices: 10 articles by David (6), Lisa (4)');
        $this->command->info('   - Golang Essentials: 10 articles by Emma (4), Sarah (3), Michael (3)');
    }
}
