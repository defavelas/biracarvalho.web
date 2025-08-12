<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Jobs\ProcessCsvImport;
use App\Models\CsvImport;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Gerenciar Importações - Administração')]
#[Layout('layouts.admin')]
class ImportManager extends Component
{
    use WithFileUploads, WithPagination;

    public $csvFile;
    public bool $showUploadModal = false;

    #[Url(as: 'date')]
    public string $dateFilter = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    protected array $rules = [
        'csvFile' => 'required|file|mimes:csv,txt|max:51200', // 50MB max
    ];

    protected array $messages = [
        'csvFile.required' => 'Selecione um arquivo CSV.',
        'csvFile.file' => 'O arquivo deve ser um arquivo válido.',
        'csvFile.mimes' => 'O arquivo deve ser do tipo CSV.',
        'csvFile.max' => 'O arquivo não pode ser maior que 50MB.',
    ];

    public function updatingDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openUploadModal(): void
    {
        $this->showUploadModal = true;
        $this->csvFile = null;
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->csvFile = null;
        $this->resetValidation();
    }

    public function uploadCsv(): void
    {
        $this->validate();

        try {
            // Store the file
            $filename = 'csv-imports/' . time() . '_' . $this->csvFile->getClientOriginalName();
            $path = $this->csvFile->storeAs('csv-imports', basename($filename));

            // Create import record
            $import = CsvImport::create([
                'filename' => $path,
                'original_filename' => $this->csvFile->getClientOriginalName(),
                'status' => 'pending',
            ]);

            // Dispatch job
            ProcessCsvImport::dispatch($import);

            $this->closeUploadModal();
            session()->flash('message', 'Arquivo enviado com sucesso. O processamento foi iniciado.');

        } catch (\Exception $e) {
            $this->addError('csvFile', 'Erro ao enviar arquivo: ' . $e->getMessage());
        }
    }

    public function retryImport(CsvImport $import): void
    {
        if ($import->isCompleted()) {
            $import->update([
                'status' => 'pending',
                'processed_rows' => 0,
                'successful_rows' => 0,
                'failed_rows' => 0,
                'errors' => null,
                'summary' => null,
                'started_at' => null,
                'completed_at' => null,
            ]);

            ProcessCsvImport::dispatch($import);
            session()->flash('message', 'Importação reiniciada com sucesso.');
        }
    }

    public function deleteImport(CsvImport $import): void
    {
        try {
            // Delete the file if it exists
            if (Storage::exists($import->filename)) {
                Storage::delete($import->filename);
            }

            // Delete the import record
            $import->delete();

            session()->flash('message', 'Importação excluída com sucesso.');
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao excluir importação: ' . $e->getMessage());
        }
    }

    public function refreshStatus(): void
    {
        // This method can be called to refresh the component
        $this->dispatch('$refresh');
    }

    public function getImportsProperty()
    {
        return CsvImport::query()
            ->when($this->dateFilter, fn ($query) => $query->byDate($this->dateFilter))
            ->when($this->statusFilter, fn ($query) => $query->byStatus($this->statusFilter))
            ->recent()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.import-manager', [
            'imports' => $this->imports,
            'statusOptions' => [
                'pending' => 'Pendente',
                'processing' => 'Processando',
                'completed' => 'Concluído',
                'failed' => 'Falhou',
            ],
        ]);
    }
}

