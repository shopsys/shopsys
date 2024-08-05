import { NavigationItem } from './NavigationItem';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { PageType } from 'store/slices/createPageLoadingStateSlice';

export type NavigationProps = {
    navigation: TypeCategoriesByColumnFragment[];
    skeletonType?: PageType;
};

export const Navigation: FC<NavigationProps> = ({ navigation, skeletonType }) => {
    return (
        <ul className="relative hidden w-full lg:flex">
            {navigation.map((navigationItem, index) => (
                <NavigationItem key={index} navigationItem={navigationItem} skeletonType={skeletonType} />
            ))}
        </ul>
    );
};
