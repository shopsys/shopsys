import { AddToCart, AddToCartContent } from 'components/Blocks/Product/AddToCart';
import { ProductInquiryButton } from 'components/Blocks/Product/ProductInquiryButton';
import { showWatchdogButton, WatchDogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { CurrentCartType } from 'types/cart';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type PurchaseAction = 'sellingDenied' | 'outOfStock' | 'inquiry' | 'chooseVariant' | 'addToCart' | 'none';

const getPurchaseAction = (product: TypeListedProductFragment, canCreateOrder: boolean): PurchaseAction => {
    if (product.isSellingDenied) {
        return 'sellingDenied';
    }

    if (product.isCurrentlyOutOfStock) {
        return 'outOfStock';
    }

    if (!product.isMainVariant && product.isInquiryType) {
        return 'inquiry';
    }

    if (!canCreateOrder) {
        return 'none';
    }

    if (product.isMainVariant) {
        return 'chooseVariant';
    }

    return 'addToCart';
};

type ProductActionProps = {
    product: TypeListedProductFragment;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    listIndex: number;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    isWatchdogButtonShownWithPurchaseAction?: boolean;
    skipKeyboardNavigation?: boolean;
    currentCart?: Pick<CurrentCartType, 'cart' | 'isCartFetchingOrUnavailable'>;
};

export const PRODUCT_VARIANTS_ID = 'product-variants';

export const ProductAction: FC<ProductActionProps> = ({
    currentCart,
    product,
    gtmProductListName,
    gtmMessageOrigin,
    listIndex,
    buttonSize = 'medium',
    isWatchdogButtonShownWithPurchaseAction = false,
    skipKeyboardNavigation = false,
}) => {
    const { t } = useTranslation();
    const { canCreateOrder } = useAuthorization();

    const purchaseAction = getPurchaseAction(product, canCreateOrder);

    // being out of stock is the only state in which the watchdog button stands in for the purchase action
    const isWatchdogButtonVisible =
        showWatchdogButton(product) && (isWatchdogButtonShownWithPurchaseAction || purchaseAction === 'outOfStock');

    const addToCartVariant: 'primary' | 'secondary' = isWatchdogButtonVisible ? 'secondary' : 'primary';

    const addToCartProps = {
        ariaPrice: product.price.priceWithVat,
        ariaProductName: product.fullName,
        ariaUnit: product.unit.name,
        buttonSize,
        buttonVariant: addToCartVariant,
        gtmMessageOrigin,
        gtmProductListName,
        listIndex,
        maxQuantity: product.isAllowedNegativeStock ? null : product.stockQuantity,
        minQuantity: 1,
        productUuid: product.uuid,
        tabIndex: skipKeyboardNavigation ? -1 : 0,
    };

    return (
        <>
            {isWatchdogButtonVisible && (
                <WatchDogButton className="w-full" listIndex={listIndex} product={product} size={buttonSize} />
            )}

            {purchaseAction === 'sellingDenied' && (
                <div className="max-w-53 text-center">{t('This item can no longer be purchased')}</div>
            )}

            {purchaseAction === 'inquiry' && (
                <ProductInquiryButton
                    buttonSize={buttonSize}
                    productName={product.fullName}
                    productUuid={product.uuid}
                    skipKeyboardNavigation={skipKeyboardNavigation}
                />
            )}

            {purchaseAction === 'chooseVariant' && (
                <LinkButton
                    className="w-full"
                    href={`${product.slug}#${PRODUCT_VARIANTS_ID}`}
                    tabIndex={skipKeyboardNavigation ? -1 : 0}
                    type="productMainVariant"
                    aria-label={t('Go to page with product variants of {{ productName }}', {
                        ns: 'accessibility',
                        productName: product.fullName,
                    })}
                >
                    {t('Choose')}
                </LinkButton>
            )}

            {purchaseAction === 'addToCart' &&
                (currentCart ? (
                    <AddToCartContent {...addToCartProps} {...currentCart} />
                ) : (
                    <AddToCart {...addToCartProps} />
                ))}
        </>
    );
};
