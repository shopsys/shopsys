import { Image } from 'components/Basic/Image/Image';
import { CartItemFragmentApi } from 'graphql/generated';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';

type SingleProductProps = {
    item: CartItemFragmentApi;
};

const TEST_IDENTIFIER = 'blocks-ordersummary-singleproduct-';

export const SingleProduct: FC<SingleProductProps> = ({ item }) => {
    const formatPrice = useFormatPrice();

    return (
        <li className="flex items-center border-b border-creamWhite py-3">
            <Image
                alt={item.product.mainImage?.name || item.product.fullName}
                className="mr-4 h-14 w-14"
                image={item.product.mainImage}
            />

            <div className="flex flex-1 items-center">
                <span className="flex-1 pr-3 text-sm">
                    <strong data-testid={TEST_IDENTIFIER + 'count'}>
                        {item.quantity} {item.product.unit.name} &nbsp;
                    </strong>
                    <span data-testid={TEST_IDENTIFIER + 'name'}>{item.product.fullName}</span>
                </span>

                <strong className="ml-auto w-24 text-right text-sm" data-testid={TEST_IDENTIFIER + 'price'}>
                    {formatPrice(mapPriceForCalculations(item.product.price.priceWithVat) * item.quantity)}
                </strong>
            </div>
        </li>
    );
};
