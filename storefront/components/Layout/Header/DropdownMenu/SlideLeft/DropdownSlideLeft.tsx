import { DropdownSlideLeftIconStyled, DropdownSlideLeftStyled } from './DropdownSlideLeft.style';
import { DropdownListLevels } from 'types/dropdown';
import { FC } from 'react';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type DropdownSlideLeftProps = {
    onClickEvent: (props: { goToMenu: DropdownListLevels }) => void;
    goToMenu: DropdownListLevels;
};

const DropdownSlideLeft: FC<DropdownSlideLeftProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <DropdownSlideLeftStyled onClick={() => props.onClickEvent(props)}>
            <DropdownSlideLeftIconStyled iconType="icon" icon="Arrow" />
            {t('Back')}
        </DropdownSlideLeftStyled>
    );
};

/* @component */
export default DropdownSlideLeft;
