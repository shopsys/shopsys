import {
    ButtonBottomItemStyled,
    ButtonBottomNameStyled,
    ButtonBottomStyled,
    InfoItemOpeningHoursStyled,
    InfoItemStyled,
    InfoStyled,
    MapStyled,
    StoreDetailContentStyled,
    StoreDetailStyled,
} from './StoreDetailContent.style';
import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
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
                    <Heading type="h1">{store.storeName}</Heading>
                    <InfoStyled>
                        {store.description !== null && (
                            <InfoItemStyled>
                                <StoreHeading text={t('Store description')} />
                                {store.description}
                            </InfoItemStyled>
                        )}
                        <InfoItemStyled>
                            <StoreHeading text={t('Store address')} />
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
                                <StoreHeading text={t('Opening hours')} />
                                <InfoItemOpeningHoursStyled>{store.openingHours}</InfoItemOpeningHoursStyled>
                            </InfoItemStyled>
                        )}
                        {store.contactInfo !== null && (
                            <InfoItemStyled>
                                <StoreHeading text={t('Contact to the department store')} />
                                {store.contactInfo}
                            </InfoItemStyled>
                        )}
                        {store.specialMessage !== null && (
                            <InfoItemStyled>
                                <StoreHeading text={t('Special announcement')} />
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
                            <Icon
                                iconType="icon"
                                icon="Chat"
                                width={24}
                                height={24}
                                className="mr-3 text-2xl text-orange xl:mr-5"
                            />
                            <NextLink href={contactUrl} passHref>
                                <ButtonBottomNameStyled>{t('Do you have any questions?')}</ButtonBottomNameStyled>
                            </NextLink>
                        </ButtonBottomItemStyled>
                        <ButtonBottomItemStyled>
                            <ButtonBottomNameStyled type="right">{t('Customer Centre')}</ButtonBottomNameStyled>
                            <Icon
                                iconType="icon"
                                icon="Arrow"
                                width={24}
                                height={24}
                                className="ml-3 text-2xl text-primary xl:ml-5"
                            />
                        </ButtonBottomItemStyled>
                    </ButtonBottomStyled>
                </StoreDetailContentStyled>
            </StoreDetailStyled>
        </Webline>
    );
};

const StoreHeading: FC<{ text: string }> = ({ text }) => (
    <Heading type="h3" className="mb-1 block font-normal text-primary">
        {text}
    </Heading>
);
