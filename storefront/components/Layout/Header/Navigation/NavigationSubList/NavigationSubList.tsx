import {
    NavigationSubListItemLinkStyled,
    NavigationSubListItemStyled,
    NavigationSubListStyled,
} from './NavigationSubList.style';
import { FC } from 'react';
import Link from 'next/link';
import { NavigationSubCategory } from '../../../../../connectors/navigation/Navigation';

type NavigationSubListProps = {
    columnCategoryChildren: NavigationSubCategory[];
};

const NavigationSubList: FC<NavigationSubListProps> = (props) => {
    return (
        <NavigationSubListStyled>
            {props.columnCategoryChildren.map((columnCategoryChild, subListIndex) => (
                <NavigationSubListItemStyled key={subListIndex}>
                    <Link href={columnCategoryChild.slug} passHref>
                        <NavigationSubListItemLinkStyled>{columnCategoryChild.name}</NavigationSubListItemLinkStyled>
                    </Link>
                </NavigationSubListItemStyled>
            ))}
        </NavigationSubListStyled>
    );
};

/* @component */
export default NavigationSubList;
