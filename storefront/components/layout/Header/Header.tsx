import {
    StyledHeader,
    StyledHeaderCart,
    StyledHeaderLinks,
    StyledHeaderLogo,
    StyledHeaderMiddle,
} from './Header.style';
import Logo from './Logo';
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
            <StyledHeaderLinks>Links</StyledHeaderLinks>
            <StyledHeaderCart>Cart</StyledHeaderCart>
        </StyledHeader>
    );
};

/* @component */
export default Header;
