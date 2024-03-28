import { StoreInfoBox } from './StoreInfoBox';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { Image } from 'components/Basic/Image/Image';
import { SeznamMap } from 'components/Basic/SeznamMap/SeznamMap';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useCallback, useMemo, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { MapMarker } from 'types/map';
import { createMapMarker } from 'utils/createMapMarker';
import { mapConnectionEdges } from 'utils/mappers/connection';

type StoresContentProps = {
    stores: TypeListedStoreConnectionFragment;
};

export const StoresContent: FC<StoresContentProps> = ({ stores }) => {
    const { t } = useTranslation();
    const { defaultLocale } = useDomainConfig();
    const [activeStoreIndex, setActiveStoreIndex] = useState<number>();
    const mappedStores = useMemo(() => mapConnectionEdges<TypeListedStoreFragment>(stores.edges), [stores.edges]);

    const markers = useMemo(() => {
        const validMarkers: Array<MapMarker> = [];
        mappedStores?.forEach(({ locationLatitude, locationLongitude, slug }) => {
            const marker = createMapMarker(locationLatitude, locationLongitude, slug);
            if (marker) {
                validMarkers.push(marker);
            }
        });

        return validMarkers;
    }, [mappedStores]);

    const activeMarkerHandler = useCallback((markerId: string) => {
        const newStoreIndex = mappedStores?.findIndex((store) => store.slug === markerId);

        setActiveStoreIndex(activeStoreIndex !== newStoreIndex ? newStoreIndex : undefined);
    }, []);

    const selectedStore = activeStoreIndex !== undefined ? mappedStores?.[activeStoreIndex] : undefined;

    return (
        <SimpleLayout standardWidth heading={t('Stores')}>
            {mappedStores && (
                <>
                    <div className="mb-8 flex w-full flex-col vl:h-[500px] vl:flex-row">
                        <div className="h-[250px] w-full md:h-[350px] vl:h-auto vl:w-[calc(100%-420px)]">
                            <SeznamMap
                                activeMarkerHandler={activeMarkerHandler}
                                activeMarkerId={selectedStore?.slug}
                                markers={markers}
                            />
                        </div>
                        <div className="relative flex flex-col items-center justify-center overflow-hidden border-2 border-greyLighter p-8 max-vl:border-t-0 vl:h-full vl:w-[420px] vl:border-l-0">
                            <div className="relative">
                                <span className="absolute right-[10%] bottom-3 z-above inline-flex h-10 w-10 flex-col items-center justify-center rounded-full bg-[linear-gradient(180deg,#ffcf09,#ffb235)] text-xl font-medium text-white sm:h-14 sm:w-14 sm:text-2xl">
                                    {mappedStores.length}x
                                </span>

                                <Image
                                    priority
                                    alt={t('Stores')}
                                    height={210}
                                    src={`/images/stores_${defaultLocale}.png`}
                                    width={210}
                                />
                            </div>

                            <div className="h3 lg:mt-6">{t('Stores')}</div>

                            {selectedStore && (
                                <StoreInfoBox
                                    closeInfoBoxCallback={() => setActiveStoreIndex(undefined)}
                                    store={selectedStore}
                                />
                            )}
                        </div>
                    </div>

                    <div className="mb-10 lg:grid lg:grid-cols-2 lg:gap-8">
                        {mappedStores.length &&
                            mappedStores.map((store) => (
                                <ExtendedNextLink
                                    key={store.slug}
                                    className="mb-4 flex w-full items-center justify-between rounded border border-greyLighter py-4 pr-4 pl-6 transition hover:no-underline lg:w-auto vl:hover:-translate-x-1 vl:hover:shadow-lg"
                                    href={store.slug}
                                    type="store"
                                >
                                    <div className="flex flex-row items-center text-lg text-primary">
                                        <MarkerIcon className="mr-3 w-6 text-2xl text-orange xl:mr-5" />
                                        <StoreButton>{store.name}</StoreButton>
                                    </div>

                                    <div className="flex flex-row items-center text-lg text-primary">
                                        <StoreButton isRight>{t('Store detail')}</StoreButton>
                                    </div>
                                </ExtendedNextLink>
                            ))}
                    </div>
                </>
            )}
        </SimpleLayout>
    );
};

const StoreButton: FC<{
    isRight?: boolean;
}> = ({ children, isRight }) => (
    <div className={twJoin('relative flex-grow text-primary md:text-lg', isRight && 'ml-5 hidden vl:block')}>
        {children}
    </div>
);
