import { BlogArticleDetailFragmentApi, SliderProductFragmentApi } from 'graphql/generated';
import { BlogArticleDetailType } from 'components/Pages/BlogArticle/types';
import { DomainConfigType } from 'utils/Domain/Domain';
import { mapImageApiData } from 'connectors/image/Image';
import { mapProductPriceApiData } from 'connectors/products/Products';
import { SliderProductItemType } from 'components/Blocks/Product/types';

export const mapBlogArticleDetailApiData = (
    apiData: BlogArticleDetailFragmentApi,
    currentDomainConfig: DomainConfigType,
): BlogArticleDetailType => {
    const mapArticleProductSlider = (
        blogArticleProducts: SliderProductFragmentApi[],

        currencyCode: string,
    ): SliderProductItemType[] => {
        return blogArticleProducts.map((blogArticleProduct) => {
            return {
                ...blogArticleProduct,
                __typename: apiData.__typename === undefined ? undefined : apiData.__typename,
                image:
                    blogArticleProduct.images[0]?.sizes[0] === null ||
                    blogArticleProduct.images[0]?.sizes[0] === undefined
                        ? null
                        : {
                              ...blogArticleProduct.images[0]?.sizes[0],
                              width:
                                  blogArticleProduct.images[0]?.sizes[0].width !== undefined &&
                                  blogArticleProduct.images[0]?.sizes[0].width !== null
                                      ? blogArticleProduct.images[0]?.sizes[0].width
                                      : 0,
                              height:
                                  blogArticleProduct.images[0]?.sizes[0].height !== undefined &&
                                  blogArticleProduct.images[0]?.sizes[0].height !== null
                                      ? blogArticleProduct.images[0]?.sizes[0].height
                                      : 0,
                          },
                price: mapProductPriceApiData(blogArticleProduct.price, currencyCode),
                isMainVariant: blogArticleProduct.__typename === 'MainVariant',
                availability: blogArticleProduct.availability.name,
                slug: blogArticleProduct.slug,
            };
        });
    };

    return {
        ...apiData,
        __typename: 'BlogArticle',
        text: apiData.text !== undefined ? apiData.text : null,
        blogArticleProducts: mapArticleProductSlider(apiData.blogArticleProducts, currentDomainConfig.currencyCode),
        publishDate: new Date(apiData.publishDate).toLocaleDateString(currentDomainConfig.defaultLocale),
        slug: apiData.slug,
        image: mapImageApiData([apiData.image]),
    };
};
