import { CloseIcon, MenuIcon } from 'components/Basic/Icon/IconsSvg';

type HamburgerIconProps = {
    isMenuOpened: boolean;
};

export const HamburgerIcon: FC<HamburgerIconProps> = ({ isMenuOpened }) => {
    if (isMenuOpened) {
        return <CloseIcon />;
    }

    return <MenuIcon className="w-4" />;
};
