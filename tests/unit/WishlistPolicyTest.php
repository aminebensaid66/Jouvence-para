<?php

declare(strict_types=1);

use JouvencePara\Core\Customers\WishlistPolicy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Customers/WishlistPolicy.php';

return [
    'wishlist product IDs are positive unique integers in stable numeric order' => static function (TestHarness $test): void {
        $test->assertSame([2, 9], WishlistPolicy::productIds(['9', 2, 0, -1, 'bad', 2, ['3']]));
    },
    'wishlist add and remove operations are replay-safe' => static function (TestHarness $test): void {
        $items = WishlistPolicy::add([9, 2], 9);
        $test->assertSame([2, 9], $items);
        $test->assertSame([9], WishlistPolicy::remove([9, 2], 2));
        $test->assertSame([9], WishlistPolicy::remove([9], 2));
    },
    'wishlist has a bounded item count without discarding existing IDs' => static function (TestHarness $test): void {
        $ids = range(1, WishlistPolicy::MAX_ITEMS);
        $test->assertSame($ids, WishlistPolicy::add($ids, WishlistPolicy::MAX_ITEMS + 1));
        $test->assertSame($ids, WishlistPolicy::add($ids, WishlistPolicy::MAX_ITEMS));
        $test->assertSame(range(2, WishlistPolicy::MAX_ITEMS), WishlistPolicy::remove($ids, 1));
    },
];
