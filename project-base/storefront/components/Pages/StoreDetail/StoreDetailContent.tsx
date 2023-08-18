import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Image } from 'components/Basic/Image/Image';
import { SeznamMap } from 'components/Basic/SeznamMap/SeznamMap';
import { Webline } from 'components/Layout/Webline/Webline';
import { StoreDetailFragmentApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { createMapMarker } from 'helpers/createMapMarker';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { twJoin } from 'tailwind-merge';
import dynamic from 'next/dynamic';
import { Chat } from 'components/Basic/Icon/IconsSvg';

const Gallery = dynamic(() => import('components/Basic/Gallery/Gallery').then((component) => component.Gallery));

type StoreDetailContentProps = {
    store: StoreDetailFragmentApi;
};

const TEST_IDENTIFIER = 'pages-storedetail';

export const StoreDetailContent: FC<StoreDetailContentProps> = ({ store }) => {
    const { t } = useTranslation();
    const storeCoordinates = createMapMarker(store.locationLatitude, store.locationLongitude);
    const { url } = useDomainConfig();
    const [contactUrl] = getInternationalizedStaticUrls(['/contact'], url);

    return (
        <Webline dataTestId={TEST_IDENTIFIER} className="mb-10">
            <div className="flex flex-col vl:flex-row vl:gap-5">
                <div className="text-center vl:order-2 vl:flex-1">
                    <Heading type="h1">{store.storeName}</Heading>

                    <OpeningStatus isOpen={store.openingHours.isOpen} />

                    <div className="mt-6 flex flex-col gap-4 md:flex-row">
                        <div className="flex-1">
                            {!!store.description && (
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
                        </div>

                        <InfoItem className="flex-1">
                            <StoreHeading text={t('Opening hours')} />
                            <OpeningHours openingHours={store.openingHours} />
                        </InfoItem>
                    </div>

                    <div>
                        {!!store.contactInfo && (
                            <InfoItem>
                                <StoreHeading text={t('Contact to the department store')} />
                                {store.contactInfo}
                            </InfoItem>
                        )}

                        {!!store.specialMessage && (
                            <InfoItem>
                                <StoreHeading text={t('Special announcement')} />
                                {store.specialMessage}
                            </InfoItem>
                        )}
                    </div>

                    <div className="mt-6 flex items-center justify-between rounded border border-greyLighter py-4 pr-4 pl-6 transition hover:no-underline vl:hover:-translate-x-1 vl:hover:shadow-lg">
                        <div className="flex flex-row items-center text-lg text-primary">
                            <Icon icon={<Chat />} className="mr-3 w-6 text-2xl text-orange xl:mr-5" />
                            <ExtendedNextLink
                                href={contactUrl}
                                passHref
                                type="static"
                                className="relative flex-grow text-primary md:text-lg"
                            >
                                {t('Do you have any questions?')}
                            </ExtendedNextLink>
                        </div>

                        <div className="flex flex-row items-center text-lg text-primary">
                            <a className="relative flex-grow text-primary md:text-lg">{t('Customer Centre')}</a>
                        </div>
                    </div>
                </div>

                <div className="mt-6 w-full basis-96 vl:mt-0 vl:basis-1/2">
                    <SeznamMap
                        center={storeCoordinates}
                        zoom={15}
                        markers={storeCoordinates ? [storeCoordinates] : []}
                    />
                </div>
            </div>

            {store.storeImages.length > 0 && (
                <div className="mt-6 bg-greyVeryLight p-3">
                    <Gallery selector=".lightboxItem">
                        <div className="flex flex-wrap justify-center lg:justify-start">
                            {store.storeImages.map((image, index) => (
                                <div
                                    key={index}
                                    title={store.storeName}
                                    className="lightboxItem basis-[304px] p-3"
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
        </Webline>
    );
};

const StoreHeading: FC<{ text: string }> = ({ text }) => (
    <Heading type="h3" className="mb-1 block font-normal text-primary">
        {text}
    </Heading>
);

const InfoItem: FC = ({ children, className }) => <div className={twJoin('mb-4 md:mb-6', className)}>{children}</div>;
