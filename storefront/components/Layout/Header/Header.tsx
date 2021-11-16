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
import HeaderContact from './Contact/HeaderContact';
import Logo from './Logo';
import MenuIconic from './MenuIconic';
import Overlay from 'components/Layout/Overlay';
import Search from './Search';
import { useRouter } from 'next/router';

const Header: FC = () => {
    const [isMenuOpened, setIsMenuOpened] = useState(false);
    const router = useRouter();
    const isOrderPageLayoutVisible = router.route.slice(0, 6) === '/order';

    const onMenuToggleHandler = () => {
        setIsMenuOpened(!isMenuOpened);
    };

    return (
        <HeaderStyled>
            <HeaderLogoStyled>
                <Logo />
            </HeaderLogoStyled>
            {isOrderPageLayoutVisible ? (
                <HeaderContact />
            ) : (
                <>
                    <HeaderMiddleStyled>
                        <Search />
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
                    <FrontendSwitcher />
                </>
            )}
        </HeaderStyled>
    );
};

/* @component */
export default Header;
