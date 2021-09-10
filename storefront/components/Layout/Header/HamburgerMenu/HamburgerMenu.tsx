import { FC, useState } from 'react';
import {
    HamburgerMenuIconOpenStyled,
    HamburgerMenuImageStyled,
    HamburgerMenuStyled,
    HamburgerMenuTextStyled,
} from './HamburgerMenu.style';
import Icon from '../../../Basic/Icon';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const HamburgerMenu: FC = () => {
    const t = useTypedTranslationFunction();
    const [isMenuOpened, setIsMenuOpened] = useState(false);

    const MenuIcon = () => {
        if (isMenuOpened) {
            return <Icon icon="Close" />;
        }
        return <HamburgerMenuIconOpenStyled icon="Menu" />;
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
