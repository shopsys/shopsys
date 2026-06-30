import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { CartCount } from 'components/Layout/Header/Cart/CartCount';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const SkeletonModuleCartInHeader: FC = () => {
    const { t } = useTranslation();
    return (
        <div className="order-3 vl:order-4 flex">
            <div className="vl:flex hidden h-11 min-w-37.75 cursor-pointer items-center justify-center gap-x-3 rounded-lg border border-button-primary-text-default px-3 text-button-primary-text-default no-underline transition-all hover:no-underline group-hover:shadow-lg">
                <span className="relative flex text-lg">
                    <CartIcon className="size-6" />
                    <CartCount>0</CartCount>
                </span>
                <span className="hidden font-bold font-secondary text-sm lg:block">{t('Empty')}</span>
            </div>
            <div className="relative vl:hidden">
                <div
                    className={twJoin(
                        'flex h-full w-full cursor-pointer items-center justify-center rounded-md border p-3 text-lg no-underline transition-colors hover:no-underline',
                        'border-button-primary-border-default bg-button-primary-bg-default text-button-primary-text-default',
                    )}
                >
                    <CartIcon className="size-6" />
                    <CartCount>0</CartCount>
                </div>
            </div>
        </div>
    );
};
