import { HamburgerMenuImageStyled, HamburgerMenuStyled, HamburgerMenuTextStyled } from './HamburgerMenu.style';
import { ReactElement, useState } from 'react';
import ShopsysIcon from '../../../basic/ShopsysIcon';
import { useTranslation } from 'react-i18next';

const HamburgerMenu = (): ReactElement => {
    const { t } = useTranslation();
    const [isMenuOpened, setIsMenuOpened] = useState(false);

    const MenuIcon = () => {
        if (isMenuOpened) {
            return <ShopsysIcon icon="close" iconHeight={14} />;
        }
        return <ShopsysIcon icon="menu" iconHeight={16} />;
    };

    const toggleMenu = () => {
        setIsMenuOpened(!isMenuOpened);
    };

    return (
        <HamburgerMenuStyled onClick={toggleMenu}>
            <HamburgerMenuImageStyled>
                <MenuIcon />
            </HamburgerMenuImageStyled>
            <HamburgerMenuTextStyled>{isMenuOpened ? t('Close') : t('Menu')}</HamburgerMenuTextStyled>
        </HamburgerMenuStyled>
    );
};

/* @component */
export default HamburgerMenu;
