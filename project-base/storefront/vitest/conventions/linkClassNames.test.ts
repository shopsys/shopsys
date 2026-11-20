import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';
import { describe, expect, test } from 'vitest';

/**
 * Text links and text actions get their look from `Link`, `TextButton` (or `getLinkClassName`), so the underline
 * and hover rules stay the same everywhere. This test stops new hand-written link styles: a class that underlines
 * text or uses a link color outside the shared components has to go through them instead.
 * Card titles underlined through a named `group-hover/...` variant and `no-underline` resets stay allowed.
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
 * Files that still style links by hand, with the number of such classes, grouped by why they stay. Lower a count
 * (or remove the file) when you migrate it to `Link` / `TextButton`; never raise it.
 */
const KNOWN_HAND_WRITTEN_LINK_CLASS_COUNTS: Record<string, number> = {
    // header: the main menu and icon menu respond with color only (an agreed exception)
    'components/Layout/Header/HamburgerMenu/HamburgerMenu.tsx': 2,
    'components/Layout/Header/MenuIconic/MenuIconicElements.tsx': 2,
    'components/Layout/Header/MenuIconic/MenuIconicPlaceholder.tsx': 2,
    'components/Layout/Header/Navigation/NavigationItem.tsx': 1,
    'components/Layout/Header/Navigation/NavigationItemContent.tsx': 1,
    'components/Layout/Header/Navigation/NavigationMoreItem.tsx': 1,
    'components/Layout/Header/Navigation/NavigationPlaceholder.tsx': 1,

    // own interactive families with a selected state: sorting tabs, blog category pills, calendar days, accordions
    'components/Blocks/BlogSignpost/BlogSignpostCategoryLink.tsx': 3,
    'components/Blocks/PromoCode/PromoCode.tsx': 1,
    'components/Blocks/SortingBar/SortingBarItem.tsx': 3,
    'components/Forms/DatePicker/datePickerUtils.ts': 1,
    'components/Pages/ProductDetail/ProductDetailGallery/ProductDetailGalleryThumbnails.tsx': 1,

    // availability keeps its status color, the card link underlines its own label
    'components/Blocks/Product/ProductAvailability.tsx': 2,
    'components/Blocks/ProductReviews/ProductListReviewsSummaryLink.tsx': 1,
    'components/Pages/ProductDetail/ProductDetailAvailability.tsx': 1,

    // icon actions on the product (wishlist, compare, ask a question): muted with the icon as the signal, the label
    // turns to the accent and gets underlined on hover (an agreed exception)
    'components/Blocks/Product/ButtonsAction/ProductCompareButton.tsx': 1,
    'components/Blocks/Product/ButtonsAction/ProductQuestionButton.tsx': 1,
    'components/Blocks/Product/ButtonsAction/ProductWishlistButton.tsx': 1,

    // waiting for a design decision: the link-colored label of a checked choice
    'components/Forms/Lib/LabelWrapper.tsx': 2,

    // developer tools, not part of the shop
    'components/Basic/CopyTextBlock/CopyTextBlock.tsx': 1,
    'components/Basic/SymfonyDebugToolbar/SymfonyDebugToolbarRequestsTable.tsx': 3,
    'components/Blocks/IgnoredErrorsManager/IgnoredErrorsManager.tsx': 1,
};

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
        const counts = getHandWrittenLinkClassCounts();
        const filesWithMoreHandWrittenClasses = Object.entries(counts)
            .filter(([path, count]) => count > (KNOWN_HAND_WRITTEN_LINK_CLASS_COUNTS[path] ?? 0))
            .map(([path, count]) => `${path}: ${count} (allowed ${KNOWN_HAND_WRITTEN_LINK_CLASS_COUNTS[path] ?? 0})`);

        expect(
            filesWithMoreHandWrittenClasses,
            'Style text links with Link (placement, tone) or TextButton instead of underline/text-link-* classes',
        ).toEqual([]);
    });

    test('the list of known hand-written link classes shrinks with every migration', () => {
        const counts = getHandWrittenLinkClassCounts();
        const outdatedEntries = Object.entries(KNOWN_HAND_WRITTEN_LINK_CLASS_COUNTS)
            .filter(([path, allowedCount]) => (counts[path] ?? 0) < allowedCount)
            .map(([path, allowedCount]) => `${path}: ${counts[path] ?? 0} (listed ${allowedCount})`);

        expect(outdatedEntries, 'Lower these counts in KNOWN_HAND_WRITTEN_LINK_CLASS_COUNTS').toEqual([]);
    });
});
