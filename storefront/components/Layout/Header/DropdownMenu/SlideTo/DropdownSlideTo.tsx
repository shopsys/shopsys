import { DropdownSlideToBackStyled, DropdownSlideToStyled } from './DropdownSlideTo.style';
import { ReactElement } from 'react';
import ShopsysIcon from '../../../../basic/ShopsysIcon';

const DropdownSlideTo = (props): ReactElement => {
    let Component = DropdownSlideToStyled;
    let iconHeight = 18;

    if (props.variant === 'stepBack') {
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
