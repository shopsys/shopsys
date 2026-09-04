import { Image } from 'components/Basic/Image/Image';
import { CartItemPartialAvailability } from 'components/Blocks/Product/CartItemPartialAvailability';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { isCartItemPartiallyAvailable } from 'utils/cart/isCartItemPartiallyAvailable';
import { generateProductImageAlt } from 'utils/productAltText';
import { OrderItemProductPrice } from './OrderItemProductPrice';

type OrderItemProductCardProps = {
    mainImage?: TypeImageFragment | null;
    fullName: string;
    categoryName: string;
    quantity: number;
    freeQuantity: number | null;
    unit: string | null;
    price: TypeProductPriceFragment;
    product?: TypeCartItemFragment['product'];
};

export const OrderItemProductCard: FC<OrderItemProductCardProps> = ({
    mainImage,
    fullName,
    categoryName,
    quantity,
    freeQuantity,
    unit,
    price,
    product,
}) => {
    return (
        <li className="flex flex-col gap-1 rounded-xl bg-background-more p-4 font-secondary">
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
                        {product !== undefined &&
                            (isCartItemPartiallyAvailable(product, quantity) ? (
                                <CartItemPartialAvailability
                                    className="font-semibold text-xs"
                                    expectedRestockingDate={product.expectedRestockingDate}
                                    stockQuantity={product.stockQuantity ?? 0}
                                    unitName={unit ?? ''}
                                />
                            ) : (
                                <ProductAvailability
                                    availability={product.availability}
                                    availableStoresCount={null}
                                    displayMode="compact"
                                    isInquiryType={false}
                                />
                            ))}
                    </div>
                    <span className="whitespace-nowrap font-semibold text-sm">
                        {quantity} {unit}
                    </span>
                </div>
            </div>

            <OrderItemProductPrice freeQuantity={freeQuantity} productPrice={price} quantity={quantity} unit={unit} />
        </li>
    );
};
