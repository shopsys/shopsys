import { NavigationItemColumnCategory } from 'components/Layout/Header/Navigation/NavigationItemColumnCategory';
import { ColumnCategoriesFragmentApi } from 'graphql/generated';

type NavigationItemColumnProps = {
    columnCategories: ColumnCategoriesFragmentApi[];
};

export const NavigationItemColumn: FC<NavigationItemColumnProps> = ({ columnCategories }) => (
    <>
        {columnCategories.map((columnCategories, columnIndex) => (
            <ul className="flex flex-col gap-9" key={columnIndex}>
                {columnCategories.categories.map((columnCategory, columnCategoryIndex) => (
                    <NavigationItemColumnCategory key={columnCategoryIndex} columnCategory={columnCategory} />
                ))}
            </ul>
        ))}
    </>
);
