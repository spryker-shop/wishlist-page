<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\WishlistPage\Dependency\Plugin\ProductDiscontinuedWidget;

use Spryker\Yves\Kernel\Dependency\Plugin\WidgetPluginInterface;

interface ProductDiscontinuedWidgetPluginInterface extends WidgetPluginInterface
{
    public const NAME = 'ProductDiscontinuedWidgetPlugin';

    /**
     * @param string $sku
     *
     * @return void
     */
    public function initialize(string $sku): void;
}
