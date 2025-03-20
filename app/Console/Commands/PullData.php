<?php

namespace App\Console\Commands;

use App\Models\AnalyticsData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PullData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pull-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull data from JSON files and store in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $platforms = ['google_analytics', 'microsoft_clarity', 'facebook', 'instagram', 'snapchat'];

        foreach ($platforms as $platform) {
            $filePath = "{$platform}.json";

            if (!Storage::exists($filePath)) {
                $this->error("File not found: $filePath");
                continue;
            }

            $records = json_decode(Storage::get($filePath), true);

            if (!is_array($records)) {
                $this->error("Invalid JSON format in $filePath");
                continue;
            }

            foreach ($records as $data) {
                if (!isset($data['date'], $data['visitors'], $data['profile_views'])) {
                    $this->error("Missing keys in data: " . json_encode($data));
                    continue;
                }

                AnalyticsData::updateOrCreate(
                    ['name' => $platform, 'date' => $data['date']],
                    ['visitors' => $data['visitors'], 'profile_views' => $data['profile_views']]
                );
            }
        }

        $this->info('Data pulled and stored successfully.');
    }
}
