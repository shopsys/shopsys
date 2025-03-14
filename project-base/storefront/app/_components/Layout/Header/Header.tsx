import { ShowAutocompleteSearchPopupAction } from './AutocompleteSearch/ShowAutocompleteSearchPopupAction';
import { AutocompleteSearch } from 'app/_components/Layout/Header/AutocompleteSearch/AutocompleteSearch';
import { MenuIconic } from 'app/_components/Layout/Header/MenuIconic/MenuIconic';
import { Navigation } from 'app/_components/Layout/Header/Navigation/Navigation';
import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { Logo } from 'components/Layout/Header/Logo/Logo';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';

export const Header = () => {
    return (
        <header className="from-backgroundBrand to-backgroundBrandLess bg-gradient-to-tr" tid={TIDs.header}>
            <Webline>
                <div className="flex flex-wrap items-center gap-y-3 pt-3 pb-4 lg:gap-x-7 lg:pt-6 lg:pb-1">
                    <Logo />

                    <div className="vl:order-2 vl:max-w-[400px] vl:flex-1 order-6 h-12 w-full transition lg:relative lg:order-4 lg:w-full xl:ml-12">
                        <AutocompleteSearch search={ShowAutocompleteSearchPopupAction} />
                    </div>

                    <div className="order-2 ml-auto flex">
                        <MenuIconic />
                    </div>

                    <div className="order-1 flex cursor-pointer items-center justify-center text-lg lg:hidden">
                        <MenuIcon className="text-linkInverted size-6" />
                    </div>

                    <div className="vl:order-4 order-3 ml-auto">
                        <CartIcon className="text-linkInverted size-6" />
                    </div>
                </div>

                <Navigation />
            </Webline>
        </header>
    );
};
