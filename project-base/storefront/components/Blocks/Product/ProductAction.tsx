import { AddToCart } from 'components/Blocks/Product/AddToCart';
import { ProductInquiryButton } from 'components/Blocks/Product/ProductInquiryButton';
import { WatchDogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductActionProps = {
    product: TypeListedProductFragment;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    listIndex: number;
    isWithSpinbox?: boolean;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    buttonVariant?: 'primary' | 'inverted';
    showResponsiveCartIcon?: boolean;
    skipKeyboardNavigation?: boolean;
};

export const ProductAction: FC<ProductActionProps> = ({
    product,
    gtmProductListName,
    gtmMessageOrigin,
    listIndex,
    isWithSpinbox = false,
    buttonSize,
    buttonVariant = 'primary',
    showResponsiveCartIcon = false,
    skipKeyboardNavigation = false,
}) => {
    const { t } = useTranslation();
    const { canCreateOrder } = useAuthorization();

    if (product.isSellingDenied) {
        return <div className="max-w-[215px] text-center">{t('This item can no longer be purchased')}</div>;
    }

    if (product.isCurrentlyOutOfStock) {
        return <WatchDogButton buttonSize="medium" className="self-start" listIndex={listIndex} product={product} />;
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

    return (
        <AddToCart
            ariaPrice={product.price.priceWithVat}
            ariaProductName={product.fullName}
            ariaUnit={product.unit.name}
            buttonSize={buttonSize}
            buttonVariant={buttonVariant}
            gtmMessageOrigin={gtmMessageOrigin}
            gtmProductListName={gtmProductListName}
            isWithSpinbox={isWithSpinbox}
            listIndex={listIndex}
            maxQuantity={product.isAllowedNegativeStock ? null : product.stockQuantity}
            minQuantity={1}
            productUuid={product.uuid}
            showResponsiveCartIcon={showResponsiveCartIcon}
            tabIndex={skipKeyboardNavigation ? -1 : 0}
        />
    );
};
