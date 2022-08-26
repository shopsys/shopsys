import { NavigationStyled } from './Navigation.style';
import { NavigationItem } from './NavigationItem/NavigationItem';
import { useNavigationItems } from 'connectors/navigation/Navigation';
import { FC } from 'react';

export const Navigation: FC = () => {
    const testIdentifier = 'layout-header-navigation';

    const navigationItems = useNavigationItems();

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
