import { DeferredAutocompleteSearch } from './AutocompleteSearch/DeferredAutocompleteSearch';
import { DeferredCartInHeader } from './Cart/DeferredCartInHeader';
import { Logo } from './Logo/Logo';
import { DeferredMenuIconic } from './MenuIconic/DeferredMenuIconic';
import { DeferredMobileMenu } from './MobileMenu/DeferredMobileMenu';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import dynamic from 'next/dynamic';

const HeaderContact = dynamic(() => import('./Contact/HeaderContact').then((component) => component.HeaderContact));

type HeaderProps = {
    simpleHeader?: boolean;
};

export const Header: FC<HeaderProps> = ({ simpleHeader }) => {
    return (
        <Webline>
            <div
                className="flex flex-wrap items-center gap-y-3 pt-3 pb-4 lg:gap-x-7 lg:pt-6 lg:pb-1"
                data-tid={TIDs.header}
            >
                <Logo />

                {simpleHeader ? (
                    <HeaderContact />
                ) : (
                    <>
                        <div className="vl:order-2 vl:max-w-[400px] vl:flex-1 order-6 h-12 w-full transition lg:relative lg:order-4 lg:w-full xl:ml-12">
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
        </Webline>
    );
};
