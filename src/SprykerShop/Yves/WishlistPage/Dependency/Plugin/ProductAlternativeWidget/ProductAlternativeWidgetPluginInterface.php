<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\WishlistPage\Dependency\Plugin\ProductAlternativeWidget;

use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\Dependency\Plugin\WidgetPluginInterface;

interface ProductAlternativeWidgetPluginInterface extends WidgetPluginInterface
{
    public const NAME = 'ProductAlternativeWidgetPlugin';

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param string $wishlistName
     *
     * @return void
     */
    public function initialize(ProductViewTransfer $productViewTransfer, string $wishlistName): void;
}
