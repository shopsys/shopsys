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
import { TFunction } from 'react-i18next';
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

const getDeliveryMessage = (daysUntilDelivery: number, isPersonalPickup: boolean, t: TFunction<string>) => {
    if (isPersonalPickup) {
        if (daysUntilDelivery < 7) {
            return t(
                '(0)[You can have immediately];(1)[Personal pickup in 1 day];(2-inf)[Personal pickup in {{count}} days];',
                { postProcess: 'interval', count: daysUntilDelivery },
            );
        }
        return t('(1)[Personal pickup in 1 week];(2-inf)[Personal pickup in {{count}} weeks];', {
            postProcess: 'interval',
            count: Math.ceil(daysUntilDelivery / 7),
        });
    }

    if (daysUntilDelivery < 7) {
        return t('(0)[You can have today];(1)[Delivery in 1 day];(2-inf)[Delivery in {{count}} days];', {
            postProcess: 'interval',
            count: daysUntilDelivery,
        });
    }
    return t('(1)[Delivery in 1 week];(2-inf)[Delivery in {{count}} weeks];', {
        postProcess: 'interval',
        count: Math.ceil(daysUntilDelivery / 7),
    });
};

export default SelectItemLabel;
