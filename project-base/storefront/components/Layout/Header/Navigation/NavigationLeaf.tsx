import { NavigationColumnCategory } from 'components/Layout/Header/Navigation/NavigationColumnCategory';
import { ColumnCategoriesFragmentApi } from 'graphql/requests/navigation/fragments/ColumnCategoriesFragment.generated';
type NavigationLeafProps = {
    columnCategories: ColumnCategoriesFragmentApi[];
};

const TEST_IDENTIFIER = 'layout-header-navigation-navigationleaf-';

export const NavigationLeaf: FC<NavigationLeafProps> = ({ columnCategories }) => (
    <>
        {columnCategories.map((columnCategories, columnIndex) => (
            <ul className="flex w-1/4 flex-col pl-11" key={columnIndex} data-testid={TEST_IDENTIFIER + columnIndex}>
                {columnCategories.categories.map((columnCategory, columnCategoryIndex) => (
                    <NavigationColumnCategory key={columnCategoryIndex} columnCategory={columnCategory} />
                ))}
            </ul>
        ))}
    </>
);
