import { AutocompleteSearch } from './AutocompleteSearch/AutocompleteSearch';
import { Cart } from './Cart/Cart';
import { HeaderContact } from './Contact/HeaderContact';
import { Logo } from './Logo/Logo';
import { MenuIconic } from './MenuIconic/MenuIconic';
import { MobileMenu } from './MobileMenu/MobileMenu';

type HeaderProps = {
    simpleHeader?: boolean;
};

export const Header: FC<HeaderProps> = ({ simpleHeader }) => {
    return (
        <div className="flex flex-wrap items-center gap-y-3 py-3 lg:gap-x-7 lg:pb-5 lg:pt-6">
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
                        <MobileMenu />
                    </div>

                    <Cart className="order-3 vl:order-4" />
                </>
            )}
        </div>
    );
};
