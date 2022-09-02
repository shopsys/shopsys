import { HamburgerIconOpenStyled } from './HamburgerIcon.style';
import Icon from 'components/Basic/Icon';
import { FC } from 'react';

type HamburgerIconProps = {
    isMenuOpened: boolean;
};

const HamburgerIcon: FC<HamburgerIconProps> = (props) => {
    if (props.isMenuOpened) {
        return <Icon iconType="icon" icon="Close" />;
    }

    return <HamburgerIconOpenStyled iconType="icon" icon="Menu" />;
};

export default HamburgerIcon;
