import { Icon } from 'components/Basic/Icon/Icon';

type HamburgerIconProps = {
    isMenuOpened: boolean;
};

export const HamburgerIcon: FC<HamburgerIconProps> = ({ isMenuOpened }) => {
    if (isMenuOpened) {
        return <Icon iconType="icon" icon="Close" />;
    }

    return <Icon iconType="icon" icon="Menu" width={16} height={16} />;
};
