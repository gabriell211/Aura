<?php

namespace App\Filament\Concerns;

use App\Models\User;

trait AuthorizesPageTabAccess
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

    public static function canAccess(): bool
    {
        return static::canAccessTab();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return parent::shouldRegisterNavigation() && static::canAccessTab();
    }
}
