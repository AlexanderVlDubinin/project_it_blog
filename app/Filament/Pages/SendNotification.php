<?php

namespace App\Filament\Pages;

use App\Enum\NotificationTypes;
use App\Models\User;
use App\Notifications\CustomUserNotification;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList; // for channels
use Filament\Notifications\Notification as FilamentNotification; // For a pop-up success message
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Guava\IconPicker\Forms\Components\IconPicker;
use Illuminate\Support\Facades\Notification as LaravelNotification; // For mass sending

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
        $this->form->fill([
            'channels' => ['database'], // Database channel by default
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('New Notification')
                    ->description('Fill in the details below')
                    ->extraAttributes(['style' => 'margin-bottom: 2rem;'])
                    ->schema([
                        // Selecting the sending channels
                        CheckboxList::make('channels')
                            ->label('Delivery Channels')
                            ->options([
                                'database' => 'Internal Dashboard (DB)',
                                'mail' => 'Email Message',
                            ])
                            ->required()
                            ->minItems(1)
                            ->columns(2),

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
                            ->options(NotificationTypes::labels())
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

        // Creating the object of notification
        $notification = new CustomUserNotification(
            channels: $formData['channels'], // Passing the selected array of channels, for example ['database', 'mail']
            title: $formData['title'],
            message: $formData['message'],
            type: $formData['notificationType'] ?? 'info',
            icon: $formData['icon'] ?? 'heroicon-o-bell'
        );

        // The logic of determining recipients
        if ($formData['target'] === 'all') {
            // Using LaravelNotification::send for mass sending, chunking 100 users each
            User::query()->chunk(100, function ($users) use ($notification) {
                LaravelNotification::send($users, $notification);
            });
        } elseif ($formData['target'] === 'single') {
            $user = User::query()->find($formData['user_id']);
            if ($user) {
                $user->notify($notification);
            }
        } elseif (in_array($formData['target'], ['admin', 'moderator', 'author', 'user'])) {
            // Sending to a group of users with a specific role
            User::query()->where('role', $formData['target'])
                ->chunk(100, function ($users) use ($notification) {
                    LaravelNotification::send($users, $notification);
                });
        }

        // Clearing the form and show the standard popup window Filament
        $this->form->fill([
            'channels' => ['database'],
        ]);

        FilamentNotification::make()
            ->title('The notification has been sent successfully!')
            ->success()
            ->send();
    }
}
