import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';

const placeholderItemTwClass =
    'w-10 sm:w-12 lg:w-auto flex flex-col items-center justify-center gap-1 rounded-tr-none text-xs font-semibold text-link-inverted-default no-underline transition-colors hover:text-link-inverted-hovered hover:no-underline font-secondary';

export const MenuIconicPlaceholder: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [storesUrl] = getInternationalizedStaticUrls(['/stores'], url);

    return (
        <ul className="flex lg:gap-7">
            <li className="max-lg:hidden">
                <ExtendedNextLink className={placeholderItemTwClass} href={storesUrl} type="stores">
                    <MarkerIcon className="size-6" />
                    {t('Stores')}
                </ExtendedNextLink>
            </li>

            <li className={placeholderItemTwClass}>
                <CompareIcon className="size-6" />
                <span className="max-lg:hidden">{t('Comparison')}</span>
            </li>

            <li className={placeholderItemTwClass}>
                <HeartIcon className="size-6" />
                <span className="max-lg:hidden">{t('Wishlist')}</span>
            </li>

            <li className={twMergeCustom(placeholderItemTwClass, 'lg:w-[72px]')}>
                <UserIcon className="size-6" />
                <Skeleton className="h-4 w-16 rounded-sm max-lg:hidden" />
            </li>
        </ul>
    );
};
