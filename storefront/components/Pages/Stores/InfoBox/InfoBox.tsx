import {
    ButtonCloseStyled,
    HeadingOpeningHoursStyled,
    HeadingStyled,
    InfoBoxStyled,
    LinkStyled,
} from './InfoBox.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { ListedStoreType } from 'types/store';

type InfoBoxProps = {
    store: ListedStoreType;
    closeInfoBoxCallback: () => void;
};

export const InfoBox: FC<InfoBoxProps> = ({ store, closeInfoBoxCallback }) => {
    const t = useTypedTranslationFunction();

    return (
        <InfoBoxStyled>
            <ButtonCloseStyled alt="" onClick={closeInfoBoxCallback} iconType="icon" icon="Remove" />
            <HeadingStyled type="h2">{store.name}</HeadingStyled>
            <div>
                {store.street}
                <br />
                {store.postcode} {store.city}
            </div>
            {store.openingHoursHtml !== null && (
                <>
                    <HeadingOpeningHoursStyled type="h3">{t('Opening hours')}</HeadingOpeningHoursStyled>
                    <div dangerouslySetInnerHTML={{ __html: store.openingHoursHtml }} />
                </>
            )}
            <br />
            <LinkStyled href={store.slug} isButton>
                {t('Store detail')}
            </LinkStyled>
        </InfoBoxStyled>
    );
};
