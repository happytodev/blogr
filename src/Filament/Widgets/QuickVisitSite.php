<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class QuickVisitSite extends Widget
{
    protected static ?int $sort = -1;
    
    protected int | string | array $columnSpan = 'full';

    protected string $view = 'blogr::filament.widgets.quick-visit-site';

    public function render(): View
    {
        return view($this->view, [
            'blogUrl' => $this->getBlogUrl(),
            'label' => $this->getLabel(),
        ]);
    }

    public function getBlogUrl(): string
    {
        try {
            $localesEnabled = config('blogr.locales.enabled', false);
            
            // Always return the homepage (whether blog or cms is configured as homepage)
            if ($localesEnabled) {
                $defaultLocale = config('blogr.locales.default', config('app.locale', 'en'));
                return route('home', ['locale' => $defaultLocale]);
            }

            return route('home');
        } catch (\Exception $e) {
            return config('app.url', '/');
        }
    }

    public function getLabel(): string
    {
        return __('blogr::ui.view_website');
    }
}

