import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

type AdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: CategoryDetailFragmentApi['readyCategorySeoMixLinks'];
};

export const AdvancedSeoCategories: FC<AdvancedSeoCategoriesProps> = ({ readyCategorySeoMixLinks }) => {
    const { t } = useTranslation();

    if (readyCategorySeoMixLinks.length === 0) {
        return null;
    }

    return (
        <>
            <div className="mb-3 break-words font-bold text-dark lg:text-lg">{t('Favorite categories')}</div>

            <ul className="mb-5 grid snap-x snap-mandatory auto-cols-[40%] gap-3 overflow-x-auto overscroll-x-contain max-lg:grid-flow-col lg:grid-cols-[repeat(auto-fill,minmax(210px,1fr))]">
                {readyCategorySeoMixLinks.map((seoMixLink, index) => (
                    <li key={index} className="snap-start">
                        <ExtendedNextLink
                            href={`/${seoMixLink.slug}`}
                            type="static"
                            className={twJoin(
                                'flex h-full items-center justify-center rounded bg-greyVeryLight p-3 text-center text-sm text-dark no-underline',
                                'hover:bg-whitesmoke hover:text-dark hover:no-underline',
                                'active:bg-whitesmoke active:text-dark active:no-underline',
                            )}
                        >
                            {seoMixLink.name}
                        </ExtendedNextLink>
                    </li>
                ))}
            </ul>
        </>
    );
};
