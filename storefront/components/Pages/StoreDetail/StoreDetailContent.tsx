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
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { StoreDetailType } from 'types/store';

type StoreDetailContentProps = {
    store: StoreDetailType;
};

const TEST_IDENTIFIER = 'pages-storedetail';

export const StoreDetailContent: FC<StoreDetailContentProps> = ({ store }) => {
    const t = useTypedTranslationFunction();
    const storeCoordinates = [{ locationLatitude: store.locationLatitude, locationLongitude: store.locationLongitude }];
    const { url } = useShopsysSelector((state) => state.domain);
    const [contactUrl] = getInternationalizedStaticUrls(['/contact'], url);

    return (
        <Webline testIdentifier={TEST_IDENTIFIER}>
            <StoreDetailStyled>
                <StoreDetailContentStyled>
                    <Heading type={'h1'}>{store.storeName}</Heading>
                    <InfoStyled>
                        {store.description !== null && (
                            <InfoItemStyled>
                                <InfoItemSubtitleStyled type="h3">{t('Store description')}</InfoItemSubtitleStyled>
                                {store.description}
                            </InfoItemStyled>
                        )}
                        <InfoItemStyled>
                            <InfoItemSubtitleStyled type="h3">{t('Store address')}</InfoItemSubtitleStyled>
                            {store.city}
                            <br />
                            {store.street}
                            <br />
                            {store.postcode}
                            <br />
                            {store.country.name}
                        </InfoItemStyled>
                        {store.openingHours !== null && (
                            <InfoItemStyled>
                                <InfoItemSubtitleStyled type="h3">{t('Opening hours')}</InfoItemSubtitleStyled>
                                <InfoItemOpeningHoursStyled>{store.openingHours}</InfoItemOpeningHoursStyled>
                            </InfoItemStyled>
                        )}
                        {store.contactInfo !== null && (
                            <InfoItemStyled>
                                <InfoItemSubtitleStyled type="h3">
                                    {t('Contact to the department store')}
                                </InfoItemSubtitleStyled>
                                {store.contactInfo}
                            </InfoItemStyled>
                        )}
                        {store.specialMessage !== null && (
                            <InfoItemStyled>
                                <InfoItemSubtitleStyled type="h3">{t('Special announcement')}</InfoItemSubtitleStyled>
                                {store.specialMessage}
                            </InfoItemStyled>
                        )}
                    </InfoStyled>
                    <MapStyled>
                        <GoogleMap
                            lat={store.locationLatitude}
                            lng={store.locationLongitude}
                            zoom={15}
                            markers={storeCoordinates}
                            isDetail
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
