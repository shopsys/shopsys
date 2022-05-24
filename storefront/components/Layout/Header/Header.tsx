import AutocompleteSearch from './AutocompleteSearch';
import Cart from './Cart';
import HeaderContact from './Contact/HeaderContact';
import DropdownMenu from './DropdownMenu';
import HamburgerMenu from './HamburgerMenu';
import {
    HeaderCartStyled,
    HeaderLinksStyled,
    HeaderLogoStyled,
    HeaderMenuButtonStyled,
    HeaderMiddleStyled,
    HeaderStyled,
} from './Header.style';
import Logo from './Logo';
import MenuIconic from './MenuIconic';
import Overlay from 'components/Layout/Overlay';
import { FC, useState } from 'react';

type HeaderProps = {
    simpleHeader?: boolean;
};

const Header: FC<HeaderProps> = ({ simpleHeader }) => {
    const testIdentifier = 'layout-header';

    const [isMenuOpened, setIsMenuOpened] = useState(false);

    const onMenuToggleHandler = () => {
        setIsMenuOpened(!isMenuOpened);
    };

    return (
        <HeaderStyled data-testid={testIdentifier}>
            <HeaderLogoStyled>
                <Logo />
            </HeaderLogoStyled>
            {simpleHeader ? (
                <HeaderContact />
            ) : (
                <>
                    <HeaderMiddleStyled>
                        <AutocompleteSearch />
                    </HeaderMiddleStyled>
                    <HeaderLinksStyled>
                        <MenuIconic />
                    </HeaderLinksStyled>
                    <HeaderMenuButtonStyled>
                        <HamburgerMenu onMenuToggleHandler={onMenuToggleHandler} isMenuOpened={isMenuOpened} />
                        <DropdownMenu onMenuToggleHandler={onMenuToggleHandler} isMenuOpened={isMenuOpened} />
                    </HeaderMenuButtonStyled>
                    <HeaderCartStyled>
                        <Cart />
                    </HeaderCartStyled>
                    <Overlay isActive={isMenuOpened} />
                </>
            )}
        </HeaderStyled>
    );
};

/* @component */
export default Header;
