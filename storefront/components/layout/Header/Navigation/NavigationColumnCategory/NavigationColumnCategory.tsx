import {
    NavigationItemSubListItemImageStyled,
    NavigationItemSubListItemLinkStyled,
    NavigationItemSubListItemStyled,
    NavigationItemSubListStyled,
} from './NavigationColumnCategory.style';
import { FC } from 'react';
import Link from 'next/link';
import { NavigationCategory } from '../../../../../connectors/navigation/Navigation';

type NavigationColumnCategoryProps = {
    columnCategory: NavigationCategory;
};

const NavigationColumnCategory: FC<NavigationColumnCategoryProps> = (props) => {
    return (
        <NavigationItemSubListItemStyled>
            <Link href={props.columnCategory.slug} passHref>
                <NavigationItemSubListItemImageStyled>
                    <img src={props.columnCategory.image.url} width={props.columnCategory.image.width} />
                </NavigationItemSubListItemImageStyled>
            </Link>
            <Link href={props.columnCategory.slug} passHref>
                <NavigationItemSubListItemLinkStyled>{props.columnCategory.name}</NavigationItemSubListItemLinkStyled>
            </Link>
            {props.columnCategory.children.length > 0 && (
                <NavigationItemSubListStyled isChildren>
                    {props.columnCategory.children.map((columnCategoryChild, index) => (
                        <NavigationItemSubListItemStyled isChildren key={index}>
                            <Link href={columnCategoryChild.slug} passHref>
                                <NavigationItemSubListItemLinkStyled isChildren>
                                    {columnCategoryChild.name}
                                </NavigationItemSubListItemLinkStyled>
                            </Link>
                        </NavigationItemSubListItemStyled>
                    ))}
                </NavigationItemSubListStyled>
            )}
        </NavigationItemSubListItemStyled>
    );
};

/* @component */
export default NavigationColumnCategory;
