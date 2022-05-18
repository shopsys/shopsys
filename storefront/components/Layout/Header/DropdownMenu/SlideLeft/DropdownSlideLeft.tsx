import { DropdownSlideLeftIconStyled, DropdownSlideLeftStyled } from './DropdownSlideLeft.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { DropdownListLevels } from 'types/dropdown';

type DropdownSlideLeftProps = {
    onClickEvent: (props: { goToMenu: DropdownListLevels }) => void;
    goToMenu: DropdownListLevels;
};

const DropdownSlideLeft: FC<DropdownSlideLeftProps> = (props) => {
    const testIdentifier = 'layout-header-dropdownmenu-slideleft';

    const t = useTypedTranslationFunction();

    return (
        <DropdownSlideLeftStyled onClick={() => props.onClickEvent(props)} data-testid={testIdentifier}>
            <DropdownSlideLeftIconStyled iconType="icon" icon="Arrow" />
            {t('Back')}
        </DropdownSlideLeftStyled>
    );
};

/* @component */
export default DropdownSlideLeft;
