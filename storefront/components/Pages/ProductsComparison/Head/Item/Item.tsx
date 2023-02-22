import {
    ItemCatnumStyled,
    ItemFlagsStyled,
    ItemFlagStyled,
    ItemImageStyled,
    ItemNameStyled,
    ItemStyled,
} from './Item.style';
import { ItemInStyled } from './Item.style';
import { ItemRemoveStyled } from './Item.style';
import { ItemRemoveIconStyled } from './Item.style';
import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/Action/ProductAction';
import { ButtonsAction } from 'components/Blocks/Product/ButtonsAction/ButtonsAction';
import { ComparedProductFragmentApi, ListedProductFragmentApi } from 'graphql/generated';
import { onClickProductDetailGtmEventHandler } from 'helpers/gtm/eventHandlers';
import { useHandleCompare } from 'hooks/product/useHandleCompare';
import { useHandleCompareTable } from 'hooks/product/useHandleCompareTable';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { FC, useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';
import { GtmListNameType } from 'types/gtm';

type ItemProps = {
    product: ComparedProductFragmentApi;
    productsCompareCount: number;
    listIndex: number;
};

const Item: FC<ItemProps> = (props) => {
    const t = useTypedTranslationFunction();
    const { url } = useShopsysSelector((state) => state.domain);
    const { handleProductInComparison } = useHandleCompare(props.product.uuid);
    const { calcMaxMarginLeft } = useHandleCompareTable(props.productsCompareCount);

    const onProductDetailRedirectHandler = useCallback(
        async (product: ListedProductFragmentApi, listName: GtmListNameType, index: number) => {
            await onClickProductDetailGtmEventHandler(product, listName, index, url);
        },
        [url],
    );

    return (
        <ItemStyled id="js-table-compare-product">
            <ItemInStyled>
                {props.product.flags.length > 0 && (
                    <ItemFlagsStyled>
                        {props.product.flags.map((flag) => {
                            return (
                                <ItemFlagStyled
                                    className={flag.name === 'Sleva' ? 'isDiscount' : undefined}
                                    key={flag.uuid}
                                    // textColor={flag.textColor} //TODO
                                    textColor="black"
                                    rgbColor={flag.rgbColor}
                                >
                                    <span>{flag.name}</span>
                                </ItemFlagStyled>
                            );
                        })}
                    </ItemFlagsStyled>
                )}
                <ItemImageStyled>
                    <Image image={props.product.image} type="list" alt={props.product.fullName} />
                </ItemImageStyled>
                <ButtonsAction
                    productUuid={props.product.uuid}
                    isMainVariant={props.product.__typename === 'MainVariant'}
                />
                <NextLink href={props.product.slug} passHref>
                    <ItemNameStyled
                        onClick={() => onProductDetailRedirectHandler(props.product, 'compare', props.listIndex)}
                    >
                        {props.product.fullName}
                    </ItemNameStyled>
                </NextLink>
                <ItemRemoveStyled
                    onClick={() => {
                        handleProductInComparison();
                        calcMaxMarginLeft();
                    }}
                >
                    <ItemRemoveIconStyled iconType="icon" icon="Remove" />
                </ItemRemoveStyled>
                <div className="flex justify-between">
                    <ItemCatnumStyled>
                        {t('Code')}: {props.product.catalogNumber}
                    </ItemCatnumStyled>
                    <ProductAction product={props.product} gtmListName="compare" listIndex={props.listIndex} />
                </div>
            </ItemInStyled>
        </ItemStyled>
    );
};

export default Item;
