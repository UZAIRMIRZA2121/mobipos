<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

class BackupToBunny extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:bunny';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database and public assets and upload to BunnyCDN Storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Backup Process...');
        Log::info('BunnyCDN Backup Process Started.');

        $apiKey = env('BUNNY_API_KEY');
        $storageZone = env('BUNNY_STORAGE_ZONE');
        $region = env('BUNNY_REGION'); // 'de'
        $endpoint = env('BUNNY_ENDPOINT'); // The user provided S3 endpoint, but let's fall back to REST if they gave S3.

        if (!$apiKey || !$storageZone) {
            $this->error('BunnyCDN credentials not configured in .env');
            return 1;
        }

        // Fix the endpoint to standard REST API endpoint if user provided S3 endpoint
        // e.g. https://de-s3.storage.bunnycdn.com -> https://storage.bunnycdn.com (or regional)
        if (strpos($endpoint, '-s3') !== false) {
            $endpoint = str_replace('-s3', '', $endpoint);
        }
        $endpoint = rtrim($endpoint, '/');

        $date = now()->format('Y-m-d_H-i-s');
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        $tempDir = storage_path('app/temp_backup');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $sqlFile = $tempDir . '/database_' . $date . '.sql';
        
        $this->info('Dumping Database...');
        // Escape password for shell
        $passwordArg = $dbPass ? "-p\"$dbPass\"" : "";
        $dumpCommand = "mysqldump -h $dbHost -P $dbPort -u $dbUser $passwordArg $dbName > \"$sqlFile\"";
        
        exec($dumpCommand, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Failed to dump database.');
            Log::error('Backup Failed: DB Dump error.');
            return 1;
        }

        $this->info('Creating ZIP archive...');
        $zipFile = storage_path('app/mobipos_backup_' . $date . '.zip');
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Add DB dump
            $zip->addFile($sqlFile, 'database.sql');

            // Add Assets (storage/app/public)
            $assetsPath = storage_path('app/public');
            if (is_dir($assetsPath)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($assetsPath, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'assets/' . substr($filePath, strlen($assetsPath) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }
            $zip->close();
            $this->info('ZIP archive created successfully.');
        } else {
            $this->error('Failed to create ZIP archive.');
            return 1;
        }

        $this->info('Uploading to BunnyCDN...');
        $fileName = basename($zipFile);
        $url = "{$endpoint}/{$storageZone}/mobipos/{$fileName}";

        $fileStream = fopen($zipFile, 'r');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "AccessKey: $apiKey",
            "Content-Type: application/octet-stream"
        ]);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, $fileStream);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($zipFile));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL for local XAMPP
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        fclose($fileStream);

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->info("Backup uploaded successfully to BunnyCDN!");
            Log::info("Backup uploaded successfully to BunnyCDN: $fileName");
        } else {
            $this->error("Failed to upload to BunnyCDN. HTTP Code: $httpCode | cURL Error: $curlError | Response: $response");
            Log::error("BunnyCDN Upload Failed. Code: $httpCode, Error: $curlError, Response: $response");
        }

        // Cleanup
        @unlink($sqlFile);
        @unlink($zipFile);

        $this->info('Backup process completed.');
        return 0;
    }
}
