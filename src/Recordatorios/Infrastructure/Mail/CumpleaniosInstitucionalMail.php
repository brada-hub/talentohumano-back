<?php

namespace Src\Recordatorios\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CumpleaniosInstitucionalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $payload
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject($this->payload['asunto'])
            ->view('emails.recordatorios.cumpleanios');
    }
}
