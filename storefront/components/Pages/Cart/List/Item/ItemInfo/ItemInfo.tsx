import {
    AvailabilityMessageStyled,
    AvailabilityStyled,
    CodeStyled,
    NameStyled,
    NameTitleStyled,
    NameTitleTextStyled,
} from './ItemInfo.style';
import { CartItemType } from 'connectors/cart/types';
import { FC } from 'react';
import NextLink from 'next/link';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

type ItemInfoProps = {
    item: CartItemType;
};

const ItemInfo: FC<ItemInfoProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <NameStyled>
                <NextLink href={props.item.product.slug} passHref>
                    <NameTitleStyled>
                        <NameTitleTextStyled>{props.item.product.fullName}</NameTitleTextStyled>
                    </NameTitleStyled>
                </NextLink>
                <CodeStyled>
                    {t('Code')}: {props.item.product.catalogNumber}
                </CodeStyled>
            </NameStyled>
            <AvailabilityStyled>
                {props.item.product.availability}
                {props.item.product.availableStoresCount > 0 && (
                    <AvailabilityMessageStyled>
                        {t('(1)[or immediately in {{ count }} store];(2-inf)[or immediately in {{ count }} stores];', {
                            postProcess: 'interval',
                            count: props.item.product.availableStoresCount,
                        })}
                    </AvailabilityMessageStyled>
                )}
            </AvailabilityStyled>
        </>
    );
};

export default ItemInfo;
