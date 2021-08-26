import { DropdownSlideToBackStyled, DropdownSlideToStyled } from './DropdownSlideTo.style';
import { DropdownItemType } from '../types';
import { FC } from 'react';
import ShopsysIcon from '../../../../basic/ShopsysIcon';

type DropdownSlideToProps = {
    iconText?: string;
    type?: 'stepBack';
};

const DropdownSlideTo: FC<DropdownSlideToProps & DropdownItemType> = (props) => {
    let Component = DropdownSlideToStyled;
    let iconHeight = 18;

    if (props.type === 'stepBack') {
        Component = DropdownSlideToBackStyled;
        iconHeight = 14;
    }

    return (
        <Component onClick={() => props.changeState(props)}>
            <ShopsysIcon iconHeight={iconHeight} icon="arrow-black" />
            {props.iconText}
        </Component>
    );
};

/* @component */
export default DropdownSlideTo;
