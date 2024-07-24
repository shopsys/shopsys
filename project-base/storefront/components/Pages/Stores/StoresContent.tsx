import { StoreInfoBox } from './StoreInfoBox';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { Image } from 'components/Basic/Image/Image';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useMemo, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { mapConnectionEdges } from 'utils/mappers/connection';

type StoresContentProps = {
    stores: TypeListedStoreConnectionFragment;
};

export const StoresContent: FC<StoresContentProps> = ({ stores }) => {
    const { t } = useTranslation();
    const { defaultLocale } = useDomainConfig();
    const [activeStoreIdentifier, setActiveStoreIdentifier] = useState<string>();
    const mappedStores = useMemo(() => mapConnectionEdges<TypeListedStoreFragment>(stores.edges), [stores.edges]);

    const activeMarkerHandler = (id: string) => setActiveStoreIdentifier(activeStoreIdentifier !== id ? id : undefined);

    const selectedStore = mappedStores?.find((store) => store.identifier === activeStoreIdentifier);

    return (
        <SimpleLayout standardWidth heading={t('Stores')}>
            {mappedStores && (
                <>
                    <div className="mb-8 flex w-full flex-col vl:h-[500px] vl:flex-row">
                        <div className="flex h-[250px] w-full md:h-[350px] vl:h-auto vl:w-[calc(100%-420px)]">
                            <GoogleMap activeMarkerHandler={activeMarkerHandler} markers={mappedStores} />
                        </div>
                        <div className="relative flex flex-col items-center justify-center overflow-hidden border-2 border-graySlate p-8 max-vl:border-t-0 vl:h-full vl:w-[420px] vl:border-l-0">
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
                                    closeInfoBoxCallback={() => setActiveStoreIdentifier(undefined)}
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
                                    className="mb-4 flex w-full items-center justify-between rounded border border-graySlate py-4 pr-4 pl-6 transition hover:no-underline lg:w-auto vl:hover:-translate-x-1 vl:hover:shadow-lg"
                                    href={store.slug}
                                    type="store"
                                >
                                    <div className="flex flex-row items-center text-lg text-primary">
                                        <MarkerIcon className="mr-3 w-6 text-2xl text-primary xl:mr-5" />
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
