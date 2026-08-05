<?php

namespace App\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Guava\IconPicker\Forms\Components\IconPicker;

class SendNotification extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Sending notifications';
    protected static ?string $title = 'Send a notification to users';

    protected string $view = 'filament.pages.send-notification';

    // Properties for storing form data
    public ?array $data = [];

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-paper-airplane';
    }

    public function mount(): void
    {
        $this->form->fill([]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('New Notification')
                    ->description('Fill in the details below')
                    ->extraAttributes(['style' => 'margin-bottom: 2rem;'])
                    ->schema([
                        Select::make('target')
                            ->label('To whom to send')
                            ->options([
                                'all' => 'To all',
                                'admin' => 'To admin',
                                'moderator' => 'To moderators',
                                'author' => 'To authors',
                                'user' => 'To users',
                                'single' => 'To a specific user',
                            ])
                            ->placeholder('Select target (user or group)')
                            ->live()
                            ->required(),

                        Select::make('user_id')
                            ->label('Select a user')
                            ->options(User::query()->pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Get $get) => $get('target') === 'single')
                            ->required(fn (Get $get) => $get('target') === 'single'),

                        TextInput::make('title')
                            ->label('Notification title')
                            ->required(),

                        Textarea::make('message')
                            ->label('Notification text')
                            ->required(),

                        Select::make('notificationType')
                            ->label('Notification type ("Info" by default)')
                            ->options([
                                'info' => 'Info (Blue color)',
                                'success' => 'Success (Green color)',
                                'warning' => 'Warning (Yellow color)',
                                'danger' => 'Danger (Red color)',
                                //'gray' => 'Neutral (Gray color)',
                            ])
                            //->native(false) // Makes the drop-down list more beautiful (custom UI Filament)
                            ->placeholder('Select notification type'),

                        IconPicker::make('icon')
                            ->label('Notification icon ("O bell" by default)')
                            ->sets(['heroicons']) // Limit to Heroicons
                            //->default('heroicon-o-bell'),
                    ]),
            ])
            ->statePath('data');
    }

    // The sending method called by the button on the page
    public function send(): void
    {
        $formData = $this->form->getState();

        $notification = Notification::make()
            ->title($formData['title'])
            ->body($formData['message'])
            //->icon('heroicon-o-information-circle')
            //->warning()
        ;

        $type = $formData['notificationType'] ?? 'info';
        $notification->$type();

        $icon = $formData['icon'] ?? 'heroicon-o-bell';
        $notification->icon($icon);

        if ($formData['target'] === 'all') {
            User::query()->chunk(100, function ($users) use ($notification) {
                foreach ($users as $user) {
                    $notification->sendToDatabase($user);
                }
            });
        } elseif ($formData['target'] === 'single') {
            $user = User::query()->find($formData['user_id']);
            if ($user) {
                $notification->sendToDatabase($user);
            }
        } elseif ( in_array($formData['target'], ['admin', 'moderator', 'author', 'user']) ) {
            // Example for groups (roles or custom selections)
            $users = User::query()->where('role', $formData['target'])->get();
            foreach ($users as $user) {
                $notification->sendToDatabase($user);
            }
        }

        $this->form->fill([]);
        Notification::make()->title('The notification has been sent successfully!')->success()->send();
    }
}
