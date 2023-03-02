import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Webline } from 'components/Layout/Webline/Webline';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
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
            <div className="mb-10">
                <div className="relative lg:min-h-[350px] lg:pl-[380px] vl:min-h-[500px] vl:pl-[530px] xl:min-h-[650px] xl:pl-[720px]">
                    <Heading type="h1">{store.storeName}</Heading>
                    <div className="md:flex md:flex-wrap">
                        {store.description !== null && (
                            <InfoItem>
                                <StoreHeading text={t('Store description')} />
                                {store.description}
                            </InfoItem>
                        )}
                        <InfoItem>
                            <StoreHeading text={t('Store address')} />
                            {store.city}
                            <br />
                            {store.street}
                            <br />
                            {store.postcode}
                            <br />
                            {store.country.name}
                        </InfoItem>
                        {store.openingHours !== null && (
                            <InfoItem>
                                <StoreHeading text={t('Opening hours')} />
                                <div className="max-w-[160px]">{store.openingHours}</div>
                            </InfoItem>
                        )}
                        {store.contactInfo !== null && (
                            <InfoItem>
                                <StoreHeading text={t('Contact to the department store')} />
                                {store.contactInfo}
                            </InfoItem>
                        )}
                        {store.specialMessage !== null && (
                            <InfoItem>
                                <StoreHeading text={t('Special announcement')} />
                                {store.specialMessage}
                            </InfoItem>
                        )}
                    </div>
                    <div className="mb-4 h-60 w-full lg:absolute lg:left-0 lg:top-0 lg:mb-0 lg:h-[350px] lg:w-[350px] vl:h-[500px] vl:w-[500px] xl:h-[650px] xl:w-[650px] ">
                        <GoogleMap
                            lat={store.locationLatitude}
                            lng={store.locationLongitude}
                            zoom={15}
                            markers={storeCoordinates}
                            isDetail
                        />
                    </div>
                    <a className="flex items-center justify-between rounded-xl border border-greyLighter py-4 pr-4 pl-6 transition hover:no-underline vl:hover:-translate-x-1 vl:hover:shadow-lg">
                        <div className="flex flex-row items-center text-lg text-primary">
                            <Icon
                                iconType="icon"
                                icon="Chat"
                                width={24}
                                height={24}
                                className="mr-3 text-2xl text-orange xl:mr-5"
                            />
                            <NextLink href={contactUrl} passHref>
                                <a className="relative flex-grow text-primary md:text-lg">
                                    {t('Do you have any questions?')}
                                </a>
                            </NextLink>
                        </div>
                        <div className="flex flex-row items-center text-lg text-primary">
                            <a className="relative flex-grow text-primary md:text-lg">{t('Customer Centre')}</a>
                            <Icon
                                iconType="icon"
                                icon="Arrow"
                                width={24}
                                height={24}
                                className="ml-3 text-2xl text-primary xl:ml-5"
                            />
                        </div>
                    </a>
                </div>
            </div>
        </Webline>
    );
};

const StoreHeading: FC<{ text: string }> = ({ text }) => (
    <Heading type="h3" className="mb-1 block font-normal text-primary">
        {text}
    </Heading>
);

const InfoItem: FC = ({ children }) => <div className="mb-4 odd:pr-3 even:pl-3 md:mb-6 md:w-1/2">{children}</div>;
