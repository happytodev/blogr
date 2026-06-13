<?php

// translations for Happytodev/Blogr
return [
    'users' => [
        'model_label' => 'Utilisateur',
        'navigation_label' => 'Utilisateurs',
        'plural_model_label' => 'Utilisateurs',
    ],

    // Blog UI
    'ui' => [
        'about_the_author' => 'À propos de l\'auteur',
        'back_to_all_posts' => 'Retour à tous les articles',
        'back_to_blog' => 'Retour au blog',
        'check_back_soon' => 'Revenez bientôt pour du nouveau contenu !',
        'current' => 'actuel',
        'featured' => 'En vedette',
        'latest_posts' => 'Derniers articles',
        'min_read' => ':minutes min de lecture',
        'no_posts_yet' => 'Aucun article pour le moment',
        'part_of_series' => 'Partie de la série',
        'post_in_series' => ':count article dans cette série',
        'posts_in_category' => 'Articles dans la catégorie',
        'posts_in_series' => ':count articles dans cette série',
        'posts_with_tag' => 'Articles avec le tag',
        'read_more' => 'Lire la suite',
        'read_post' => 'Lire l\'article',
        'reading_time' => ':time min de lecture',
        'table_of_contents' => 'Table des matières',
        'tags' => 'Tags',
        'translation_available_in' => 'Disponible en',
        'translation_unavailable_message' => 'Ce contenu n\'est pas disponible en :requested. Version :showing affichée à la place.',
        'translation_unavailable_title' => 'Traduction non disponible',
        'untitled' => 'Sans titre',
        'visit_site' => 'Visiter le blog',
        'quick_access' => 'Accès rapide',
        'visit_blog' => 'Ouvrir dans un nouvel onglet',
        'view_website' => 'Voir le site',
    ],

    // Series
    'series' => [
        'all_posts_in_series' => 'Tous les articles de cette série',
        'authors' => 'Auteurs',
        'current' => 'actuel',
        'description' => 'Parcourez toutes nos séries de blog et apprenez étape par étape.',
        'featured' => 'En vedette',
        'featured_series' => 'Séries en vedette',
        'no_series' => 'Aucune série publiée pour le moment',
        'part_number' => 'Partie :number',
        'part_of_series' => 'Partie de la série',
        'posts_count' => ':count articles',
        'series' => 'Cette article fait partie de la série',
        'show_less_posts' => 'Voir moins',
        'show_more_posts' => 'Voir les :count articles supplémentaires dans cette série',
        'started_on' => 'Commencé le :date',
        'title' => 'Séries de blog',
        'view_all_series' => 'Voir toutes les séries',
        'view_serie' => 'Voir la série',
    ],

    // Dates
    'date' => [
        'draft' => 'Brouillon',
        'published_on' => 'Publié le :date',
        'updated_on' => 'Mis à jour le :date',
    ],

    // Notifications
    'notifications' => [
        'post_saved_subject' => '[Blogr] Article enregistré par :author',
        'post_saved_line1' => 'L\'utilisateur :author a enregistré un article intitulé ":title".',
        'post_saved_line2' => 'Vous recevez cette notification car vous êtes administrateur.',
        'view_post' => 'Voir l\'article',
    ],

    // Settings
    'settings' => [
        'save' => 'Enregistrer les paramètres',
        'navigation_label' => 'Paramètres',
        'saved_successfully' => 'Paramètres enregistrés avec succès !',
        'run_sync_command' => 'Pour appliquer le nouveau chemin d\'accès à l\'interface d\'administration, exécutez en console : php artisan blogr:sync-admin-path',
        'env_not_writable' => 'Attention : le fichier .env n\'est pas accessible en écriture. Les identifiants mail n\'ont pas été sauvegardés.',
    ],

    // Feeds
    'feeds' => [
        'title' => 'Flux RSS',
        'description' => 'Abonnez-vous à nos flux RSS pour rester informé des derniers contenus.',
        'main_feed' => 'Flux principal',
        'all_posts' => 'Tous les articles',
        'main_feed_desc' => 'Derniers articles de toutes les catégories',
        'categories' => 'Catégories',
        'tags' => 'Tags',
        'posts' => 'articles',
    ],

    // AI Translation
    'translation' => [
        'local_counter' => 'Compteur local : caractères traduits via cette instance uniquement. Le total peut être plus élevé si la même clé est utilisée depuis plusieurs sites.',
        'view_azure_usage' => 'Voir l\'utilisation réelle sur le portail Azure →',
        'azure_metrics_help' => 'Allez dans <strong>Supervision &gt; Métriques</strong>, choisissez <em>Text Characters Translated</em> comme métrique et réglez la plage de date en haut à droite du graphique.',
        'chars' => 'car.',
        'remaining' => 'restants',
        'select_provider' => 'Sélectionnez un fournisseur pour voir les statistiques d\'utilisation.',
        'no_data' => 'Aucune donnée d\'utilisation pour ce mois.',
    ],
];
