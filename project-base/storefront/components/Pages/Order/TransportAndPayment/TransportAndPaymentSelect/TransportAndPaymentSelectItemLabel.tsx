import { ImageSizesFragmentApi, ListedStoreFragmentApi } from 'graphql/generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { Translate } from 'next-translate';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { Image } from 'components/Basic/Image/Image';

type TransportAndPaymentSelectItemLabelProps = {
    name: string;
    price?: { priceWithVat: string; priceWithoutVat: string; vatAmount: string };
    daysUntilDelivery?: number;
    description?: string | null;
    image?: ImageSizesFragmentApi | null;
    pickupPlaceDetail?: ListedStoreFragmentApi | null;
};

const TEST_IDENTIFIER = 'pages-order-selectitem-label';

export const TransportAndPaymentSelectItemLabel: FC<TransportAndPaymentSelectItemLabelProps> = ({
    name,
    price,
    daysUntilDelivery,
    description,
    image,
    pickupPlaceDetail,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <div className="flex w-full flex-row items-center gap-3" data-testid={TEST_IDENTIFIER}>
            <Image
                alt={image?.name}
                type="default"
                image={image}
                wrapperClassName="shrink-0"
                className="h-6 w-11 shrink-0 basis-11"
            />

            <div className="flex flex-1 flex-col text-sm lg:flex-auto lg:basis-full lg:flex-row lg:items-center lg:gap-3">
                <div data-testid={TEST_IDENTIFIER + '-name'}>{name}</div>

                {description && (
                    <div className="text-greyLight lg:hidden" data-testid={TEST_IDENTIFIER + '-description'}>
                        {description}
                    </div>
                )}

                {pickupPlaceDetail && (
                    <div>
                        <div className="text-greyLight" data-testid={TEST_IDENTIFIER + '-place'}>
                            {pickupPlaceDetail.name}
                        </div>

                        <div className="text-greyLight" data-testid={TEST_IDENTIFIER + '-address'}>
                            {pickupPlaceDetail.street +
                                ', ' +
                                pickupPlaceDetail.postcode +
                                ', ' +
                                pickupPlaceDetail.city}
                        </div>

                        <div className="my-1 text-greyLight">{t('Open') + ': '}</div>

                        <OpeningHours
                            openingHours={pickupPlaceDetail.openingHours}
                            className="items-start gap-1 lg:items-start"
                        />
                    </div>
                )}

                {daysUntilDelivery !== undefined && (
                    <div
                        className="text-sm text-inStock lg:ml-auto lg:basis-36 lg:text-right"
                        data-testid={TEST_IDENTIFIER + '-delivery'}
                    >
                        {getDeliveryMessage(daysUntilDelivery, !!pickupPlaceDetail, t)}
                    </div>
                )}
            </div>

            {price && (
                <div
                    className="shrink-0 text-right text-sm font-bold lg:basis-20"
                    data-testid={TEST_IDENTIFIER + '-price'}
                >
                    {formatPrice(price.priceWithVat)}
                </div>
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
