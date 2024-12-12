import { MenuIconic } from './MenuIconic/MenuIconic';
import Navigation from 'app/_components/Layout/Header/Navigation/Navigation';
import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { Logo } from 'components/Layout/Header/Logo/Logo';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';

export default async function Header() {
    return (
        <header className="mb-4 bg-gradient-to-tr from-backgroundBrand to-backgroundBrandLess" tid={TIDs.header}>
            <Webline>
                <div className="flex flex-wrap items-center gap-y-3 pb-4 pt-3 lg:gap-x-7 lg:pb-1 lg:pt-6">
                    <Logo />

                    <div className="order-6 h-12 w-full transition lg:relative lg:order-4 lg:w-full vl:order-2 vl:max-w-[400px] vl:flex-1 xl:ml-12">
                        {/* <AutocompleteSearch /> */}
                    </div>

                    <div className="order-2 ml-auto flex">
                        <MenuIconic />
                    </div>

                    <div className="order-1 flex cursor-pointer items-center justify-center text-lg lg:hidden">
                        <MenuIcon className="size-6 text-linkInverted" />
                    </div>

                    <div className="order-3 ml-auto vl:order-4">
                        <CartIcon className="size-6 text-linkInverted" />
                    </div>
                </div>

                <Navigation />
            </Webline>
        </header>
    );
}
