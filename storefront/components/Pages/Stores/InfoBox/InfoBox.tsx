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

type InfoBoxProps = ListedStoreType & {
    isClosed: () => void;
};

export const InfoBox: FC<InfoBoxProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <InfoBoxStyled>
            <ButtonCloseStyled alt="" onClick={props.isClosed} iconType="icon" icon="Remove" />
            <HeadingStyled type="h2">{props.name}</HeadingStyled>
            <div>
                {props.street}
                <br />
                {props.postcode} {props.city}
            </div>
            {props.openingHoursHtml !== null && (
                <>
                    <HeadingOpeningHoursStyled type="h3">{t('Opening hours')}</HeadingOpeningHoursStyled>
                    <div dangerouslySetInnerHTML={{ __html: props.openingHoursHtml }} />
                </>
            )}
            <br />
            <LinkStyled href={props.slug} isButton>
                {t('Store detail')}
            </LinkStyled>
        </InfoBoxStyled>
    );
};
