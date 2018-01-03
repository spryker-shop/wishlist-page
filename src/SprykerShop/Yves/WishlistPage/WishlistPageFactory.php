<?php

/**
 * This file is part of the Spryker Demoshop.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerShop\Yves\WishlistPage;

use Generated\Shared\Transfer\WishlistTransfer;
use Spryker\Client\ProductStorage\Dependency\Plugin\ProductViewExpanderPluginInterface;
use Spryker\Shared\Application\ApplicationConstants;
use Spryker\Yves\Kernel\AbstractFactory;
use SprykerShop\Yves\WishlistPage\Business\MoveToCartHandler;
use SprykerShop\Yves\WishlistPage\Dependency\Client\WishlistPageToCustomerClientInterface;
use SprykerShop\Yves\WishlistPage\Dependency\Client\WishlistPageToProductStorageClientInterface;
use SprykerShop\Yves\WishlistPage\Dependency\Client\WishlistPageToWishlistClientInterface;
use SprykerShop\Yves\WishlistPage\Form\AddAllAvailableProductsToCartFormType;
use SprykerShop\Yves\WishlistPage\Form\DataProvider\AddAllAvailableProductsToCartFormDataProvider;
use SprykerShop\Yves\WishlistPage\Form\DataProvider\WishlistFormDataProvider;
use SprykerShop\Yves\WishlistPage\Form\WishlistFormType;

class WishlistPageFactory extends AbstractFactory
{
    /**
     * @return \SprykerShop\Yves\WishlistPage\Dependency\Client\WishlistPageToCustomerClientInterface
     */
    public function getCustomerClient(): WishlistPageToCustomerClientInterface
    {
        return $this->getProvidedDependency(WishlistPageDependencyProvider::CLIENT_CUSTOMER);
    }

    /**
     * @param \Generated\Shared\Transfer\WishlistTransfer|null $data
     * @param array $options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getWishlistForm(WishlistTransfer $data = null, array $options = [])
    {
        return $this->getFormFactory()->create($this->createWishlistFormType(), $data, $options);
    }

    /**
     * @return \SprykerShop\Yves\WishlistPage\Form\DataProvider\WishlistFormDataProvider
     */
    public function createWishlistFormDataProvider()
    {
        return new WishlistFormDataProvider(
            $this->getWishlistClient(),
            $this->getCustomerClient()
        );
    }

    /**
     * @param array $data
     * @param array $options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getAddAllAvailableProductsToCartForm(array $data, array $options = [])
    {
        return $this->getFormFactory()->create($this->createAddAllAvailableProductsToCartFormType(), $data, $options);
    }

    /**
     * @return \SprykerShop\Yves\WishlistPage\Form\DataProvider\AddAllAvailableProductsToCartFormDataProvider
     */
    public function createAddAllAvailableProductsToCartFormDataProvider()
    {
        return new AddAllAvailableProductsToCartFormDataProvider();
    }

    /**
     * @return \SprykerShop\Yves\WishlistPage\Form\WishlistFormType
     */
    protected function createWishlistFormType()
    {
        return new WishlistFormType();
    }

    /**
     * @return \SprykerShop\Yves\WishlistPage\Form\AddAllAvailableProductsToCartFormType
     */
    protected function createAddAllAvailableProductsToCartFormType()
    {
        return new AddAllAvailableProductsToCartFormType();
    }

    /**
     * @return \Symfony\Component\Form\FormFactory
     */
    protected function getFormFactory()
    {
        return $this->getProvidedDependency(ApplicationConstants::FORM_FACTORY);
    }

    /**
     * @return \SprykerShop\Yves\WishlistPage\Business\MoveToCartHandlerInterface
     */
    public function createMoveToCartHandler()
    {
        return new MoveToCartHandler($this->getWishlistClient(), $this->getCustomerClient());
    }

    /**
     * @return \SprykerShop\Yves\WishlistPage\Dependency\Client\WishlistPageToWishlistClientInterface
     */
    public function getWishlistClient(): WishlistPageToWishlistClientInterface
    {
        return $this->getProvidedDependency(WishlistPageDependencyProvider::CLIENT_WISHLIST);
    }

    /**
     * @return WishlistPageToProductStorageClientInterface
     */
    public function getProductStorageClient()
    {
        return $this->getProvidedDependency(WishlistPageDependencyProvider::CLIENT_PRODUCT_STORAGE);
    }

    /**
     * @return ProductViewExpanderPluginInterface[]
     */
    public function getWishlistItemExpanderPlugins()
    {
        return $this->getProvidedDependency(WishlistPageDependencyProvider::PLUGIN_WISHLIST_ITEM_EXPANDERS);
    }
}