import {
    StyledHeader,
    StyledHeaderCart,
    StyledHeaderLinks,
    StyledHeaderLogo,
    StyledHeaderMenuButton,
    StyledHeaderMiddle,
} from './Header.style';
import Cart from './Cart';
import HamburgerMenu from './HamburgerMenu';
import Logo from './Logo';
import MenuIconic from './MenuIconic';
import { ReactElement } from 'react';
import Search from './Search';

const Header = (): ReactElement => {
    return (
        <StyledHeader>
            <StyledHeaderLogo>
                <Logo />
            </StyledHeaderLogo>
            <StyledHeaderMiddle>
                <Search />
            </StyledHeaderMiddle>
            <StyledHeaderLinks>
                <MenuIconic />
            </StyledHeaderLinks>
            <StyledHeaderMenuButton>
                <HamburgerMenu />
            </StyledHeaderMenuButton>
            <StyledHeaderCart>
                <Cart />
            </StyledHeaderCart>
        </StyledHeader>
    );
};

/* @component */
export default Header;
