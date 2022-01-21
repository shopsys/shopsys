import { DropdownSlideRightIconStyled, DropdownSlideRightStyled } from './DropdownSlideRight.style';
import { FC, useContext } from 'react';
import { DropdownItemType } from 'types/dropdown';
import { DropdownMenuContext } from 'components/Layout/Header/DropdownMenu/DropdownMenu';

const DropdownSlideRight: FC<DropdownItemType> = (props) => {
    const testIdentifier = 'layout-header-dropdownmenu-slideright';

    const context = useContext(DropdownMenuContext);

    return (
        <DropdownSlideRightStyled onClick={() => context.slideRight(props)} data-testid={testIdentifier}>
            <DropdownSlideRightIconStyled iconType="icon" icon="Arrow" />
        </DropdownSlideRightStyled>
    );
};

/* @component */
export default DropdownSlideRight;
