import { DeferredAutocompleteSearch } from './AutocompleteSearch/DeferredAutocompleteSearch';
import { DeferredCartInHeader } from './Cart/DeferredCartInHeader';
import { Logo } from './Logo/Logo';
import { DeferredMenuIconic } from './MenuIconic/DeferredMenuIconic';
import { DeferredMobileMenu } from './MobileMenu/DeferredMobileMenu';
import { TIDs } from 'cypress/tids';
import dynamic from 'next/dynamic';

const HeaderContact = dynamic(() => import('./Contact/HeaderContact').then((component) => ({
    default: component.HeaderContact
})));

type HeaderProps = {
    simpleHeader?: boolean;
};

export const Header: FC<HeaderProps> = ({ simpleHeader }) => {
    return (
        <div className="flex flex-wrap items-center gap-y-3 pb-4 pt-3 lg:gap-x-7 lg:pb-1 lg:pt-6" tid={TIDs.header}>
            <Logo />

            {simpleHeader ? (
                <HeaderContact />
            ) : (
                <>
                    <div className="order-6 h-12 w-full transition lg:relative lg:order-4 lg:w-full vl:order-2 vl:max-w-[400px] vl:flex-1 xl:ml-12">
                        <DeferredAutocompleteSearch />
                    </div>

                    <div className="order-2 ml-auto flex">
                        <DeferredMenuIconic />
                    </div>

                    <DeferredMobileMenu />

                    <DeferredCartInHeader />
                </>
            )}
        </div>
    );
};
