import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { StoreIcon } from 'components/Basic/Icon/StoreIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const placeholderItemTwClass =
    'w-10 sm:w-12 lg:w-auto flex flex-col items-center justify-center gap-1 text-xs rounded-sm font-semibold text-link-inverted-default no-underline transition-colors hover:text-link-inverted-hovered hover:no-underline font-secondary';

export const MenuIconicPlaceholder: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [storesUrl] = getInternationalizedStaticUrls(['/stores'], url);

    return (
        <ul className="flex lg:gap-7">
            <li className="max-lg:hidden">
                <ExtendedNextLink className={placeholderItemTwClass} href={storesUrl} type="stores">
                    <StoreIcon className="size-6" />
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

            <li className={placeholderItemTwClass}>
                <UserIcon className="size-6" />
                <span className="max-lg:hidden">{t('Account')}</span>
            </li>
        </ul>
    );
};
