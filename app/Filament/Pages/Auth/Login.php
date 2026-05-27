<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function getTitle(): string | Htmlable
    {
        return 'Entrar';
    }

    public function getHeading(): string | Htmlable | null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return 'Verificacao em duas etapas';
        }

        return 'Acesso do cliente';
    }

    public function getSubheading(): string | Htmlable | null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return 'Digite o codigo de autenticacao para concluir o acesso.';
        }

        return new HtmlString(
            'Acesse seu painel Aura ERP MPS. Ainda nao tem conta? ' .
            '<a href="/" class="aura-login-link">Inicie seu teste de 30 dias</a>.'
        );
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label('Email corporativo');
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->label('Senha');
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label('Lembrar de mim');
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Entrar');
    }

    protected function getMultiFactorAuthenticateFormAction(): Action
    {
        return parent::getMultiFactorAuthenticateFormAction()
            ->label('Validar acesso');
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => 'As credenciais informadas sao invalidas.',
        ]);
    }
}
