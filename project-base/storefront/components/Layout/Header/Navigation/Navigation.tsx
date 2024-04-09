import { NavigationItem } from './NavigationItem';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';

export type NavigationProps = {
    navigation: TypeCategoriesByColumnFragment[];
};

export const Navigation: FC<NavigationProps> = ({ navigation }) => {
    return (
        <ul className="relative hidden w-full lg:flex lg:gap-6 xl:gap-12">
            {navigation.map((navigationItem, index) => (
                <NavigationItem key={index} navigationItem={navigationItem} />
            ))}
        </ul>
    );
};
