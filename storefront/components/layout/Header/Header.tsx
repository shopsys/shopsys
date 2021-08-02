import {
    StyledHeader,
    StyledHeaderCart,
    StyledHeaderLinks,
    StyledHeaderLogo,
    StyledHeaderMiddle,
} from './Header.style';
import { ReactElement } from 'react';

const Header = (): ReactElement => {
    return (
        <StyledHeader>
            <StyledHeaderLogo>Logo</StyledHeaderLogo>
            <StyledHeaderMiddle>Middle</StyledHeaderMiddle>
            <StyledHeaderLinks>Links</StyledHeaderLinks>
            <StyledHeaderCart>Cart</StyledHeaderCart>
        </StyledHeader>
    );
};

/* @component */
export default Header;
