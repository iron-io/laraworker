
### What is LaraWorker

LaraWorker is a helper package that makes integrating your Laravel application with Iron.io's IronWorker very easy!

### Why IronWorker

IronWorker makes it super easy to add queuing and background processing to your Laravel applications.

### Installation

1. Add the following packages to the requires section of `composer.json`

`"iron-io/iron_mq": "dev-master",`
`"iron-io/iron_worker": "dev-master",`
`"iron-io/laraworker": "dev-master",`

2. Run `composer update`

3. Install the IronWorker artisan commands for upload and run

`php vendor/iron-io/laraworker/LaraWorker.php -i true`

This script will also copy worker example `ExampleLaraWorker.php` to workers directory in root of your project.
asd

### Upload your workers to Iron.io

To upload a single worker:

`php artisan ironworker:upload --worker_name=ExampleWorker --exec_worker_file_name=ExampleWorker.php` 

To upload all workers:

`php artisan ironworker:upload --worker_name=* --exec_worker_file_name=*`


### Run your worker from the console

`php artisan ironworker:run --queue_name=ExampleWorker`



### Queuing up your worker from inside your Laravel Application

`Queue::pushRaw("This is Hello World payload :)", 'ExampleWorker'));`



### License

This software is released under the BSD 2-Clause License. You can find the full text of
this license under LICENSE.txt in the module's root directory.
