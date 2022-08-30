import {
    AvailabilityMessageStyled,
    AvailabilityStyled,
    CodeStyled,
    NameStyled,
    NameTitleStyled,
    NameTitleTextStyled,
} from './CartListItem.style';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { CartItemType } from 'types/cart';

type CartListItemInfoProps = {
    item: CartItemType;
};

const TEST_IDENTIFIER = 'pages-cart-list-item-iteminfo-';

export const CartListItemInfo: FC<CartListItemInfoProps> = ({ item }) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <NameStyled data-testid={TEST_IDENTIFIER + 'name'}>
                <NextLink href={item.product.slug} passHref>
                    <NameTitleStyled>
                        <NameTitleTextStyled>{item.product.fullName}</NameTitleTextStyled>
                    </NameTitleStyled>
                </NextLink>
                <CodeStyled>
                    {t('Code')}: {item.product.catalogNumber}
                </CodeStyled>
            </NameStyled>
            <AvailabilityStyled data-testid={TEST_IDENTIFIER + 'availability'}>
                {item.product.availability.name}
                {item.product.availableStoresCount > 0 && (
                    <AvailabilityMessageStyled>
                        {t('or immediately in {{ count }} stores', {
                            count: item.product.availableStoresCount,
                        })}
                    </AvailabilityMessageStyled>
                )}
            </AvailabilityStyled>
        </>
    );
};
