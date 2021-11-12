import {
    ListItemBlockStyled,
    ListItemCountStyled,
    ListItemImageStyled,
    ListItemNameStyled,
    ListItemNameWrapperStyled,
} from './ListItem.style';
import { FC } from 'react';
import Image from 'components/Basic/Image';
import { ImageType } from 'components/Basic/Image/types';
import NextLink from 'next/link';

type ListItemProps = {
    listedItem: {
        slug: string;
        image: ImageType | null;
        name: string;
        products?: { totalCount: number };
    };
};

const ListItem: FC<ListItemProps> = (props) => {
    return (
        <NextLink href={props.listedItem.slug} passHref>
            <ListItemBlockStyled>
                <ListItemImageStyled>
                    <Image image={props.listedItem.image} alt={props.listedItem.name} />
                </ListItemImageStyled>
                <ListItemNameWrapperStyled>
                    <ListItemNameStyled>{props.listedItem.name}</ListItemNameStyled>
                    {props.listedItem.products?.totalCount !== undefined && (
                        <ListItemCountStyled>({props.listedItem.products.totalCount})</ListItemCountStyled>
                    )}
                </ListItemNameWrapperStyled>
            </ListItemBlockStyled>
        </NextLink>
    );
};

export default ListItem;
