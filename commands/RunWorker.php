<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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
        $this->info(Queue::pushRaw(
            "This is Hello World payload :)",
            $queue_name));
    }

    protected function getOptions()
    {
        return array(
            array('queue_name', null, InputOption::VALUE_REQUIRED, 'Queue name.', null),
        );
    }

}
