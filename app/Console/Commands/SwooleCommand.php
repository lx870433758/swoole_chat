<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwooleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
        //
        $ws = new swoole_websocket_server("0.0.0.0", 9505);


        $ws->on('open', function ($ws, $request) {
            $fd[] = $request->fd;
            $GLOBALS['fd'][] = $fd;
        });

        $ws->on('message', function ($ws, $frame) {
            $msg =  "用户$frame->fd :$frame->data\n";
            foreach($GLOBALS['fd'] as $aa){
                foreach($aa as $i){
                    $ws->push($i,$msg);
                }
            }
        });

        $ws->on('close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
        });

        $ws->start();
    }
}
