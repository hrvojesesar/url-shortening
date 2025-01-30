<?php

namespace App\Jobs;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;



class ReceiveUrlFromRabbitMQ implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, Dispatchable;

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

        $queueName = 'url_queue';
        $channel->queue_declare($queueName, false, true, false, false);


        $callback = function (AMQPMessage $message) {
            $data = json_decode($message->getBody(), true);

            if ($data['event'] === 'url_created') {
                Redis::set($data['data']['short_url'], $data['data']['real_url']);
                echo "URL created and stored in Redis: " . $data['data']['short_url'] . "\n";
            } elseif ($data['event'] === 'url_deleted') {
                Redis::del($data['data']['short_url']);
                echo "URL deleted from Redis: " . $data['data']['short_url'] . "\n";
            }
            $message->ack();
        };

        $channel->basic_consume('url_queue', '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }
}
