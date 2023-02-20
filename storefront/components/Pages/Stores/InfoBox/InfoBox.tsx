import { InfoBoxStyled, LinkStyled } from './InfoBox.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
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
            <Icon
                onClick={closeInfoBoxCallback}
                iconType="icon"
                icon="Remove"
                width={22}
                height={22}
                className="absolute top-4 right-4 cursor-pointer text-primary transition-colors hover:text-orangeDarker "
            />
            <Heading type="h2" className="mb-3">
                {store.name}
            </Heading>
            <div>
                {store.street}
                <br />
                {store.postcode} {store.city}
            </div>
            {store.openingHoursHtml !== null && (
                <>
                    <Heading type="h3" className="m-0 mt-3">
                        {t('Opening hours')}
                    </Heading>
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
