import {
    HeaderCartStyled,
    HeaderLinksStyled,
    HeaderLogoStyled,
    HeaderMenuButtonStyled,
    HeaderMiddleStyled,
    HeaderStyled,
} from './Header.style';
import Cart from './Cart';
import HamburgerMenu from './HamburgerMenu';
import Logo from './Logo';
import MenuIconic from './MenuIconic';
import { ReactElement } from 'react';
import Search from './Search';

const Header = (): ReactElement => {
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
        </HeaderStyled>
    );
};

/* @component */
export default Header;
