import {
    StyledHeader,
    StyledHeaderCart,
    StyledHeaderLinks,
    StyledHeaderLogo,
    StyledHeaderMiddle,
} from './Header.style';
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
            <StyledHeaderCart>Cart</StyledHeaderCart>
        </StyledHeader>
    );
};

/* @component */
export default Header;
