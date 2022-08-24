import { DropdownSlideLeftIconStyled, DropdownSlideLeftStyled } from './DropdownSlideLeft.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { DropdownListLevels } from 'types/dropdown';

type DropdownSlideLeftProps = {
    onClickEvent: (props: { goToMenu: DropdownListLevels }) => void;
    goToMenu: DropdownListLevels;
};

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-slideleft';

export const DropdownSlideLeft: FC<DropdownSlideLeftProps> = ({ goToMenu, onClickEvent }) => {
    const t = useTypedTranslationFunction();

    return (
        <DropdownSlideLeftStyled onClick={() => onClickEvent({ goToMenu })} data-testid={TEST_IDENTIFIER}>
            <DropdownSlideLeftIconStyled iconType="icon" icon="Arrow" />
            {t('Back')}
        </DropdownSlideLeftStyled>
    );
};
