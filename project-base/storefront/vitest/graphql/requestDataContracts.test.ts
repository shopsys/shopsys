import { type DocumentNode, print } from 'graphql';
import { AdvertsQueryDocument } from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import { BlogArticlesQueryDocument } from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.generated';
import { BlogCategoryArticlesDocument } from 'graphql/requests/blogCategories/queries/BlogCategoryArticlesQuery.generated';
import { CartQueryDocument } from 'graphql/requests/cart/queries/CartQuery.generated';
import { CatalogCategoriesQueryDocument } from 'graphql/requests/categories/queries/CatalogCategoriesQuery.generated';
import { CreateComplaintDocument } from 'graphql/requests/complaints/mutations/CreateComplaintMutation.generated';
import { ComplaintQueryDocument } from 'graphql/requests/complaints/queries/ComplaintQuery.generated';
import { PersonalDataDetailQueryDocument } from 'graphql/requests/personalData/queries/PersonalDataDetailQuery.generated';
import { CreateProductReviewMutationDocument } from 'graphql/requests/productReviews/mutations/CreateProductReviewMutation.generated';
import { CurrentCustomerUserProductFamilyReviewsQueryDocument } from 'graphql/requests/productReviews/queries/CurrentCustomerUserProductFamilyReviewsQuery.generated';
import { ProductDetailQueryDocument } from 'graphql/requests/products/queries/ProductDetailQuery.generated';
import { SearchProductsQueryDocument } from 'graphql/requests/search/queries/SearchProductsQuery.generated';
import { SearchQueryDocument } from 'graphql/requests/search/queries/SearchQuery.generated';
import { StoresQueryDocument } from 'graphql/requests/stores/queries/StoresQuery.generated';
import { describe, expect, test } from 'vitest';
import { getFields, getSelectionAt } from 'vitest/helpers/graphqlSelections';

const fieldsAt = (document: DocumentNode, path: string[]) =>
    Array.from(new Set(getFields(document, getSelectionAt(document, path)).map((field) => field.name.value))).sort();

describe('operation-owned data contracts', () => {
    test.each([
        'noLongerListableCartItems',
        'cartItemsWithModifiedPrice',
        'cartItemsWithChangedQuantity',
        'cartItemsWithRemovedAdditionalServices',
        'cartItemsWithModifiedAdditionalServicePrices',
    ])('%s only fetches message identity/name while live cart items retain complete data', (message) => {
        const path = ['cart', 'modifications', 'itemModifications', message];
        expect(fieldsAt(CartQueryDocument, path)).toEqual(['__typename', 'product', 'uuid']);
        expect(fieldsAt(CartQueryDocument, [...path, 'product'])).toEqual(['__typename', 'fullName', 'uuid']);
        expect(fieldsAt(CartQueryDocument, ['cart', 'items'])).toEqual(
            expect.arrayContaining(['quantity', 'additionalServices', 'product']),
        );
    });

    test('omits targeting metadata, unused store images and unused gift pricing', () => {
        const adverts = fieldsAt(AdvertsQueryDocument, ['adverts']);
        expect(adverts).not.toContain('type');
        expect(adverts).not.toContain('categories');
        expect(fieldsAt(StoresQueryDocument, ['stores', 'edges', 'node'])).not.toContain('mainImage');
        expect(fieldsAt(ProductDetailQueryDocument, ['product', 'gifts'])).not.toContain('giftPrice');
        expect(fieldsAt(CreateProductReviewMutationDocument, ['CreateProductReview'])).toEqual(['__typename', 'uuid']);
    });

    test('full search operations keep identical root arguments for their shared result selection', () => {
        const searchField = (document: DocumentNode) =>
            getFields(document, getSelectionAt(document, [])).find((field) => field.name.value === 'productsSearch');
        expect(searchField(SearchQueryDocument)?.arguments?.map(print)).toEqual(
            searchField(SearchProductsQueryDocument)?.arguments?.map(print),
        );
    });

    test('complaints fetch the order identity and required item data, not the complete order', () => {
        expect(fieldsAt(ComplaintQueryDocument, ['complaint', 'order'])).toEqual([
            '__typename',
            'customerUser',
            'number',
            'uuid',
        ]);
        expect(fieldsAt(ComplaintQueryDocument, ['complaint', 'items', 'orderItem'])).toEqual([
            '__typename',
            'relatedItems',
            'totalPrice',
            'unit',
            'uuid',
        ]);
        expect(fieldsAt(CreateComplaintDocument, ['CreateComplaint'])).toEqual(['uuid']);
    });

    test('personal data keeps export fields without full order item and customer account graphs', () => {
        expect(fieldsAt(PersonalDataDetailQueryDocument, ['accessPersonalData', 'orders', 'items'])).toEqual([
            '__typename',
            'name',
            'type',
            'uuid',
        ]);
        expect(fieldsAt(PersonalDataDetailQueryDocument, ['accessPersonalData', 'orders', 'productItems'])).toEqual([
            '__typename',
            'quantity',
            'uuid',
        ]);
        expect(fieldsAt(PersonalDataDetailQueryDocument, ['accessPersonalData', 'customerUser'])).not.toEqual(
            expect.arrayContaining(['pricingGroup', 'roles']),
        );
    });

    test('catalog links do not resolve product counts', () => {
        expect(fieldsAt(CatalogCategoriesQueryDocument, ['categories'])).not.toContain('products');
    });

    test('product-family reviews keep moderation identity without account-page product details', () => {
        const fields = fieldsAt(CurrentCustomerUserProductFamilyReviewsQueryDocument, [
            'currentCustomerUserProductReviews',
            'edges',
            'node',
        ]);
        expect(fields).toEqual(expect.arrayContaining(['uuid', 'productUuid', 'status', 'rating', 'text', 'images']));
        expect(fields).not.toContain('product');
        expect(fields).not.toContain('rejectionReason');
    });

    test('blog homepage omits pagination while category retains next-page information', () => {
        expect(fieldsAt(BlogArticlesQueryDocument, ['blogArticles'])).not.toContain('pageInfo');
        expect(fieldsAt(BlogCategoryArticlesDocument, ['blogCategory', 'blogArticles', 'pageInfo'])).toEqual([
            'hasNextPage',
        ]);
    });
});
