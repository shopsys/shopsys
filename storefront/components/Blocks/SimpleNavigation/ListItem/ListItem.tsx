import {
    ListItemBlockStyled,
    ListItemCountStyled,
    ListItemImageStyled,
    ListItemNameStyled,
    ListItemNameWrapperStyled,
} from './ListItem.style';
import { Image } from 'components/Basic/Image/Image';
import NextLink from 'next/link';
import { FC } from 'react';
import { ListedItemPropType } from 'types/simpleNavigation';

type ListItemProps = {
    listedItem: ListedItemPropType;
    imageType?: string;
};

const TEST_IDENTIFIER = 'blocks-simplenavigation-listitem';

export const ListItem: FC<ListItemProps> = ({ listedItem, imageType }) => {
    return (
        <NextLink href={listedItem.slug} passHref>
            <ListItemBlockStyled data-testid={TEST_IDENTIFIER}>
                {'image' in listedItem && (
                    <ListItemImageStyled>
                        <Image image={listedItem.image} type={imageType ?? 'default'} alt={listedItem.name} />
                    </ListItemImageStyled>
                )}
                <ListItemNameWrapperStyled>
                    <ListItemNameStyled>{listedItem.name}</ListItemNameStyled>
                    {'totalCount' in listedItem && listedItem.totalCount !== undefined && (
                        <ListItemCountStyled>({listedItem.totalCount})</ListItemCountStyled>
                    )}
                </ListItemNameWrapperStyled>
            </ListItemBlockStyled>
        </NextLink>
    );
};
