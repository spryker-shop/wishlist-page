<?php

/**
 * This file is part of the Spryker Demoshop.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerShop\Yves\WishlistPage\Controller;

use Generated\Shared\Transfer\ProductViewTransfer;
use Generated\Shared\Transfer\WishlistItemMetaTransfer;
use Generated\Shared\Transfer\WishlistItemTransfer;
use Generated\Shared\Transfer\WishlistOverviewRequestTransfer;
use Generated\Shared\Transfer\WishlistOverviewResponseTransfer;
use Generated\Shared\Transfer\WishlistTransfer;
use SprykerShop\Yves\ShopApplication\Controller\AbstractController;
use SprykerShop\Yves\CustomerPage\Plugin\Provider\CustomerPageControllerProvider;
use SprykerShop\Yves\WishlistPage\Form\AddAllAvailableProductsToCartFormType;
use SprykerShop\Yves\WishlistPage\Plugin\Provider\WishlistPageControllerProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method \SprykerShop\Yves\WishlistPage\WishlistPageFactory getFactory()
 */
class WishlistController extends AbstractController
{
    const DEFAULT_NAME = 'My wishlist';
    const DEFAULT_ITEMS_PER_PAGE = 10;

    const PARAM_ITEMS_PER_PAGE = 'ipp';
    const PARAM_PAGE = 'page';
    const PARAM_PRODUCT_ID = 'product-id';
    const PARAM_SKU = 'sku';
    const PARAM_WISHLIST_NAME = 'wishlist-name';

