<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesPageTabAccess;
use App\Models\Company;
use App\Support\PanelTabs;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read Schema $form
 */
class CompanySettings extends Page
{
    use AuthorizesPageTabAccess;

    protected static ?string $title = 'Configuracoes da Empresa';

    protected static ?string $navigationLabel = 'Configuracoes';

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string | \UnitEnum | null $navigationGroup = 'Administrativo';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'configuracoes';

    protected static function tabAccessKey(): string
    {
        return PanelTabs::SETTINGS;
    }

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    public function getTitle(): string | Htmlable
    {
        return static::$title;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->model($this->getCompany())
            ->operation('edit')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $billingBankOptions = (array) config('aura.billing.banks', []);

        return $schema
            ->components([
                Section::make('Identidade Visual')
                    ->description('Personalize o painel para sua empresa.')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo da empresa')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('company-logos')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                            ->maxSize(2048)
                            ->helperText('Formatos: JPG, PNG, WEBP ou SVG. Tamanho maximo: 2MB.'),
                    ]),
                Section::make('Dados Cadastrais')
                    ->schema([
                        TextInput::make('legal_name')
                            ->label('Razao social')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('trade_name')
                            ->label('Nome fantasia')
                            ->maxLength(255),
                        TextInput::make('document')
                            ->label('Documento (CNPJ)')
                            ->maxLength(30),
                        TextInput::make('email')
                            ->label('E-mail principal')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(40),
                        Select::make('billing_bank')
                            ->label('Banco de faturamento')
                            ->options($billingBankOptions)
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Integracao Printwayy')
                    ->description('Credenciais exclusivas por empresa para sincronizacao do parque.')
                    ->schema([
                        Toggle::make('printwayy_enabled')
                            ->label('Printwayy habilitado'),
                        TextInput::make('printwayy_api_base_url')
                            ->label('URL base da API')
                            ->maxLength(255)
                            ->url()
                            ->placeholder('https://api.printwayy.com/devices/v1')
                            ->helperText('Se vazio, usa o padrao global do sistema.'),
                        TextInput::make('printwayy_workspace_id')
                            ->label('Workspace ID')
                            ->maxLength(255),
                        TextInput::make('printwayy_api_token')
                            ->label('Token da Printwayy')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->maxLength(4096)
                            ->helperText('Deixe vazio para manter o token atual.'),
                    ])
                    ->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->mutateFormDataBeforeSave($this->form->getState());

        $this->getCompany()->update($data);

        Notification::make()
            ->success()
            ->title('Configuracoes salvas com sucesso.')
            ->send();

        $this->fillForm();
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar configuracoes')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth(false)
                    ->key('form-actions'),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Never expose the decrypted token in the form.
        $data['printwayy_api_token'] = null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['printwayy_api_base_url'] = filled($data['printwayy_api_base_url'] ?? null)
            ? trim((string) $data['printwayy_api_base_url'])
            : null;

        $data['printwayy_workspace_id'] = filled($data['printwayy_workspace_id'] ?? null)
            ? trim((string) $data['printwayy_workspace_id'])
            : null;

        if (blank($data['printwayy_api_token'] ?? null)) {
            unset($data['printwayy_api_token']);
        }

        return $data;
    }

    protected function fillForm(): void
    {
        $data = $this->mutateFormDataBeforeFill($this->getCompany()->attributesToArray());

        $this->form->fill($data);
    }

    protected function getCompany(): Company
    {
        $user = Auth::user();
        $companyId = (int) ($user?->company_id ?: $user?->tenant_id ?: 0);

        abort_if($companyId < 1, 403, 'Empresa do usuario nao encontrada.');

        return Company::query()->findOrFail($companyId);
    }
}
