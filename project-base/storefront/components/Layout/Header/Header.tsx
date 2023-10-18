import { AutocompleteSearch } from './AutocompleteSearch/AutocompleteSearch';
import { Cart } from './Cart/Cart';
import { HeaderContact } from './Contact/HeaderContact';
import { DropdownMenu } from './DropdownMenu/DropdownMenu';
import { HamburgerMenu } from './HamburgerMenu/HamburgerMenu';
import { Logo } from './Logo/Logo';
import { MenuIconic } from './MenuIconic/MenuIconic';
import dynamic from 'next/dynamic';
import { useState } from 'react';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

type HeaderProps = {
    simpleHeader?: boolean;
};

const TEST_IDENTIFIER = 'layout-header';

export const Header: FC<HeaderProps> = ({ simpleHeader }) => {
    const [isMenuOpened, setIsMenuOpened] = useState(false);

    const onMenuToggleHandler = () => setIsMenuOpened(!isMenuOpened);

    return (
        <div
            className="flex flex-wrap items-center gap-y-3 py-3 lg:gap-x-7 lg:pb-5 lg:pt-6"
            data-testid={TEST_IDENTIFIER}
        >
            <Logo />

            {simpleHeader ? (
                <HeaderContact />
            ) : (
                <>
                    <div className="order-6 h-12 w-full transition lg:relative lg:order-4 lg:w-full vl:order-2 vl:flex-1">
                        <AutocompleteSearch />
                    </div>
                    <div className="order-2 flex">
                        <MenuIconic />
                    </div>
                    <div className="order-4 ml-3 flex cursor-pointer items-center justify-center text-lg lg:hidden">
                        <HamburgerMenu isMenuOpened={isMenuOpened} onMenuToggleHandler={onMenuToggleHandler} />
                        <DropdownMenu isMenuOpened={isMenuOpened} onMenuToggleHandler={onMenuToggleHandler} />
                    </div>
                    <Cart className="order-3 vl:order-4" />
                    <Overlay isHiddenOnDesktop isActive={isMenuOpened} onClick={onMenuToggleHandler} />
                </>
            )}
        </div>
    );
};
