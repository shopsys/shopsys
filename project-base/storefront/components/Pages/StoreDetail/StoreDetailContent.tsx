import { Gallery } from 'components/Basic/Gallery/Gallery';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Image } from 'components/Basic/Image/Image';
import { SeznamMap } from 'components/Basic/SeznamMap/SeznamMap';
import { Webline } from 'components/Layout/Webline/Webline';
import { StoreDetailFragmentApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { createMapMarker } from 'helpers/map/createMapMarker';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';

type StoreDetailContentProps = {
    store: StoreDetailFragmentApi;
};

const TEST_IDENTIFIER = 'pages-storedetail';

export const StoreDetailContent: FC<StoreDetailContentProps> = ({ store }) => {
    const t = useTypedTranslationFunction();
    const storeCoordinates = createMapMarker(store.locationLatitude, store.locationLongitude);
    const { url } = useDomainConfig();
    const [contactUrl] = getInternationalizedStaticUrls(['/contact'], url);

    return (
        <Webline dataTestId={TEST_IDENTIFIER}>
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
                        <SeznamMap
                            center={storeCoordinates}
                            zoom={15}
                            markers={storeCoordinates ? [storeCoordinates] : []}
                        />
                    </div>
                    <a className="flex items-center justify-between rounded-xl border border-greyLighter py-4 pr-4 pl-6 transition hover:no-underline vl:hover:-translate-x-1 vl:hover:shadow-lg">
                        <div className="flex flex-row items-center text-lg text-primary">
                            <Icon iconType="icon" icon="Chat" className="mr-3 w-6 text-2xl text-orange xl:mr-5" />
                            <ExtendedNextLink href={contactUrl} passHref type="static">
                                <a className="relative flex-grow text-primary md:text-lg">
                                    {t('Do you have any questions?')}
                                </a>
                            </ExtendedNextLink>
                        </div>
                        <div className="flex flex-row items-center text-lg text-primary">
                            <a className="relative flex-grow text-primary md:text-lg">{t('Customer Centre')}</a>
                            <Icon iconType="icon" icon="Arrow" className="ml-3 w-6 text-2xl text-primary xl:ml-5" />
                        </div>
                    </a>
                </div>
                {store.storeImages.length > 0 && (
                    <div className="mt-10 bg-greyVeryLight p-3">
                        <Gallery selector=".lightboxItem">
                            <div className="flex flex-wrap justify-center lg:justify-start">
                                {store.storeImages.map((image, index) => (
                                    <div
                                        key={index}
                                        title={store.storeName}
                                        className="lightboxItem basis-[304px]  p-3"
                                        data-src={image.sizes.find((size) => size.size === 'default')?.url}
                                    >
                                        <Image
                                            image={image}
                                            alt={image.name || `${store.storeName}-${index}`}
                                            type="thumbnail"
                                            className="cursor-pointer"
                                        />
                                    </div>
                                ))}
                            </div>
                        </Gallery>
                    </div>
                )}
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
