import { NavigationSubCategoriesLinkFragmentApi } from 'graphql/generated';
import NextLink from 'next/link';

type NavigationSubListProps = {
    columnCategoryChildren: NavigationSubCategoriesLinkFragmentApi['children'];
};

const TEST_IDENTIFIER = 'layout-header-navigation-navigationsublist';

export const NavigationSubList: FC<NavigationSubListProps> = ({ columnCategoryChildren }) => (
    <ul className="flex w-full flex-col pl-0" data-testid={TEST_IDENTIFIER}>
        {columnCategoryChildren.map((columnCategoryChild, subListIndex) => (
            <li className="w-full" key={subListIndex} data-testid={TEST_IDENTIFIER + '-' + subListIndex}>
                <NextLink href={columnCategoryChild.slug} passHref>
                    <a className="mb-1 block text-sm text-dark no-underline">{columnCategoryChild.name}</a>
                </NextLink>
            </li>
        ))}
    </ul>
);
