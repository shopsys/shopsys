import {
    AvailabilityMessageStyled,
    AvailabilityStyled,
    CodeStyled,
    NameStyled,
    NameTitleStyled,
    NameTitleTextStyled,
} from './ItemInfo.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { CartItemType } from 'types/cart';

type ItemInfoProps = {
    item: CartItemType;
};

const ItemInfo: FC<ItemInfoProps> = (props) => {
    const testIdentifier = 'pages-cart-list-item-iteminfo-';

    const t = useTypedTranslationFunction();

    return (
        <>
            <NameStyled data-testid={testIdentifier + 'name'}>
                <NextLink href={props.item.product.slug} passHref>
                    <NameTitleStyled>
                        <NameTitleTextStyled>{props.item.product.fullName}</NameTitleTextStyled>
                    </NameTitleStyled>
                </NextLink>
                <CodeStyled>
                    {t('Code')}: {props.item.product.catalogNumber}
                </CodeStyled>
            </NameStyled>
            <AvailabilityStyled data-testid={testIdentifier + 'availability'}>
                {props.item.product.availability}
                {props.item.product.availableStoresCount > 0 && (
                    <AvailabilityMessageStyled>
                        {t('or immediately in {{ count }} stores', {
                            count: props.item.product.availableStoresCount,
                        })}
                    </AvailabilityMessageStyled>
                )}
            </AvailabilityStyled>
        </>
    );
};

export default ItemInfo;
