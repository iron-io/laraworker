<?php

use Illuminate\Console\Command;
use Illuminate\Queue\IronQueue;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UploadWorker extends Command
{

    protected $name = 'ironworker:upload';
    protected $description = 'Upload iron worker.';
    protected $laravel;
    protected $subscriber_url;
    protected $worker;
    protected $iron_worker_name;
    protected $worker_file_name;
    protected $workers;
    protected $upload_all;
    protected $workers_dir = 'workers';
    protected $worker_params;

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $iron = $this->laravel['queue']->connection();
        if (!$iron instanceof IronQueue) {
            throw new RuntimeException("Iron.io based queue must be default.");
        }
        if (!is_dir(getcwd() . '/' . $this->workers_dir . '/'))
            throw new RuntimeException("Workers directory doesn't exist!");

        $this->init();
        if ($this->upload_all) {
            foreach ($this->workers as $worker_file_name) {
                if (is_dir(getcwd() . '/' . $this->workers_dir . '/' . $worker_file_name))
                    continue;
                $iron_worker_name = $this->remove_extension($worker_file_name);
                $this->subscriber_url = "ironworker:///" . $iron_worker_name;
                $this->upload_worker($iron_worker_name, $worker_file_name);
                if ($this->need_to_update_queue($iron_worker_name)) {
                    $this->update_queue($iron, $iron_worker_name);
                }
            }
            $this->show_workers_queues_list($this->workers);
        } else {
            $this->upload_worker($this->iron_worker_name, $this->worker_file_name);
            if ($this->need_to_update_queue($this->iron_worker_name)) {
                $this->update_queue($iron, $this->iron_worker_name);
            }
        }

    }

    protected function init()
    {
        $token = Config::get('queue.connections.iron.token', 'xxx');
        $project_id = Config::get('queue.connections.iron.project', 'xxx');
        $this->worker_params = Config::get('ironworker.params', null);
        if ($this->option('max_concurrency'))
            $this->worker_params['max_concurrency'] = intval($this->option('max_concurrency'));

        $this->worker = new \IronWorker(array(
            'token' => $token,
            'project_id' => $project_id
        ));
        if ($this->option('worker_name') == '*' and $this->option('exec_worker_file_name') == '*') {
            $this->upload_all = true;
            $workers = scandir(getcwd() . '/' . $this->workers_dir . '/');
            array_shift($workers);
            array_shift($workers);
            $this->workers = $workers;
        } elseif (!$this->option('worker_name') or !$this->option('exec_worker_file_name')) {
            throw new RuntimeException("Please specify the following options: 'worker_name' and 'exec_worker_file_name'.");
        } else {
            $this->iron_worker_name = $this->option('worker_name');
            $this->worker_file_name = $this->option('exec_worker_file_name');
            $this->subscriber_url = "ironworker:///" . $this->iron_worker_name;
        }
    }


    /**
     * Get the queue options.
     *
     * @param $queue_name
     * @return array
     */
    protected function getQueueOptions($queue_name)
    {
        return array(
            'push_type' => $this->getPushType($queue_name), 'subscribers' => $this->getSubscriberList($queue_name)
        );
    }

    /**
     * Get the push type for the queue.
     *
     * @param $queue_name
     * @return string
     */
    protected function getPushType($queue_name)
    {
        if ($this->option('push_queue_type')) return $this->option('push_queue_type');

        try {
            return $this->getQueue($queue_name)->push_type;
        } catch (\Exception $e) {
            return 'multicast';
        }
    }

    /**
     * Get the current subscribers for the queue.
     *
     * @param $queue_name
     * @return array
     */
    protected function getSubscriberList($queue_name)
    {
        $subscribers = $this->getCurrentSubscribers($queue_name);
        $subscribers[] = array('url' => $this->subscriber_url);
        return $subscribers;
    }

    /**
     * Get the current subscriber list.
     *
     * @param $queue_name
     * @return array
     */
    protected function getCurrentSubscribers($queue_name)
    {
        try {
            return $this->getQueue($queue_name)->subscribers;
        } catch (\Exception $e) {
            return array();
        }
    }

    /**
     * Get the queue information from Iron.io.
     *
     * @param $queue_name
     * @return object
     */
    protected function getQueue($queue_name)
    {
        //if (isset($this->meta)) return $this->meta;

        return $this->laravel['queue']->getIron()->getQueue($queue_name);
    }

    /**
     * Get list of subscribers and compare it with current subscriber url
     *
     * @param $queue_name
     * @internal param $subscriber_url
     * @return bool
     */
    protected function need_to_update_queue($queue_name)
    {
        foreach ($this->getCurrentSubscribers($queue_name) as $subscriber) {
            if ($subscriber->url == $this->subscriber_url)
                return false;
        }
        return true;
    }

    /**
     * Upload worker
     *
     * @param $worker_name
     * @param $worker_file_name
     */
    protected function upload_worker($worker_name, $worker_file_name)
    {
        $this->info("<info>Starting to upload <comment>$worker_name</comment> worker</info>");
        @$this->worker->upload(getcwd(), $this->workers_dir . '/' . $worker_file_name, $worker_name, $this->worker_params);
        $this->info("<info>Worker <comment>$worker_name</comment> uploaded</info>" . PHP_EOL);
    }

    /**
     * Update push queue
     *
     * @param $iron
     * @param $queue_name
     */
    protected function update_queue($iron, $queue_name)
    {
        $this->info("<info>Creating or updating push queue <comment>$this->iron_worker_name</comment></info>");
        $iron->getIron()->updateQueue($queue_name, $this->getQueueOptions($queue_name));
        $this->line("<info>Push Queue <comment>$queue_name</comment> with subscriber <comment>$this->subscriber_url</comment> created or updated.</info>" . PHP_EOL);
    }

    /**
     * Remove extension of the file
     *
     * @param $filename
     * @return mixed
     */
    protected function remove_extension($filename)
    {
        return preg_replace("/\\.[^.\\s]{3,4}$/", "", $filename);
    }

    /**
     * Show list of the uploaded workers and created/updated queues
     *
     * @param $workers
     * @return mixed
     */

    protected function show_workers_queues_list($workers)
    {
        $this->line("<info>Your workers:</info>");
        foreach ($workers as $worker_file_name) {
            if (is_dir(getcwd() . '/' . $this->workers_dir . '/' . $worker_file_name))
                continue;
            $this->line('<comment>' . $this->remove_extension($worker_file_name) . '</comment>');
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('worker_name', null, InputOption::VALUE_REQUIRED, 'Worker name.', null),
            array('exec_worker_file_name', null, InputOption::VALUE_REQUIRED, 'Execute worker file name.', null),
            array('push_queue_type', null, InputOption::VALUE_OPTIONAL, 'Type of the push queue.', null),
            array('max_concurrency', null, InputOption::VALUE_OPTIONAL, 'Max concurrency.', null),
        );
    }

}
