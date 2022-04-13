import {
    DescriptionStyled,
    InfoStyled,
    NameWrapperStyled,
    PriceStyled,
    SelectItemLabelStyled,
    TransportDaysUntilDeliveryStyled,
} from './SelectItemLabel.style';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import { PickupPlaceType } from 'types/pickupPlace';
import { Translate } from 'next-translate';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type SelectItemLabelProps = {
    name: string;
    price?: { priceWithVat: number; priceWithoutVat: number; vatAmount: number };
    daysUntilDelivery?: number;
    description?: string;
    pickupPlaceDetail?: PickupPlaceType | null;
    type?: string;
};

const SelectItemLabel: FC<SelectItemLabelProps> = (props) => {
    const testIdentifier = 'pages-order-selectitem-label';

    const t = useTypedTranslationFunction();
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    return (
        <SelectItemLabelStyled data-testid={testIdentifier}>
            <NameWrapperStyled>
                <span data-testid={testIdentifier + '-name'}>{props.name}</span>
                <DescriptionStyled data-testid={testIdentifier + '-description'}>{props.description}</DescriptionStyled>
                {props.pickupPlaceDetail !== null && props.pickupPlaceDetail !== undefined && (
                    <>
                        <InfoStyled data-testid={testIdentifier + '-place'}>{props.pickupPlaceDetail.name}</InfoStyled>
                        <InfoStyled data-testid={testIdentifier + '-address'}>
                            {props.pickupPlaceDetail.street +
                                ', ' +
                                props.pickupPlaceDetail.postcode +
                                ', ' +
                                props.pickupPlaceDetail.city}
                        </InfoStyled>
                        <InfoStyled>{t('Open') + ': '}</InfoStyled>
                        <InfoStyled
                            dangerouslySetInnerHTML={{ __html: props.pickupPlaceDetail.openingHoursHtml }}
                            data-testid={testIdentifier + '-openinghours'}
                        />
                    </>
                )}
            </NameWrapperStyled>
            {props.daysUntilDelivery !== undefined && (
                <TransportDaysUntilDeliveryStyled data-testid={testIdentifier + '-delivery'}>
                    {getDeliveryMessage(props.daysUntilDelivery, props.pickupPlaceDetail !== undefined, t)}
                </TransportDaysUntilDeliveryStyled>
            )}
            {props.price !== undefined && (
                <PriceStyled data-testid={testIdentifier + '-price'}>
                    {formatPrice(props.price.priceWithVat, currencyCode, t)}
                </PriceStyled>
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

export default SelectItemLabel;
