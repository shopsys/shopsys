import { GiftBadge } from 'components/Basic/GiftBadge/GiftBadge';
import { Image } from 'components/Basic/Image/Image';
import { OrderItemProductPrice } from 'components/Blocks/OrderItemProductCard/OrderItemProductPrice';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { TIDs } from 'cypress/tids';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { TypeAvailability } from 'graphql/types';
import { generateProductImageAlt } from 'utils/productAltText';

type OrderItemGiftCardProps = {
    availability?: TypeAvailability;
    mainImage?: TypeImageFragment | null;
    fullName: string;
    categoryName: string;
    quantity: number;
    unit: string | null;
    price: TypeProductPriceFragment;
};

export const OrderItemGiftCard: FC<OrderItemGiftCardProps> = ({
    mainImage,
    fullName,
    categoryName,
    quantity,
    unit,
    price,
    availability,
}) => {
    return (
        <li className="relative flex flex-col gap-1 rounded-xl bg-background-more p-4 font-secondary">
            <GiftBadge />

            <div className="flex items-center gap-2.5">
                <div className="flex size-20 items-center justify-center">
                    <Image
                        alt={generateProductImageAlt(fullName, categoryName)}
                        className="size-auto max-h-20 max-w-20 mix-blend-multiply"
                        height={80}
                        src={mainImage?.url}
                        tid={TIDs.order_summary_cart_item_image}
                        width={80}
                    />
                </div>
                <div className="flex flex-1 items-center justify-between gap-2.5">
                    <div className="flex flex-col gap-0.5">
                        <span className="max-w-44 font-semibold text-sm">{fullName}</span>
                        {availability !== undefined && (
                            <ProductAvailability
                                availability={availability}
                                availableStoresCount={null}
                                displayMode="compact"
                                isInquiryType={false}
                            />
                        )}
                    </div>
                    <span className="whitespace-nowrap font-semibold text-sm">
                        {quantity} {unit}
                    </span>
                </div>
            </div>

            <OrderItemProductPrice freeQuantity={null} productPrice={price} quantity={quantity} unit={unit} />
        </li>
    );
};
