# RGPD Compliance Plan for Blogr

> **Note** : Le RGPD (Règlement Général sur la Protection des Données) est un règlement européen. Blogr est un CMS auto-hébergé. Le responsable du traitement est l'installateur. Blogr fournit les outils pour permettre la conformité. Un plugin séparé `blogr/gdpr` implémente les fonctionnalités RGPD avancées.

---

## Table des matières

1. [Architecture proposée : Core + Plugin](#1-architecture-proposée--core--plugin)
2. [Phase 1 : Préparer le core (hooks)](#2-phase-1--préparer-le-core-hooks)
3. [Phase 2 : Plugin GDPR `blogr/gdpr`](#3-phase-2--plugin-gdpr-blogrgdpr)
4. [Phase 3 : Architecture plugin](#4-phase-3--architecture-plugin)
5. [Annexe : Audits & Références](#5-annexe--audits--références)

---

## 1. Architecture proposée : Core + Plugin

### Principe

Le RGPD concerne uniquement les visiteurs européens. Un site aux US ou au Japon n'en a pas besoin. Pour éviter d'alourdir le core, les fonctionnalités RGPD sont fournies via un **plugin optionnel** `blogr/gdpr`.

**Ce qui reste dans le core** (utile pour tous, pas que le RGPD) :

- `@stack()` dans les vues clés (points d'injection pour plugins)
- Events (ContactFormSubmitted, AnalyticsScriptRendered, etc.)
- Nettoyage des logs (suppression des données personnelles dans les logs d'erreur)
- Option anonymize IP dans les settings analytics

**Ce qui part dans le plugin `blogr/gdpr`** :

- Cookie consent banner
- Analytics gating (conditionner le chargement des scripts au consentement)
- Consent checkbox dans le formulaire de contact
- Privacy policy CMS template + page de démo
- Data export utilisateur (portabilité)
- Data erasure utilisateur (droit à l'effacement)
- GDPR settings tab (paramètres additionnels)
- Consent logs (table de traçabilité)
- Newsletter backend (double opt-in + subscribers)
- Footer privacy link
- Registre des traitements (admin)

---

## 2. Phase 1 : Préparer le core (hooks)

### 2.1 Blade stacks à ajouter

**Fichier : `resources/views/layouts/blog.blade.php`**

```blade
<head>
    ...
    @stack('meta')
    @stack('head-styles')
    @stack('head-scripts')
    @stack('head-end')
</head>
<body>
    @yield('content')

    @stack('body-scripts')
    @stack('cookie-consent')
    @stack('body-end')
</body>
```

**Fichier : `resources/views/components/analytics-tracker.blade.php`**

```blade
@stack('analytics-consent')

<script>
    // existing analytics code wrapped in consent check
</script>
```

**Fichier : `resources/views/components/blocks/contact_form.blade.php`**

```blade
@stack('contact-form-extras')
```

**Fichier : `resources/views/components/footer.blade.php`**

```blade
@stack('footer-links')
```

**Fichier : `resources/views/components/navigation.blade.php`**

```blade
@stack('nav-links-end')
```

**Nouveau fichier : `resources/views/components/blocks/newsletter.blade.php`**

Ajouter stacks pour permettre au plugin de brancher le vrai backend newsletter.

**Fichier : profil utilisateur frontend (à créer)**

```blade
@stack('user-profile-actions')
```

### 2.2 Events à créer

**Dossier : `src/Events/`**

```php
# src/Events/ContactFormSubmitted.php
class ContactFormSubmitted
{
    public function __construct(
        public string $name,
        public string $email,
        public string $message,
        public string $ip,
    ) {}
}

# src/Events/AnalyticsScriptRendered.php
class AnalyticsScriptRendered
{
    public function __construct(
        public string $provider,
        public bool $consentGiven,
    ) {}
}

# src/Events/UserDataExported.php
class UserDataExported
{
    public function __construct(
        public $user,
        public string $format,
    ) {}
}

# src/Events/UserAccountDeleted.php
class UserAccountDeleted
{
    public function __construct(
        public $user,
        public bool $anonymizePosts,
    ) {}
}

# src/Events/SettingsSaved.php
class SettingsSaved
{
    public function __construct(
        public array $data,
        public array $previousData,
    ) {}
}
```

### 2.3 Nettoyage des logs

**Fichier : `src/Http/Controllers/CmsContactController.php`**

Remplacer le log actuel (qui contient les données personnelles + credentials mail) par :

```php
Log::warning('Blogr contact form failed', [
    'error_type' => get_class($e),
    'error_code' => $e->getCode(),
]);
```

Ne plus logger : message d'erreur complet, trace, credentials mail, email destinataire, données du formulaire.

**Fichier : `src/Services/BlogrImportService.php`**

Parcourir tous les `Log::info()` et `Log::error()` — remplacer les logs contenant des emails, slugs, noms par des logs anonymisés (IDs seulement).

**Fichier : `src/Filament/Pages/BlogrSettings.php`**

Supprimer ou anonymiser les logs contenant des paths de fichiers ou des données utilisateur.

### 2.4 Option anonymize IP dans analytics

**Fichier : `src/Filament/Pages/BlogrSettings.php`**

Ajouter dans l'onglet Analytics un toggle :

```php
Toggle::make('analytics_anonymize_ip')
    ->label('Anonymize IP addresses')
    ->helperText('Removes the last octet of visitor IP addresses before sending to analytics providers. Recommended for GDPR compliance.')
    ->default(true)
    ->columnSpan(1),
```

**Fichier : `resources/views/components/analytics-tracker.blade.php`**

Ajouter `'anonymize_ip' => true` au snippet Google Analytics :

```js
gtag('config', '{{ $measurementId }}', {
    'anonymize_ip': true
});
```

Et pour Matomo :

```js
_paq.push([['setDocumentTitle', ...], ['setDoNotTrack', true], ['enableJSErrors'], ['enableHeartBeatTimer']]);
// Ajouter :
_paq.push([['setAnonymizeIp', true]]);
```

### 2.5 Ajouter les stacks aux vues existantes

Voir section 2.1 ci-dessus pour la liste exhaustive.

---

## 3. Phase 2 : Plugin GDPR `blogr/gdpr`

Le plugin est un package Laravel/Filament séparé qui s'installe via Composer :

```
composer require happytodev/blogr-gdpr
```

### 3.1 Architecture du package

```
blogr-gdpr/
├── composer.json
├── src/
│   ├── BlogrGdprServiceProvider.php
│   ├── BlogrGdprPlugin.php              (Filament plugin si nécessaire)
│   ├── Listeners/
│   │   ├── HandleContactFormConsent.php  (écoute ContactFormSubmitted)
│   │   └── HandleAnalyticsConsent.php    (écoute AnalyticsScriptRendered)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DataExportController.php
│   │   │   ├── DataErasureController.php
│   │   │   └── NewsletterController.php
│   │   └── Middleware/
│   │       └── CheckConsent.php
│   ├── Models/
│   │   ├── ConsentLog.php
│   │   └── NewsletterSubscriber.php
│   ├── Filament/
│   │   └── Pages/
│   │       └── GdprSettings.php
│   └── Enums/
│       └── ConsentType.php
├── database/
│   └── migrations/
│       ├── xxxx_create_consent_logs_table.php
│       └── xxxx_create_newsletter_subscribers_table.php
├── resources/
│   ├── views/
│   │   ├── components/
│   │   │   ├── cookie-consent.blade.php
│   │   │   ├── consent-checkbox.blade.php
│   │   │   └── privacy-policy-link.blade.php
│   │   └── filaments/
│   │       └── pages/
│   │           └── gdpr-settings.blade.php
│   └── lang/
│       └── en/
│           └── gdpr.php
├── routes/
│   └── web.php
└── tests/
    ├── ConsentTest.php
    ├── DataExportTest.php
    └── NewsletterTest.php
```

### 3.2 Fonctionnalités du plugin

#### 3.2.1 Cookie Consent Banner

- Banner plein largeur en bas de page avec Alpine.js
- 3 boutons : "Accepter tous", "Refuser", "Paramètres"
- Stockage dans localStorage : `blogr_consent` avec timestamp et liste des providers acceptés
- Ne s'affiche que si analytics est activé ET que le provider nécessite des cookies (Google, Matomo)
- Ne s'affiche pas pour Plausible/Umami (cookieless)

**Modal paramètres** :
- Cases à cocher par catégorie : Analytics, Marketing, Fonctionnels
- Blogr ne gère que les analytics → bouton unique

#### 3.2.2 Analytics Gating

- Écoute l'event `AnalyticsScriptRendered`
- Vérifie `localStorage.blogr_consent` avant d'injecter le script
- Injecte le script via JavaScript après consentement (pas de chargement bloquant)
- Pour Google Analytics : utilise `gtag` avec `'allow_google_signals': false` si marketing refusé

#### 3.2.3 Consent Checkbox (Contact Form)

- Injecte via `@stack('contact-form-extras')` une checkbox obligatoire
- Texte : "J'accepte que ce site stocke les informations soumises pour répondre à ma demande."
- Lien vers la page politique de confidentialité
- Bouton submit désactivé tant que la checkbox n'est pas cochée
- Logge le consentement dans `consent_logs` avec date et IP anonymisée

#### 3.2.4 Privacy Policy

- Ajoute un slug réservé `privacy-policy`
- Ajoute un template `privacy` avec contenu générique adapté aux fonctionnalités de Blogr
- Ajoute une page de démo dans le seeder avec texte complet RGPD
- Contenu générique couvrant :
  - Qui collecte les données
  - Quelles données sont collectées
  - Finalités
  - Base légale
  - Durée de conservation
  - Destinataires
  - Vos droits
  - Contact DPO

#### 3.2.5 Data Portability (User Export)

- Nouvelle page dans le profil frontend : `GET /account/export`
- Export JSON des données personnelles de l'utilisateur connecté
- Données incluses : name, email, bio (toutes locales), posts écrits, date d'inscription
- Utilise `BlogrExportService` filtré par `user_id`
- Fichier téléchargeable, supprimé après envoi

#### 3.2.6 Data Erasure (Right to be Forgotten)

- Nouvelle action dans le profil frontend : "Delete my account"
- Processus :
  1. L'utilisateur clique "Delete my account"
  2. Modal de confirmation avec checkbox "I understand this is irreversible"
  3. Email de confirmation envoyé (vérification)
  4. Après confirmation : suppression OU anonymisation
  5. Supprime : User, UserTranslations
  6. Anonymise : posts (auteur → "Anonymous"), commentaires (si existent)
  7. Log dans `consent_logs`

#### 3.2.7 DPO Contact Settings

- Nouvel onglet "GDPR" dans les settings (via `@stack` ou extension)
- Champs :
  - DPO Name
  - DPO Email
  - Data retention period (dropdown : 6, 12, 24 months)
  - Privacy policy page (select from CMS pages)
  - Cookie consent position (bottom / bottom-left / bottom-right)
- Affiché uniquement si le plugin est installé

#### 3.2.8 Consent Logs

- Table `consent_logs` :
  - `id`, `type` (cookie, contact, newsletter), `granted` (bool), `ip_anonymized`, `user_agent` (anonymisé), `locale`, `created_at`
  - PAS d'email, PAS d'IP complète
- Consultable dans l'admin : "Consent History" (tableau filtré par type et date)
- Exportable pour prouver la conformité en cas de contrôle CNIL

#### 3.2.9 Newsletter avec double opt-in

- Table `newsletter_subscribers` :
  - `id`, `email`, `locale`, `confirmed_at`, `unsubscribed_at`, `token`, `created_at`
- Processus :
  1. L'utilisateur saisit son email dans le bloc newsletter
  2. Email de confirmation avec lien (double opt-in)
  3. Après confirmation, abonné actif
  4. Lien de désabonnement dans chaque email
  5. Export des abonnés depuis l'admin
- Utilise la config mail de Blogr (Brevo, SMTP, etc.)

#### 3.2.10 Footer Privacy Link

- Injecte un lien "Privacy Policy" via `@stack('footer-links')`
- Lien configurable dans les settings GDPR
- Texte : "Privacy Policy" / "Politique de confidentialité" selon la locale

### 3.3 Tests du plugin

- `ConsentTest.php` — test du cookie banner (affichage, acceptation, refus)
- `DataExportTest.php` — test de l'export des données utilisateur
- `NewsletterTest.php` — test du double opt-in, confirmation, désabonnement
- `DataErasureTest.php` — test de la suppression de compte, anonymisation
- `ContactConsentTest.php` — test de la checkbox dans le formulaire de contact
- `PrivacyPolicyTest.php` — test de l'affichage de la page politique

---

## 4. Phase 3 : Architecture plugin

### 4.1 Points d'extension du core

Le core expose des points d'extension pour que les plugins puissent se greffer sans modifier le code source :

| Mécanisme | Usage |
|---|---|
| **Blade `@stack()`** | Injection de contenu dans les vues |
| **Events** | Hooks pour écouter et réagir aux actions |
| **Contracts / Interfaces** | classes que les plugins peuvent implémenter (ex: `AnalyticsProvider`) |
| **Config merge** | Les plugins peuvent publier dans `config/blogr.php` via `mergeConfigFrom()` |
| **Filament extension** | Les plugins peuvent ajouter des onglets aux settings via des events |

### 4.2 Comment créer un plugin Blogr

Documenté dans `docs/PLUGIN_ARCHITECTURE.md` (à créer). Le principe général :

```php
// ServiceProvider du plugin
class BlogrGdprServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Écouter les events du core
        Event::listen(
            \Happytodev\Blogr\Events\ContactFormSubmitted::class,
            \Happytodev\BlogrGdpr\Listeners\HandleContactFormConsent::class
        );

        // Injecter des vues via les stacks
        View::composer('blogr::layouts.blog', function ($view) {
            $view->with('gdprConsent', $this->getConsentData());
        });

        // Publier les migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publier les vues
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'blogr-gdpr');

        // Enregistrer les routes
        Route::middleware('web')->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }
}
```

---

## 5. Annexe : Audits & Références

### 5.1 Données personnelles collectées par Blogr (core)

| Donnée | Où | Finalité | Base légale | Durée de conservation |
|---|---|---|---|---|
| Name, Email | User model | Identification pour l'authentification | Contrat (exécution du service) | Durée du compte |
| Bio | User model, UserTranslation | Affichage profil auteur | Consentement explicite | Durée du compte |
| Avatar | User model | Photo de profil | Consentement explicite | Durée du compte |
| Slug (pseudo) | User model | URL du profil auteur | Intérêt légitime | Durée du compte |
| Mot de passe (hashé) | User model | Authentification | Contrat | Durée du compte + 6 mois |
| Name, Email, Message | Contact form (email uniquement) | Réponse à la demande | Consentement explicite | Non conservé |
| Adresse IP | Analytics providers | Statistiques de fréquentation | Consentement (si cookies requis) | Variable selon provider |
| Cookies _ga, _gid, _gat | Google Analytics | Analytics | Consentement | 2 ans max |
| Cookies _pk_* | Matomo | Analytics | Consentement | 1 an max |
| Thème choisi | localStorage (navigateur) | Préférence d'affichage | Intérêt légitime | Jusqu'à effacement localStorage |

### 5.2 Sous-traitants utilisés par Blogr

| Service | Données transmises | Localisation | Garanties |
|---|---|---|---|
| Bunny CDN (fonts) | IP du visiteur | Monde (CDN) | Pas d'accord DPC |
| Brevo (email) | email destinataire + contenu | UE (serveurs Brevo) | DPC signé |
| Google Analytics | IP, user-agent, navigation | Monde | DPC signé, Privacy Shield |
| Plausible | IP anonymisée, user-agent | UE | Cookieless, pas de DPC requis |
| Umami | IP anonymisée, user-agent | Variable | Cookieless, pas de DPC requis |
| YouTube (via oembed) | IP du visiteur | Monde | DPC signé |
| Vimeo (via oembed) | IP du visiteur | Monde | DPC signé |
| OpenStreetMap (map block) | IP du visiteur | Monde | Pas d'accord DPC |

### 5.3 Références légales

- [RGPD — Règlement (UE) 2016/679](https://eur-lex.europa.eu/legal-content/FR/TXT/?uri=CELEX:32016R0679)
- [CNIL — Guide de la sécurité des données personnelles](https://www.cnil.fr/fr/securite-des-donnees)
- [CNIL — Cookie consent](https://www.cnil.fr/fr/cookies-et-traceurs-que-dit-la-loi)
- [DPC — Standard Contractual Clauses](https://ec.europa.eu/info/law/law-topic/data-protection/international-dimension-data-protection/standard-contractual-clauses-scc_fr)
- [Brevo DPC](https://www.brevo.com/legal/terms-of-use/)
- [Google GDPR compliance](https://privacy.google.com/businesses/compliance/)

---

*Document généré pour Blogr v1.2.1. Mise à jour : 2026-06-07*
