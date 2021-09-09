import {
    NavigationSubListItemLinkStyled,
    NavigationSubListItemStyled,
    NavigationSubListStyled,
} from './NavigationSubList.style';
import { FC } from 'react';
import { NavigationSubCategory } from '../../../../../connectors/navigation/Navigation';
import NextLink from 'next/link';

type NavigationSubListProps = {
    columnCategoryChildren: NavigationSubCategory[];
};

const NavigationSubList: FC<NavigationSubListProps> = (props) => {
    return (
        <NavigationSubListStyled>
            {props.columnCategoryChildren.map((columnCategoryChild, subListIndex) => (
                <NavigationSubListItemStyled key={subListIndex}>
                    <NextLink href={columnCategoryChild.slug} passHref>
                        <NavigationSubListItemLinkStyled>{columnCategoryChild.name}</NavigationSubListItemLinkStyled>
                    </NextLink>
                </NavigationSubListItemStyled>
            ))}
        </NavigationSubListStyled>
    );
};

/* @component */
export default NavigationSubList;
