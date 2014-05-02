# LaraWorker

LaraWorker is bootstrap for integration IronWorker to Laravel.

## Installation

Add next repository to laravel `composer.json`

`"iron-io/laraworker": "dev-master",`

Then run `composer update`

After that you must install commands for upload and run IronWorker via next console command:

`php vendor/iron-io/laraworker/LaraWorker.php -i true`

This script will also copy worker example `ExampleLaraWorker.php` to workers directory in root of your project.

Then you can upload your worker with next console command:

`php artisan ironworker:upload --worker_name=ExampleWorker --exec_worker_file_name=ExampleWorker.php` 

Or upload all of your workers which are in workers directory

`php artisan ironworker:upload --worker_name=* --exec_worker_file_name=*`

You can run your worker in code with command

`Queue::pushRaw("This is Hello World payload :)", 'ExampleWorker'));`

Or run from console

`php artisan ironworker:run --queue_name=ExampleWorker`

## License

This software is released under the BSD 2-Clause License. You can find the full text of
this license under LICENSE.txt in the module's root directory.
