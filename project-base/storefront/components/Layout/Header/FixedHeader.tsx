import { Webline } from 'components/Layout/Webline/Webline';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import type { RefCallback } from 'react';
import { RemoveScroll } from 'react-remove-scroll';
import { twJoin } from 'tailwind-merge';
import { AutocompleteSearch } from './AutocompleteSearch/AutocompleteSearch';
import { CartInHeader } from './Cart/CartInHeader';
import { Logo } from './Logo/Logo';
import { MenuIconic } from './MenuIconic/MenuIconic';
import { Navigation } from './Navigation/Navigation';

type FixedHeaderProps = {
    fixedHeaderRef: RefCallback<HTMLDivElement>;
    isVisible: boolean;
    navigation?: TypeNavigationQuery['navigation'];
};

export const FixedHeader: FC<FixedHeaderProps> = ({ fixedHeaderRef, isVisible, navigation }) => {
    const { canCreateOrder } = useAuthorization();

    return (
        <div
            aria-hidden={!isVisible || undefined}
            className={twJoin(
                'fixed inset-x-0 top-0 z-1040 bg-background-brand shadow-lg',
                RemoveScroll.classNames.fullWidth,
                isVisible
                    ? 'motion-safe:animate-[fixedHeaderSlideDown_200ms_ease-out]'
                    : 'pointer-events-none invisible -translate-y-full opacity-0',
            )}
            data-tid={isVisible ? TIDs.fixed_header : undefined}
            inert={!isVisible || undefined}
            ref={fixedHeaderRef}
        >
            <Webline>
                <div className="flex flex-wrap items-center justify-between gap-x-7 gap-y-3 pt-3">
                    <Logo imageClassName="vl:w-32" />

                    <div className="order-6 vl:order-2 h-10 w-full vl:max-w-md vl:flex-1 lg:relative lg:order-4 lg:w-full xl:ml-12">
                        <AutocompleteSearch inputClassName="h-10" inputId="fixed-header-search-input" />
                    </div>

                    <div className="order-2 flex items-center gap-7">
                        <MenuIconic isCompact loginFormName="fixed-header-login-form" shouldUseLocalUserMenuState />

                        {canCreateOrder && <CartInHeader className="order-3 vl:order-4" isCompact />}
                    </div>
                </div>
            </Webline>

            {!!navigation?.length && (
                <Webline className="relative">
                    <Navigation id="fixed-main-navigation" itemClassName="p-3" navigation={navigation} />
                </Webline>
            )}
        </div>
    );
};
