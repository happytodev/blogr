<?php


use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('blogr_ui.navigation_enabled', true);
        $this->migrator->add('blogr_ui.navigation_sticky', true);
        $this->migrator->add('blogr_ui.navigation_show_logo', true);
        $this->migrator->add('blogr_ui.navigation_show_language_switcher', true);
        $this->migrator->add('blogr_ui.navigation_show_theme_switcher', true);

        $this->migrator->add('blogr_ui.footer_enabled', true);
        $this->migrator->add('blogr_ui.footer_text', '© ' . date('Y') . ' My Blog. All rights reserved.');
        $this->migrator->add('blogr_ui.footer_show_social_links', false);
        $this->migrator->add('blogr_ui.footer_twitter', null);
        $this->migrator->add('blogr_ui.footer_github', null);
        $this->migrator->add('blogr_ui.footer_linkedin', null);
        $this->migrator->add('blogr_ui.footer_facebook', null);

        $this->migrator->add('blogr_ui.theme_default', 'light');
        $this->migrator->add('blogr_ui.theme_primary_color', '#3b82f6');

        $this->migrator->add('blogr_ui.posts_default_image', null);
        $this->migrator->add('blogr_ui.posts_show_language_switcher', true);
    }
};
