import {
    ButtonBottomIconStyled,
    ButtonBottomItemStyled,
    ButtonBottomNameStyled,
    ButtonBottomStyled,
    InfoItemOpeningHoursStyled,
    InfoItemStyled,
    InfoItemSubtitleStyled,
    InfoStyled,
    MapStyled,
    StoreDetailContentStyled,
    StoreDetailStyled,
} from './StoreDetailContent.style';
import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { Heading } from 'components/Basic/Heading/Heading';
import { Webline } from 'components/Layout/Webline/Webline';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { StoreDetailType } from 'types/store';

type StoreDetailContentProps = {
    store: StoreDetailType;
};

export const StoreDetailContent: FC<StoreDetailContentProps> = (props) => {
    const testIdentifier = 'pages-storedetail';

    const t = useTypedTranslationFunction();
    const storeCoordinates = [
        { locationLatitude: props.store.locationLatitude, locationLongitude: props.store.locationLongitude },
    ];
    const { url } = useShopsysSelector((state) => state.domain);
    const [contactUrl] = getInternationalizedStaticUrls(['/contact'], url);

    return (
        <Webline data-testid={testIdentifier}>
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
                            {props.store.country.name}
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
                    <MapStyled>
                        <GoogleMap
                            lat={props.store.locationLatitude}
                            lng={props.store.locationLongitude}
                            zoom={15}
                            markers={storeCoordinates}
                            isDetail={true}
                        />
                    </MapStyled>
                    <ButtonBottomStyled>
                        <ButtonBottomItemStyled>
                            <ButtonBottomIconStyled alt="" iconType="icon" icon="Chat" />
                            <NextLink href={contactUrl} passHref>
                                <ButtonBottomNameStyled>{t('Do you have any questions?')}</ButtonBottomNameStyled>
                            </NextLink>
                        </ButtonBottomItemStyled>
                        <ButtonBottomItemStyled>
                            <ButtonBottomNameStyled type="right">{t('Customer Centre')}</ButtonBottomNameStyled>
                            <ButtonBottomIconStyled alt="" iconType="icon" icon="Arrow" type="right" />
                        </ButtonBottomItemStyled>
                    </ButtonBottomStyled>
                </StoreDetailContentStyled>
            </StoreDetailStyled>
        </Webline>
    );
};
