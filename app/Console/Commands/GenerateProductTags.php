<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Masters; // adjust if your controller name is different

class GenerateProductTags extends Command
{
    protected $signature = 'products:generate-tags';
    protected $description = 'Generate AI-based tags and descriptions for products without tags';

    public function handle()
    {
        $controller = new Masters();
        $summary = $controller->generateMissingTagsBatch($this);
        $this->info("\n" . $summary);
        return Command::SUCCESS;
    }
}
