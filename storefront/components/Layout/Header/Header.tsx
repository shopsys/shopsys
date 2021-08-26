import { FC, useState } from 'react';
import {
    HeaderCartStyled,
    HeaderLinksStyled,
    HeaderLogoStyled,
    HeaderMenuButtonStyled,
    HeaderMiddleStyled,
    HeaderStyled,
} from './Header.style';
import Cart from './Cart';
import DropdownMenu from './DropdownMenu';
import FrontendSwitcher from 'components/Blocks/FrontendSwitcher';
import HamburgerMenu from './HamburgerMenu';
import Logo from './Logo';
import MenuIconic from './MenuIconic';
import Overlay from '../Overlay';
import Search from './Search';

const Header: FC = () => {
    const [isMenuOpened, setIsMenuOpened] = useState(false);

    const onMenuToggleHandler = () => {
        setIsMenuOpened(!isMenuOpened);
    };

    return (
        <HeaderStyled>
            <HeaderLogoStyled>
                <Logo />
            </HeaderLogoStyled>
            <HeaderMiddleStyled>
                <Search />
            </HeaderMiddleStyled>
            <HeaderLinksStyled>
                <MenuIconic />
            </HeaderLinksStyled>
            <HeaderMenuButtonStyled>
                <HamburgerMenu onMenuToggleHandler={onMenuToggleHandler} isMenuOpened={isMenuOpened} />
                <DropdownMenu isMenuOpened={isMenuOpened} />
            </HeaderMenuButtonStyled>
            <HeaderCartStyled>
                <Cart />
            </HeaderCartStyled>
            <Overlay isActive={isMenuOpened} />
            <FrontendSwitcher />
        </HeaderStyled>
    );
};

/* @component */
export default Header;
