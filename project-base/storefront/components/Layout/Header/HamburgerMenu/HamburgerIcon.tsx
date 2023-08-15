import { Icon } from 'components/Basic/Icon/Icon';
import { Close, Menu } from 'components/Basic/Icon/IconsSvg';

type HamburgerIconProps = {
    isMenuOpened: boolean;
};

export const HamburgerIcon: FC<HamburgerIconProps> = ({ isMenuOpened }) => {
    if (isMenuOpened) {
        return <Icon icon={<Close />} />;
    }

    return <Icon icon={<Menu />} className="w-4" />;
};
