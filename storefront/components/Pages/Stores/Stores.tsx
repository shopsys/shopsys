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
import { FC, useState } from 'react';
import { getStores } from 'connectors/stores/Stores';
import GoogleMap from 'components/Basic/GoogleMap';
import Image from 'next/image';
import InfoBox from './InfoBox';
import SimpleLayout from 'components/Layout/SimpleLayout';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const Stores: FC = () => {
    const t = useTypedTranslationFunction();
    const stores = getStores();
    const { defaultLocale } = useShopsysSelector((state) => state.domain);
    const [activeInfoBox, setActiveInfoBox] = useState(-1);
    const [closeInfoBox, setCloseInfoBox] = useState(true);

    const activeMarkerHandler = (index: number) => {
        setActiveInfoBox(activeInfoBox !== index ? index : -1);

        if (activeInfoBox !== index) {
            setCloseInfoBox(false);
        }
    };

    const closeInfoBoxHandler = () => {
        setCloseInfoBox(true);
    };

    return (
        <SimpleLayout
            standardWidth={true}
            heading={t('Stores')}
            breadcrumb={[{ __typename: 'StoreEdge', name: t('Department stores'), slug: '' }]}
        >
            <StoresStyled>
                <MapStyled>
                    <GoogleMap markers={stores} activeMarker={activeMarkerHandler} closeMarkers={closeInfoBox} />
                </MapStyled>
                <InfoStyled>
                    {`/images/stores_${defaultLocale}.png` !== undefined &&
                        `/images/stores_${defaultLocale}2x.png` !== undefined && (
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
                        )}
                    <InfoTitleStyled type={'h3'}>{t('Stores')}</InfoTitleStyled>
                    {activeInfoBox !== -1 && <InfoBox isClosed={closeInfoBoxHandler} {...stores[activeInfoBox]} />}
                </InfoStyled>
            </StoresStyled>
            <StoresList>
                {stores !== undefined &&
                    Array.isArray(stores) &&
                    stores.length !== 0 &&
                    stores.map((store, index) => (
                        <ButtonBottomStyled key={index} href={store.slug}>
                            <ButtonBottomItemStyled>
                                <ButtonBottomIconStyled icon="Marker" />
                                <ButtonBottomNameStyled>{store.name}</ButtonBottomNameStyled>
                            </ButtonBottomItemStyled>
                            <ButtonBottomItemStyled>
                                <ButtonBottomNameStyled type="right">{t('Store detail')}</ButtonBottomNameStyled>
                                <ButtonBottomIconStyled icon="Arrow" type="right" />
                            </ButtonBottomItemStyled>
                        </ButtonBottomStyled>
                    ))}
            </StoresList>
        </SimpleLayout>
    );
};

export default Stores;
