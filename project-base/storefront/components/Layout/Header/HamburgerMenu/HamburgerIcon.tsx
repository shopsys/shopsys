import { Close, Menu } from 'components/Basic/Icon/IconsSvg';

type HamburgerIconProps = {
    isMenuOpened: boolean;
};

export const HamburgerIcon: FC<HamburgerIconProps> = ({ isMenuOpened }) => {
    if (isMenuOpened) {
        return <Close />;
    }

    return <Menu className="w-4" />;
};
