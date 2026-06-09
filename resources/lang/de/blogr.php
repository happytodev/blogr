<?php

// translations for Happytodev/Blogr
return [
    'users' => [
        'navigation_label' => 'Benutzer',
        'model_label' => 'Benutzer',
        'plural_model_label' => 'Benutzer',
    ],

    // Blog UI
    'ui' => [
        'table_of_contents' => 'Inhaltsverzeichnis',
        'back_to_all_posts' => 'Zurück zu allen Beiträgen',
        'back_to_blog' => 'Zurück zum Blog',
        'read_more' => 'Mehr lesen',
        'read_post' => 'Beitrag lesen',
        'no_posts_yet' => 'Noch keine Beiträge',
        'check_back_soon' => 'Schauen Sie bald wieder vorbei für neue Inhalte!',
        'featured' => 'Hervorgehoben',
        'part_of_series' => 'Teil der Serie',
        'post_in_series' => ':count Beitrag in dieser Serie',
        'posts_in_category' => 'Beiträge in dieser Kategorie',
        'posts_in_series' => ':count Beiträge in dieser Serie',
        'posts_with_tag' => 'Beiträge mit dem Tag',
        'current' => 'aktuell',
        'tags' => 'Tags',
        'reading_time' => ':time Min. Lesezeit',
        'min_read' => ':minutes Min. Lesezeit',
        'untitled' => 'Ohne Titel',
        'translation_unavailable_title' => 'Übersetzung nicht verfügbar',
        'translation_unavailable_message' => 'Dieser Inhalt ist nicht in :requested verfügbar. Zeige :showing Version stattdessen.',
        'translation_available_in' => 'Verfügbar in',
        'visit_site' => 'Blog besuchen',
        'quick_access' => 'Schnellzugriff',
        'visit_blog' => 'In neuer Registerkarte öffnen',
        'view_website' => 'Website anzeigen',
    ],

    // Series
    'series' => [
        'title' => 'Blog-Serien',
        'description' => 'Durchsuchen Sie alle unsere Blog-Serien und lernen Sie Schritt für Schritt.',
        'no_series' => 'Noch keine Serien veröffentlicht',
        'part_of_series' => 'Teil der Serie',
        'posts_count' => ':count Beiträge',
        'view_serie' => 'Serie anzeigen',
        'started_on' => 'Begonnen am :date',
        'all_posts_in_series' => 'Alle Beiträge in dieser Serie',
        'part_number' => 'Teil :number',
        'authors' => 'Autoren',
        'current' => 'aktuell',
        'view_all_series' => 'Alle Serien ansehen',
    ],

    // Dates
    'date' => [
        'published_on' => 'Veröffentlicht am :date',
        'updated_on' => 'Aktualisiert am :date',
        'draft' => 'Entwurf',
    ],

    // Settings
    'settings' => [
        'save' => 'Einstellungen speichern',
        'navigation_label' => 'Einstellungen',
        'saved_successfully' => 'Einstellungen erfolgreich gespeichert!',
        'run_sync_command' => 'Um den neuen Admin-Panel-Pfad anzuwenden, führen Sie aus: php artisan blogr:sync-admin-path',
        'env_not_writable' => 'Warnung: Die .env-Datei ist nicht beschreibbar. E-Mail-Anmeldedaten wurden nicht gespeichert.',
    ],

    // Notifications
    'notifications' => [
        'post_saved_subject' => '[Blogr] Artikel von :author gespeichert',
        'post_saved_line1' => 'Der Benutzer :author hat einen Artikel mit dem Titel ":title" gespeichert.',
        'post_saved_line2' => 'Sie erhalten diese Benachrichtigung, weil Sie ein Administrator sind.',
        'view_post' => 'Artikel ansehen',
    ],

    // Feeds
    'feeds' => [
        'title' => 'Feeds',
        'description' => 'Abonnieren Sie unsere RSS-Feeds, um mit den neuesten Inhalten auf dem Laufenden zu bleiben.',
        'main_feed' => 'Haupt-Feed',
        'all_posts' => 'Alle Beiträge',
        'main_feed_desc' => 'Neueste Beiträge aus allen Kategorien',
        'categories' => 'Kategorien',
        'tags' => 'Tags',
        'posts' => 'Beiträge',
    ],
];
