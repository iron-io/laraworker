<?php
$version = get_laravel_version();

$current_path = getcwd() . '/vendor/iron-io/laraworker/';
$workers_dir_path = getcwd() . '/workers';

$install_option = getopt("i:");
@$install_option = $install_option['i'] === 'true' ? true : false;
if (!$install_option)
    return;

if ($version == 4){
    $command_destination_path = getcwd() . '/app/commands';
    $config_destination_path = getcwd() . '/app/config';
    $artisan_file_path = getcwd() . '/app/start/artisan.php';
    $commands = array('RunWorker.php', 'UploadWorker.php');

    //register commands
    foreach ($commands as $command) {
        $register_command_text = "Artisan::add(new " . remove_extension($command) . ");";
        if (!is_command_registered($artisan_file_path, $register_command_text))
            file_put_contents($artisan_file_path, "\r\n" . $register_command_text, FILE_APPEND);
    }
} elseif ($version == 5){
    $command_destination_path = getcwd() . '/app/Console/Commands';
    $config_destination_path = getcwd() . '/config';
    $artisan_file_path = getcwd() . '/app/Console/Kernel.php';
    $commands = array('App\Console\Commands\RunWorker', 'App\Console\Commands\UploadWorker');

    $register_command_text = "";
    foreach ($commands as $command) {
        $register_command_text .=  "'" . $command . "',\n";
    }
    insert_to_file($artisan_file_path, "protected \$commands", $register_command_text);
    insert_to_file($current_path.'commands/UploadWorker.php', "<?php", "namespace App\Console\Commands;\nuse Config;\n");
    insert_to_file($current_path.'commands/RunWorker.php', "<?php", "namespace App\Console\Commands;\nuse Queue;\n");
    replace_in_file(getcwd().'/vendor/laravel/framework/src/Illuminate/Queue/Connectors/IronConnector.php', 'IronMQ\IronMQ', 'IronMQ');
    replace_in_file(getcwd().'/vendor/laravel/framework/src/Illuminate/Queue/IronQueue.php', 'IronMQ\IronMQ', 'IronMQ');
} else {
    echo "Error: unrecognized version of Laravel";
    return;
}

//copy config
if (!file_exists($config_destination_path . '/ironworker.php'))
    recurse_copy($current_path . '/config', $config_destination_path);

recurse_copy($current_path . '/config', $config_destination_path);
//copy commands
recurse_copy($current_path . '/commands', $command_destination_path);
//copy example worker with worker_boot
recurse_copy($current_path . '/workers', $workers_dir_path);

echo "LaraWorker package installed." . PHP_EOL;


function remove_extension($filename)
{
    return preg_replace("/\\.[^.\\s]{3,4}$/", "", $filename);
}

function recurse_copy($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function is_command_registered($artisan_file_path, $register_command_text)
{
    $artisan_file = file_get_contents($artisan_file_path);
    $pattern = preg_quote($register_command_text, '/');
    $pattern = "/^.*$pattern.*\$/m";
    if (preg_match_all($pattern, $artisan_file, $matches))
        return true;
    else
        return false;
}

function get_laravel_version()
{
    $version = shell_exec("php artisan --version | grep -E 'ersion[ ]{1,9}' | grep -Eo '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}'");
    $version_arr = explode(".", $version);
    return intval($version_arr[0]);
}

function insert_to_file($filename, $after_line, $text)
{
    if(is_command_registered($filename,$text))
    {
        return;
    }
    $st = "";
    $fp = fopen($filename, "r+");
    while($buf = fgets($fp)){
        $st .= $buf;
        if(strpos($buf, $after_line) > -1) {
            $st .= $text;
        }
    }
    file_put_contents($filename, $st);
}

function replace_in_file($file, $from, $to)
{
    $content = file_get_contents($file);
    $res = str_replace($from, $to, $content);
    file_put_contents($file, $res);
}
