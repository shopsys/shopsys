import { VariantIcon } from 'components/Basic/Icon/VariantIcon';
import { twJoin } from 'tailwind-merge';
import { getServerT } from 'utils/getServerTranslation';

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
    const t = await getServerT();

    return (
        <>
            <div
                className={twJoin(
                    'grow overflow-hidden break-words font-secondary font-semibold group-hover:text-link group-hover:underline',
                    textSize === 'xs' ? 'text-xs' : 'text-sm',
                )}
            >
                {fullName}
            </div>

            {typename === 'MainVariant' && (
                <div className="flex w-fit items-center gap-1.5 whitespace-nowrap rounded-md bg-background px-2.5 py-1.5 font-secondary text-xs group-hover:text-text">
                    <VariantIcon className="size-3 text-textAccent" />
                    {variantsCount} {t('variants count', { count: variantsCount })}
                </div>
            )}
        </>
    );
};
