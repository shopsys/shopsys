import { FC } from 'react';
import { HamburgerIconOpenStyled } from './HamburgerIcon.style';
import Icon from 'components/Basic/Icon';

type HamburgerIcon = {
    isMenuOpened: boolean;
};

const HamburgerIcon: FC<HamburgerIcon> = (props) => {
    if (props.isMenuOpened) {
        return <Icon iconType="icon" icon="Close" />;
    }

    return <HamburgerIconOpenStyled iconType="icon" icon="Menu" />;
};

/* @component */
export default HamburgerIcon;
