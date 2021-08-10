import { ProductItemApiType, ProductItemType } from '../../components/blocks/product/types';
import { useFetchQuery } from '../../hooks/UseFetchQuery';
import { useShopsysSelector } from '../../redux/store';

export const promotedProductsQuery = `
        query promotedProducts {
            promotedProducts {
                __typename
                slug
                name
                flags {
                    name
                    rgbColor
                }
                images (size: "list") {
                    url
                    width
                    height
                }
                availability {
                    name
                }
                price {
                    priceWithVat
                    priceWithoutVat
                    vatAmount
                    isPriceFrom
                }
                availableStoresCount
                exposedStoresCount
            }
        }
    ` as const;

const mapProductApiData = (apiData: ProductItemApiType[], currencyCode: string) => {
    return apiData.map((apiProduct) => {
        return {
            ...apiProduct,
            detailSlug: apiProduct.slug,
            image: apiProduct.images.length === 0 ? null : apiProduct.images[0],
            price: {
                ...apiProduct.price,
                currencyCode,
            },
            isMainVariant: apiProduct.__typename === 'MainVariant',
            availability: apiProduct.availability.name,
        };
    });
};

export const getPromotedProducts = (): ProductItemType[] | undefined => {
    const result = useFetchQuery({ query: promotedProductsQuery });
    const currentDomainConfig = useShopsysSelector((state) => state.domain);
    const apiData = result?.data?.promotedProducts;
    if (apiData === undefined) {
        return undefined;
    }

    return mapProductApiData(apiData, currentDomainConfig.currencyCode);
};
