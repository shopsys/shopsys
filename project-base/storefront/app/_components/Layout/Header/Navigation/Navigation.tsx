import { NavigationList } from './NavigationList';
import { getNavitagionQuery } from 'app/_queries/getNavitagionQuery';

export const Navigation = async () => {
    const navigationData = await getNavitagionQuery();

    if (!navigationData) {
        return null;
    }

    return <NavigationList navigation={navigationData.navigation} />;
};
