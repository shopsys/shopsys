import { Icon } from 'components/Basic/Icon/Icon';
import { Image } from 'components/Basic/Image/Image';
import { ButtonsAction } from 'components/Blocks/Product/ButtonsAction/ButtonsAction';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ComparedProductFragmentApi, ListedProductFragmentApi } from 'graphql/generated';
import { onClickProductDetailGtmEventHandler } from 'helpers/gtm/eventHandlers';
import { useHandleCompare } from 'hooks/product/useHandleCompare';
import { useHandleCompareTable } from 'hooks/product/useHandleCompareTable';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';
import { twJoin } from 'tailwind-merge';
import { GtmListNameType } from 'types/gtm';

type ItemProps = {
    product: ComparedProductFragmentApi;
    productsCompareCount: number;
    listIndex: number;
};

export const HeadItem: FC<ItemProps> = (props) => {
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
        <th className="relative px-3 pb-3 sm:px-5 sm:pb-5" id="js-table-compare-product">
            <div className="flex w-[182px] flex-col sm:w-[211px]">
                {props.product.flags.length > 0 && (
                    <div className="absolute left-0 top-0 mt-7 flex flex-col items-start">
                        {props.product.flags.map((flag) => (
                            <div
                                className={twJoin(
                                    'mb-1 rounded-r py-1 px-2 text-xs font-medium uppercase',
                                    flag.name === 'Sleva' && 'flex flex-col items-center text-center',
                                )}
                                key={flag.uuid}
                                // TODO color: {flag.textColor}
                                style={{ backgroundColor: flag.rgbColor, color: 'black' }}
                            >
                                <span>{flag.name}</span>
                            </div>
                        ))}
                    </div>
                )}
                <div className="my-3 flex h-52 items-center justify-center">
                    <Image image={props.product.image} type="list" alt={props.product.fullName} />
                </div>
                <ButtonsAction
                    productUuid={props.product.uuid}
                    isMainVariant={props.product.__typename === 'MainVariant'}
                />
                <NextLink href={props.product.slug} passHref>
                    <a
                        className="text-primary no-underline hover:no-underline"
                        onClick={() => onProductDetailRedirectHandler(props.product, 'compare', props.listIndex)}
                    >
                        {props.product.fullName}
                    </a>
                </NextLink>
                <div
                    className="absolute top-1 right-1 flex h-10 w-10 cursor-pointer items-center justify-center rounded border border-greyLight bg-white transition-colors hover:bg-greyVeryLight"
                    onClick={() => {
                        handleProductInComparison();
                        calcMaxMarginLeft();
                    }}
                >
                    <Icon width={14} height={14} className="text-grey" iconType="icon" icon="Remove" />
                </div>
                <div className="flex justify-between">
                    <p className="text-xs">
                        {t('Code')}: {props.product.catalogNumber}
                    </p>
                    <ProductAction product={props.product} gtmListName="compare" listIndex={props.listIndex} />
                </div>
            </div>
        </th>
    );
};
