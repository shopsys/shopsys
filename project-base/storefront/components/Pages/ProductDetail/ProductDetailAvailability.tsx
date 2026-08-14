import { useOpenDeliveryOptionsPopup } from 'components/Blocks/Popup/DeliveryOptionsPopup/useOpenDeliveryOptionsPopup';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { isProductSellable } from 'utils/product/isProductSellable';

type ProductDetailAvailabilityProps = {
    product: TypeProductDetailFragment;
};

export const ProductDetailAvailability: FC<ProductDetailAvailabilityProps> = ({ product }) => {
    const openDeliveryOptionsPopup = useOpenDeliveryOptionsPopup();

    if (product.isSellingDenied || product.isInquiryType) {
        return null;
    }

    const productAvailability = (
        <ProductAvailability
            availability={product.availability}
            availableStoresCount={product.availableStoresCount}
            displayMode="detail"
            isInquiryType={product.isInquiryType}
        />
    );

    if (!isProductSellable(product)) {
        return productAvailability;
    }

    return (
        <button
            aria-haspopup="dialog"
            className="w-fit cursor-pointer rounded-md"
            type="button"
            onClick={() => openDeliveryOptionsPopup([product], product.uuid)}
        >
            {productAvailability}
        </button>
    );
};
