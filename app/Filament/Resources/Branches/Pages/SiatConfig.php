<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Models\AmyrConnectionBranch;
use App\Models\Branch;
use App\Services\AmyrUserApiService;
use DB;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Log;

class SiatConfig extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];
    
    protected static string $resource = BranchResource::class;

    public Branch $branch;

    public function mount(int $branch_id): void
    {
        $this->branch = Branch::find($branch_id);
        $amyrConnectionBranch = AmyrConnectionBranch::where('branch_id', $branch_id)->first();

        if($amyrConnectionBranch) {
            $this->data['amyr_user'] = $amyrConnectionBranch->amyr_user;
            $this->data['amyr_password'] = $amyrConnectionBranch->amyr_password;
            $this->data['sucursal'] = $amyrConnectionBranch->sucursal;
            $this->data['point_sale'] = $amyrConnectionBranch->point_sale;
            $this->data['is_actived'] = $amyrConnectionBranch->is_actived;
        } else {
            // Valores por defecto al crear
            $this->data['sucursal'] = 0;
            $this->data['point_sale'] = 0;
        }
        
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'sm' => 1,
                'xl' => 2,
                '2xl' => 4,
            ])
            ->components([
                TextInput::make('amyr_user')
                    ->label('Usuario facturación')
                    ->required(),
                TextInput::make('amyr_password')
                    ->label('Código del sistema')
                    ->password()
                    ->required(),
                TextInput::make('sucursal')
                    ->label('Sucursal')
                    ->integer()->minValue(0)
                    ->maxValue(1)
                    ->required(),
                TextInput::make('point_sale')
                    ->label('Punto de venta')
                    ->integer()->minValue(0)
                    ->maxValue(4)
                    ->required(),
                Fieldset::make('Estado y Validación')
                    ->schema([
                        Toggle::make('is_actived')
                            ->label('Activo'),
                    ])->columns(1),
            ])
            ->statePath('data')
            ->model($this->data);
    }

    /**
     * @param AmyrUserApiService $amyrUserApiService
     */
    public function submit(AmyrUserApiService $amyrUserApiService)
    {
        // $data = $this->validate();
        $data = $this->form->getState();

        $authData = $amyrUserApiService->authenticate(
            $data['amyr_user'], 
            $data['amyr_password']
        );

        if (!$authData) {
            Notification::make()
            ->title('Error de autenticación con AMYR API.')
            ->danger()
            ->send();
            return;
        }

        Log::info('AMYR Authenticated TOKEN: '. $authData['token']);

        $token = $authData['token'];
        DB::transaction(function () use ($token) {
            $amyrConnectionBranch = AmyrConnectionBranch::updateOrCreate(
                ['branch_id' => $this->branch->id],
                [
                    'amyr_user' => $this->data['amyr_user'],
                    'amyr_password' => $this->data['amyr_password'],
                    'sucursal' => $this->data['sucursal'],
                    'point_sale' => $this->data['point_sale'],
                    'token' => $token,
                    'is_actived' => $this->data['is_actived'] ?? false,
                ]
            );
        });

        Notification::make()
            ->title('Configuración SIAT guardada correctamente.')
            ->success()
            ->send();
    }

    protected string $view = 'filament.resources.branches.pages.siat-config';
}
