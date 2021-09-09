import { HamburgerMenuImageStyled, HamburgerMenuStyled, HamburgerMenuTextStyled } from './HamburgerMenu.style';
import { ReactElement, useState } from 'react';
import Icon from '../../../Basic/Icon';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const HamburgerMenu = (): ReactElement => {
    const t = useTypedTranslationFunction();
    const [isMenuOpened, setIsMenuOpened] = useState(false);

    const MenuIcon = () => {
        if (isMenuOpened) {
            return <Icon icon="NotImplementedYet" iconHeight={14} />;
        }
        return <Icon icon="NotImplementedYet" iconHeight={16} />;
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
