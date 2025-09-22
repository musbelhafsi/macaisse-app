<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\Imports\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Import $import) {}

    public function handle(): void
    {
        $import = $this->import->fresh();
        if (!$import || $import->status === 'completed') return;

        $import->update(['status' => 'processing', 'started_at' => now()]);

        try {
            (new ImportService())->process($import);
            $import->update(['status' => 'completed', 'finished_at' => now()]);
        } catch (Throwable $e) {
            $errors = $import->errors ?? [];
            $errors[] = ['_fatal' => $e->getMessage()];
            $import->update(['status' => 'failed', 'errors' => array_slice($errors, 0, 50), 'finished_at' => now()]);
            throw $e;
        }
    }
}