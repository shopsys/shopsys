import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { getLinkType } from 'components/Blocks/SimpleNavigation/simpleNavigationUtils';
import { TIDs } from 'cypress/tids';
import { TypePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { twMergeCustom } from 'utils/twMerge';

type PromotedCategoriesContentProps = {
    promotedCategoriesData: TypePromotedCategoriesQuery;
};
export const PromotedCategoriesContent: FC<PromotedCategoriesContentProps> = ({ promotedCategoriesData }) => {
    const { t } = useTranslation();
    const categoriesLength = promotedCategoriesData.promotedCategories.length;

    return (
        <ul
            className={twMergeCustom(
                'vl:overflow-visible hide-scrollbar overflow-x-auto overflow-y-hidden overscroll-x-contain',
                'vl:grid-flow-row vl:grid-cols-4 vl:gap-5 grid auto-cols-[150px] grid-flow-col gap-3 lg:auto-cols-[200px]',
                categoriesLength > 4 ? 'vl:grid-rows-2' : 'vl:grid-rows-1',
            )}
        >
            {promotedCategoriesData.promotedCategories.map((category, index) => {
                const itemImage = 'mainImage' in category ? category.mainImage : null;
                const href = getStringWithoutTrailingSlash(category.slug) + '/';
                const linkType = getLinkType(category.__typename);
                const isFirstItemLarge = categoriesLength > 4 && index === 0;

                return (
                    <li
                        key={category.uuid}
                        className={twJoin(index === 0 && categoriesLength > 4 && 'vl:col-span-2 vl:row-span-2')}
                    >
                        <ExtendedNextLink
                            aria-label={t('Go to category {{ categoryName }}', { categoryName: category.name })}
                            href={href}
                            type={linkType}
                            className={twMergeCustom(
                                'flex cursor-pointer flex-col items-center gap-5 rounded-xl text-center no-underline transition',
                                'border-background-more bg-background-more text-text-default border',
                                'hover:border-border-less hover:bg-background-default hover:text-text-default hover:no-underline',
                                'vl:px-10 px-6 py-2.5 md:py-4',
                                'aspect-square size-full max-h-[150px] lg:max-h-[200px]',
                                isFirstItemLarge ? 'vl:max-h-[590px] vl:py-5' : 'vl:max-h-[285px] vl:py-7',
                            )}
                        >
                            {itemImage && (
                                <div
                                    data-tid={TIDs.simple_navigation_image}
                                    className={twJoin(
                                        'relative flex items-center justify-center',
                                        'vl:size-full size-[60px] lg:size-[100px]',
                                        isFirstItemLarge
                                            ? 'vl:max-h-[500px] vl:max-w-[500px]'
                                            : 'lg:max-h-[180px] lg:max-w-[180px]',
                                    )}
                                >
                                    <Image
                                        fill
                                        alt={itemImage.name || category.name}
                                        className="object-contain mix-blend-multiply"
                                        src={itemImage.url}
                                        sizes={
                                            isFirstItemLarge
                                                ? '(max-width: 768px) 60px, (max-width: 1023px) 100px, 500px'
                                                : '(max-width: 768px) 60px, (max-width: 1023px) 100px, 180px'
                                        }
                                    />
                                </div>
                            )}

                            <h3 className="h4 vl:line-clamp-1 line-clamp-2">{category.name}</h3>
                        </ExtendedNextLink>
                    </li>
                );
            })}
        </ul>
    );
};
