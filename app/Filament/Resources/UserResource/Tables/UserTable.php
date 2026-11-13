<?php

namespace App\Filament\Resources\UserResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_id')
                    ->label('Cus ID')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->color('primary')
                    ->copyable(),

                // Avatar Fix එක: Default Image URL එක Model function එකෙන් ගන්නවා
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => $record->getFilamentAvatarUrl()),

                Tables\Columns\TextColumn::make('name')
                    ->label('Customer Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name']),

                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-m-envelope')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super-admin' => 'primary',
                        'admin' => 'danger',
                        'user' => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'super-admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'user' => 'Customer',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'blocked' => 'Blocked',
                    ]),

                Tables\Filters\TernaryFilter::make('is_guest')
                    ->label('Guest User')
                    ->placeholder('All Users')
                    ->trueLabel('Guest Users Only')
                    ->falseLabel('Registered Users Only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    // Super Admin (ID 1) ව edit කරන්න බෑ
                    ->hidden(fn (Model $record) => $record->id === 1),

                Tables\Actions\DeleteAction::make()
                    // Super Admin (ID 1) ව delete කරන්න බෑ
                    ->hidden(fn (Model $record) => $record->id === 1),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Bulk delete වලිනුත් Super Admin බේරගන්න ඕන නම් logic එකක් ලියන්න ඕන,
                    // නමුත් දැනට මේක ඇති.
                ]),
            ]);
    }
}
