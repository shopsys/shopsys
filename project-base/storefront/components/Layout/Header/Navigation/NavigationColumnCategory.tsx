import { Image } from 'components/Basic/Image/Image';
import { NavigationSubList } from 'components/Layout/Header/Navigation/NavigationSubList';
import { ColumnCategoryFragmentApi } from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import NextLink from 'next/link';

type NavigationColumnCategoryProps = {
    columnCategory: ColumnCategoryFragmentApi;
};

const TEST_IDENTIFIER = 'layout-header-navigation-navigationcolumncategory';

export const NavigationColumnCategory: FC<NavigationColumnCategoryProps> = ({ columnCategory }) => {
    const columnImage = getFirstImageOrNull(columnCategory.images);

    return (
        <li className="mb-9 w-full last:mb-0" data-testid={TEST_IDENTIFIER}>
            <NextLink href={columnCategory.slug} passHref>
                <a className="mb-4 flex h-16 justify-center rounded-xl bg-dark bg-opacity-5 p-2">
                    <Image
                        image={columnImage}
                        type="default"
                        alt={columnImage?.name || columnCategory.name}
                        className="mix-blend-multiply"
                    />
                </a>
            </NextLink>
            <NextLink href={columnCategory.slug} passHref>
                <a className="mb-1 block font-bold text-dark no-underline">{columnCategory.name}</a>
            </NextLink>
            {columnCategory.children.length > 0 && (
                <NavigationSubList columnCategoryChildren={columnCategory.children} />
            )}
        </li>
    );
};
