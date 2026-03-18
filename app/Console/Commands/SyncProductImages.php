<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class SyncProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-product-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download images from temp_image (Google Drive links) and store them locally';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = DB::table('products')
            ->whereNull('image')
            ->whereNotNull('temp_image')
            ->get();
        if ($records->isEmpty()) {
            $this->info('No records found to sync.');
            return;
        }
        $directory = public_path('product images');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        foreach ($records as $record) {
            try {
                preg_match('/\/d\/(.*?)\//', $record->temp_image, $matches);
                if (!isset($matches[1])) {
                    $this->warn("⚠️ Invalid Google Drive URL for ID: {$record->id}");
                    continue;
                }
                $fileId = $matches[1];
                $downloadUrl = "https://drive.google.com/uc?export=download&id={$fileId}";
                $response = Http::timeout(60)->get($downloadUrl);
                if (!$response->ok()) {
                    $this->warn("❌ Failed to download image for ID: {$record->id}");
                    continue;
                }
                $imageName = Str::uuid() . '.jpg';
                $imagePath = $directory . '/' . $imageName;
                file_put_contents($imagePath, $response->body());
                DB::table('products')
                    ->where('id', $record->id)
                    ->update([
                        'image' => $imageName
                    ]);
                $this->info("✅ Image synced for record ID: {$record->id}");
            } catch (Exception $e) {
                $this->error("Error syncing ID {$record->id}: " . $e->getMessage());
            }
        }
    }
}
