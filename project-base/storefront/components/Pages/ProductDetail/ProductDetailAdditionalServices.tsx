import { AdditionalServices } from 'components/Blocks/Product/AdditionalServices/AdditionalServices';
import { SkeletonModuleProductDetailAdditionalServices } from 'components/Blocks/Skeleton/SkeletonModuleProductDetailAdditionalServices';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useProductAdditionalServices } from 'utils/cart/useProductAdditionalServices';
import { isProductSellable } from 'utils/product/isProductSellable';

type ProductDetailAdditionalServicesProps = {
    product: TypeProductDetailFragment;
};

export const ProductDetailAdditionalServices: FC<ProductDetailAdditionalServicesProps> = ({ product }) => {
    const { canCreateOrder } = useAuthorization();
    const { isCartFetchingOrUnavailable } = useCurrentCart();
    const { cartItem, isAddToCartFlowPending, isSettingAdditionalServices, selectedServiceUuids, onToggleService } =
        useProductAdditionalServices({
            productUuid: product.uuid,
            gtmProductListName: GtmProductListNameType.product_detail,
        });

    if (!canCreateOrder || !isProductSellable(product) || product.additionalServices.length === 0) {
        return null;
    }

    return (
        <>
            {isCartFetchingOrUnavailable ? (
                <SkeletonModuleProductDetailAdditionalServices additionalServices={product.additionalServices} />
            ) : (
                <AdditionalServices
                    additionalServices={product.additionalServices}
                    isDisabled={isAddToCartFlowPending || isSettingAdditionalServices}
                    quantity={cartItem?.quantity}
                    selectedServiceUuids={selectedServiceUuids}
                    tidDiscriminator={product.catalogNumber}
                    unitName={product.unit.name}
                    showSelectedServiceTotalPrice
                    onToggleService={onToggleService}
                />
            )}
        </>
    );
};
