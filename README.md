<p align="center">
<img align="center" src="../master/images/laraworker.png" alt="laraworker">
</p>


LaraWorker is a helper package that makes integrating your Laravel application with Iron.io's IronWorker very easy!

[IronWorker](http://www.iron.io) makes it super easy to add queuing and background processing to your Laravel applications.

### Installation

1. Add the following packages to the requires section of `composer.json`

        "iron-io/iron_mq": "dev-master",
        "iron-io/iron_worker": "dev-master",
        "iron-io/laraworker": "dev-master",

2. Run `composer update`

3. Set Iron.io credentials in app/config/queue.php and set default to iron --> `'default' => 'iron',`

    To get your [Iron.io](http://www.iron.io) credentials, signup for a free account at [Iron.io](http://www.iron.io).

4. Install the IronWorker artisan commands for upload and run

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


#### License

This software is released under the BSD 2-Clause License. You can find the full text of
this license under LICENSE.txt in the module's root directory.
