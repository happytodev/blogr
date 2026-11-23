<?php

namespace Happytodev\Blogr\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Happytodev\Blogr\Models\AuthorBio;
use Happytodev\Blogr\Services\LocaleService;
use UnitEnum;

/**
 * An author writes and keeps their own bios here (issue #373).
 *
 * One bio says who you are to a reader who wonders where the article came from,
 * another to a reader who wants the CV. A post then picks the one it shows, per
 * translation; without a choice, the author's default bio for the language wins.
 *
 * @property Schema $form
 */
class AuthorBios extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static string|UnitEnum|null $navigationGroup = 'Blog';

    protected static ?string $navigationLabel = 'My bios';

    protected static ?string $title = 'My bios';

    protected static ?int $navigationSort = 90;

    protected string $view = 'blogr::filament.pages.author-bios';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $this->form->fill([
            'bios' => $this->rows(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $localeService = app(LocaleService::class);
        $locales = collect($localeService->getAvailable())
            ->mapWithKeys(fn ($locale) => [$locale => $localeService->localeLabel($locale)])
            ->all();

        return $schema
            ->components([
                Section::make('Bios')
                    ->description('Each bio is written in one language. The default one is used when a post does not pick another.')
                    ->schema([
                        Repeater::make('bios')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('locale')
                                    ->label('Language')
                                    ->options($locales)
                                    ->required()
                                    ->native(false),
                                TextInput::make('label')
                                    ->label('Name')
                                    ->helperText("What the post's editor sees: \"short\", \"dev\", \"artwork\".")
                                    ->required()
                                    ->maxLength(60),
                                Toggle::make('is_default')
                                    ->label('Default for this language')
                                    ->inline(false),
                                Textarea::make('body')
                                    ->label('Bio')
                                    ->rows(5)
                                    ->helperText('Markdown. Printed under the article, under "About the author".')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Add a bio')
                            ->itemLabel(fn (array $state): ?string => ($state['label'] ?? null)
                                ? $state['label'].' ('.$state['locale'].')'
                                : null)
                            ->dehydrated(true),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function rows(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return AuthorBio::query()
            ->where('user_id', $user->getKey())
            ->orderBy('locale')
            ->orderBy('label')
            ->get()
            ->map(fn (AuthorBio $bio): array => [
                'locale' => $bio->locale,
                'label' => $bio->label,
                'is_default' => (bool) $bio->is_default,
                'body' => $bio->body,
            ])
            ->all();
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $state = $this->form->getState();
        $bios = $state['bios'] ?? [];

        /** @var array<int, array<string, mixed>> $rows */
        $rows = [];

        if (is_array($bios)) {
            foreach ($bios as $row) {
                if (is_array($row)) {
                    $rows[] = $row;
                }
            }
        }

        // The table is a set, not a list: replace this author's bios with what
        // the form holds, which also removes the ones deleted from the repeater.
        $userId = $user->getAuthIdentifier();

        if (! is_int($userId) && ! is_string($userId)) {
            return;
        }

        AuthorBio::syncFor((int) $userId, $rows);

        Notification::make()
            ->title('Bios saved')
            ->success()
            ->send();

        $this->mount();
    }
}
