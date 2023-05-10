import { AutocompleteSearch } from './AutocompleteSearch/AutocompleteSearch';
import { Cart } from './Cart/Cart';
import { HeaderContact } from './Contact/HeaderContact';
import { DropdownMenu } from './DropdownMenu/DropdownMenu';
import { HamburgerMenu } from './HamburgerMenu/HamburgerMenu';
import { Logo } from './Logo/Logo';
import { MenuIconic } from './MenuIconic/MenuIconic';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { useCallback, useState } from 'react';

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
        <div className="flex flex-wrap items-center pt-2 pb-3 lg:pt-4 vl:pt-6 vl:pb-4 " data-testid={TEST_IDENTIFIER}>
            <div className="order-1 mr-auto flex flex-1 vl:mr-5 vl:flex-none xl:mr-8">
                <Logo />
            </div>
            {simpleHeader ? (
                <HeaderContact />
            ) : (
                <>
                    <div className="vl:max-[400px] order-6 mt-3 w-full lg:order-4 lg:mt-5 vl:order-2 vl:mt-0 vl:ml-auto vl:mr-5 vl:flex-1 xl:mr-8">
                        <AutocompleteSearch />
                    </div>
                    <div className="order-2 flex lg:mr-5 lg:ml-auto xl:mr-8">
                        <MenuIconic />
                    </div>
                    <div className="order-4 ml-4 flex h-10 w-auto cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                        <HamburgerMenu onMenuToggleHandler={onMenuToggleHandler} isMenuOpened={isMenuOpened} />
                        <DropdownMenu onMenuToggleHandler={onMenuToggleHandler} isMenuOpened={isMenuOpened} />
                    </div>
                    <div className="relative order-3 flex vl:order-4">
                        <Cart />
                    </div>
                    <Overlay isActive={isMenuOpened} onClick={onMenuToggleHandler} isHiddenOnDesktop />
                </>
            )}
        </div>
    );
};
