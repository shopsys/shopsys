import {
    DescriptionStyled,
    InfoStyled,
    NameWrapperStyled,
    PriceStyled,
    SelectItemLabelStyled,
    TransportDaysUntilDeliveryStyled,
} from './TransportAndPaymentSelectItemLabel.style';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { Translate } from 'next-translate';
import { FC } from 'react';
import { PickupPlaceType } from 'types/pickupPlace';

type TransportAndPaymentSelectItemLabelProps = {
    name: string;
    price?: { priceWithVat: number; priceWithoutVat: number; vatAmount: number };
    daysUntilDelivery?: number;
    description?: string;
    pickupPlaceDetail?: PickupPlaceType | null;
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
        <SelectItemLabelStyled data-testid={TEST_IDENTIFIER}>
            <NameWrapperStyled>
                <span data-testid={TEST_IDENTIFIER + '-name'}>{name}</span>
                <DescriptionStyled data-testid={TEST_IDENTIFIER + '-description'}>{description}</DescriptionStyled>
                {pickupPlaceDetail !== null && pickupPlaceDetail !== undefined && (
                    <>
                        <InfoStyled data-testid={TEST_IDENTIFIER + '-place'}>{pickupPlaceDetail.name}</InfoStyled>
                        <InfoStyled data-testid={TEST_IDENTIFIER + '-address'}>
                            {pickupPlaceDetail.street +
                                ', ' +
                                pickupPlaceDetail.postcode +
                                ', ' +
                                pickupPlaceDetail.city}
                        </InfoStyled>
                        <InfoStyled>{t('Open') + ': '}</InfoStyled>
                        <InfoStyled
                            dangerouslySetInnerHTML={{ __html: pickupPlaceDetail.openingHoursHtml }}
                            data-testid={TEST_IDENTIFIER + '-openinghours'}
                        />
                    </>
                )}
            </NameWrapperStyled>
            {daysUntilDelivery !== undefined && (
                <TransportDaysUntilDeliveryStyled data-testid={TEST_IDENTIFIER + '-delivery'}>
                    {getDeliveryMessage(daysUntilDelivery, pickupPlaceDetail !== undefined, t)}
                </TransportDaysUntilDeliveryStyled>
            )}
            {price !== undefined && (
                <PriceStyled data-testid={TEST_IDENTIFIER + '-price'}>{formatPrice(price.priceWithVat)}</PriceStyled>
            )}
        </SelectItemLabelStyled>
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
