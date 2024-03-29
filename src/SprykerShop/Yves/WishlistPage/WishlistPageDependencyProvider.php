<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\WishlistPage;

use Spryker\Yves\Kernel\AbstractBundleDependencyProvider;
use Spryker\Yves\Kernel\Container;
use SprykerShop\Yves\WishlistPage\Dependency\Client\WishlistPageToCustomerClientBridge;
use SprykerShop\Yves\WishlistPage\Dependency\Client\WishlistPageToGlossaryStorageClientBridge;
use SprykerShop\Yves\WishlistPage\Dependency\Client\WishlistPageToProductStorageClientBridge;
use SprykerShop\Yves\WishlistPage\Dependency\Client\WishlistPageToWishlistClientBridge;

class WishlistPageDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_CUSTOMER = 'CLIENT_CUSTOMER';

    /**
     * @var string
     */
    public const CLIENT_WISHLIST = 'CLIENT_WISHLIST';

    /**
     * @var string
     */
    public const CLIENT_GLOSSARY_STORAGE = 'CLIENT_GLOSSARY';

    /**
     * @var string
     */
    public const CLIENT_PRODUCT_STORAGE = 'CLIENT_PRODUCT_STORAGE';

    /**
     * @var string
     */
    public const PLUGIN_WISHLIST_ITEM_EXPANDERS = 'PLUGIN_WISHLIST_ITEM_EXPANDERS';

    /**
     * @var string
     */
    public const PLUGIN_WISHLIST_VIEW_WIDGETS = 'PLUGIN_WISHLIST_VIEW_WIDGETS';

    /**
     * @var string
     */
    public const PLUGIN_WISHLIST_ITEM_REQUEST_EXPANDERS = 'PLUGIN_WISHLIST_ITEM_REQUEST_EXPANDERS';

    /**
     * @var string
     */
    public const PLUGIN_WISHLIST_ITEM_META_FORM_EXPANDERS = 'PLUGIN_WISHLIST_ITEM_META_FORM_EXPANDERS';

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    public function provideDependencies(Container $container)
    {
        $container = $this->addCustomerClient($container);
        $container = $this->addWishlistClient($container);
        $container = $this->addGlossaryStorageClient($container);
        $container = $this->addProductStorageClient($container);
        $container = $this->addWishlistItemExpanderPlugins($container);
        $container = $this->addWishlistViewWidgetPlugins($container);
        $container = $this->addWishlistItemRequestExpanderPlugins($container);
        $container = $this->addWishlistItemMetaFormExpanderPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addCustomerClient(Container $container): Container
    {
        $container->set(static::CLIENT_CUSTOMER, function (Container $container) {
            return new WishlistPageToCustomerClientBridge($container->getLocator()->customer()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addWishlistClient(Container $container): Container
    {
        $container->set(static::CLIENT_WISHLIST, function (Container $container) {
            return new WishlistPageToWishlistClientBridge($container->getLocator()->wishlist()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addProductStorageClient(Container $container)
    {
        $container->set(static::CLIENT_PRODUCT_STORAGE, function (Container $container) {
            return new WishlistPageToProductStorageClientBridge($container->getLocator()->productStorage()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addWishlistItemExpanderPlugins(Container $container)
    {
        $container->set(static::PLUGIN_WISHLIST_ITEM_EXPANDERS, function () {
            return $this->getWishlistItemExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addWishlistViewWidgetPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_WISHLIST_VIEW_WIDGETS, function () {
            return $this->getWishlistViewWidgetPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addWishlistItemRequestExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_WISHLIST_ITEM_REQUEST_EXPANDERS, function () {
            return $this->getWishlistItemRequestExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addWishlistItemMetaFormExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_WISHLIST_ITEM_META_FORM_EXPANDERS, function () {
            return $this->getWishlistItemMetaFormExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addGlossaryStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_GLOSSARY_STORAGE, function (Container $container) {
            return new WishlistPageToGlossaryStorageClientBridge($container->getLocator()->glossaryStorage()->client());
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Client\ProductStorage\Dependency\Plugin\ProductViewExpanderPluginInterface>
     */
    protected function getWishlistItemExpanderPlugins()
    {
        return [];
    }

    /**
     * Returns a list of widget plugin class names that implement
     * Spryker\Yves\Kernel\Dependency\Plugin\WidgetPluginInterface.
     *
     * @return array<string>
     */
    protected function getWishlistViewWidgetPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\SprykerShop\Yves\WishlistPageExtension\Dependency\Plugin\WishlistItemRequestExpanderPluginInterface>
     */
    protected function getWishlistItemRequestExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\SprykerShop\Yves\WishlistPageExtension\Dependency\Plugin\WishlistItemMetaFormExpanderPluginInterface>
     */
    protected function getWishlistItemMetaFormExpanderPlugins(): array
    {
        return [];
    }
}
