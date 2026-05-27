<?php

namespace App\Filament\Resources\Users;

use App\Filament\Concerns\AuthorizesTabAccess;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Support\PanelTabs;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    use AuthorizesTabAccess;

    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Usuarios';

    protected static string | \UnitEnum | null $navigationGroup = 'Administrativo';

    protected static ?int $navigationSort = 80;

    protected static ?string $slug = 'usuarios';

    protected static function tabAccessKey(): string
    {
        return PanelTabs::USERS;
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var User|null $currentUser */
        $currentUser = auth()->user();
        $tenantId = static::resolveCurrentTenantId();

        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        if ($currentUser === null || $tenantId < 1) {
            return $query->whereRaw('1 = 0');
        }

        $query->where(function (Builder $tenantScopedQuery) use ($tenantId): void {
            $tenantScopedQuery
                ->where('tenant_id', $tenantId)
                ->orWhere('company_id', $tenantId);
        });

        if (! $currentUser->isAdmin()) {
            $query->where('role', '!=', 'admin');
        }

        return $query;
    }

    public static function resolveCurrentTenantId(): int
    {
        /** @var User|null $user */
        $user = auth()->user();

        return (int) ($user?->tenant_id ?: $user?->company_id ?: 0);
    }

    public static function canAssignAdminRole(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->isAdmin() ?? false;
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny() && static::canAccessRecord($record);
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny() && static::canAccessRecord($record);
    }

    public static function canDelete(Model $record): bool
    {
        /** @var User|null $currentUser */
        $currentUser = auth()->user();

        if ($currentUser === null) {
            return false;
        }

        return static::canEdit($record) && ((int) $currentUser->id !== (int) $record->getKey());
    }

    public static function canForceDelete(Model $record): bool
    {
        /** @var User|null $currentUser */
        $currentUser = auth()->user();

        if ($currentUser === null) {
            return false;
        }

        return static::canEdit($record) && ((int) $currentUser->id !== (int) $record->getKey());
    }

    protected static function canAccessRecord(Model $record): bool
    {
        if (! $record instanceof User) {
            return false;
        }

        /** @var User|null $currentUser */
        $currentUser = auth()->user();

        if ($currentUser === null) {
            return false;
        }

        return $currentUser->isAdmin() || $record->role !== 'admin';
    }
}
