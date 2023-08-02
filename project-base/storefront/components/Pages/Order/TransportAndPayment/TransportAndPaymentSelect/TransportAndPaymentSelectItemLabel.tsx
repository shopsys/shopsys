import { ListedStoreFragmentApi } from 'graphql/generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { Translate } from 'next-translate';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';

type TransportAndPaymentSelectItemLabelProps = {
    name: string;
    price?: { priceWithVat: string; priceWithoutVat: string; vatAmount: string };
    daysUntilDelivery?: number;
    description?: string | null;
    pickupPlaceDetail?: ListedStoreFragmentApi | null;
};

const TEST_IDENTIFIER = 'pages-order-selectitem-label';

export const TransportAndPaymentSelectItemLabel: FC<TransportAndPaymentSelectItemLabelProps> = ({
    name,
    price,
    daysUntilDelivery,
    description,
    pickupPlaceDetail,
}) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    return (
        <div className="flex w-full flex-row flex-wrap items-center lg:w-auto lg:flex-1" data-testid={TEST_IDENTIFIER}>
            <div className="mr-4 flex w-full flex-col flex-wrap text-sm lg:w-auto lg:flex-1">
                <span data-testid={TEST_IDENTIFIER + '-name'}>{name}</span>
                <span
                    className="inline text-sm text-greyLight lg:hidden"
                    data-testid={TEST_IDENTIFIER + '-description'}
                >
                    {description}
                </span>
                {pickupPlaceDetail !== null && pickupPlaceDetail !== undefined && (
                    <>
                        <span className="text-sm text-greyLight" data-testid={TEST_IDENTIFIER + '-place'}>
                            {pickupPlaceDetail.name}
                        </span>
                        <span className="text-sm text-greyLight" data-testid={TEST_IDENTIFIER + '-address'}>
                            {pickupPlaceDetail.street +
                                ', ' +
                                pickupPlaceDetail.postcode +
                                ', ' +
                                pickupPlaceDetail.city}
                        </span>
                        <span className="text-sm text-greyLight">{t('Open') + ': '}</span>
                        <OpeningHours openingHours={pickupPlaceDetail.openingHours} className="items-start" />
                    </>
                )}
            </div>
            {daysUntilDelivery !== undefined && (
                <span
                    className="w-1/2 text-sm text-inStock lg:w-36 lg:self-center lg:text-right"
                    data-testid={TEST_IDENTIFIER + '-delivery'}
                >
                    {getDeliveryMessage(daysUntilDelivery, pickupPlaceDetail !== undefined, t)}
                </span>
            )}
            {price !== undefined && (
                <strong className="w-1/2 text-right text-sm lg:w-24" data-testid={TEST_IDENTIFIER + '-price'}>
                    {formatPrice(price.priceWithVat)}
                </strong>
            )}
        </div>
    );
};

const getDeliveryMessage = (daysUntilDelivery: number, isPersonalPickup: boolean, t: Translate) => {
    if (isPersonalPickup) {
        if (daysUntilDelivery < 7) {
            return t('Personal pickup in {{ count }} days', { count: daysUntilDelivery });
        }
        return t('Personal pickup in {{count}} weeks', {
            count: Math.ceil(daysUntilDelivery / 7),
        });
    }

    if (daysUntilDelivery < 7) {
        return t('Delivery in {{count}} days', {
            count: daysUntilDelivery,
        });
    }
    return t('Delivery in {{count}} weeks', {
        count: Math.ceil(daysUntilDelivery / 7),
    });
};
