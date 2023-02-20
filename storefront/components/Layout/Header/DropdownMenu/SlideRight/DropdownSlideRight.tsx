import { DropdownSlideRightStyled } from './DropdownSlideRight.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { DropdownMenuContext } from 'components/Layout/Header/DropdownMenu/DropdownMenuContext';
import { FC, useContext } from 'react';
import { DropdownItemType } from 'types/dropdown';

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-slideright';

export const DropdownSlideRight: FC<DropdownItemType> = (dropdownItemProps) => {
    const context = useContext(DropdownMenuContext);

    return (
        <DropdownSlideRightStyled onClick={() => context.slideRight(dropdownItemProps)} data-testid={TEST_IDENTIFIER}>
            <Icon iconType="icon" icon="Arrow" width={17} height={17} className="-rotate-90" />
        </DropdownSlideRightStyled>
    );
};
