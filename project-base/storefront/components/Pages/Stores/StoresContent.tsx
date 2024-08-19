import { StoreInfoBox } from './StoreInfoBox';
import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { Image } from 'components/Basic/Image/Image';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useMemo, useState } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type StoresContentProps = {
    stores: TypeListedStoreConnectionFragment;
};

export const StoresContent: FC<StoresContentProps> = ({ stores }) => {
    const { t } = useTranslation();
    const { defaultLocale } = useDomainConfig();
    const [activeStoreIdentifier, setActiveStoreIdentifier] = useState<string>();
    const mappedStores = useMemo(() => mapConnectionEdges<StoreOrPacketeryPoint>(stores.edges), [stores.edges]);

    const activeMarkerHandler = (id: string) => setActiveStoreIdentifier(activeStoreIdentifier !== id ? id : undefined);

    const selectedStore = mappedStores?.find((store) => store.identifier === activeStoreIdentifier);

    return (
        <SimpleLayout standardWidth heading={t('Stores')}>
            {mappedStores && (
                <>
                    <div className="mb-8 flex w-full flex-col vl:h-[500px] vl:flex-row">
                        <div
                            className="flex h-[250px] w-full md:h-[350px] vl:h-auto vl:w-[calc(100%-420px)]"
                            tid={TIDs.stores_map}
                        >
                            <GoogleMap activeMarkerHandler={activeMarkerHandler} markers={mappedStores} />
                        </div>
                        <div className="relative flex flex-col items-center justify-center overflow-hidden border-2 border-borderAccent p-8 max-vl:border-t-0 vl:h-full vl:w-[420px] vl:border-l-0">
                            <div className="relative">
                                <span className="absolute right-[10%] bottom-3 z-above inline-flex h-10 w-10 flex-col items-center justify-center rounded-full bg-backgroundAccent text-xl font-medium text-textInverted sm:h-14 sm:w-14 sm:text-2xl">
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
                                    closeInfoBoxCallback={() => setActiveStoreIdentifier(undefined)}
                                    store={selectedStore}
                                />
                            )}
                        </div>
                    </div>

                    {mappedStores.length && (
                        <SimpleNavigation
                            isWithoutSlider
                            className="lg:grid-cols-2"
                            linkTypeOverride="store"
                            listedItems={mappedStores.map((store) => ({
                                name: store.name,
                                slug: store.slug,
                                icon: <MarkerIcon className="mr-3 w-6 text-2xl xl:mr-5" />,
                            }))}
                        />
                    )}
                </>
            )}
        </SimpleLayout>
    );
};
