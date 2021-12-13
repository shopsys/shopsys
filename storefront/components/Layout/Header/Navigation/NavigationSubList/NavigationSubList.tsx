import { initialState, userActions } from 'redux/slices/user';
import {
    NavigationSubListItemLinkStyled,
    NavigationSubListItemStyled,
    NavigationSubListStyled,
} from './NavigationSubList.style';
import { FC } from 'react';
import { NavigationSubCategory } from 'connectors/navigation/Navigation';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { useShopsysDispatch } from 'redux/main';

type NavigationSubListProps = {
    columnCategoryChildren: NavigationSubCategory[];
};

const NavigationSubList: FC<NavigationSubListProps> = (props) => {
    const dispatch = useShopsysDispatch();
    const router = useRouter();
    return (
        <NavigationSubListStyled>
            {props.columnCategoryChildren.map((columnCategoryChild, subListIndex) =>
                columnCategoryChild.slug === router.asPath ? (
                    <NavigationSubListItemStyled
                        key={subListIndex}
                        onClick={() => dispatch(userActions.setPagination({ ...initialState.pagination }))}
                    >
                        <NextLink href={columnCategoryChild.slug} passHref>
                            <NavigationSubListItemLinkStyled>
                                {columnCategoryChild.name}
                            </NavigationSubListItemLinkStyled>
                        </NextLink>
                    </NavigationSubListItemStyled>
                ) : (
                    <NavigationSubListItemStyled key={subListIndex}>
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
