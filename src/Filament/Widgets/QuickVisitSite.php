<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickVisitSite extends Widget
{
    protected static ?int $sort = -1; // Afficher en premier
    
    protected int | string | array $columnSpan = 'full';

    protected string $view = 'blogr::filament.widgets.quick-visit-site';
}

