import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import dynamic from 'next/dynamic';
import { DeferredAutocompleteSearch } from './AutocompleteSearch/DeferredAutocompleteSearch';
import { DeferredCartInHeader } from './Cart/DeferredCartInHeader';
import { Logo } from './Logo/Logo';
import { DeferredMenuIconic } from './MenuIconic/DeferredMenuIconic';
import { MobileBottomNavigation } from './MobileBottomNavigation/MobileBottomNavigation';

const HeaderContact = dynamic(() => import('./Contact/HeaderContact').then((component) => component.HeaderContact));

type HeaderProps = {
    simpleHeader?: boolean;
};

export const Header: FC<HeaderProps> = ({ simpleHeader }) => {
    return (
        <Webline>
            {simpleHeader ? (
                <div className="flex items-center py-3 lg:py-7.5" data-tid={TIDs.header}>
                    <Logo />

                    <HeaderContact />
                </div>
            ) : (
                <div
                    className="flex flex-wrap items-center vl:gap-x-7 gap-y-3 pt-3 vl:pt-6 pb-3 vl:pb-1"
                    data-tid={TIDs.header}
                >
                    <Logo />

                    <div className="vl:relative order-6 vl:order-2 vl:block hidden h-12 w-full vl:max-w-100 vl:flex-1 transition xl:ml-12">
                        <DeferredAutocompleteSearch />
                    </div>

                    <div className="order-2 ml-auto vl:flex hidden">
                        <DeferredMenuIconic />
                    </div>

                    <DeferredCartInHeader />

                    <MobileBottomNavigation />
                </div>
            )}
        </Webline>
    );
};
