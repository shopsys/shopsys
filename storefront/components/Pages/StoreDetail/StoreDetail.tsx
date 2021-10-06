import {
    ButtonBottomIconStyled,
    ButtonBottomItemStyled,
    ButtonBottomNameStyled,
    ButtonBottomStyled,
    InfoItemOpeningHoursStyled,
    InfoItemStyled,
    InfoItemSubtitleStyled,
    InfoStyled,
    StoreDetailContentStyled,
    StoreDetailStyled,
} from './StoreDetail.style';
import { FC } from 'react';
import GoogleMap from './GoogleMap';
import Heading from 'components/Basic/Heading';
import { StoreDetailType } from 'connectors/stores/types';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type StoreDetailProps = {
    store: StoreDetailType;
};

const StoreDetail: FC<StoreDetailProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <Webline>
            <StoreDetailStyled>
                <StoreDetailContentStyled>
                    <Heading type={'h1'}>{props.store.storeName}</Heading>
                    <InfoStyled>
                        {props.store.description !== null && (
                            <InfoItemStyled>
                                <InfoItemSubtitleStyled type="h3">{t('Store description')}</InfoItemSubtitleStyled>
                                {props.store.description}
                            </InfoItemStyled>
                        )}
                        <InfoItemStyled>
                            <InfoItemSubtitleStyled type="h3">{t('Store address')}</InfoItemSubtitleStyled>
                            {props.store.city}
                            <br />
                            {props.store.street}
                            <br />
                            {props.store.postcode}
                            <br />
                            {props.store.country}
                        </InfoItemStyled>
                        {props.store.openingHours !== null && (
                            <InfoItemStyled>
                                <InfoItemSubtitleStyled type="h3">{t('Opening hours')}</InfoItemSubtitleStyled>
                                <InfoItemOpeningHoursStyled>{props.store.openingHours}</InfoItemOpeningHoursStyled>
                            </InfoItemStyled>
                        )}
                        {props.store.contactInfo !== null && (
                            <InfoItemStyled>
                                <InfoItemSubtitleStyled type="h3">
                                    {t('Contact to the department store')}
                                </InfoItemSubtitleStyled>
                                {props.store.contactInfo}
                            </InfoItemStyled>
                        )}
                        {props.store.specialMessage !== null && (
                            <InfoItemStyled>
                                <InfoItemSubtitleStyled type="h3">{t('Special announcement')}</InfoItemSubtitleStyled>
                                {props.store.specialMessage}
                            </InfoItemStyled>
                        )}
                    </InfoStyled>
                    {props.store.locationLatitude !== null && props.store.locationLongitude !== null && (
                        <GoogleMap lat={props.store.locationLatitude} lng={props.store.locationLongitude} zoom={15} />
                    )}
                    <ButtonBottomStyled>
                        <ButtonBottomItemStyled>
                            <ButtonBottomIconStyled icon="Chat" />
                            <ButtonBottomNameStyled>{t('Do you have any questions?')}</ButtonBottomNameStyled>
                        </ButtonBottomItemStyled>
                        <ButtonBottomItemStyled>
                            <ButtonBottomNameStyled type="right">{t('Customer Centre')}</ButtonBottomNameStyled>
                            <ButtonBottomIconStyled icon="Arrow" type="right" />
                        </ButtonBottomItemStyled>
                    </ButtonBottomStyled>
                </StoreDetailContentStyled>
            </StoreDetailStyled>
        </Webline>
    );
};

export default StoreDetail;
