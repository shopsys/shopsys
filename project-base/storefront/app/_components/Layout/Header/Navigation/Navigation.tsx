import { NavigationList } from './NavigationList';
import getNavitagionQuery from 'app/_queries/getNavitagionQuery';

export default async function Navigation() {
    const navigationData = await getNavitagionQuery();

    if (!navigationData) {
        return null;
    }

    return <NavigationList navigation={navigationData.navigation} />;
}
