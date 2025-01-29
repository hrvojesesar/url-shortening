<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Foundation\Bus\Dispatchable;

class SendUrlToRabbitMQ implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $messageData;

    public function __construct(array $messageData)
    {
        $this->messageData = $messageData;
    }

    public function handle()
    {
        $connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.host'),
            config('queue.connections.rabbitmq.port'),
            config('queue.connections.rabbitmq.login'),
            config('queue.connections.rabbitmq.password'),
            config('queue.connections.rabbitmq.vhost')
        );

        $channel = $connection->channel();

        $channel->exchange_declare('url_events', 'direct', false, true, false);
        $channel->queue_declare('url_created_queue', false, true, false, false);
        $channel->queue_bind('url_created_queue', 'url_events', 'url.created');

        $msg = new AMQPMessage(
            json_encode($this->messageData),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $channel->basic_publish($msg, 'url_events', 'url.created');

        $channel->close();
        $connection->close();
    }
}
