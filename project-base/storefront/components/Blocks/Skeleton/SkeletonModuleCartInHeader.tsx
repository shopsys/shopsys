import { CartIcon } from 'components/Basic/Icon/CartIcon';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

export const SkeletonModuleCartInHeader: FC = () => {
    const { t } = useTranslation();
    return (
        <div className="vl:order-4 order-3 flex">
            <div className="border-actionPrimaryText text-actionPrimaryText hidden h-11 min-w-[151px] cursor-pointer items-center justify-center gap-x-2 rounded-lg border px-3 no-underline transition-all group-hover:shadow-lg hover:no-underline lg:flex">
                <span className="relative flex text-lg">
                    <CartIcon className="size-6" />
                </span>
                <span className="font-secondary hidden text-sm font-bold lg:block">{t('Empty')}</span>
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
