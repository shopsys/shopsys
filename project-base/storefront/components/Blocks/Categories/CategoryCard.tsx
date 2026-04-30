import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { ChevronEmptyDotIcon } from 'components/Basic/Icon/ChevronEmptyDotIcon';
import { Image } from 'components/Basic/Image/Image';
import { getLinkType } from 'components/Blocks/SimpleNavigation/simpleNavigationUtils';
import { TIDs } from 'cypress/tids';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { twMergeCustom } from 'utils/twMerge';

type CategoryCardCategory = {
    __typename?: 'Category';
    name: string;
    slug: string;
    mainImage?: TypeImageFragment | null;
    children?: CategoryCardCategory[];
};

type CategoryCardVariant = 'homepage' | 'catalog';
type CategoryCardSize = 'default' | 'large';

type CategoryCardProps = {
    category: CategoryCardCategory;
    showChildren?: boolean;
    variant?: CategoryCardVariant;
    size?: CategoryCardSize;
};

const MAX_VISIBLE_SUBCATEGORIES = 6;

export const CategoryCard: FC<CategoryCardProps> = ({
    category,
    showChildren = true,
    variant = 'homepage',
    size = 'default',
}) => {
    const { t } = useTranslation();
    const isLarge = size === 'large';
    const isCatalogVariant = variant === 'catalog';
    const itemImage = category.mainImage ?? null;
    const href = `${getStringWithoutTrailingSlash(category.slug)}/`;
    const linkType = getLinkType(category.__typename);
    const hasChildren = isCatalogVariant && showChildren && !!category.children?.length;
    const visibleChildren = category.children?.slice(0, MAX_VISIBLE_SUBCATEGORIES) ?? [];
    const hasHiddenChildren = (category.children?.length ?? 0) > MAX_VISIBLE_SUBCATEGORIES;
    const primaryAriaLabel = t('Go to category {{ categoryName }}', {
        ns: 'accessibility',
        categoryName: category.name,
    });
    const imageWrapperClassName = twJoin(
        'relative flex items-center justify-center',
        isCatalogVariant ? 'size-[125px] lg:size-[135px] xl:size-[145px]' : 'size-[60px] vl:size-full lg:size-[100px]',
        variant === 'homepage' && isLarge
            ? 'vl:max-h-[500px] vl:max-w-[500px]'
            : !isCatalogVariant && 'lg:max-h-[180px] lg:max-w-[180px]',
    );

    const primaryContent = (
        <>
            {itemImage ? (
                <div data-tid={TIDs.simple_navigation_image} className={imageWrapperClassName}>
                    <Image
                        fill
                        alt={itemImage.name || category.name}
                        className="object-contain mix-blend-multiply"
                        src={itemImage.url}
                        sizes={
                            isCatalogVariant
                                ? '(max-width: 768px) 125px, (max-width: 1023px) 135px, 145px'
                                : isLarge
                                  ? '(max-width: 768px) 60px, (max-width: 1023px) 100px, 500px'
                                  : '(max-width: 768px) 60px, (max-width: 1023px) 100px, 180px'
                        }
                    />
                </div>
            ) : (
                <div aria-hidden="true" className={imageWrapperClassName} />
            )}

            <h3 className="h4 line-clamp-2">{category.name}</h3>
        </>
    );

    if (variant === 'homepage') {
        return (
            <ExtendedNextLink
                aria-label={primaryAriaLabel}
                href={href}
                type={linkType}
                className={twMergeCustom(
                    'flex cursor-pointer flex-col items-center gap-5 rounded-xl text-center no-underline transition',
                    'border border-background-more bg-background-more text-text-default',
                    'hover:border-border-less hover:bg-background-default hover:text-text-default hover:no-underline',
                    'px-5 py-2.5 vl:py-5',
                    'aspect-square size-full max-h-[150px] lg:max-h-[200px]',
                    isLarge ? 'vl:max-h-[590px]' : 'vl:max-h-[285px]',
                )}
            >
                {primaryContent}
            </ExtendedNextLink>
        );
    }

    if (!hasChildren) {
        return (
            <ExtendedNextLink
                aria-label={primaryAriaLabel}
                href={href}
                type={linkType}
                className={twMergeCustom(
                    'flex h-full cursor-pointer flex-col items-center gap-4 rounded-xl border border-background-more bg-background-more p-5 text-center text-text-default no-underline transition hover:no-underline',
                    'hover:border-border-less hover:bg-background-default hover:text-text-default',
                )}
            >
                {primaryContent}
            </ExtendedNextLink>
        );
    }

    return (
        <article
            className={twMergeCustom(
                'flex h-full flex-col rounded-xl border border-background-more bg-background-more p-5 text-text-default',
                'transition',
                'hover:border-border-less hover:bg-background-default',
                'justify-start',
            )}
        >
            <ExtendedNextLink
                aria-label={primaryAriaLabel}
                className="flex cursor-pointer flex-col items-center gap-4 rounded-lg text-center text-text-default no-underline transition hover:no-underline"
                href={href}
                type={linkType}
            >
                {primaryContent}
            </ExtendedNextLink>

            <ul className="mt-5 flex flex-wrap items-center gap-2 leading-none">
                {visibleChildren.map((child, index) => (
                    <li key={child.slug}>
                        {index > 0 && <ChevronEmptyDotIcon className="mr-2 size-1 text-icon-accent" />}
                        <ExtendedNextLink
                            className="rounded-sm font-secondary font-semibold text-text-default text-xs no-underline hover:text-text-default hover:underline"
                            href={`${getStringWithoutTrailingSlash(child.slug)}/`}
                            type={getLinkType(child.__typename)}
                        >
                            {child.name}
                        </ExtendedNextLink>
                    </li>
                ))}
                {hasHiddenChildren && (
                    <li className="inline">
                        <ChevronEmptyDotIcon className="mr-2 size-1 text-icon-accent" />
                        <ExtendedNextLink
                            className="inline-flex items-center gap-1 rounded-sm font-secondary font-semibold text-text-default text-xs no-underline hover:text-text-default hover:underline"
                            href={href}
                            type={linkType}
                        >
                            {t('Next')}
                            <ArrowSecondaryIcon className="size-2 -rotate-90" />
                        </ExtendedNextLink>
                    </li>
                )}
            </ul>
        </article>
    );
};
