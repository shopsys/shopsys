import { Loader } from 'components/Basic/Loader/Loader';
import { ProductInquiryButton } from 'components/Blocks/Product/ProductInquiryButton';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useRef } from 'react';
import { useAddToCartAriaLabel } from 'utils/accessibility/useAddToCartAriaLabel';
import { useAddToCartHandler } from 'utils/cart/useAddToCartHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export type ProductDetailAddToCartProps = {
    product: TypeProductDetailFragment;
};

export const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = ({ product }) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const { t } = useTranslation();
    const { canCreateOrder } = useAuthorization();

    const { onAddToCartHandler, isAddingToCart } = useAddToCartHandler({
        spinboxRef,
        productUuid: product.uuid,
        gtmMessageOrigin: GtmMessageOriginType.product_detail_page,
        gtmProductListName: GtmProductListNameType.product_detail,
    });

    const { ariaLabel, onFocusHandler } = useAddToCartAriaLabel({
        spinboxRef,
        productName: product.name,
        priceWithVat: product.price.priceWithVat,
        unitName: product.unit.name,
    });

    if (product.isSellingDenied) {
        return <p className="text-text-error">{t('This item can no longer be purchased')}</p>;
    }

    if (product.isCurrentlyOutOfStock) {
        return (
            <p className="text-text-error">
                {t('This item is currently out of stock and cannot be purchased at the moment.')}
            </p>
        );
    }

    if (product.isInquiryType) {
        return (
            <ProductInquiryButton
                buttonSize="large"
                className="w-fit"
                productName={product.fullName}
                productUuid={product.uuid}
            />
        );
    }

    if (!canCreateOrder) {
        return null;
    }

    const isWatchdogButtonVisible =
        (product.uuid && product.availability.status === TypeAvailabilityStatusEnum.OutOfStock) ||
        product.isSellingDenied;

    return (
        <div className="flex items-center gap-2">
            <Spinbox
                defaultValue={1}
                id={product.uuid}
                max={product.isAllowedNegativeStock ? null : product.stockQuantity}
                min={1}
                ref={spinboxRef}
                size="xlarge"
                step={1}
            />

            <div className="relative">
                {isAddingToCart && (
                    <Loader className="z-overlay bg-background-more absolute inset-0 flex h-full w-full items-center justify-center rounded-sm py-2 opacity-50" />
                )}

                <Button
                    aria-haspopup="dialog"
                    aria-label={ariaLabel}
                    className="whitespace-nowrap"
                    disabled={isAddingToCart}
                    hasDisabledLook={isAddingToCart}
                    size="xlarge"
                    tid={TIDs.pages_productdetail_addtocart_button}
                    title={t('Add to cart')}
                    variant={isWatchdogButtonVisible ? 'inverted' : 'primary'}
                    onClick={onAddToCartHandler}
                    onFocus={onFocusHandler}
                >
                    {t('Add to cart')}
                </Button>
            </div>
        </div>
    );
};
