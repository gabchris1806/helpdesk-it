<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\AdminPasswordReset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Manage User';
    }

    public static function getModelLabel(): string
    {
        return 'user';
    }

    public static function getPluralModelLabel(): string
    {
        return 'manage user';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                        Forms\Components\Select::make('theme_mode')
                            ->label('Tema')
                            ->options([
                                'light' => 'Light',
                                'dark' => 'Dark',
                                'system' => 'System',
                            ])
                            ->default('light')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Hak Akses (Permissions)')
                    ->description('Pilih apa yang boleh dilakukan pengguna ini.')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Daftar Izin')
                            ->options([
                                '*' => 'SUPER ADMIN (Akses Penuh)',
                                'ticket.view' => 'View Tickets',
                                'ticket.create' => 'Create Tickets',
                                'ticket.update' => 'Update Tickets (Reply/Status)',
                                'ticket.delete' => 'Delete Tickets',
                                'ticket.change_sla' => 'Change Ticket SLA',
                                'ticket.export' => 'Export Tickets',
                                'dashboard.view' => 'View Dashboard Stats',
                                'category.view' => 'View Categories',
                                'category.manage' => 'Manage Categories',
                                'location.view' => 'View Locations',
                                'location.manage' => 'Manage Locations',
                                'sla.view' => 'View SLAs',
                                'sla.manage' => 'Manage SLAs',
                                'master_lapor.view' => 'View Data Karyawan',
                                'master_lapor.manage' => 'Manage Data Karyawan',
                                'user.view' => 'View Users',
                                'user.manage' => 'Manage Users',
                            ])
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('theme_mode')
                    ->label('Tema')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'dark' => 'Dark',
                        'system' => 'System',
                        default => 'Light',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'dark' => 'gray',
                        'system' => 'info',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('permissions')
                    ->label('Permissions')
                    ->badge()
                    ->color(fn ($state) => $state === '*' ? 'success' : 'primary')
                    ->formatStateUsing(function ($state) {
                        $labels = [
                            '*' => 'SUPER ADMIN',
                            'ticket.view' => 'Lihat Tiket',
                            'ticket.create' => 'Buat Tiket',
                            'ticket.update' => 'Update Tiket',
                            'ticket.delete' => 'Hapus Tiket',
                            'ticket.change_sla' => 'Ubah SLA Tiket',
                            'ticket.export' => 'Export Tiket',
                            'dashboard.view' => 'Lihat Dashboard',
                            'category.view' => 'Lihat Kategori',
                            'category.manage' => 'Kelola Kategori',
                            'location.view' => 'Lihat Lokasi',
                            'location.manage' => 'Kelola Lokasi',
                            'sla.view' => 'Lihat SLA',
                            'sla.manage' => 'Kelola SLA',
                            'master_lapor.view' => 'Lihat Data Karyawan',
                            'master_lapor.manage' => 'Kelola Data Karyawan',
                            'user.view' => 'Lihat Pengguna',
                            'user.manage' => 'Kelola Pengguna',
                        ];

                        return $labels[$state] ?? $state;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('theme_mode')
                    ->label('Tema')
                    ->options([
                        'light' => 'Light',
                        'dark' => 'Dark',
                        'system' => 'System',
                    ]),
                Filter::make('super_admin')
                    ->label('Super Admin')
                    ->query(fn (Builder $query): Builder => $query->whereJsonContains('permissions', '*')),
            ])
            ->actions([
                Tables\Actions\Action::make('sendResetPassword')
                    ->label('Kirim Reset Password')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->visible(fn (User $record): bool => auth()->user()->can('update', $record))
                    ->requiresConfirmation()
                    ->modalHeading('Kirim link reset password?')
                    ->modalDescription('User akan menerima link untuk membuat password baru.')
                    ->action(function (User $record): void {
                        abort_unless(auth()->user()->can('update', $record), 403);

                        AdminPasswordReset::send($record);

                        Notification::make()
                            ->title('Link reset password berhasil dibuat')
                            ->body($record->email.'. '.AdminPasswordReset::deliveryHint())
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang dipilih'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
