import { Image } from 'components/Basic/Image/Image';
import { OrderItemProductPrice } from 'components/Blocks/OrderItemProductCard/OrderItemProductPrice';
import { TIDs } from 'cypress/tids';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { generateProductImageAlt } from 'utils/productAltText';

type OrderItemGiftCardProps = {
    availability: TypeAvailability;
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
    const { t } = useTranslation();

    return (
        <li className="bg-background-more font-secondary relative flex flex-col gap-1 rounded-xl p-4">
            <div className="absolute top-0 left-0 z-10 rounded-tl-xl rounded-br-md bg-linear-to-r from-purple-600 to-pink-600 px-2 py-0.5 text-xs font-semibold text-white shadow-md">
                {t('Gift')}
            </div>

            <div className="isolate flex items-center gap-2.5">
                <Image
                    alt={generateProductImageAlt(fullName, categoryName)}
                    className="size-auto max-h-20 max-w-20 mix-blend-multiply"
                    height={80}
                    src={mainImage?.url}
                    tid={TIDs.order_summary_cart_item_image}
                    width={80}
                />
                <div className="flex flex-1 items-center justify-between gap-2.5">
                    <div className="flex flex-col gap-0.5">
                        <span className="max-w-44 text-sm font-semibold">{fullName}</span>
                        <span
                            className={twJoin(
                                'text-xs font-semibold',
                                availability.status === TypeAvailabilityStatusEnum.InStock &&
                                    'text-availability-in-stock',
                                availability.status === TypeAvailabilityStatusEnum.OutOfStock &&
                                    'text-availability-out-of-stock',
                            )}
                        >
                            {availability.name}
                        </span>
                    </div>
                    <span className="text-sm font-semibold whitespace-nowrap">
                        {quantity} {unit}
                    </span>
                </div>
            </div>

            <OrderItemProductPrice freeQuantity={null} productPrice={price} quantity={quantity} unit={unit} />
        </li>
    );
};
