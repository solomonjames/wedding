<?php

namespace App\Console\Commands;

use App\Mail\SaveTheDate;
use Generator;
use http\Exception\InvalidArgumentException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class SendSaveTheDates extends Command
{
    private const BATCH_SIZE = 10;
    private const WAIT_PERIOD = 2;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:save-the-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send save the dates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('Starting to send emails.');

        $currentBatchCount = 0;
        foreach ($this->emails() as $emailData) {
            Log::info(sprintf('Current batch size: %s', $currentBatchCount));

            if ($currentBatchCount >= static::BATCH_SIZE) {
                $currentBatchCount = 0;
                Log::info('Sleeping...');
                sleep(static::WAIT_PERIOD);
            }

            Log::info(sprintf('Dispatching email: %s', $emailData['to']));

            $email = new SaveTheDate($emailData['name']);
            $email->replyTo(['jgibriano@gmail.com', 'solomonjames@gmail.com']);
            Mail::to($emailData['to'])->send($email);

            $currentBatchCount++;
        }

        Log::info('Done sending emails.');

        return 0;
    }

    private function emails(): Generator
    {
        if (!($guestListFilePath = env('GUEST_LIST_CSV', false))) {
            throw new InvalidArgumentException('Missing guest list file env.');
        }

        $csvFileRaw = file_get_contents($guestListFilePath);
        $csvFileLines = Str::of($csvFileRaw)->split('/\r\n/');

        // Remove CSV header
        $csvFileLines->shift();

        $csvFileLines->transform(function (string $item, int $key) {
            $values = Str::of($item)->split('/,/');
            return [
                'to' => $values->get(0),
                'name' => $values->get(1)
            ];
        });

        foreach ($csvFileLines as $email) {
            yield $email;
        }
    }
}
