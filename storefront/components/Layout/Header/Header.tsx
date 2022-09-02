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
import { FC, useCallback, useState } from 'react';

type HeaderProps = {
    simpleHeader?: boolean;
};

const TEST_IDENTIFIER = 'layout-header';

const Header: FC<HeaderProps> = ({ simpleHeader }) => {
    const [isMenuOpened, setIsMenuOpened] = useState(false);

    const onMenuToggleHandler = useCallback(() => {
        setIsMenuOpened((prev) => !prev);
    }, []);

    return (
        <HeaderStyled data-testid={TEST_IDENTIFIER}>
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
                    <Overlay isActive={isMenuOpened} onCloseHandler={onMenuToggleHandler} />
                </>
            )}
        </HeaderStyled>
    );
};

export default Header;
