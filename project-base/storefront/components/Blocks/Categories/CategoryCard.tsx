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
    const imageSize = isCatalogVariant ? 145 : isLarge ? 170 : 110;
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
        'relative flex shrink-0 items-center justify-center',
        isCatalogVariant && 'size-31.25 lg:size-33.75 xl:size-36.25',
        variant === 'homepage' && !isLarge && 'size-22.5 vl:size-20 xl:size-27.5',
        variant === 'homepage' && isLarge && 'size-22.5 vl:size-33.75 xl:size-42.5',
    );

    const imageContent = itemImage ? (
        <div data-tid={TIDs.simple_navigation_image} className={imageWrapperClassName}>
            <Image
                alt={itemImage.name || category.name}
                className="size-full object-contain mix-blend-multiply"
                height={imageSize}
                src={itemImage.url}
                width={imageSize}
            />
        </div>
    ) : (
        <div aria-hidden="true" className={imageWrapperClassName} />
    );

    const primaryContent = (
        <>
            {imageContent}

            <div className={twJoin(isLarge && 'vl:min-w-0')}>
                <h3 className="h5 line-clamp-2">{category.name}</h3>
                {variant === 'homepage' && isLarge && (
                    <p className="mt-2 vl:block hidden font-secondary font-semibold text-sm text-text-less">
                        {t('Our most popular category')}
                    </p>
                )}
            </div>
        </>
    );

    if (variant === 'homepage') {
        return (
            <ExtendedNextLink
                aria-label={primaryAriaLabel}
                href={href}
                type={linkType}
                className={twMergeCustom(
                    'flex aspect-square size-full max-h-37.5 cursor-pointer flex-col items-center justify-center gap-0 rounded-xl px-5 py-2.5 text-center no-underline transition-[box-shadow,border-color,background-color,color] duration-300 ease-in-out',
                    'border border-background-more bg-background-more text-text-default',
                    'group-hover:border-border-less group-hover:bg-background-default group-hover:text-text-default group-hover:no-underline',
                    'pointer-fine:group-hover:shadow-[0_12px_24px_-18px_rgb(37_40_61/40%),0_4px_10px_-8px_rgb(37_40_61/24%)] motion-reduce:duration-0',
                    'vl:aspect-auto vl:max-h-none vl:px-4 vl:py-2.5',
                    isLarge && 'vl:gap-4 vl:px-6 vl:py-5 xl:px-8',
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
                    'flex h-full cursor-pointer flex-col items-center gap-4 rounded-xl border border-background-more bg-background-more p-5 text-center text-text-default no-underline transition-[box-shadow,border-color,background-color,color] duration-200 ease-out hover:no-underline',
                    'hover:border-border-less hover:bg-background-default hover:text-text-default',
                    'pointer-fine:hover:shadow-[0_12px_24px_-18px_rgb(37_40_61/40%),0_4px_10px_-8px_rgb(37_40_61/24%)] motion-reduce:duration-0',
                )}
            >
                {primaryContent}
            </ExtendedNextLink>
        );
    }

    return (
        <article
            className={twMergeCustom(
                'flex h-full flex-col rounded-xl border border-background-more bg-background-more p-5 text-text-default transition-[box-shadow,border-color,background-color,color] duration-200 ease-out',
                'hover:border-border-less hover:bg-background-default',
                'pointer-fine:hover:shadow-[0_12px_24px_-18px_rgb(37_40_61/40%),0_4px_10px_-8px_rgb(37_40_61/24%)] motion-reduce:duration-0',
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
