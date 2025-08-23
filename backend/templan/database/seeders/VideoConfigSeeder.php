<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class VideoConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuração padrão da origem dos vídeos
        Setting::firstOrCreate(
            ['key' => 'video_source'],
            ['value' => 'local']
        );

        // Configurações padrão do Cloudflare R2
        Setting::firstOrCreate(
            ['key' => 'cloudflare_r2_config'],
            ['value' => [
                'account_id' => '',
                'access_key_id' => '',
                'secret_access_key' => '',
                'bucket_name' => '',
                'region' => 'auto',
                'endpoint' => '',
                'public_url' => ''
            ]]
        );

        // Configurações padrão do YouTube API
        Setting::firstOrCreate(
            ['key' => 'youtube_api_config'],
            ['value' => [
                'api_key' => '',
                'channel_id' => '',
                'enabled' => false
            ]]
        );

        $this->command->info('Configurações de vídeo criadas com sucesso!');
    }
}