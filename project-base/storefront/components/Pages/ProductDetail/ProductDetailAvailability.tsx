import { useOpenDeliveryOptionsPopup } from 'components/Blocks/Popup/DeliveryOptionsPopup/useOpenDeliveryOptionsPopup';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isProductSellable } from 'utils/product/isProductSellable';
import { showProductDeliveryOptionsPopup } from 'utils/product/showProductDeliveryOptionsPopup';

type ProductDetailAvailabilityProps = {
    product: TypeProductDetailFragment;
};

export const ProductDetailAvailability: FC<ProductDetailAvailabilityProps> = ({ product }) => {
    const openDeliveryOptionsPopup = useOpenDeliveryOptionsPopup();
    const { canCreateOrder } = useAuthorization();
    const { t } = useTranslation();

    if (product.isSellingDenied || product.isInquiryType) {
        return null;
    }

    const isInteractive = showProductDeliveryOptionsPopup(product);
    const productAvailability = (
        <ProductAvailability
            availability={product.availability}
            availableStoresCount={product.availableStoresCount}
            className={isInteractive ? 'transition-colors group-hover/availability:text-link-hovered' : undefined}
            isPersonalPickupOnly={product.isPersonalPickupOnly}
            displayMode="detail"
            isInquiryType={product.isInquiryType}
        />
    );

    const canOrderUnavailableProduct =
        canCreateOrder &&
        isProductSellable(product) &&
        (product.availability.status === TypeAvailabilityStatusEnum.ExpectedRestock ||
            product.availability.status === TypeAvailabilityStatusEnum.OutOfStock);

    return (
        <div className="flex flex-col gap-1">
            {isInteractive ? (
                <button
                    aria-haspopup="dialog"
                    className="group/availability w-fit cursor-pointer rounded-md"
                    type="button"
                    onClick={() => openDeliveryOptionsPopup([product], product.uuid)}
                >
                    {productAvailability}
                </button>
            ) : (
                productAvailability
            )}
            {canOrderUnavailableProduct && (
                <p className="font-secondary text-sm text-text-less">
                    {product.availability.status === TypeAvailabilityStatusEnum.ExpectedRestock
                        ? t("You can order this item now. We'll ship it once it's back in stock.")
                        : t('You can order this item now. The restock date is not yet known.')}
                </p>
            )}
        </div>
    );
};
