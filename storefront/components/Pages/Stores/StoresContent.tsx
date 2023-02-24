import { InfoBox } from './InfoBox';
import { GoogleMap } from 'components/Basic/GoogleMap/GoogleMap';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Image from 'next/image';
import NextLink from 'next/link';
import { FC, useCallback, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { twJoin } from 'tailwind-merge';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { ListedStoreType } from 'types/store';

type StoresContentProps = {
    stores: ListedStoreType[];
    breadcrumbs: BreadcrumbItemType[];
};

export const StoresContent: FC<StoresContentProps> = ({ stores, breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const { defaultLocale } = useShopsysSelector((state) => state.domain);
    const [activeInfoBox, setActiveInfoBox] = useState(-1);
    const [closeInfoBox, setCloseInfoBox] = useState(true);

    const activeMarkerHandler = useCallback((index: number) => {
        setActiveInfoBox((prev) => (prev !== index ? index : -1));
        if (index === -1) {
            setCloseInfoBox(false);
        }
    }, []);

    const closeInfoBoxHandler = () => {
        setCloseInfoBox(true);
    };

    return (
        <SimpleLayout standardWidth heading={t('Stores')} breadcrumb={breadcrumbs}>
            <div className="mb-8 flex w-full flex-col vl:h-[500px] vl:flex-row">
                <div className="h-[250px] w-full md:h-[350px] vl:h-auto vl:w-[calc(100%-420px)]">
                    <GoogleMap markers={stores} activeMarkerHandler={activeMarkerHandler} closeMarkers={closeInfoBox} />
                </div>
                <div className="relative flex flex-col items-center justify-center overflow-hidden rounded-b-xl border-2 border-greyLighter p-8 max-lg:border-t-0 vl:h-full vl:w-[420px] vl:border-l-0">
                    <div className="relative">
                        <span className="bg-[linear-gradient(180deg, #ffcf09 0%, #ffb235 100%)] absolute right-[10%] bottom-3 z-above inline-flex h-10 w-10 flex-col items-center justify-center rounded-full text-xl font-medium text-white sm:h-14 sm:w-14 sm:text-2xl ">
                            {stores.length}x
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
                        <InfoBox closeInfoBoxCallback={closeInfoBoxHandler} store={stores[activeInfoBox]} />
                    )}
                </div>
            </div>
            <div className="mb-10 lg:grid lg:grid-cols-2 lg:gap-8">
                {stores.length !== 0 &&
                    stores.map((store) => (
                        <NextLink key={store.slug} href={store.slug} passHref>
                            <a className="mb-4 flex w-full items-center justify-between rounded-xl border border-greyLighter py-4 pr-4 pl-6 transition hover:no-underline lg:w-auto vl:hover:-translate-x-1 vl:hover:shadow-lg">
                                <div className="flex flex-row items-center text-lg text-primary">
                                    <Icon
                                        iconType="icon"
                                        icon="Marker"
                                        width={24}
                                        height={24}
                                        className="mr-3 text-2xl text-orange xl:mr-5"
                                    />
                                    <ButtonBottomName>{store.name}</ButtonBottomName>
                                </div>
                                <div className="flex flex-row items-center text-lg text-primary">
                                    <ButtonBottomName isRight>{t('Store detail')}</ButtonBottomName>
                                    <Icon
                                        iconType="icon"
                                        icon="Arrow"
                                        width={24}
                                        height={24}
                                        className="ml-3 text-2xl text-primary xl:ml-5"
                                    />
                                </div>
                            </a>
                        </NextLink>
                    ))}
            </div>
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
