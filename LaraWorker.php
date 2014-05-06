<?php
$commands = array('RunWorker.php', 'UploadWorker.php');
$command_destination_path = getcwd() . '/app/commands';
$config_destination_path = getcwd() . '/app/config';
$current_path = getcwd() . '/vendor/iron-io/laraworker/';
$artisan_file_path = getcwd() . '/app/start/artisan.php';
$workers_dir_path = getcwd() . '/workers';

$install_option = getopt("i:");
@$install_option = $install_option['i'] === 'true' ? true : false;
if (!$install_option)
    return;

//register commands
foreach ($commands as $command) {
    $register_command_text = "Artisan::add(new " . remove_extension($command) . ");";
    if (!is_command_registered($artisan_file_path, $register_command_text))
        file_put_contents($artisan_file_path, "\r\n" . $register_command_text, FILE_APPEND);

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

