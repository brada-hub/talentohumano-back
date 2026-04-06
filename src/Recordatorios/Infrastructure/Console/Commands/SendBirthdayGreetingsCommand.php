<?php

namespace Src\Recordatorios\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\Recordatorios\Domain\Repositories\RecordatorioRepositoryInterface;

class SendBirthdayGreetingsCommand extends Command
{
    protected $signature = 'recordatorios:enviar-cumpleanios';
    protected $description = 'Envia felicitaciones institucionales de cumpleanios';

    public function __construct(
        private readonly RecordatorioRepositoryInterface $repository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $results = $this->repository->sendCumpleaniosDelDia();
        $this->info('Recordatorios procesados: ' . count($results));
        return self::SUCCESS;
    }
}
