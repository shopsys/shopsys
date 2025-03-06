import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { CartCount } from 'components/Layout/Header/Cart/CartCount';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

export const SkeletonModuleCartInHeader: FC = () => {
    const { t } = useTranslation();
    return (
        <div className="vl:order-4 order-3 flex">
            <div className="border-actionPrimaryText text-actionPrimaryText hidden h-11 min-w-[151px] cursor-pointer items-center justify-center gap-x-2 rounded-lg border px-3 no-underline transition-all group-hover:shadow-lg hover:no-underline lg:flex">
                <span className="relative flex text-lg">
                    <CartIcon className="size-6" />
                    <CartCount>0</CartCount>
                </span>
                <span className="font-secondary hidden text-sm font-bold lg:block">{t('Empty')}</span>
            </div>
            <div className="vl:hidden flex cursor-pointer items-center justify-center text-lg outline-none">
                <div
                    className={twJoin(
                        'relative flex h-full w-full items-center justify-center rounded-md border p-3 no-underline transition-colors hover:no-underline',
                        'border-actionPrimaryBorder bg-actionPrimaryBackground text-actionPrimaryText',
                        'hover:border-actionPrimaryBorderHovered hover:bg-actionPrimaryBackgroundHovered hover:text-actionPrimaryTextHovered',
                        'active:border-actionPrimaryBorderActive active:bg-actionPrimaryBackgroundActive active:text-actionPrimaryTextActive',
                    )}
                >
                    <CartIcon className="size-6" />
                    <CartCount>0</CartCount>
                </div>
            </div>
        </div>
    );
};
