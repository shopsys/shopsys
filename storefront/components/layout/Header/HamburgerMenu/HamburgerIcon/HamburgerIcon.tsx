import { FC } from 'react';
import ShopsysIcon from '../../../../basic/ShopsysIcon';

type HamburgerIcon = {
    isMenuOpened: boolean;
};

const HamburgerIcon: FC<HamburgerIcon> = (props) => {
    if (props.isMenuOpened) {
        return <ShopsysIcon icon="close" iconHeight={14} />;
    }

    return <ShopsysIcon icon="menu" iconHeight={16} />;
};

/* @component */
export default HamburgerIcon;
