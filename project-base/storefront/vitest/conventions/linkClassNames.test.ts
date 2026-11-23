import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';
import { describe, expect, test } from 'vitest';

/**
 * Text links and text actions get their look from `Link`, `TextButton` (or `getLinkClassName`), so the underline
 * and hover rules stay the same everywhere. This test stops new hand-written link styles: a class that underlines
 * text or uses a link color outside the shared components has to go through them instead.
 * Cards are not text links and never get an underline. `no-underline` resets stay allowed.
 */
const STOREFRONT_ROOT = join(__dirname, '..', '..');
const COMPONENTS_DIRECTORY = join(STOREFRONT_ROOT, 'components');

const HAND_WRITTEN_LINK_CLASS = /^(?:(?:hover|active|focus|focus-visible|disabled):)?(?:underline|text-link-[a-z-]+)$/;

const SHARED_LINK_COMPONENT_PATHS = [
    'components/Basic/Link/',
    'components/Basic/TextButton/',
    'components/Forms/Button/',
    'components/Pages/Styleguide/',
];

/**
 * Files allowed to style their links by hand, grouped by why. A component with a good reason for its own link look
 * (a menu, a selected state, a developer tool) is added here; everything else uses `Link` / `TextButton`.
 */
const OWN_LINK_STYLE_PATHS = new Set([
    // header: the main menu and icon menu respond with color only (an agreed exception)
    'components/Layout/Header/HamburgerMenu/HamburgerMenu.tsx',
    'components/Layout/Header/MenuIconic/MenuIconicElements.tsx',
    'components/Layout/Header/MenuIconic/MenuIconicPlaceholder.tsx',
    'components/Layout/Header/Navigation/NavigationItem.tsx',
    'components/Layout/Header/Navigation/NavigationItemContent.tsx',
    'components/Layout/Header/Navigation/NavigationMoreItem.tsx',
    'components/Layout/Header/Navigation/NavigationPlaceholder.tsx',

    // own interactive families with a selected state: sorting tabs, blog category pills, calendar days, accordions
    'components/Blocks/BlogSignpost/BlogSignpostCategoryLink.tsx',
    'components/Blocks/PromoCode/PromoCode.tsx',
    'components/Blocks/SortingBar/SortingBarItem.tsx',
    'components/Forms/DatePicker/datePickerUtils.ts',
    'components/Pages/ProductDetail/ProductDetailGallery/ProductDetailGalleryThumbnails.tsx',

    // availability keeps its status color, the card link underlines its own label
    'components/Blocks/Popup/AddToCartPopup.tsx',
    'components/Blocks/Product/ProductAvailability.tsx',
    'components/Blocks/ProductReviews/ProductListReviewsSummaryLink.tsx',
    'components/Pages/Cart/CartList/CartListItem.tsx',

    // icon actions on the product (wishlist, compare, ask a question): muted with the icon as the signal, the label
    // turns to the accent and gets underlined on hover (an agreed exception)
    'components/Blocks/Product/ButtonsAction/ProductCompareButton.tsx',
    'components/Blocks/Product/ButtonsAction/ProductQuestionButton.tsx',
    'components/Blocks/Product/ButtonsAction/ProductWishlistButton.tsx',

    // waiting for a design decision: the link-colored label of a checked choice
    'components/Forms/Lib/LabelWrapper.tsx',

    // developer tools, not part of the shop
    'components/Basic/CopyTextBlock/CopyTextBlock.tsx',
    'components/Basic/SymfonyDebugToolbar/SymfonyDebugToolbarRequestsTable.tsx',
    'components/Blocks/IgnoredErrorsManager/IgnoredErrorsManager.tsx',
]);

const getTsxFiles = (directory: string): string[] =>
    readdirSync(directory).flatMap((entry) => {
        const path = join(directory, entry);

        if (statSync(path).isDirectory()) {
            return getTsxFiles(path);
        }

        return path.endsWith('.tsx') || path.endsWith('.ts') ? [path] : [];
    });

const countHandWrittenLinkClasses = (source: string) =>
    source.split(/[\s'"`{}(),]+/).filter((token) => HAND_WRITTEN_LINK_CLASS.test(token)).length;

const getHandWrittenLinkClassCounts = (): Record<string, number> =>
    Object.fromEntries(
        getTsxFiles(COMPONENTS_DIRECTORY)
            .map((path) => relative(STOREFRONT_ROOT, path))
            .filter((path) => !SHARED_LINK_COMPONENT_PATHS.some((sharedPath) => path.startsWith(sharedPath)))
            .map((path): [string, number] => [
                path,
                countHandWrittenLinkClasses(readFileSync(join(STOREFRONT_ROOT, path), 'utf8')),
            ])
            .filter(([, count]) => count > 0),
    );

describe('link class names', () => {
    test('new text links use Link or TextButton instead of hand-written link classes', () => {
        const filesWithHandWrittenClasses = Object.entries(getHandWrittenLinkClassCounts())
            .filter(([path]) => !OWN_LINK_STYLE_PATHS.has(path))
            .map(([path, count]) => `${path}: ${count}`);

        expect(
            filesWithHandWrittenClasses,
            'Style text links with Link (variant, tone) or TextButton, or add the file to OWN_LINK_STYLE_PATHS with a reason',
        ).toEqual([]);
    });
});
