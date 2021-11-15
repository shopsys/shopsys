import {
    ButtonCloseStyled,
    HeadingOpeningHoursStyled,
    HeadingStyled,
    InfoBoxStyled,
    LinkStyled,
} from './InfoBox.style';
import { FC } from 'react';
import { StoreListType } from 'connectors/stores/types';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type InfoBoxProps = StoreListType & {
    isClosed: () => void;
};

const InfoBox: FC<InfoBoxProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <InfoBoxStyled>
            <ButtonCloseStyled onClick={props.isClosed} iconType="icon" icon="Remove" />
            <HeadingStyled type="h2">{props.name}</HeadingStyled>
            <div dangerouslySetInnerHTML={{ __html: props.address }}></div>
            {props.openingHours !== null && props.openingHours !== undefined && (
                <>
                    <HeadingOpeningHoursStyled type="h3">{t('Opening hours')}</HeadingOpeningHoursStyled>
                    {props.openingHours}
                </>
            )}
            <br />
            <LinkStyled href={props.slug} isButton={true}>
                {t<string>('Store detail')}
            </LinkStyled>
        </InfoBoxStyled>
    );
};

export default InfoBox;
