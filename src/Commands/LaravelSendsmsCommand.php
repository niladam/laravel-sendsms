<?php

namespace Niladam\LaravelSendsms\Commands;

use Exception;
use Illuminate\Console\Command;
use Niladam\LaravelSendsms\SendSmsMessage;

class LaravelSendsmsCommand extends Command
{
    public $hidden = false;

    public $signature = "laravel:sendsms {to?} {message?} {from?}";

    public $description = "Send an SMS message using SendSMS";

    public function handle()
    {
        $to = $this->argument("to") ?: $this->ask("Enter a phone number");
        $message = $this->argument("message") ?: $this->ask("Enter a message");
        $from = $this->argument("from") ?: $this->ask("FROM (optional)");

        try {
            $message = SendSmsMessage::create(
                to: $to,
                message: $message,
                from: $from
            )->send();
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

        $this->info("Message sent.");
        $this->newLine(2);
        $this->info($message);
        $this->newLine(2);
    }
}
