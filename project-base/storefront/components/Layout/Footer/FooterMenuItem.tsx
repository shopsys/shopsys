import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { AnimatePresence } from 'framer-motion';
import { TypeSimpleNotBlogArticleFragment } from 'graphql/requests/articlesInterface/articles/fragments/SimpleNotBlogArticleFragment.generated';
import { useState } from 'react';
import { createAriaParameter } from 'utils/accessibility/createAriaParameter';
import { twMergeCustom } from 'utils/twMerge';

type FooterMenuItemProps = {
    title: string;
    items: TypeSimpleNotBlogArticleFragment[];
};

export const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => {
    const [isExpanded, setIsExpanded] = useState(false);
    const contentId = createAriaParameter('footer-menu', title);

    const toggleExpanded = () => {
        setIsExpanded(!isExpanded);
    };

    return (
        <>
            {/* Desktop layout */}
            <div className="hidden lg:flex lg:flex-1 lg:flex-col lg:gap-6">
                <FooterMenuItemTitle title={title} />

                <ul className="space-y-2 lg:space-y-4">
                    {items.map((item) => (
                        <li key={item.uuid}>
                            <FooterMenuItemLink item={item} />
                        </li>
                    ))}
                </ul>
            </div>

            {/* Mobile accordion layout */}
            <div className="rounded-xl bg-background-default px-5 py-4 lg:hidden">
                <button
                    aria-controls={contentId}
                    aria-expanded={isExpanded}
                    className="flex w-full cursor-pointer items-center justify-between"
                    tabIndex={0}
                    type="button"
                    onClick={toggleExpanded}
                >
                    <FooterMenuItemTitle title={title} />

                    <ArrowIcon
                        className={twMergeCustom(
                            '0 size-6 rotate-0 text-icon-less transition-all',
                            isExpanded && 'rotate-180',
                        )}
                    />
                </button>

                <div id={contentId}>
                    <AnimatePresence initial={false}>
                        {isExpanded && (
                            <AnimateCollapseDiv className="block!" keyName={`footer-menu-${title}`}>
                                <ul className="space-y-5 pt-5">
                                    {items.map((item) => (
                                        <li key={item.uuid}>
                                            <FooterMenuItemLink item={item} />
                                        </li>
                                    ))}
                                </ul>
                            </AnimateCollapseDiv>
                        )}
                    </AnimatePresence>
                </div>
            </div>
        </>
    );
};

const FooterMenuItemTitle: FC<{ title: string }> = ({ title }) => {
    return <span className="font-secondary font-semibold text-xs uppercase tracking-wider">{title}</span>;
};

const FooterMenuItemLink: FC<{ item: TypeSimpleNotBlogArticleFragment }> = ({ item }) => {
    // ArticleLink can point to any URL (product, category, external site, ...), so it must not be
    // routed as an article detail page and has to go through the friendly URL resolution instead
    const isArticleSite = item.__typename === 'ArticleSite';

    return (
        <ExtendedNextLink
            className="block font-secondary font-semibold text-sm text-text-default tracking-wider no-underline hover:text-text-default hover:underline"
            href={isArticleSite ? item.slug : item.url}
            rel={item.external ? 'nofollow noreferrer noopener' : undefined}
            skeletonType={isArticleSite ? 'article' : undefined}
            target={item.external ? '_blank' : undefined}
            type={isArticleSite ? 'article' : undefined}
        >
            {item.name}
        </ExtendedNextLink>
    );
};
