import InfoBox from './InfoBox';
import {
    ButtonBottomIconStyled,
    ButtonBottomItemStyled,
    ButtonBottomNameStyled,
    ButtonBottomStyled,
    ImageStyled,
    ImageTextStyled,
    InfoStyled,
    InfoTitleStyled,
    MapStyled,
    StoresList,
    StoresStyled,
} from './Stores.style';
import GoogleMap from 'components/Basic/GoogleMap';
import SimpleLayout from 'components/Layout/SimpleLayout';
import { useStores } from 'connectors/stores/Stores';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Image from 'next/image';
import NextLink from 'next/link';
import { FC, useCallback, useState } from 'react';
import { useShopsysSelector } from 'redux/main';

const Stores: FC = () => {
    const t = useTypedTranslationFunction();
    const stores = useStores();
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
        <SimpleLayout
            standardWidth={true}
            heading={t('Stores')}
            breadcrumb={[{ name: t('Department stores'), slug: '' }]}
        >
            <StoresStyled>
                <MapStyled>
                    <GoogleMap markers={stores} activeMarkerHandler={activeMarkerHandler} closeMarkers={closeInfoBox} />
                </MapStyled>
                <InfoStyled>
                    <ImageStyled>
                        <ImageTextStyled>{stores.length}x</ImageTextStyled>
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
                    </ImageStyled>
                    <InfoTitleStyled type={'h3'}>{t('Stores')}</InfoTitleStyled>
                    {activeInfoBox !== -1 && <InfoBox isClosed={closeInfoBoxHandler} {...stores[activeInfoBox]} />}
                </InfoStyled>
            </StoresStyled>
            <StoresList>
                {stores.length !== 0 &&
                    stores.map((store) => (
                        <NextLink key={store.slug} href={store.slug} passHref>
                            <ButtonBottomStyled>
                                <ButtonBottomItemStyled>
                                    <ButtonBottomIconStyled iconType="icon" icon="Marker" />
                                    <ButtonBottomNameStyled>{store.name}</ButtonBottomNameStyled>
                                </ButtonBottomItemStyled>
                                <ButtonBottomItemStyled>
                                    <ButtonBottomNameStyled type="right">{t('Store detail')}</ButtonBottomNameStyled>
                                    <ButtonBottomIconStyled iconType="icon" icon="Arrow" type="right" />
                                </ButtonBottomItemStyled>
                            </ButtonBottomStyled>
                        </NextLink>
                    ))}
            </StoresList>
        </SimpleLayout>
    );
};

export default Stores;
