import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import type { ReactNode } from 'react';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({
        children,
        href,
        type: _type,
        ...props
    }: {
        children: ReactNode;
        href: string;
        type?: string;
    }) => (
        <a href={href} {...props}>
            {children}
        </a>
    ),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

const createBlogCategory = (
    uuid: string,
    name: string,
    children: ListedBlogCategoryRecursiveType[] = [],
): ListedBlogCategoryRecursiveType => ({
    __typename: 'BlogCategory',
    uuid,
    name,
    link: `/blog/${uuid}`,
    parent: null,
    children,
});

const screenTechnologiesCategory = createBlogCategory('screen-technologies', 'Screen technologies');
const televisionsCategory = createBlogCategory('televisions', 'Televisions', [screenTechnologiesCategory]);
const audioCategory = createBlogCategory('audio', 'Audio and headphones');
const buyingGuidesCategory = createBlogCategory('buying-guides', 'Buying guides', [televisionsCategory, audioCategory]);
const inspirationCategory = createBlogCategory('inspiration', 'Inspiration');
const rootCategory = createBlogCategory('root', 'Blog', [buyingGuidesCategory, inspirationCategory]);
const blogCategoryItems = [rootCategory];

describe('BlogSignpost', () => {
    test('renders crawlable links for every drill-down panel before user interaction', () => {
        const { container } = render(<BlogSignpost activeItem="root" blogCategoryItems={blogCategoryItems} />);

        const linkHrefs = Array.from(container.querySelectorAll<HTMLAnchorElement>('a[href]')).map((link) =>
            link.getAttribute('href'),
        );

        expect(linkHrefs).toEqual([
            '/blog/inspiration',
            '/blog/root',
            '/blog/audio',
            '/blog/buying-guides',
            '/blog/screen-technologies',
            '/blog/televisions',
        ]);
        expect(screen.getByRole('link', { hidden: true, name: 'All articles: Buying guides' })).toHaveAttribute(
            'href',
            '/blog/buying-guides',
        );
    });

    test('drills down and returns with one action per visible row', async () => {
        const user = userEvent.setup();
        render(<BlogSignpost activeItem="root" blogCategoryItems={blogCategoryItems} />);

        await user.click(screen.getByRole('button', { name: 'Buying guides' }));

        expect(screen.getByRole('link', { name: 'All articles: Buying guides' })).toBeVisible();
        expect(screen.getAllByRole('link').map((link) => link.textContent)).toEqual([
            'Audio and headphones',
            'All articles: Buying guides',
        ]);
        expect(screen.queryByRole('link', { name: 'All articles' })).not.toBeInTheDocument();
        await waitFor(() => expect(screen.getByRole('button', { name: 'Back' })).toHaveFocus());

        await user.click(screen.getByRole('button', { name: 'Televisions' }));

        expect(screen.getByRole('link', { name: 'All articles: Televisions' })).toBeVisible();
        expect(screen.getByRole('link', { name: 'Screen technologies' })).toBeVisible();

        await user.click(screen.getByRole('button', { name: 'Back' }));

        expect(screen.getByRole('link', { name: 'All articles: Buying guides' })).toBeVisible();
        expect(screen.getByRole('button', { name: 'Televisions' })).toBeVisible();
    });

    test('keeps inactive panels out of the accessibility tree while navigating', async () => {
        const user = userEvent.setup();
        render(<BlogSignpost activeItem="root" blogCategoryItems={blogCategoryItems} />);

        const rootPanel = document.getElementById('blog-signpost-panel-root');
        const buyingGuidesPanel = document.getElementById('blog-signpost-panel-buying-guides');

        expect(buyingGuidesPanel).toHaveAttribute('aria-hidden', 'true');
        expect(buyingGuidesPanel).toHaveAttribute('inert');

        await user.click(screen.getByRole('button', { name: 'Buying guides' }));

        expect(rootPanel).toHaveAttribute('aria-hidden', 'true');
        expect(rootPanel).toHaveAttribute('inert');
        expect(buyingGuidesPanel).not.toHaveAttribute('aria-hidden');
        expect(buyingGuidesPanel).not.toHaveAttribute('inert');

        await user.click(screen.getByRole('button', { name: 'Back' }));

        expect(rootPanel).not.toHaveAttribute('aria-hidden');
        expect(rootPanel).not.toHaveAttribute('inert');
        expect(buyingGuidesPanel).toHaveAttribute('aria-hidden', 'true');
        expect(buyingGuidesPanel).toHaveAttribute('inert');
    });

    test('keeps only adjacent panels visible during rapid drill-down', async () => {
        const user = userEvent.setup();
        render(<BlogSignpost activeItem="root" blogCategoryItems={blogCategoryItems} />);

        const rootPanel = document.getElementById('blog-signpost-panel-root');
        const buyingGuidesPanel = document.getElementById('blog-signpost-panel-buying-guides');
        const televisionsPanel = document.getElementById('blog-signpost-panel-televisions');

        await user.click(screen.getByRole('button', { name: 'Buying guides' }));
        await user.click(screen.getByRole('button', { name: 'Televisions' }));

        expect(rootPanel).toHaveClass('invisible');
        expect(buyingGuidesPanel).not.toHaveClass('invisible');
        expect(televisionsPanel).not.toHaveClass('invisible');
    });

    test('hides the previous panel after its transition finishes', async () => {
        const user = userEvent.setup();
        render(<BlogSignpost activeItem="root" blogCategoryItems={blogCategoryItems} />);
        const rootPanel = document.getElementById('blog-signpost-panel-root');

        await user.click(screen.getByRole('button', { name: 'Buying guides' }));
        expect(rootPanel).not.toHaveClass('invisible');

        fireEvent.transitionEnd(rootPanel!, { propertyName: 'transform' });

        expect(rootPanel).toHaveClass('invisible');
    });

    test('does not restore focus after the panel focus request has been consumed', async () => {
        const user = userEvent.setup();
        const { rerender } = render(
            <>
                <button type="button">Outside action</button>
                <BlogSignpost activeItem="root" blogCategoryItems={blogCategoryItems} />
            </>,
        );

        await user.click(screen.getByRole('button', { name: 'Buying guides' }));
        await waitFor(() => expect(screen.getByRole('button', { name: 'Back' })).toHaveFocus());
        const outsideAction = screen.getByRole('button', { name: 'Outside action' });
        outsideAction.focus();

        rerender(
            <>
                <button type="button">Outside action</button>
                <BlogSignpost activeItem="televisions" blogCategoryItems={blogCategoryItems} />
            </>,
        );
        await waitFor(() => expect(screen.getByRole('link', { name: 'All articles: Televisions' })).toBeVisible());
        rerender(
            <>
                <button type="button">Outside action</button>
                <BlogSignpost activeItem="buying-guides" blogCategoryItems={blogCategoryItems} />
            </>,
        );
        await waitFor(() => expect(screen.getByRole('link', { name: 'All articles: Buying guides' })).toBeVisible());

        expect(outsideAction).toHaveFocus();
    });

    test('opens the parent panel of an active leaf category', () => {
        render(<BlogSignpost activeItem="screen-technologies" blogCategoryItems={blogCategoryItems} />);

        expect(screen.getByRole('link', { name: 'All articles: Televisions' })).toBeVisible();
        const currentCategoryLink = screen.getByRole('link', { name: 'Screen technologies' });

        expect(currentCategoryLink).toHaveAttribute('aria-current', 'page');
    });
});
