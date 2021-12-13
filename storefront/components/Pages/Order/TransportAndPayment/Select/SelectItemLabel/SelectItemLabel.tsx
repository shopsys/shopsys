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
import { PickupPlaceType } from 'connectors/transports/pickupPlace/types';
import { TFunction } from 'react-i18next';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type SelectItemLabelProps = {
    name: string;
    price?: { priceWithVat: number; priceWithoutVat: number; vatAmount: number };
    daysUntilDelivery?: number;
    description?: string;
    pickupPlaceDetail?: PickupPlaceType | null;
};

const SelectItemLabel: FC<SelectItemLabelProps> = (props) => {
    const t = useTypedTranslationFunction();
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    return (
        <SelectItemLabelStyled>
            <NameWrapperStyled>
                <span>{props.name}</span>
                <DescriptionStyled>{props.description}</DescriptionStyled>
                {props.pickupPlaceDetail !== null && props.pickupPlaceDetail !== undefined && (
                    <>
                        <InfoStyled>{props.pickupPlaceDetail.name}</InfoStyled>
                        <InfoStyled>
                            {props.pickupPlaceDetail.street +
                                ', ' +
                                props.pickupPlaceDetail.postcode +
                                ', ' +
                                props.pickupPlaceDetail.city}
                        </InfoStyled>
                        <InfoStyled>{t('Open') + ': '}</InfoStyled>
                        <InfoStyled dangerouslySetInnerHTML={{ __html: props.pickupPlaceDetail.openingHours }} />
                    </>
                )}
            </NameWrapperStyled>
            {props.daysUntilDelivery !== undefined && (
                <TransportDaysUntilDeliveryStyled>
                    {getDeliveryMessage(props.daysUntilDelivery, props.pickupPlaceDetail !== undefined, t)}
                </TransportDaysUntilDeliveryStyled>
            )}
            {props.price !== undefined && (
                <PriceStyled>{formatPrice(props.price.priceWithVat, currencyCode, t)}</PriceStyled>
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
