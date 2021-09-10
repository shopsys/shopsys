import { DropdownSlideRightIconStyled, DropdownSlideRightStyled } from './DropdownSlideRight.style';
import { FC, useContext } from 'react';
import { DropdownItemType } from '../types';
import { DropdownMenuContext } from '../DropdownMenu';

const DropdownSlideRight: FC<DropdownItemType> = (props) => {
    const context = useContext(DropdownMenuContext);

    return (
        <DropdownSlideRightStyled onClick={() => context.slideRight(props)}>
            <DropdownSlideRightIconStyled icon="Arrow" />
        </DropdownSlideRightStyled>
    );
};

/* @component */
export default DropdownSlideRight;
