import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { Image } from 'components/Basic/Image/Image';
import { Infobox } from 'components/Basic/Infobox/Infobox';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { StoreContact } from 'components/Blocks/StoreList/StoreContact';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeStoreDetailFragment } from 'graphql/requests/stores/fragments/StoreDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useState } from 'react';

const ModalGallery = dynamic(() =>
    import('components/Basic/ModalGallery/ModalGallery').then((component) => component.ModalGallery),
);

type StoreDetailContentProps = {
    store: TypeStoreDetailFragment;
};

export const StoreDetailContent: FC<StoreDetailContentProps> = ({ store }) => {
    const { t } = useTranslation();

    const [selectedGalleryItemIndex, setSelectedGalleryItemIndex] = useState<number>();

    return (
        <VerticalStack gap="sm">
            <Webline>
                <div className="flex flex-col gap-2.5 lg:flex-row lg:items-center lg:gap-5">
                    <h1>{store.storeName}</h1>

                    <OpeningStatus status={store.openingHours.status} />
                </div>
            </Webline>

            <Webline>
                <div className="flex w-full flex-col gap-5 lg:flex-row">
                    <div className="flex w-full flex-col gap-8 lg:basis-1/2">
                        {!!store.specialMessage && <Infobox message={store.specialMessage} />}

                        {!!store.description && (
                            <StoreSection title={t('Store description')}>
                                <p dangerouslySetInnerHTML={{ __html: store.description }} />
                            </StoreSection>
                        )}

                        <StoreSection title={t('Store address')}>
                            <p>
                                {store.street}
                                <br />
                                {store.city}
                                <br />
                                {store.postcode}
                                <br />
                                {store.country.name}
                            </p>
                        </StoreSection>

                        {!!store.directions && (
                            <StoreSection title={t('How to reach us')}>
                                <p dangerouslySetInnerHTML={{ __html: store.directions }} />
                            </StoreSection>
                        )}

                        {store.phone || store.email ? <StoreContact email={store.email} phone={store.phone} /> : null}

                        <StoreSection title={t('Opening hours')}>
                            <OpeningHours className="mx-auto" openingHours={store.openingHours} />
                        </StoreSection>
                    </div>
                    <div className="w-full lg:basis-1/2">
                        <div className="bg-background-more flex aspect-square w-full rounded-xl p-5">
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
            </Webline>

            {store.storeImages.length > 0 && (
                <Webline>
                    <div className="max-vl:grid-flow-col vl:gap-8 grid snap-x snap-mandatory gap-4 overflow-y-hidden overscroll-x-contain max-lg:overflow-x-auto lg:flex lg:flex-wrap">
                        {store.storeImages.map((image, index) => (
                            <button
                                key={image.url}
                                className="m-0.5 flex h-[190px] w-[280px] cursor-pointer snap-start justify-center overflow-hidden rounded-xl border-0 bg-transparent p-0"
                                data-src={image.url}
                                tabIndex={0}
                                title={t('View store image')}
                                type="button"
                                onClick={() => setSelectedGalleryItemIndex(index)}
                            >
                                <Image
                                    alt={image.name || `${t('Store image of')} ${store.storeName} - ${index + 1}`}
                                    className="object-cover"
                                    height={190}
                                    loading="lazy"
                                    src={image.url}
                                    width={280}
                                />
                            </button>
                        ))}
                    </div>
                </Webline>
            )}

            {selectedGalleryItemIndex !== undefined && (
                <ModalGallery
                    galleryName={store.storeName}
                    initialIndex={selectedGalleryItemIndex}
                    items={store.storeImages}
                    onCloseModal={() => setSelectedGalleryItemIndex(undefined)}
                />
            )}
        </VerticalStack>
    );
};

const StoreSection: FC<{ title: string }> = ({ title, children }) => {
    return (
        <div>
            <p className="h5 mb-2">{title}</p>
            {children}
        </div>
    );
};
