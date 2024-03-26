import { NavigationItem } from './NavigationItem';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';

export const Navigation: FC = () => {
    const [{ data: navigationData }] = useNavigationQuery();

    if (!navigationData?.navigation.length) {
        return null;
    }

    return (
        <ul className="relative hidden w-full lg:flex lg:gap-6 xl:gap-12">
            {navigationData.navigation.map((navigationItem, index) => (
                <NavigationItem key={index} navigationItem={navigationItem} />
            ))}
        </ul>
    );
};
