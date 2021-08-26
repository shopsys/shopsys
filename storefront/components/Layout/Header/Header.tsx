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
import Search from './Search';

const Header: FC = () => {
    const [isMenuOpened, setIsMenuOpened] = useState(false);

    const toggleMenu = () => {
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
                <HamburgerMenu toggleMenu={toggleMenu} isMenuOpened={isMenuOpened} />
                <DropdownMenu isMenuOpened={isMenuOpened} />
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
