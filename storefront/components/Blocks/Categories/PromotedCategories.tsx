import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { usePromotedCategories } from 'connectors/categories/PromotedCategories';

export const PromotedCategories: FC = () => {
    const promotedCategories = usePromotedCategories();

    if (promotedCategories === undefined) {
        return null;
    }

    return <SimpleNavigation listedItems={promotedCategories} className="mb-6" />;
};
