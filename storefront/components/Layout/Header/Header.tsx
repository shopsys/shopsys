import { AutocompleteSearch } from './AutocompleteSearch/AutocompleteSearch';
import { Cart } from './Cart/Cart';
import { HeaderContact } from './Contact/HeaderContact';
import { DropdownMenu } from './DropdownMenu/DropdownMenu';
import { HamburgerMenu } from './HamburgerMenu/HamburgerMenu';
import {
    HeaderCartStyled,
    HeaderLinksStyled,
    HeaderLogoStyled,
    HeaderMenuButtonStyled,
    HeaderMiddleStyled,
    HeaderStyled,
} from './Header.style';
import { Logo } from './Logo/Logo';
import { MenuIconic } from './MenuIconic/MenuIconic';
import { Overlay } from 'components/Layout/Overlay/Overlay';
import { FC, useCallback, useState } from 'react';

type HeaderProps = {
    simpleHeader?: boolean;
};

const TEST_IDENTIFIER = 'layout-header';

export const Header: FC<HeaderProps> = ({ simpleHeader }) => {
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
