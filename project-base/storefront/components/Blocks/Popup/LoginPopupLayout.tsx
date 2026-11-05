import { Popup } from 'components/Layout/Popup/Popup';
import { twJoin } from 'tailwind-merge';
import { useMediaMin } from 'utils/ui/useMediaMin';

type LoginPopupLayoutProps = {
    title: string;
    isWide?: boolean;
    focusFallbackSelector?: string;
    children: React.ReactNode;
};

export const LoginPopupLayout: FC<LoginPopupLayoutProps> = ({ title, isWide, focusFallbackSelector, children }) => {
    const isDesktop = useMediaMin('vl');

    if (isDesktop === undefined) {
        return null;
    }

    return (
        <Popup
            className={twJoin(
                'p-0',
                isDesktop && 'max-h-[90dvh] rounded-2xl',
                isDesktop && isWide && 'w-[min(58rem,calc(100vw-2.5rem))]',
            )}
            contentClassName={twJoin(
                'min-h-0 overflow-y-auto overscroll-contain',
                isDesktop ? 'rounded-2xl' : 'pb-[env(safe-area-inset-bottom)]',
            )}
            headerClassName="absolute top-3 right-3 z-above mb-0 rounded-full bg-background-default"
            focusFallbackSelector={focusFallbackSelector}
            isDrawer={!isDesktop}
            isTitleHidden
            size="small"
            title={title}
        >
            {children}
        </Popup>
    );
};
