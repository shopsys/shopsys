import { getNavigationItems } from 'connectors/navigation/Navigation';
import NavigationItem from './NavigationItem';
import { NavigationStyled } from './Navigation.style';
import { ReactElement } from 'react';

const Navigation = (): ReactElement | null => {
    const testIdentifier = 'layout-header-navigation';

    const navigationItems = getNavigationItems();

    if (navigationItems.length === 0) {
        return null;
    }

    return (
        <NavigationStyled data-testid={testIdentifier}>
            {navigationItems.map((navigationItem, index) => (
                <NavigationItem navigationItem={navigationItem} key={index} />
            ))}
        </NavigationStyled>
    );
};

/* @component */
export default Navigation;
