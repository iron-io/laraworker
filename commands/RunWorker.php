<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Encryption\Encrypter;

class RunWorker extends Command
{

    protected $name = 'ironworker:run';
    protected $description = 'Upload iron worker.';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $queue_name = $this->option('queue_name');

//        //Post message via IronMQ lib - https://github.com/iron-io/iron_mq_php
//        $mq = new \IronMQ(array(
//            'token' => Config::get('queue.connections.iron.token', 'xxx'),
//            'project_id' => Config::get('queue.connections.iron.project', 'xxx')
//        ));
//	    //Every payload must be crypted, worker will decrypt it.
//        $mq->postMessages($queue_name, array(
//                $this->encryptPayload("This is Hello World payload_1"),
//                $this->encryptPayload("This is Hello World payload_2")
//            )
//        );

        //Post message via Laravel Queue class
        Queue::pushRaw("This is Hello World payload :)", $queue_name);
        $this->info("<info>Message pushed to the <comment>$queue_name</comment> queue.</info>");
    }

    protected function getOptions()
    {
        return array(
            array('queue_name', null, InputOption::VALUE_REQUIRED, 'Queue name.', null),
        );
    }

    protected function encryptPayload($payload)
    {
        $crypt = new Encrypter(Config::get('app.key'));
        return $crypt->encrypt($payload);
    }

}

