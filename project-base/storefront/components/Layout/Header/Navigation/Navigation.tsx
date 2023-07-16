import { NavigationItem } from './NavigationItem';
import { useNavigationQueryApi } from 'graphql/generated';

const TEST_IDENTIFIER = 'layout-header-navigation';

export const Navigation: FC = () => {
    const [{ data: navigationData }] = useNavigationQueryApi();

    if (navigationData?.navigation === undefined || navigationData.navigation.length === 0) {
        return null;
    }

    return (
        <ul className="relative hidden w-full lg:block" data-testid={TEST_IDENTIFIER}>
            {navigationData.navigation.map((navigationItem, index) => (
                <NavigationItem navigationItem={navigationItem} key={index} />
            ))}
        </ul>
    );
};
