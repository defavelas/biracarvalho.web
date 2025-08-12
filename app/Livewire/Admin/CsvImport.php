<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\ImportStatus;
use App\Jobs\ProcessCsvImport;
use App\Models\ImportJob;
use App\Services\CsvProcessor;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Importar CSV - Administração')]
#[Layout('layouts.admin')]
final class CsvImport extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $csvFile;
    public ?string $statusFilter = '';
    public ?string $searchDate = '';
    public array $importResult = [];
    public bool $showResult = false;
    public bool $showImportModal = false;

    protected array $rules = [
        'csvFile' => 'required|file|mimes:csv,txt|max:10240',
    ];

    protected array $messages = [
        'csvFile.required' => 'Selecione um arquivo CSV.',
        'csvFile.file' => 'O arquivo deve ser um arquivo válido.',
        'csvFile.mimes' => 'O arquivo deve ser do tipo CSV.',
        'csvFile.max' => 'O arquivo não pode ser maior que 10MB.',
    ];

    public function openImportModal(): void
    {
        $this->showImportModal = true;
        $this->reset('csvFile');
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset('csvFile');
    }

    public function import(): void
    {
        $this->validate();

        $path = $this->csvFile->store('imports');

        $job = ImportJob::create([
            'status' => ImportStatus::Pending,
            'filename' => $this->csvFile->getClientOriginalName(),
            'created_by' => Auth::id(),
        ]);

        ProcessCsvImport::dispatch($path, $this->csvFile->getClientOriginalName(), $job->id);

        $this->reset('csvFile');
        $this->closeImportModal();
        session()->flash('message', 'Importação enfileirada com sucesso.');
    }

    public function downloadSample(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $processor = new CsvProcessor();
        $csvContent = $processor->generateSampleCsv();

        return response()->streamDownload(function () use ($csvContent): void {
            echo $csvContent;
        }, 'exemplo-locais.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="exemplo-locais.csv"',
        ]);
    }

    public function resetImport(): void
    {
        $this->reset(['csvFile', 'importResult', 'showResult']);
    }

    public function render()
    {
        $processor = new CsvProcessor();

        $imports = ImportJob::query()
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->searchDate, function ($q): void {
                $date = \Carbon\Carbon::parse($this->searchDate);
                $q->whereDate('created_at', $date);
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.csv-import', [
            'expectedHeaders' => $processor->getExpectedHeaders(),
            'imports' => $imports,
            'statusOptions' => collect(ImportStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->toArray(),
        ]);
    }
}
