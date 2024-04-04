import { Image } from 'components/Basic/Image/Image';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { mapPriceForCalculations } from 'utils/mappers/price';

type SingleProductProps = {
    item: TypeCartItemFragment;
};

export const SingleProduct: FC<SingleProductProps> = ({ item }) => {
    const formatPrice = useFormatPrice();

    return (
        <li className="flex items-center border-b border-creamWhite py-3">
            <div className="mr-4 flex w-14 items-center justify-center">
                <Image
                    alt={item.product.mainImage?.name || item.product.fullName}
                    className="max-h-14 w-auto"
                    height={56}
                    src={item.product.mainImage?.url}
                    width={56}
                />
            </div>

            <div className="flex flex-1 items-center">
                <span className="flex-1 pr-3 text-sm">
                    <strong>
                        {item.quantity} {item.product.unit.name} &nbsp;
                    </strong>
                    <span>{item.product.fullName}</span>
                </span>

                <strong className="ml-auto w-24 text-right text-sm">
                    {formatPrice(mapPriceForCalculations(item.product.price.priceWithVat) * item.quantity)}
                </strong>
            </div>
        </li>
    );
};
