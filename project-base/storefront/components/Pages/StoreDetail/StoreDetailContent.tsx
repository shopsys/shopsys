import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ChatIcon } from 'components/Basic/Icon/IconsSvg';
import { Image } from 'components/Basic/Image/Image';
import { SeznamMap } from 'components/Basic/SeznamMap/SeznamMap';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { Webline } from 'components/Layout/Webline/Webline';
import { StoreDetailFragmentApi } from 'graphql/generated';
import { createMapMarker } from 'helpers/createMapMarker';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';

const ModalGallery = dynamic(() =>
    import('components/Basic/ModalGallery/ModalGallery').then((component) => component.ModalGallery),
);

type StoreDetailContentProps = {
    store: StoreDetailFragmentApi;
};

const TEST_IDENTIFIER = 'pages-storedetail';

export const StoreDetailContent: FC<StoreDetailContentProps> = ({ store }) => {
    const { t } = useTranslation();
    const storeCoordinates = createMapMarker(store.locationLatitude, store.locationLongitude);
    const { url } = useDomainConfig();
    const [contactUrl] = getInternationalizedStaticUrls(['/contact'], url);

    const [selectedGalleryItemIndex, setSelectedGalleryItemIndex] = useState<number>();

    return (
        <Webline className="mb-10" dataTestId={TEST_IDENTIFIER}>
            <div className="flex flex-col vl:flex-row vl:gap-5">
                <div className="text-center vl:order-2 vl:flex-1">
                    <h1 className="mb-3">{store.storeName}</h1>

                    <OpeningStatus isOpen={store.openingHours.isOpen} />

                    <div className="mt-6 flex flex-col gap-4 md:flex-row">
                        <div className="flex-1">
                            {!!store.description && (
                                <InfoItem>
                                    <StoreHeading text={t('Store description')} />
                                    <div dangerouslySetInnerHTML={{ __html: store.description }} />
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
                            <OpeningHours className="mx-auto w-80" openingHours={store.openingHours} />
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
                            <ChatIcon className="mr-3 w-6 text-2xl text-orange xl:mr-5" />
                            <ExtendedNextLink
                                passHref
                                className="relative flex-grow text-primary md:text-lg"
                                href={contactUrl}
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
                        markers={storeCoordinates ? [storeCoordinates] : []}
                        zoom={15}
                    />
                </div>
            </div>

            {store.storeImages.length > 0 && (
                <div className="mt-6 bg-greyVeryLight p-3">
                    <ul className="flex flex-wrap justify-center lg:justify-start">
                        {store.storeImages.map((image, index) => (
                            <li
                                key={image.url}
                                className="lightboxItem basis-[304px] p-3"
                                data-src={image.url}
                                title={store.storeName}
                                onClick={() => setSelectedGalleryItemIndex(index)}
                            >
                                <Image
                                    alt={image.name || `${store.storeName}-${index}`}
                                    className="w-auto cursor-pointer"
                                    height={190}
                                    loading="lazy"
                                    src={image.url}
                                    width={280}
                                />
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            {selectedGalleryItemIndex !== undefined && (
                <ModalGallery
                    galleryName={store.storeName}
                    initialIndex={selectedGalleryItemIndex}
                    items={store.storeImages}
                    onCloseModal={() => setSelectedGalleryItemIndex(undefined)}
                />
            )}
        </Webline>
    );
};

const StoreHeading: FC<{ text: string }> = ({ text }) => (
    <div className="h3 mb-1 block font-normal text-primary">{text}</div>
);

const InfoItem: FC = ({ children, className }) => <div className={twJoin('mb-4 md:mb-6', className)}>{children}</div>;
