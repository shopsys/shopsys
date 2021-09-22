import { FC } from 'react';
import { HamburgerIconOpenStyled } from './HamburgerIcon.style';
import Icon from 'components/Basic/Icon';

type HamburgerIcon = {
    isMenuOpened: boolean;
};

const HamburgerIcon: FC<HamburgerIcon> = (props) => {
    if (props.isMenuOpened) {
        return <Icon icon="Close" />;
    }

    return <HamburgerIconOpenStyled icon="Menu" />;
};

/* @component */
export default HamburgerIcon;
