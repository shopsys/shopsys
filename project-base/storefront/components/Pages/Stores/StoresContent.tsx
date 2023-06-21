import { InfoBox } from './InfoBox';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { SeznamMap } from 'components/Basic/SeznamMap/SeznamMap';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { BreadcrumbFragmentApi, ListedStoreConnectionFragmentApi, ListedStoreFragmentApi } from 'graphql/generated';
import { createMapMarker } from 'helpers/map/createMapMarker';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import Image from 'next/image';
import NextLink from 'next/link';
import { useCallback, useMemo, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { MapMarker } from 'types/map';

type StoresContentProps = {
    stores: ListedStoreConnectionFragmentApi;
    breadcrumbs: BreadcrumbFragmentApi[];
};

export const StoresContent: FC<StoresContentProps> = ({ stores, breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const { defaultLocale } = useDomainConfig();
    const [activeInfoBox, setActiveInfoBox] = useState(-1);
    const mappedStores = useMemo(() => mapConnectionEdges<ListedStoreFragmentApi>(stores.edges), [stores.edges]);

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

    const [activeMarkerId, setActiveMarkerId] = useState<string>();

    const activeMarkerHandler = useCallback(
        (markerId: string) => {
            const index = mappedStores!.findIndex((store) => store.slug === markerId);

            setActiveInfoBox((prev) => (prev !== index ? index : -1));
            setActiveMarkerId((prev) => (prev !== markerId ? markerId : undefined));
        },
        [mappedStores],
    );

    const closeInfoBoxHandler = () => {
        setActiveInfoBox(-1);
        setActiveMarkerId(undefined);
    };

    return (
        <SimpleLayout standardWidth heading={t('Stores')} breadcrumb={breadcrumbs}>
            {mappedStores !== undefined && (
                <>
                    <div className="mb-8 flex w-full flex-col vl:h-[500px] vl:flex-row">
                        <div className="h-[250px] w-full md:h-[350px] vl:h-auto vl:w-[calc(100%-420px)]">
                            <SeznamMap
                                markers={markers}
                                activeMarkerHandler={activeMarkerHandler}
                                activeMarkerId={activeMarkerId}
                            />
                        </div>
                        <div className="relative flex flex-col items-center justify-center overflow-hidden rounded-b-xl border-2 border-greyLighter p-8 max-lg:border-t-0 vl:h-full vl:w-[420px] vl:border-l-0">
                            <div className="relative">
                                <span className="absolute right-[10%] bottom-3 z-above inline-flex h-10 w-10 flex-col items-center justify-center rounded-full bg-[linear-gradient(180deg,#ffcf09,#ffb235)] text-xl font-medium text-white sm:h-14 sm:w-14 sm:text-2xl">
                                    {mappedStores.length}x
                                </span>
                                <picture>
                                    <source
                                        srcSet={`/images/stores_${defaultLocale}2x.png 2x, /images/stores_${defaultLocale}.png 1x`}
                                    />
                                    <Image
                                        src={`/images/stores_${defaultLocale}.png`}
                                        alt={t('Stores')}
                                        width={210}
                                        height={160}
                                    />
                                </picture>
                            </div>
                            <Heading type="h3" className="m-0 lg:mt-6">
                                {t('Stores')}
                            </Heading>
                            {activeInfoBox !== -1 && (
                                <InfoBox
                                    closeInfoBoxCallback={closeInfoBoxHandler}
                                    store={mappedStores[activeInfoBox]}
                                />
                            )}
                        </div>
                    </div>
                    <div className="mb-10 lg:grid lg:grid-cols-2 lg:gap-8">
                        {mappedStores.length &&
                            mappedStores.map((store) => (
                                <NextLink key={store.slug} href={store.slug} passHref>
                                    <a className="mb-4 flex w-full items-center justify-between rounded-xl border border-greyLighter py-4 pr-4 pl-6 transition hover:no-underline lg:w-auto vl:hover:-translate-x-1 vl:hover:shadow-lg">
                                        <div className="flex flex-row items-center text-lg text-primary">
                                            <Icon
                                                iconType="icon"
                                                icon="Marker"
                                                className="mr-3 w-6 text-2xl text-orange xl:mr-5"
                                            />
                                            <ButtonBottomName>{store.name}</ButtonBottomName>
                                        </div>
                                        <div className="flex flex-row items-center text-lg text-primary">
                                            <ButtonBottomName isRight>{t('Store detail')}</ButtonBottomName>
                                            <Icon
                                                iconType="icon"
                                                icon="Arrow"
                                                className="ml-3 w-6 text-2xl text-primary xl:ml-5"
                                            />
                                        </div>
                                    </a>
                                </NextLink>
                            ))}
                    </div>
                </>
            )}
        </SimpleLayout>
    );
};

type ButtonBottomNameProps = {
    isRight?: boolean;
};

const ButtonBottomName: FC<ButtonBottomNameProps> = ({ children, isRight }) => (
    <div className={twJoin('relative flex-grow text-primary md:text-lg', isRight && 'ml-5 hidden vl:block')}>
        {children}
    </div>
);
