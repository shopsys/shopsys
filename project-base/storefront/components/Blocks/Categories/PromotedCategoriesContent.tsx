import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { getLinkType } from 'components/Blocks/SimpleNavigation/simpleNavigationUtils';
import { TIDs } from 'cypress/tids';
import { TypePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { twJoin } from 'tailwind-merge';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { twMergeCustom } from 'utils/twMerge';

type PromotedCategoriesContentProps = {
    promotedCategoriesData: TypePromotedCategoriesQuery;
};
export const PromotedCategoriesContent: FC<PromotedCategoriesContentProps> = ({ promotedCategoriesData }) => {
    const categoriesLength = promotedCategoriesData.promotedCategories.length;

    return (
        <ul
            className={twMergeCustom(
                'overflow-x-auto overflow-y-hidden overscroll-x-contain',
                'grid gap-3 vl:gap-5 grid-flow-col vl:grid-flow-row auto-cols-[45%] sm:auto-cols-[30%] lg:auto-cols-[20%] vl:grid-cols-4',
                categoriesLength > 4 ? 'vl:grid-rows-2' : 'vl:grid-rows-1',
                '[-ms-overflow-style:"none"] [scrollbar-width:"none"] [&::-webkit-scrollbar]:hidden',
            )}
        >
            {promotedCategoriesData.promotedCategories.map((category, index) => {
                const itemImage = 'mainImage' in category ? category.mainImage : null;
                const href = getStringWithoutTrailingSlash(category.slug) + '/';
                const linkType = getLinkType(category.__typename);

                return (
                    <li
                        key={category.uuid}
                        className={twJoin(index === 0 && categoriesLength > 4 && 'vl:row-span-2 vl:col-span-2')}
                    >
                        <ExtendedNextLink
                            href={href}
                            type={linkType}
                            className={twMergeCustom(
                                'flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl no-underline transition',
                                'border border-backgroundMore bg-backgroundMore text-text',
                                'hover:border-borderAccentLess hover:bg-background hover:text-text hover:no-underline',
                                'px-6 py-4 vl:p-10 size-full text-center',
                            )}
                        >
                            {itemImage && (
                                <div
                                    className="h-full flex items-center justify-center"
                                    tid={TIDs.simple_navigation_image}
                                >
                                    <Image
                                        alt={itemImage.name || category.name}
                                        className="mix-blend-multiply object-contain aspect-square"
                                        height={index === 0 ? 500 : 180}
                                        src={itemImage.url}
                                        width={index === 0 ? 500 : 180}
                                    />
                                </div>
                            )}

                            <h4>{category.name}</h4>
                        </ExtendedNextLink>
                    </li>
                );
            })}
        </ul>
    );
};
