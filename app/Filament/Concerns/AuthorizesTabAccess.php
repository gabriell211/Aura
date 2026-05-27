<?php

namespace App\Filament\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesTabAccess
{
    protected static function tabAccessKey(): string
    {
        return '';
    }

    protected static function canAccessTab(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        $tab = static::tabAccessKey();

        return $tab !== '' && $user->hasTabAccess($tab);
    }

    public static function canViewAny(): bool
    {
        return static::canAccessTab();
    }

    public static function canView(Model $record): bool
    {
        return static::canAccessTab();
    }

    public static function canCreate(): bool
    {
        return static::canAccessTab();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canAccessTab();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canAccessTab();
    }

    public static function canDeleteAny(): bool
    {
        return static::canAccessTab();
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::canAccessTab();
    }

    public static function canForceDeleteAny(): bool
    {
        return static::canAccessTab();
    }

    public static function canRestore(Model $record): bool
    {
        return static::canAccessTab();
    }

    public static function canRestoreAny(): bool
    {
        return static::canAccessTab();
    }
}
