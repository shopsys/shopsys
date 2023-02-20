import { DropdownSlideLeftStyled } from './DropdownSlideLeft.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
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
            <Icon iconType="icon" icon="Arrow" className="mr-2 rotate-90" />
            {t('Back')}
        </DropdownSlideLeftStyled>
    );
};
