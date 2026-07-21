import { Image } from 'components/Basic/Image/Image';
import { Infobox } from 'components/Basic/Infobox/Infobox';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { StoreContact } from 'components/Blocks/StoreList/StoreContact';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeStoreDetailFragment } from 'graphql/requests/stores/fragments/StoreDetailFragment.generated';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type StoreDetailContentProps = {
    store: TypeStoreDetailFragment;
};

const GoogleMapPlaceholder: FC = () => (
    <div aria-hidden="true" className="h-full w-full rounded-lg bg-background-default" />
);

const GoogleMap = dynamic(
    () => import('components/Basic/GoogleMap/GoogleMap').then((component) => component.GoogleMap),
    {
        ssr: false,
        loading: () => <GoogleMapPlaceholder />,
    },
);

const ModalGallery = dynamic(
    () => import('components/Basic/ModalGallery/ModalGallery').then((component) => component.ModalGallery),
    { ssr: false },
);

export const StoreDetailContent: FC<StoreDetailContentProps> = ({ store }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);

    const openGallery = (initialIndex: number) => {
        storeCurrentFocus();

        updatePortalContent(
            <ModalGallery
                galleryName={store.storeName}
                initialIndex={initialIndex}
                items={store.storeImages}
                onCloseModal={closePortalContent}
            />,
        );
    };

    return (
        <VerticalStack gap="sm">
            <Webline>
                <div className="flex flex-col gap-2.5 lg:flex-row lg:items-center lg:gap-5">
                    <h1>{store.storeName}</h1>

                    <div data-tid={TIDs.store_opening_status}>
                        <OpeningStatus status={store.openingHours.status} />
                    </div>
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
                        <div
                            className="flex aspect-square w-full rounded-xl bg-background-more p-5"
                            data-tid={TIDs.stores_map}
                        >
                            <GoogleMap
                                isDetail
                                defaultZoom={15}
                                latitude={store.latitude}
                                longitude={store.longitude}
                                markers={[
                                    {
                                        identifier: store.uuid,
                                        name: store.city,
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
                    <div
                        className="grid snap-x snap-mandatory gap-4 vl:gap-8 overflow-y-hidden overscroll-x-contain max-lg:overflow-x-auto max-vl:grid-flow-col lg:flex lg:flex-wrap"
                        data-tid={TIDs.store_gallery_images}
                    >
                        {store.storeImages.map((image, index) => (
                            <button
                                key={image.url}
                                aria-label={t('Open image gallery of store {{ storeName }}, image {{ imageNumber }}', {
                                    ns: 'accessibility',
                                    storeName: store.storeName,
                                    imageNumber: index + 1,
                                })}
                                className="m-0.5 flex h-48 w-70 cursor-pointer snap-start justify-center overflow-hidden rounded-xl border-0 bg-transparent p-0"
                                data-src={image.url}
                                tabIndex={0}
                                title={t('View store image')}
                                type="button"
                                onClick={() => openGallery(index)}
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
