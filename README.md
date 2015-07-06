<p align="center">
<img align="center" src="../master/images/laraworker.png" alt="laraworker">
</p>


LaraWorker is a helper package that makes integrating your Laravel application with Iron.io's IronWorker very easy!

[IronWorker](http://www.iron.io) makes it super easy to add queuing and background processing to your Laravel applications.

### Installation

1. Run `composer require iron-io/laraworker`.

2. Set Iron.io credentials in `app/config/queue.php` (or `config/queue.php` in Laravel 5.0 and higher) and set default to iron --> `'default' => 'iron',`

    To get your [Iron.io](http://www.iron.io) credentials, signup for a free account at [Iron.io](http://www.iron.io).

3. Install the IronWorker artisan commands for upload and run

    `php vendor/iron-io/laraworker/LaraWorker.php -i true`

    This script will also copy worker example `ExampleLaraWorker.php` to the workers directory in the root of your project.

### Uploading Workers

IronWorker is a cloud service that runs your Laravel app and waits for jobs to be queued up.

To upload all workers:

`php artisan ironworker:upload --worker_name=* --exec_worker_file_name=*`

To upload a single worker:

`php artisan ironworker:upload --worker_name=ExampleLaraWorker --exec_worker_file_name=ExampleLaraWorker.php`


### Queuing up jobs

From the console:

`php artisan ironworker:run --queue_name=ExampleLaraWorker`


From inside your laravel application, insert this code into your app:

`Queue::pushRaw("This is Hello World payload :)", 'ExampleLaraWorker'));`

To access the functionality of [IronMQ PHP lib](https://github.com/iron-io/iron_mq_php) use IronMq class instead of Laravel Queue

```
use Illuminate\Encryption\Encrypter;
....

$crypt = new Encrypter(Config::get('app.key'));

$ironmq = new \IronMQ(array(
    'token' => Config::get('queue.connections.iron.token', 'xxx'),
    'project_id' => Config::get('queue.connections.iron.project', 'xxx')
)); 
$ironmq->postMessages($queue_name, array(
        return $crypt->encrypt("This is Hello World payload_1"),
        return $crypt->encrypt("This is Hello World payload_2")
    )
);

```


#### License

This software is released under the BSD 2-Clause License. You can find the full text of
this license under LICENSE.txt in the module's root directory.
