import { NavigationItem } from './NavigationItem';
import { useNavigationQueryApi } from 'graphql/generated';

const TEST_IDENTIFIER = 'layout-header-navigation';

export const Navigation: FC = () => {
    const [{ data: navigationData }] = useNavigationQueryApi();

    if (!navigationData?.navigation.length) {
        return null;
    }

    return (
        <ul className="relative hidden w-full lg:flex lg:gap-6 xl:gap-12" data-testid={TEST_IDENTIFIER}>
            {navigationData.navigation.map((navigationItem, index) => (
                <NavigationItem navigationItem={navigationItem} key={index} />
            ))}
        </ul>
    );
};
