<?php

declare(strict_types=1);

namespace JouvencePara\Core\Support;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use JouvencePara\Core\Contracts\Module;

final class Compatibility implements Module
{
    public function register(): void
    {
        add_action(
            'before_woocommerce_init',
            static function (): void {
                if (class_exists(FeaturesUtil::class)) {
                    FeaturesUtil::declare_compatibility(
                        'custom_order_tables',
                        JOUVENCE_PARA_CORE_FILE,
                        true
                    );
                }
            }
        );
    }
}
