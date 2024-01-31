<?php

if (!function_exists('unlink_dir'))
{
    function unlink_dir(string $dir): void
    {
        if (!is_dir($dir))
        {
            return;
        }

        $iterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file)
        {
            if ($file->isDir())
            {
                rmdir($file->getPathname());
            }
            else
            {
                unlink($file->getPathname());
            }
        }
        rmdir($dir);
    }
}
