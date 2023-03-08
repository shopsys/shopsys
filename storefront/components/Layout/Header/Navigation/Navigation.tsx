import { NavigationItem } from './NavigationItem/NavigationItem';
import { useNavigationItems } from 'connectors/navigation/Navigation';

const TEST_IDENTIFIER = 'layout-header-navigation';

export const Navigation: FC = () => {
    const navigationItems = useNavigationItems();

    if (navigationItems.length === 0) {
        return null;
    }

    return (
        <ul className="relative hidden w-full lg:block" data-testid={TEST_IDENTIFIER}>
            {navigationItems.map((navigationItem, index) => (
                <NavigationItem navigationItem={navigationItem} key={index} />
            ))}
        </ul>
    );
};
