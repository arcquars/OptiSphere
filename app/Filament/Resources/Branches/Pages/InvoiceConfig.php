<?php

namespace App\Filament\Resources\Branches\Pages;

use Filament\Forms\Form;
use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

// use Filament\Schemas\Components\Form;

class InvoiceConfig extends Page implements HasSchemas
{
    use InteractsWithSchemas;
    
    public ?array $data = []; 
    
    protected static string $resource = BranchResource::class;

    protected static ?string $title = 'Configurar SIAT';

    protected string $view = 'filament.resources.branches.pages.invoice-config';

    public Branch $branch;

    public function mount(int $branch_id): void
    {
        $this->branch = Branch::find($branch_id);
        $this->form->fill();
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
                TextInput::make('system_name')
                    ->label('Nombre del sistema')
                    ->required(),
                TextInput::make('system_code')
                    ->label('Código del sistema')
                    ->required(),
                TextInput::make('nit')
                    ->label('NIT')
                    ->required(),
                TextInput::make('company_name')
                    ->label('Código del sistema')
                    ->required(),
                TextInput::make('company_name')
                    ->label('Razón social')
                    ->required(),
                Select::make('modality')
                    ->label('Modalidad')
                    ->options([
                        '1' => 'Electronica en linea',
                        '2' => 'Computarizada en linea',
                    ])
                    ->required(),
                Select::make('environment')
                    ->label('Ambiente')
                    ->options([
                        '1' => 'Produccion',
                        '2' => 'Pruebas y Piloto',
                    ])
                    ->required(),
                TextInput::make('city')
                    ->label('Ciudad')
                    ->required(),
                TextInput::make('token')
                    ->columnSpan('4')
                    ->label('Token delegado')
                    ->required(),
                TextInput::make('phone')
                    ->label('Teleéfono'),
                TextInput::make('cafc')
                    ->label('CAFC'),
                TextInput::make('cafc_ini')
                    ->label('CAFC Nro Inicio de factura'),
                TextInput::make('cafc_end')
                    ->label('CAFC Nro Fin de Factura'),
                Select::make('print_size')
                    ->label('Tipo de impresion')
                    ->options([
                        'page' => 'Media Pagina',
                        'rollo' => 'Ticket/Rollo',
                    ])
                    ->required(),
                FileUpload::make('attachment')
                    ->label('Logo')
                    ->image()
                
            ])
            ->statePath('data');
    }
    
    public function submit(): void
    {
        dd($this->form->getState());
    }

    protected function getViewData(): array
    {
        return [
            'branch' => $this->branch,
        ];
    }



}
