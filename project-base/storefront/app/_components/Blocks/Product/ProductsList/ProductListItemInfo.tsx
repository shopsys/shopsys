import { getTranslation } from 'app/_utils/translation/getTranslation';
import { VariantIcon } from 'components/Basic/Icon/VariantIcon';
import { twJoin } from 'tailwind-merge';

export type ProductListItemInfoProps = {
    fullName: string;
    typename: string;
    variantsCount: number;
    textSize?: 'xs' | 'sm';
};

export const ProductListItemInfo: FC<ProductListItemInfoProps> = async ({
    textSize,
    fullName,
    typename,
    variantsCount,
}) => {
    const t = await getTranslation();

    return (
        <>
            <div
                className={twJoin(
                    'font-secondary group-hover:text-link-default grow overflow-hidden font-semibold break-words group-hover:underline',
                    textSize === 'xs' ? 'text-xs' : 'text-sm',
                )}
            >
                {fullName}
            </div>

            {typename === 'MainVariant' && (
                <div className="bg-background-default font-secondary group-hover:text-text-default flex w-fit items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs whitespace-nowrap">
                    <VariantIcon className="text-text-accent size-3" />
                    {variantsCount} {t('variants count', { count: variantsCount })}
                </div>
            )}
        </>
    );
};
