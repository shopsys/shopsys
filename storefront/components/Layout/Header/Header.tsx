import {
    HeaderCartStyled,
    HeaderLinksStyled,
    HeaderLogoStyled,
    HeaderMenuButtonStyled,
    HeaderMiddleStyled,
    HeaderStyled,
} from './Header.style';
import Cart from './Cart';
import { FC } from 'react';
import FrontendSwitcher from 'components/Blocks/FrontendSwitcher';
import HamburgerMenu from './HamburgerMenu';
import Logo from './Logo';
import MenuIconic from './MenuIconic';
import Search from './Search';

const Header: FC = () => {
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
                <HamburgerMenu />
            </HeaderMenuButtonStyled>
            <HeaderCartStyled>
                <Cart />
            </HeaderCartStyled>
            <FrontendSwitcher />
        </HeaderStyled>
    );
};

/* @component */
export default Header;