    /**
     * @param string $wishlistName
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Spryker\Yves\Kernel\View\View
     */
    public function indexAction($wishlistName, Request $request)
    {
        $pageNumber = $this->getPageNumber($request);
        $itemsPerPage = $this->getItemsPerPage($request);

        $customerTransfer = $this->getFactory()
            ->getCustomerClient()
            ->getCustomer();

        $wishlistTransfer = (new WishlistTransfer())
            ->setName($wishlistName)
            ->setFkCustomer($customerTransfer->getIdCustomer());

        $wishlistOverviewRequest = (new WishlistOverviewRequestTransfer())
            ->setWishlist($wishlistTransfer)
            ->setPage($pageNumber)
            ->setItemsPerPage($itemsPerPage);

        $wishlistOverviewResponse = $this->getFactory()
            ->getWishlistClient()
            ->getWishlistOverviewWithoutProductDetails($wishlistOverviewRequest);

        if (!$wishlistOverviewResponse->getWishlist()->getIdWishlist()) {
            throw new NotFoundHttpException();
        }

        $wishlistItems = $this->getWishlistItems($wishlistOverviewResponse);

        $addAllAvailableProductsToCartForm = $this->createAddAllAvailableProductsToCartForm($wishlistOverviewResponse);

        return $this->view([
            'wishlistItems' => $wishlistItems,
            'wishlistOverview' => $wishlistOverviewResponse,
            'currentPage' => $wishlistOverviewResponse->getPagination()->getPage(),
            'totalPages' => $wishlistOverviewResponse->getPagination()->getPagesTotal(),
            'wishlistName' => $wishlistName,
            'addAllAvailableProductsToCartForm' => $addAllAvailableProductsToCartForm->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addItemAction(Request $request)
    {
        $wishlistItemTransfer = $this->getWishlistItemTransferFromRequest($request);
        if (!$wishlistItemTransfer) {
            return $this->redirectResponseInternal(CustomerPageControllerProvider::ROUTE_LOGIN);
        }

        $wishlistItemTransfer = $this->getFactory()
            ->getWishlistClient()
            ->addItem($wishlistItemTransfer);
        if (!$wishlistItemTransfer->getIdWishlistItem()) {
            $this->addErrorMessage('customer.account.wishlist.item.not_added');
        }

        return $this->redirectResponseInternal(WishlistPageControllerProvider::ROUTE_WISHLIST_DETAILS, [
            'wishlistName' => $wishlistItemTransfer->getWishlistName(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeItemAction(Request $request)
    {
        $wishlistItemTransfer = $this->getWishlistItemTransferFromRequest($request);
        if (!$wishlistItemTransfer) {
            return $this->redirectResponseInternal(CustomerPageControllerProvider::ROUTE_LOGIN);
        }

        $this->getFactory()
            ->getWishlistClient()
            ->removeItem($wishlistItemTransfer);

        $this->addSuccessMessage('customer.account.wishlist.item.removed');

        return $this->redirectResponseInternal(WishlistPageControllerProvider::ROUTE_WISHLIST_DETAILS, [
            'wishlistName' => $wishlistItemTransfer->getWishlistName(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function moveToCartAction(Request $request)
    {
        $wishlistItemTransfer = $this->getWishlistItemTransferFromRequest($request);
        if (!$wishlistItemTransfer) {
            return $this->redirectResponseInternal(CustomerPageControllerProvider::ROUTE_LOGIN);
        }

        $wishlistItemMetaTransferCollection = [
            (new WishlistItemMetaTransfer())
                ->setSku($wishlistItemTransfer->getSku()),
        ];

        $result = $this->getFactory()
            ->createMoveToCartHandler()
            ->moveAllAvailableToCart(
                $wishlistItemTransfer->getWishlistName(),
                $wishlistItemMetaTransferCollection
            );

        if ($result->getRequests()->count()) {
            $this->addErrorMessage('customer.account.wishlist.item.moved_to_cart.failed');
            return $this->redirectResponseInternal(WishlistPageControllerProvider::ROUTE_WISHLIST_DETAILS, [
                'wishlistName' => $wishlistItemTransfer->getWishlistName(),
            ]);
        }

        $this->addSuccessMessage('customer.account.wishlist.item.moved_to_cart');
        return $this->redirectResponseInternal(WishlistPageControllerProvider::ROUTE_WISHLIST_DETAILS, [
            'wishlistName' => $wishlistItemTransfer->getWishlistName(),
        ]);
    }

    /**
     * @param string $wishlistName
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function moveAllAvailableToCartAction($wishlistName, Request $request)
    {
        $addAllAvailableProductsToCartForm = $this
            ->createAddAllAvailableProductsToCartForm()
            ->handleRequest($request);

        if ($addAllAvailableProductsToCartForm->isValid()) {
            $wishlistItemMetaTransferCollection = $addAllAvailableProductsToCartForm
                ->get(AddAllAvailableProductsToCartFormType::WISHLIST_ITEM_META_COLLECTION)
                ->getData();

            $result = $this->getFactory()
                ->createMoveToCartHandler()
                ->moveAllAvailableToCart($wishlistName, $wishlistItemMetaTransferCollection);

            if ($result->getRequests()->count()) {
                $this->addErrorMessage('customer.account.wishlist.item.moved_all_available_to_cart.failed');
                return $this->redirectResponseInternal(WishlistPageControllerProvider::ROUTE_WISHLIST_DETAILS, [
                    'wishlistName' => $wishlistName,
                ]);
            }

            $this->addSuccessMessage('customer.account.wishlist.item.moved_all_available_to_cart');
        }

        return $this->redirectResponseInternal(WishlistPageControllerProvider::ROUTE_WISHLIST_DETAILS, [
            'wishlistName' => $wishlistName,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return int
     */
    protected function getPageNumber(Request $request)
    {
        $pageNumber = $request->query->getInt(self::PARAM_PAGE, 1);
        $pageNumber = $pageNumber <= 0 ? 1 : $pageNumber;

        return $pageNumber;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return int
     */
    protected function getItemsPerPage(Request $request)
    {
        $itemsPerPage = $request->query->getInt(self::PARAM_ITEMS_PER_PAGE, self::DEFAULT_ITEMS_PER_PAGE);
        $itemsPerPage = ($itemsPerPage <= 0) ? 1 : $itemsPerPage;
        $itemsPerPage = ($itemsPerPage > 100) ? 10 : $itemsPerPage;

        return $itemsPerPage;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Generated\Shared\Transfer\WishlistItemTransfer|null
     */
    protected function getWishlistItemTransferFromRequest(Request $request)
    {
        $customerClient = $this->getFactory()->getCustomerClient();
        $customerTransfer = $customerClient->getCustomer();

        if (!$customerTransfer) {
            return null;
        }

        $wishlistName = $request->get(self::PARAM_WISHLIST_NAME) ?: self::DEFAULT_NAME;

        return (new WishlistItemTransfer())
            ->setIdProduct($request->query->getInt(self::PARAM_PRODUCT_ID))
            ->setSku($request->query->get(self::PARAM_SKU))
            ->setFkCustomer($customerTransfer->getIdCustomer())
            ->setWishlistName($wishlistName);
    }

    /**
     * @param \Generated\Shared\Transfer\WishlistOverviewResponseTransfer|null $wishlistOverviewResponse
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createAddAllAvailableProductsToCartForm(WishlistOverviewResponseTransfer $wishlistOverviewResponse = null)
    {
        $addAllAvailableProductsToCartFormDataProvider = $this->getFactory()->createAddAllAvailableProductsToCartFormDataProvider();
        $addAllAvailableProductsToCartForm = $this->getFactory()->getAddAllAvailableProductsToCartForm(
            $addAllAvailableProductsToCartFormDataProvider->getData($wishlistOverviewResponse)
        );

        return $addAllAvailableProductsToCartForm;
    }

    /**
     * @param WishlistOverviewResponseTransfer $wishlistOverviewResponse
     *
     * @return ProductViewTransfer[]
     */
    protected function getWishlistItems(WishlistOverviewResponseTransfer $wishlistOverviewResponse): array
    {
        $wishlistItems = [];
        foreach ($wishlistOverviewResponse->getItems() as $wishlistItemTransfer) {
            $wishlistItems[] = $this->createProductView($wishlistItemTransfer);
        }

        return $wishlistItems;
    }

    /**
     * @param WishlistItemTransfer $wishlistItemTransfer
     *
     * @return ProductViewTransfer
     */
    protected function createProductView(WishlistItemTransfer $wishlistItemTransfer)
    {
        $productConcreteStorageData = $this->getFactory()
            ->getProductStorageClient()
            ->getProductConcreteStorageData($wishlistItemTransfer->getIdProduct(), $this->getLocale());

        $productViewTransfer = new ProductViewTransfer();
        $productViewTransfer->fromArray($productConcreteStorageData, true);

        foreach ($this->getFactory()->getWishlistItemExpanderPlugins() as $productViewExpanderPlugin) {
            $productViewTransfer = $productViewExpanderPlugin->expandProductViewTransfer(
                $productViewTransfer,
                $productConcreteStorageData,
                $this->getLocale()
            );
        }

        return $productViewTransfer;
    }
}