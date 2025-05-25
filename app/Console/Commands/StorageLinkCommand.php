<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StorageLinkCommand extends Command
{
    protected $signature = 'storage:link';
    protected $description = 'Create a symbolic link from "public/storage" to "storage/app/public"';

    public function handle()
    {
        $publicPath = base_path('public/storage');
        $storagePath = storage_path('app/public');

        if (file_exists($publicPath)) {
            return $this->error('The "public/storage" directory already exists.');
        }

        $this->laravel->make('files')->link(
            $storagePath,
            $publicPath
        );

        $this->info('The [public/storage] directory has been linked.');
    }
}
