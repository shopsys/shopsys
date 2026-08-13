import { AddToCart, AddToCartContent } from 'components/Blocks/Product/AddToCart';
import { ProductInquiryButton } from 'components/Blocks/Product/ProductInquiryButton';
import { WatchDogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { CurrentCartType } from 'types/cart';
import { FunctionComponentProps } from 'types/globals';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductActionProps = {
    product: TypeListedProductFragment;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    listIndex: number;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    buttonVariant?: 'primary' | 'inverted';
    skipKeyboardNavigation?: boolean;
    currentCart?: Pick<CurrentCartType, 'cart' | 'isCartFetchingOrUnavailable'>;
} & FunctionComponentProps;

export const ProductAction: FC<ProductActionProps> = ({
    currentCart,
    product,
    gtmProductListName,
    gtmMessageOrigin,
    listIndex,
    buttonSize,
    buttonVariant = 'primary',
    skipKeyboardNavigation = false,
}) => {
    const { t } = useTranslation();
    const { canCreateOrder } = useAuthorization();

    if (product.isSellingDenied) {
        return <div className="max-w-53 text-center">{t('This item can no longer be purchased')}</div>;
    }

    if (product.isCurrentlyOutOfStock) {
        return (
            <WatchDogButton className="w-full" listIndex={listIndex} product={product} size={buttonSize ?? 'medium'} />
        );
    }

    if (!product.isMainVariant && product.isInquiryType) {
        return (
            <ProductInquiryButton
                buttonSize={buttonSize}
                productName={product.fullName}
                productUuid={product.uuid}
                skipKeyboardNavigation={skipKeyboardNavigation}
            />
        );
    }

    if (!canCreateOrder) {
        return null;
    }

    if (product.isMainVariant) {
        return (
            <LinkButton
                className="w-full"
                href={product.slug}
                tabIndex={skipKeyboardNavigation ? -1 : 0}
                type="productMainVariant"
                aria-label={t('Go to page with product variants of {{ productName }}', {
                    ns: 'accessibility',
                    productName: product.fullName,
                })}
            >
                {t('Choose')}
            </LinkButton>
        );
    }

    const addToCartProps = {
        ariaPrice: product.price.priceWithVat,
        ariaProductName: product.fullName,
        ariaUnit: product.unit.name,
        buttonSize,
        buttonVariant,
        gtmMessageOrigin,
        gtmProductListName,
        listIndex,
        maxQuantity: product.isAllowedNegativeStock ? null : product.stockQuantity,
        minQuantity: 1,
        productUuid: product.uuid,
        tabIndex: skipKeyboardNavigation ? -1 : 0,
    };

    if (currentCart) {
        return <AddToCartContent {...addToCartProps} {...currentCart} />;
    }

    return <AddToCart {...addToCartProps} />;
};
