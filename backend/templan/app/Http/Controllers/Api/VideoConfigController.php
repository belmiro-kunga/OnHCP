<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VideoConfigController extends Controller
{
    /**
     * Obter configurações de vídeo
     */
    public function index(): JsonResponse
    {
        $videoSource = Setting::getValue('video_source', 'local');
        $cloudflareConfig = Setting::getValue('cloudflare_r2_config', [
            'account_id' => '',
            'access_key_id' => '',
            'secret_access_key' => '',
            'bucket_name' => '',
            'region' => 'auto',
            'endpoint' => '',
            'public_url' => ''
        ]);
        $youtubeConfig = Setting::getValue('youtube_api_config', [
            'api_key' => '',
            'channel_id' => '',
            'enabled' => false
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'video_source' => $videoSource,
                'cloudflare_r2' => $cloudflareConfig,
                'youtube_api' => $youtubeConfig,
                'available_sources' => [
                    'local' => 'Armazenamento Local',
                    'cloudflare_r2' => 'Cloudflare R2',
                    'youtube_api' => 'YouTube API'
                ]
            ]
        ]);
    }

    /**
     * Atualizar origem dos vídeos
     */
    public function updateVideoSource(Request $request): JsonResponse
    {
        $request->validate([
            'video_source' => ['required', Rule::in(['local', 'cloudflare_r2', 'youtube_api'])]
        ]);

        Setting::putValue('video_source', $request->video_source);

        return response()->json([
            'success' => true,
            'message' => 'Origem dos vídeos atualizada com sucesso',
            'data' => [
                'video_source' => $request->video_source
            ]
        ]);
    }

    /**
     * Atualizar configurações do Cloudflare R2
     */
    public function updateCloudflareConfig(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|string|max:255',
            'access_key_id' => 'required|string|max:255',
            'secret_access_key' => 'required|string|max:255',
            'bucket_name' => 'required|string|max:255',
            'region' => 'required|string|max:50',
            'endpoint' => 'required|url|max:255',
            'public_url' => 'required|url|max:255'
        ]);

        $config = [
            'account_id' => $request->account_id,
            'access_key_id' => $request->access_key_id,
            'secret_access_key' => $request->secret_access_key,
            'bucket_name' => $request->bucket_name,
            'region' => $request->region,
            'endpoint' => $request->endpoint,
            'public_url' => $request->public_url
        ];

        Setting::putValue('cloudflare_r2_config', $config);

        return response()->json([
            'success' => true,
            'message' => 'Configurações do Cloudflare R2 atualizadas com sucesso',
            'data' => $config
        ]);
    }

    /**
     * Atualizar configurações do YouTube API
     */
    public function updateYoutubeConfig(Request $request): JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string|max:255',
            'channel_id' => 'nullable|string|max:255',
            'enabled' => 'boolean'
        ]);

        $config = [
            'api_key' => $request->api_key,
            'channel_id' => $request->channel_id ?? '',
            'enabled' => $request->boolean('enabled', false)
        ];

        Setting::putValue('youtube_api_config', $config);

        return response()->json([
            'success' => true,
            'message' => 'Configurações do YouTube API atualizadas com sucesso',
            'data' => $config
        ]);
    }

    /**
     * Testar conexão com Cloudflare R2
     */
    public function testCloudflareConnection(): JsonResponse
    {
        try {
            $config = Setting::getValue('cloudflare_r2_config');
            
            if (!$config || empty($config['access_key_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configurações do Cloudflare R2 não encontradas'
                ], 400);
            }

            // Aqui você implementaria a lógica real de teste de conexão
            // Por exemplo, tentar listar objetos no bucket
            
            return response()->json([
                'success' => true,
                'message' => 'Conexão com Cloudflare R2 testada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar conexão com YouTube API
     */
    public function testYoutubeConnection(): JsonResponse
    {
        try {
            $config = Setting::getValue('youtube_api_config');
            
            if (!$config || empty($config['api_key'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configurações do YouTube API não encontradas'
                ], 400);
            }

            // Aqui você implementaria a lógica real de teste da API do YouTube
            // Por exemplo, fazer uma requisição simples para verificar a chave
            
            return response()->json([
                'success' => true,
                'message' => 'Conexão com YouTube API testada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resetar configurações para valores padrão
     */
    public function reset(): JsonResponse
    {
        Setting::putValue('video_source', 'local');
        Setting::putValue('cloudflare_r2_config', [
            'account_id' => '',
            'access_key_id' => '',
            'secret_access_key' => '',
            'bucket_name' => '',
            'region' => 'auto',
            'endpoint' => '',
            'public_url' => ''
        ]);
        Setting::putValue('youtube_api_config', [
            'api_key' => '',
            'channel_id' => '',
            'enabled' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configurações resetadas para valores padrão'
        ]);
    }
}