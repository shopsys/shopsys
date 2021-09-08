import { FC, useContext } from 'react';
import { DropdownItemType } from '../types';
import { DropdownMenuContext } from '../DropdownMenu';
import { DropdownSlideRightStyled } from './DropdownSlideRight.style';
import ShopsysIcon from '../../../../basic/ShopsysIcon';

const DropdownSlideRight: FC<DropdownItemType> = (props) => {
    const context = useContext(DropdownMenuContext);

    return (
        <DropdownSlideRightStyled onClick={() => context.slideRight(props)}>
            <ShopsysIcon iconHeight={18} icon="arrow-black" />
        </DropdownSlideRightStyled>
    );
};

/* @component */
export default DropdownSlideRight;
