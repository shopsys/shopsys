import { NavigationList } from './NavigationList';
import { getNavigationQuery } from 'app/_queries/getNavigationQuery';

export const Navigation = async () => {
    const navigationData = await getNavigationQuery();

    if (!navigationData) {
        return null;
    }

    return <NavigationList navigation={navigationData} />;
};
