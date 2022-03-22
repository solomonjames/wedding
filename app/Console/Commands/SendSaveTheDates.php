<?php

namespace App\Console\Commands;

use App\Mail\SaveTheDate;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $currentBatchCount = 0;
        foreach ($this->emails() as $emailData) {
            if ($currentBatchCount >= static::BATCH_SIZE) {
                sleep(static::WAIT_PERIOD);
            }

            Log::info(sprintf('Dispatching email: %s', $emailData['to']));

            $email = new SaveTheDate($emailData['name']);
            $email->replyTo(['jgibriano@gmail.com', 'solomonjames@gmail.com']);
            Mail::to($emailData['to'])->send($email);

            $currentBatchCount++;
        }

        return 0;
    }

    private function emails(): Generator
    {
        $emails = [
            [
                'name' => 'James Solomon',
                'to' => 'solomonjames@gmail.com',
            ],
            [
                'name' => 'Jaimie & James',
                'to' => 'jgibriano@gmail.com',
            ],
        ];

        foreach ($emails as $email) {
            yield $email;
        }
    }
}
