import {
    HamburgerMenuIconOpenStyled,
    HamburgerMenuImageStyled,
    HamburgerMenuStyled,
    HamburgerMenuTextStyled,
} from './HamburgerMenu.style';
import { FC } from 'react';
import Icon from '../../../Basic/Icon';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

type HamburgerMenuProps = {
    isMenuOpened: boolean;
    toggleMenu: any;
};

const HamburgerMenu: FC<HamburgerMenuProps> = (props) => {
    const t = useTypedTranslationFunction();

    const MenuIcon = () => {
        if (props.isMenuOpened) {
            return <Icon icon="Close" />;
        }
        return <HamburgerMenuIconOpenStyled icon="Menu" />;
    };

    return (
        <HamburgerMenuStyled onClick={props.toggleMenu}>
            <HamburgerMenuImageStyled>
                <MenuIcon />
            </HamburgerMenuImageStyled>
            <HamburgerMenuTextStyled>{props.isMenuOpened ? t('Close') : t('Menu')}</HamburgerMenuTextStyled>
        </HamburgerMenuStyled>
    );
};

/* @component */
export default HamburgerMenu;
