import {
    NavigationSubListItemLinkStyled,
    NavigationSubListItemStyled,
    NavigationSubListStyled,
} from './NavigationSubList.style';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { NavigationSubCategory } from 'types/navigation';

type NavigationSubListProps = {
    columnCategoryChildren: NavigationSubCategory[];
};

const TEST_IDENTIFIER = 'layout-header-navigation-navigationsublist';

export const NavigationSubList: FC<NavigationSubListProps> = ({ columnCategoryChildren }) => {
    const router = useRouter();

    return (
        <NavigationSubListStyled data-testid={TEST_IDENTIFIER}>
            {columnCategoryChildren.map((columnCategoryChild, subListIndex) =>
                columnCategoryChild.slug === router.asPath ? (
                    <NavigationSubListItemStyled key={subListIndex} data-testid={TEST_IDENTIFIER + '-' + subListIndex}>
                        <NextLink href={columnCategoryChild.slug} passHref>
                            <NavigationSubListItemLinkStyled>
                                {columnCategoryChild.name}
                            </NavigationSubListItemLinkStyled>
                        </NextLink>
                    </NavigationSubListItemStyled>
                ) : (
                    <NavigationSubListItemStyled key={subListIndex} data-testid={TEST_IDENTIFIER + '-' + subListIndex}>
                        <NextLink href={columnCategoryChild.slug} passHref>
                            <NavigationSubListItemLinkStyled>
                                {columnCategoryChild.name}
                            </NavigationSubListItemLinkStyled>
                        </NextLink>
                    </NavigationSubListItemStyled>
                ),
            )}
        </NavigationSubListStyled>
    );
};
