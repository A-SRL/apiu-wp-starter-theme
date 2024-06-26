<?php

namespace App\Classes;

use Exception;
use Illuminate\View\View;

class Vite
{
    const string AVT_VITE_DIST_DIR = 'dist';
    const string AVT_VITE_RESOURCE_DIR = '';
    const string AVT_VITE_SERVER_BASE = 'http://localhost:5173';

    public static function boot(): void
    {
        add_filter('script_loader_tag', [__CLASS__, 'avt_vite_client_add_module_attr'], 10, 3);
        add_action('wp_enqueue_scripts', [__CLASS__, 'avt_vite_enqueue_styles_scripts']);
    }


    public static function avt_vite_hmr_host(): string
    {
        $theme_uri = get_stylesheet_directory_uri();
        $theme_relative_uri = str_replace(home_url(), '', $theme_uri);
        return self::AVT_VITE_SERVER_BASE . $theme_relative_uri;
    }

    public static function avt_vite_hmr_active(): bool
    {
        $hotFile = get_stylesheet_directory() . '/hot';
        return file_exists($hotFile);
    }

    public static function avt_vite_load_manifest(): array
    {
        $vite_manifest_path = get_template_directory() . '/' . self::AVT_VITE_DIST_DIR . '/.vite/manifest.json';
        return json_decode(file_get_contents($vite_manifest_path), true);
    }

    public static function avt_vite_client_add_module_attr($tag, $handle, $source)
    {
        if ('avt-vite-hmr-client' === $handle) {
            $tag = '<script type="module" src="' . $source . '"></script>';
        }
        return $tag;
    }

    /**
     * @throws Exception
     */
    public static function enqueue(string $resource): string
    {
        $vite_hmr_host = self::avt_vite_hmr_host();
        $vite_manifest = self::avt_vite_load_manifest();
        $source_file = $resource;

        if (!isset($vite_manifest[$source_file])) {
            throw new Exception("Resource '{$resource}' does not exist.");
        }

        $dist_file = $vite_manifest[$source_file]['file'];

        if (self::avt_vite_hmr_active()) {
            $res = $vite_hmr_host . '/' . $source_file;
        } else {
            $res = get_stylesheet_directory_uri() . '/' . self::AVT_VITE_DIST_DIR . '/' . $dist_file;
        }
        return $res;
    }

    public static function avt_vite_enqueue_styles_scripts(): void
    {
        if (self::avt_vite_hmr_active()) {
            wp_enqueue_script('avt-vite-hmr-client', self::avt_vite_hmr_host() . '/@vite/client', [], null, false);
        }
    }
}
