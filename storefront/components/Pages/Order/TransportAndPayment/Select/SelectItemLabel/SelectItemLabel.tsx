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
import { StoreType } from 'connectors/transports/types';
import { TFunction } from 'react-i18next';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type SelectItemLabelProps = {
    name: string;
    price?: { priceWithVat: number; priceWithoutVat: number; vatAmount: number };
    daysUntilDelivery?: number;
    personalPickup?: boolean;
    description?: string;
    storeOpeningHours?: string;
    personalPickupStoreDetail?: StoreType;
};

const SelectItemLabel: FC<SelectItemLabelProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <SelectItemLabelStyled>
            <NameWrapperStyled>
                <span>{props.name}</span>
                <DescriptionStyled>{props.description}</DescriptionStyled>
                {props.personalPickupStoreDetail !== undefined && props.personalPickup && (
                    <>
                        <InfoStyled>{props.personalPickupStoreDetail.name}</InfoStyled>
                        <InfoStyled>
                            {props.personalPickupStoreDetail.street +
                                ', ' +
                                props.personalPickupStoreDetail.postcode +
                                ', ' +
                                props.personalPickupStoreDetail.city}
                        </InfoStyled>
                        <InfoStyled>{t('Open') + ': ' + props.personalPickupStoreDetail.openingHours}</InfoStyled>
                    </>
                )}
                {props.storeOpeningHours !== undefined && <InfoStyled>{props.storeOpeningHours}</InfoStyled>}
            </NameWrapperStyled>
            {props.daysUntilDelivery !== undefined && props.personalPickup !== undefined && (
                <TransportDaysUntilDeliveryStyled>
                    {getDeliveryMessage(props.daysUntilDelivery, props.personalPickup, t)}
                </TransportDaysUntilDeliveryStyled>
            )}
            {props.price !== undefined && <PriceStyled>{formatPrice(props.price.priceWithVat, 'CZK')}</PriceStyled>}
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
