import { getNavigationItems } from 'connectors/navigation/Navigation';
import NavigationItem from './NavigationItem';
import { NavigationStyled } from './Navigation.style';
import { ReactElement } from 'react';

const Navigation = (): ReactElement | null => {
    const navigationItems = getNavigationItems();

    if (navigationItems === undefined || (Array.isArray(navigationItems) && navigationItems.length === 0)) {
        return null;
    }

    return (
        <NavigationStyled>
            {navigationItems.map((navigationItem, index) => (
                <NavigationItem navigationItem={navigationItem} key={index} />
            ))}
        </NavigationStyled>
    );
};

/* @component */
export default Navigation;
