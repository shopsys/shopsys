import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import dynamic from 'next/dynamic';

const TEST_IDENTIFIER = 'layout-header-menuiconic-login';

const MenuIconicItemUserAuthenticated = dynamic(() =>
    import('components/Layout/Header/MenuIconic/MenuIconicItemUserAuthenticated').then(
        (component) => component.MenuIconicItemUserAuthenticated,
    ),
);

const MenuIconicItemUserUnauthenticated = dynamic(() =>
    import('components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticated').then(
        (component) => component.MenuIconicItemUserUnauthenticated,
    ),
);

export const MenuIconicItemUser: FC = () => {
    const { isUserLoggedIn } = useCurrentUserData();

    return isUserLoggedIn ? (
        <MenuIconicItemUserAuthenticated dataTestId={TEST_IDENTIFIER} />
    ) : (
        <MenuIconicItemUserUnauthenticated dataTestId={TEST_IDENTIFIER} />
    );
};
