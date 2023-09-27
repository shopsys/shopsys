import dynamic from 'next/dynamic';
import { AutocompleteSearch } from './AutocompleteSearch/AutocompleteSearch';
import { Cart } from './Cart/Cart';
import { HeaderContact } from './Contact/HeaderContact';
import { DropdownMenu } from './DropdownMenu/DropdownMenu';
import { HamburgerMenu } from './HamburgerMenu/HamburgerMenu';
import { Logo } from './Logo/Logo';
import { MenuIconic } from './MenuIconic/MenuIconic';
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
            className="flex flex-wrap items-center justify-between gap-y-3 py-3 lg:gap-x-7 lg:pb-5 lg:pt-6"
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
                        <HamburgerMenu onMenuToggleHandler={onMenuToggleHandler} isMenuOpened={isMenuOpened} />
                        <DropdownMenu onMenuToggleHandler={onMenuToggleHandler} isMenuOpened={isMenuOpened} />
                    </div>
                    <Cart className="order-3 vl:order-4" />
                    <Overlay isActive={isMenuOpened} onClick={onMenuToggleHandler} isHiddenOnDesktop />
                </>
            )}
        </div>
    );
};
