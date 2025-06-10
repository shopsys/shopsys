import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { CartCount } from 'components/Layout/Header/Cart/CartCount';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

export const SkeletonModuleCartInHeader: FC = () => {
    const { t } = useTranslation();
    return (
        <div className="vl:order-4 order-3 flex">
            <div className="border-button-primary-text-default text-button-primary-text-default vl:flex hidden h-11 min-w-[151px] cursor-pointer items-center justify-center gap-x-2 rounded-lg border px-3 no-underline transition-all group-hover:shadow-lg hover:no-underline">
                <span className="relative flex text-lg">
                    <CartIcon className="size-6" />
                    <CartCount>0</CartCount>
                </span>
                <span className="font-secondary hidden text-sm font-bold lg:block">{t('Empty')}</span>
            </div>
            <div className="vl:hidden relative">
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
