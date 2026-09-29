<?php

declare(strict_types=1);

namespace JouvencePara\Core\Webhooks;

use JouvencePara\Core\Contracts\Module;
use WP_REST_Request;
use WP_REST_Response;

final class WebhooksModule implements Module
{
    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        $adapters = apply_filters('jp_webhook_adapters', []);
        if (! is_array($adapters)) {
            return;
        }
        foreach ($adapters as $provider => $adapter) {
            if (! is_string($provider) || preg_match('/^[a-z0-9][a-z0-9_-]{0,39}$/D', $provider) !== 1 || ! $adapter instanceof WebhookAdapter) {
                continue;
            }
            register_rest_route('jouvence-para/v1', '/webhooks/' . $provider, [
                'methods' => 'POST',
                // Provider signatures authenticate these server-to-server calls, not WP cookies/nonces.
                'permission_callback' => '__return_true',
                'callback' => static function (WP_REST_Request $request) use ($provider, $adapter): WP_REST_Response {
                    $result = (new WebhookProcessor())->handle($provider, $adapter, $request->get_body(), $request->get_headers());
                    return new WP_REST_Response(['state' => $result['state']], $result['status']);
                },
            ]);
        }
    }
}
