import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { type MouseEvent, useEffect, useState } from 'react';
import { type ArticleHeading } from 'types/articleHeading';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

export const ARTICLE_INTRODUCTION_ANCHOR_ID = 'article-introduction';

type ArticleAnchorNavigationProps = {
    headings: ArticleHeading[];
};

type ArticleAnchorNavigationLinkProps = {
    href: string;
    isActive: boolean;
    title: string;
    onClick: (event: MouseEvent<HTMLAnchorElement>) => void;
};

const ArticleAnchorNavigationLink: FC<ArticleAnchorNavigationLinkProps> = ({ href, isActive, title, onClick }) => (
    <li>
        <a
            href={href}
            className={twMergeCustom(
                'flex items-center gap-1 rounded-sm font-secondary font-semibold text-sm text-text-default no-underline hover:text-link-hovered',
                isActive && 'text-link-default no-underline',
            )}
            onClick={onClick}
        >
            {isActive && <ArrowIcon className="rotate-90 text-link-default transition" />}
            {title}
        </a>
    </li>
);

export const ArticleAnchorNavigation: FC<ArticleAnchorNavigationProps> = ({ headings }) => {
    const { t } = useTranslation();
    const [activeHeadingId, setActiveHeadingId] = useState<string | null>(null);
    const [isBackToTopVisible, setIsBackToTopVisible] = useState(false);

    useEffect(() => {
        setActiveHeadingId(window.location.hash.slice(1) || ARTICLE_INTRODUCTION_ANCHOR_ID);
    }, []);

    useEffect(() => {
        const handleScroll = () => {
            setIsBackToTopVisible(window.scrollY > 400);
        };

        handleScroll();
        window.addEventListener('scroll', handleScroll, { passive: true });

        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    if (!headings.length) {
        return null;
    }

    const scrollToHeading = (headingId: string): boolean => {
        const headingElement = document.getElementById(headingId);

        if (!headingElement) {
            return false;
        }

        setActiveHeadingId(headingId);
        window.history.replaceState(null, '', `#${headingId}`);
        headingElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

        return true;
    };

    const handleAnchorLinkClick = (headingId: string) => (event: MouseEvent<HTMLAnchorElement>) => {
        if (scrollToHeading(headingId)) {
            event.preventDefault();
        }
    };

    const handleBackToTopClick = () => {
        setActiveHeadingId(ARTICLE_INTRODUCTION_ANCHOR_ID);
        window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}`);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    return (
        <>
            <nav aria-label={t('Article content')} className="flex flex-col gap-2.5">
                <p className="font-secondary font-semibold">{t('Article content')}</p>

                <ul className="flex flex-col gap-2.5 rounded-xl bg-background-more p-5">
                    <ArticleAnchorNavigationLink
                        href={`#${ARTICLE_INTRODUCTION_ANCHOR_ID}`}
                        isActive={activeHeadingId === ARTICLE_INTRODUCTION_ANCHOR_ID}
                        title={t('Introduction')}
                        onClick={handleAnchorLinkClick(ARTICLE_INTRODUCTION_ANCHOR_ID)}
                    />

                    {headings.map((heading) => (
                        <ArticleAnchorNavigationLink
                            key={heading.id}
                            href={`#${heading.id}`}
                            isActive={activeHeadingId === heading.id}
                            title={heading.title}
                            onClick={handleAnchorLinkClick(heading.id)}
                        />
                    ))}
                </ul>
            </nav>

            {isBackToTopVisible && (
                <button
                    type="button"
                    className="fixed right-5 bottom-[calc(5rem+env(safe-area-inset-bottom))] vl:bottom-5 z-above flex size-12 items-center justify-center rounded-full bg-background-brand-less text-text-inverted shadow-md xl:hidden"
                    title={t('Back to top')}
                    onClick={handleBackToTopClick}
                >
                    <ArrowIcon className="size-6 rotate-180" />
                    <span className="sr-only">{t('Back to top')}</span>
                </button>
            )}
        </>
    );
};
