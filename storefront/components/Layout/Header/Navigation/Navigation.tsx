import { NavigationStyled } from './Navigation.style';
import { NavigationItem } from './NavigationItem/NavigationItem';
import { useNavigationItems } from 'connectors/navigation/Navigation';
import { FC } from 'react';

const TEST_IDENTIFIER = 'layout-header-navigation';

export const Navigation: FC = () => {
    const navigationItems = useNavigationItems();

    if (navigationItems.length === 0) {
        return null;
    }

    return (
        <NavigationStyled data-testid={TEST_IDENTIFIER}>
            {navigationItems.map((navigationItem, index) => (
                <NavigationItem navigationItem={navigationItem} key={index} />
            ))}
        </NavigationStyled>
    );
};
