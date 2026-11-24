import { fireEvent, screen } from '@testing-library/react';
import { PRODUCT_VARIANTS_ID } from 'components/Blocks/Product/productActionConstants';
import { ProductDetailSecondaryActions } from 'components/Pages/ProductDetail/ProductDetailSecondaryActions/ProductDetailSecondaryActions';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { createProduct } from '../../helpers/productFixture';
import { renderWithTooltipProvider as render } from '../../helpers/renderWithTooltipProvider';

const openVariantsPopup = vi.hoisted(() => vi.fn());
vi.mock('components/Blocks/Popup/useOpenProductVariantsPopup', () => ({
    useOpenProductVariantsPopup: () => openVariantsPopup,
}));

const lists = vi.hoisted(() => ({
    toggleComparison: vi.fn(),
    toggleWishlist: vi.fn(),
    comparison: new Set<string>(),
    comparisonFamilies: new Set<string>(),
    wishlist: new Set<string>(),
    wishlistFamilies: new Set<string>(),
}));

vi.mock('utils/productLists/comparison/useComparison', () => ({
    useComparison: () => ({
        isProductInComparison: (uuid: string) => lists.comparison.has(uuid),
        isProductOrItsVariantInComparison: (uuid: string) =>
            lists.comparison.has(uuid) || lists.comparisonFamilies.has(uuid),
        toggleProductInComparison: lists.toggleComparison,
    }),
}));
vi.mock('utils/productLists/wishlist/useWishlist', () => ({
    useWishlist: () => ({
        isProductInWishlist: (uuid: string) => lists.wishlist.has(uuid),
        isProductOrItsVariantInWishlist: (uuid: string) => lists.wishlist.has(uuid) || lists.wishlistFamilies.has(uuid),
        toggleProductInWishlist: lists.toggleWishlist,
    }),
}));
vi.mock('components/Blocks/Product/ButtonsAction/ProductQuestionButton', () => ({
    ProductQuestionButton: () => null,
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: { productName?: string }) =>
            key.replace('{{ productName }}', options?.productName ?? ''),
    }),
}));

const mainVariant = {
    ...createProduct('tv'),
    __typename: 'MainVariant',
    fullName: 'Television',
} as unknown as TypeMainVariantDetailFragment;

describe('ProductDetailSecondaryActions', () => {
    let variantList: HTMLUListElement;
    let scrollIntoView: ReturnType<typeof vi.fn<HTMLElement['scrollIntoView']>>;

    beforeEach(() => {
        vi.clearAllMocks();
        for (const list of Object.values(lists)) {
            if (list instanceof Set) {
                list.clear();
            }
        }
        window.matchMedia = vi.fn().mockReturnValue({ matches: false }) as unknown as typeof window.matchMedia;
        variantList = document.createElement('ul');
        variantList.id = PRODUCT_VARIANTS_ID;
        variantList.tabIndex = -1;
        scrollIntoView = vi.fn<HTMLElement['scrollIntoView']>();
        variantList.scrollIntoView = scrollIntoView;
        document.body.append(variantList);
    });

    afterEach(() => {
        variantList.remove();
    });

    test('highlights a main variant with a listed variant and leads to the variants instead of saving the family', () => {
        lists.comparisonFamilies.add('tv');
        lists.wishlistFamilies.add('tv');
        render(<ProductDetailSecondaryActions product={mainVariant} />);

        const compareButton = screen.getByRole('button', { name: 'Choose a variant of Television to compare' });
        const wishlistButton = screen.getByRole('button', { name: 'Choose a variant of Television for wishlist' });
        expect(compareButton).toHaveTextContent('In comparison');
        expect(wishlistButton).toHaveTextContent('In wishlist');

        fireEvent.click(compareButton);
        fireEvent.click(wishlistButton);

        expect(scrollIntoView).toHaveBeenCalledTimes(2);
        expect(variantList).toHaveFocus();
        expect(lists.toggleComparison).not.toHaveBeenCalled();
        expect(lists.toggleWishlist).not.toHaveBeenCalled();
    });

    test('keeps a family saved earlier removable from the detail', () => {
        lists.comparison.add('tv');
        lists.wishlist.add('tv');
        render(<ProductDetailSecondaryActions product={mainVariant} />);

        fireEvent.click(screen.getByRole('button', { name: 'Remove from comparison product Television' }));
        fireEvent.click(screen.getByRole('button', { name: 'Remove from wishlist product Television' }));

        expect(lists.toggleComparison).toHaveBeenCalledWith(mainVariant, GtmProductListNameType.product_detail);
        expect(lists.toggleWishlist).toHaveBeenCalledWith(mainVariant, GtmProductListNameType.product_detail);
        expect(scrollIntoView).not.toHaveBeenCalled();
    });

    test('opens the variant popup when the detail has no variant list to scroll to', () => {
        variantList.remove();
        render(<ProductDetailSecondaryActions product={mainVariant} />);

        fireEvent.click(screen.getByRole('button', { name: 'Choose a variant of Television to compare' }));

        expect(openVariantsPopup).toHaveBeenCalledWith(mainVariant, {
            gtmProductListName: GtmProductListNameType.product_detail,
        });
        expect(lists.toggleComparison).not.toHaveBeenCalled();
    });
});
