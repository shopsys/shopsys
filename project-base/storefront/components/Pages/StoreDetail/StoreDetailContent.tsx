import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { ChatIcon } from 'components/Basic/Icon/ChatIcon';
import { Image } from 'components/Basic/Image/Image';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeStoreDetailFragment } from 'graphql/requests/stores/fragments/StoreDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const ModalGallery = dynamic(() =>
    import('components/Basic/ModalGallery/ModalGallery').then((component) => component.ModalGallery),
);

type StoreDetailContentProps = {
    store: TypeStoreDetailFragment;
};

export const StoreDetailContent: FC<StoreDetailContentProps> = ({ store }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [contactUrl] = getInternationalizedStaticUrls(['/contact'], url);

    const [selectedGalleryItemIndex, setSelectedGalleryItemIndex] = useState<number>();

    return (
        <Webline className="mb-10">
            <div className="flex flex-col w-full lg:flex-row lg:gap-5">
                <div className="w-full lg:basis-1/2">
                    <div className="mb-5 lg:flex lg:items-center">
                        <h1>{store.storeName}</h1>

                        <div className="lg:mb-5 lg:ml-5">
                            <OpeningStatus isOpen={store.openingHours.isOpen} />
                        </div>
                    </div>

                    {!!store.description && (
                        <InfoItem>
                            <StoreHeading text={t('Store description')} />
                            <div dangerouslySetInnerHTML={{ __html: store.description }} />
                        </InfoItem>
                    )}
                    <InfoItem>
                        <StoreHeading text={t('Store address')} />
                        <p>
                            {store.street}
                            <br />
                            {store.city}
                            <br />
                            {store.postcode}
                            <br />
                            {store.country.name}
                        </p>
                    </InfoItem>

                    <InfoItem className="flex-1">
                        <StoreHeading text={t('Opening hours')} />
                        <OpeningHours className="mx-auto" openingHours={store.openingHours} />
                    </InfoItem>

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

                    <LinkButton href={contactUrl} size="medium" type="contact" variant="inverted">
                        <ChatIcon className="mr-3 w-6 text-2xl xl:mr-5" />
                        {t('Do you have any questions?')}
                    </LinkButton>
                </div>
                <div className="w-full lg:basis-1/2">
                    <div className="flex aspect-square w-full mt-5 p-5 bg-backgroundMore rounded-xl lg:mt-0">
                        <GoogleMap
                            isDetail
                            defaultZoom={15}
                            latitude={store.latitude}
                            longitude={store.longitude}
                            markers={[
                                {
                                    identifier: store.uuid,
                                    latitude: store.latitude,
                                    longitude: store.longitude,
                                },
                            ]}
                        />
                    </div>
                </div>
            </div>

            {store.storeImages.length > 0 && (
                <div className="mt-10 gap-4 grid snap-x snap-mandatory vl:gap-8 overflow-y-hidden overscroll-x-contain max-vl:grid-flow-col max-lg:overflow-x-auto lg:flex lg:flex-wrap">
                    {store.storeImages.map((image, index) => (
                        <div
                            key={image.url}
                            className="lightboxItem snap-start m-0.5 w-[280px] h-[190px] overflow-hidden rounded-xl flex justify-center"
                            data-src={image.url}
                            title={store.storeName}
                            onClick={() => setSelectedGalleryItemIndex(index)}
                        >
                            <Image
                                alt={image.name || `${store.storeName}-${index}`}
                                className="cursor-pointer object-cover"
                                height={190}
                                loading="lazy"
                                src={image.url}
                                width={280}
                            />
                        </div>
                    ))}
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

const StoreHeading: FC<{ text: string }> = ({ text }) => <div className="h5 mb-2">{text}</div>;

const InfoItem: FC = ({ children, className }) => <div className={twJoin('mb-7', className)}>{children}</div>;
