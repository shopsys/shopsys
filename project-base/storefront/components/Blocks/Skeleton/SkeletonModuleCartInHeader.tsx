import { CartIcon } from 'components/Basic/Icon/CartIcon';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

export const SkeletonModuleCartInHeader: FC = () => {
    const { t } = useTranslation();
    return (
        <div className="order-3 flex vl:order-4">
            <div className="hidden h-11 min-w-[151px] cursor-pointer items-center justify-center gap-x-2 rounded-lg border border-actionPrimaryText px-3 text-actionPrimaryText no-underline transition-all hover:no-underline group-hover:shadow-lg lg:flex">
                <span className="relative flex text-lg">
                    <CartIcon className="size-6" />
                </span>
                <span className="hidden font-secondary text-sm font-bold lg:block">{t('Empty')}</span>
            </div>
            <div
                className={twJoin(
                    'flex h-full w-full items-center justify-center rounded-lg border p-3 lg:hidden',
                    'border-actionPrimaryBorder bg-actionPrimaryBackground text-actionPrimaryText',
                    'hover:border-actionPrimaryBorderHovered hover:bg-actionPrimaryBackgroundHovered hover:text-actionPrimaryTextHovered',
                    'active:border-actionPrimaryBorderActive active:bg-actionPrimaryBackgroundActive active:text-actionPrimaryTextActive',
                )}
            >
                <CartIcon className="size-6" />
            </div>
        </div>
    );
};
