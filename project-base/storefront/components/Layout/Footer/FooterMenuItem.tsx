import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { AnimatePresence } from 'framer-motion';
import { TypeSimpleNotBlogArticleFragment } from 'graphql/requests/articlesInterface/articles/fragments/SimpleNotBlogArticleFragment.generated';
import { useState } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type FooterMenuItemProps = {
    title: string;
    items: TypeSimpleNotBlogArticleFragment[];
};

export const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => {
    const [isExpanded, setIsExpanded] = useState(false);

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
            <div className="bg-background-default rounded-xl px-5 py-4 lg:hidden">
                <button
                    aria-controls={`footer-menu-${title.toLowerCase().replace(/\s+/g, '-')}`}
                    aria-expanded={isExpanded}
                    className="flex w-full cursor-pointer items-center justify-between"
                    tabIndex={0}
                    type="button"
                    onClick={toggleExpanded}
                >
                    <FooterMenuItemTitle title={title} />

                    <ArrowIcon
                        className={twMergeCustom(
                            'text-icon-less 0 size-6 rotate-0 transition-all',
                            isExpanded && 'rotate-180',
                        )}
                    />
                </button>

                <AnimatePresence initial={false}>
                    {isExpanded && (
                        <AnimateCollapseDiv className="!block" keyName={`footer-menu-${title}`}>
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
        </>
    );
};

const FooterMenuItemTitle: FC<{ title: string }> = ({ title }) => {
    return <h3 className="font-secondary text-xs font-semibold tracking-wider uppercase">{title}</h3>;
};

const FooterMenuItemLink: FC<{ item: TypeSimpleNotBlogArticleFragment }> = ({ item }) => {
    return (
        <ExtendedNextLink
            className="text-text-default hover:text-text-default font-secondary block text-sm font-semibold tracking-wider no-underline hover:underline"
            href={item.__typename === 'ArticleSite' ? item.slug : item.url}
            rel={item.external ? 'nofollow noreferrer noopener' : undefined}
            skeletonType="article"
            target={item.external ? '_blank' : undefined}
            type="article"
        >
            {item.name}
        </ExtendedNextLink>
    );
};
