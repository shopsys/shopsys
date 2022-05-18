import {
    NavigationSubListItemLinkStyled,
    NavigationSubListItemStyled,
    NavigationSubListStyled,
} from './NavigationSubList.style';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { useShopsysDispatch } from 'redux/main';
import { initialState, userActions } from 'redux/slices/user';
import { NavigationSubCategory } from 'types/navigation';

type NavigationSubListProps = {
    columnCategoryChildren: NavigationSubCategory[];
};

const NavigationSubList: FC<NavigationSubListProps> = (props) => {
    const testIdentifier = 'layout-header-navigation-navigationsublist';

    const dispatch = useShopsysDispatch();
    const router = useRouter();
    return (
        <NavigationSubListStyled data-testid={testIdentifier}>
            {props.columnCategoryChildren.map((columnCategoryChild, subListIndex) =>
                columnCategoryChild.slug === router.asPath ? (
                    <NavigationSubListItemStyled
                        key={subListIndex}
                        onClick={() => dispatch(userActions.setPagination({ ...initialState.pagination }))}
                        data-testid={testIdentifier + '-' + subListIndex}
                    >
                        <NextLink href={columnCategoryChild.slug} passHref>
                            <NavigationSubListItemLinkStyled>
                                {columnCategoryChild.name}
                            </NavigationSubListItemLinkStyled>
                        </NextLink>
                    </NavigationSubListItemStyled>
                ) : (
                    <NavigationSubListItemStyled key={subListIndex} data-testid={testIdentifier + '-' + subListIndex}>
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

/* @component */
export default NavigationSubList;
