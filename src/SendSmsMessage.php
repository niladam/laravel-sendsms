<?php

namespace Niladam\LaravelSendsms;

use Niladam\LaravelSendsms\Exceptions\InvalidRequiredParametersException;

class SendSmsMessage
{
    public function __construct(
        public ?string $to = '',
        public ?string $message = '',
        public ?string $from = ''
    ) {
    }

    public static function create(
        ?string $to = '',
        ?string $message = '',
        ?string $from = ''
    ): SendSmsMessage {
        return new self($to, $message, $from);
    }

    public function to(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function text(string $text): self
    {
        $this->message = $text;

        return $this;
    }

    public function from(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return array|string
     */
    public function send(): array|string
    {
        throw_if(
            ! $this->to || ! $this->message,
            InvalidRequiredParametersException::class,
            "Unable to send message, as the required parameters as invalid: Destination: {$this->to} / Message: {$this->message}"
        );

        return app(LaravelSendsms::class)->send(
            $this->to,
            $this->message,
            $this->from
        );
    }
}
