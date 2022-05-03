import {
    ListItemBlockStyled,
    ListItemCountStyled,
    ListItemImageStyled,
    ListItemNameStyled,
    ListItemNameWrapperStyled,
} from './ListItem.style';
import { FC } from 'react';
import Image from 'components/Basic/Image';
import { ListedItemPropType } from 'types/simpleNavigation';
import NextLink from 'next/link';

type ListItemProps = {
    listedItem: ListedItemPropType;
    imageType?: string;
};

const ListItem: FC<ListItemProps> = (props) => {
    const testIdentifier = 'blocks-simplenavigation-listitem';

    return (
        <NextLink href={props.listedItem.slug} passHref>
            <ListItemBlockStyled data-testid={testIdentifier}>
                {'image' in props.listedItem && (
                    <ListItemImageStyled>
                        <Image
                            image={props.listedItem.image}
                            type={props.imageType ?? 'default'}
                            alt={props.listedItem.name}
                        />
                    </ListItemImageStyled>
                )}
                <ListItemNameWrapperStyled>
                    <ListItemNameStyled>{props.listedItem.name}</ListItemNameStyled>
                    {'totalCount' in props.listedItem && props.listedItem.totalCount !== undefined && (
                        <ListItemCountStyled>({props.listedItem.totalCount})</ListItemCountStyled>
                    )}
                </ListItemNameWrapperStyled>
            </ListItemBlockStyled>
        </NextLink>
    );
};

export default ListItem;
