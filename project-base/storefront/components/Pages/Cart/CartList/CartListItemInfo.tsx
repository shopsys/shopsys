import { CartItemFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';

type CartListItemInfoProps = {
    item: CartItemFragmentApi;
};

const TEST_IDENTIFIER = 'pages-cart-list-item-iteminfo-';

export const CartListItemInfo: FC<CartListItemInfoProps> = ({ item }) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <div className="h-full pr-4 text-left vl:w-[16.875rem]" data-testid={TEST_IDENTIFIER + 'name'}>
                <NextLink href={item.product.slug} passHref>
                    <a className="text-sm font-bold uppercase leading-4 text-dark no-underline hover:text-dark hover:no-underline">
                        <span className="mr-5">{item.product.fullName}</span>
                    </a>
                </NextLink>
                <div className="text-sm text-greyLight">
                    {t('Code')}: {item.product.catalogNumber}
                </div>
            </div>
            <div className="block flex-1 vl:text-center" data-testid={TEST_IDENTIFIER + 'availability'}>
                {item.product.availability.name}
                {item.product.availableStoresCount > 0 && (
                    <span className="ml-1 inline font-normal vl:ml-0 vl:block">
                        {t('or immediately in {{ count }} stores', {
                            count: item.product.availableStoresCount,
                        })}
                    </span>
                )}
            </div>
        </>
    );
};
