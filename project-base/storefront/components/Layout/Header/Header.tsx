import { DeferredAutocompleteSearch } from './AutocompleteSearch/DeferredAutocompleteSearch';
import { DeferredCart } from './Cart/DeferredCart';
import { Logo } from './Logo/Logo';
import { DeferredMenuIconic } from './MenuIconic/DeferredMenuIconic';
import { DeferredMobileMenu } from './MobileMenu/DeferredMobileMenu';
import dynamic from 'next/dynamic';

const HeaderContact = dynamic(() => import('./Contact/HeaderContact').then((component) => component.HeaderContact));

type HeaderProps = {
    simpleHeader?: boolean;
};

export const Header: FC<HeaderProps> = ({ simpleHeader }) => {
    return (
        <div className="flex flex-wrap items-center gap-y-3 py-3 gap-x-1 lg:gap-x-7 lg:pb-5 lg:pt-6">
            <Logo />

            {simpleHeader ? (
                <HeaderContact />
            ) : (
                <>
                    <div className="order-6 h-12 w-full transition lg:relative lg:order-4 lg:w-full vl:order-2 vl:flex-1">
                        <DeferredAutocompleteSearch />
                    </div>

                    <div className="order-2 flex">
                        <DeferredMenuIconic />
                    </div>

                    <DeferredMobileMenu />

                    <DeferredCart />
                </>
            )}
        </div>
    );
};
