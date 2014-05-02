<?php
$commands = array('RunWorker.php', 'UploadWorker.php');
$command_destination_path = getcwd() . '/app/commands/';
$current_path = getcwd() . '/vendor/lara-worker/';
$artisan_file_path = getcwd() . '/app/start/artisan.php';
$workers_dir_path = getcwd() . '/workers';

foreach ($commands as $command) {
    if (!copy($current_path . '/commands/' . $command, $command_destination_path . $command))
        echo "Cannot copy commands!" . PHP_EOL;

    $register_command_text = "\r\nArtisan::add(new " . remove_extension($command) . ");";
    file_put_contents($artisan_file_path, $register_command_text, FILE_APPEND);
}

recurse_copy($current_path . '/workers', $workers_dir_path);



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