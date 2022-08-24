import { DropdownSlideRightIconStyled, DropdownSlideRightStyled } from './DropdownSlideRight.style';
import { DropdownMenuContext } from 'components/Layout/Header/DropdownMenu/DropdownMenuContext';
import { FC, useContext } from 'react';
import { DropdownItemType } from 'types/dropdown';

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-slideright';

export const DropdownSlideRight: FC<DropdownItemType> = (props) => {
    const context = useContext(DropdownMenuContext);

    return (
        <DropdownSlideRightStyled onClick={() => context.slideRight(props)} data-testid={TEST_IDENTIFIER}>
            <DropdownSlideRightIconStyled iconType="icon" icon="Arrow" />
        </DropdownSlideRightStyled>
    );
};
