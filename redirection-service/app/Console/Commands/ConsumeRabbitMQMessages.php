<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ReceiveUrlFromRabbitMQ;
use Illuminate\Container\Attributes\Log;

class ConsumeRabbitMQMessages extends Command
{
    protected $signature = 'rabbitmq:consume';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $job = new ReceiveUrlFromRabbitMQ();
        $job->handle();
    }
}
