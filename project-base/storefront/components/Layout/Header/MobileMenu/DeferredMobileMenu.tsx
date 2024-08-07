import { HamburgerMenu } from 'components/Layout/Header/HamburgerMenu/HamburgerMenu';
import dynamic from 'next/dynamic';
import { desktopFirstSizes } from 'utils/mediaQueries';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';
import { useDeferredRender } from 'utils/useDeferredRender';

const MobileMenu = dynamic(() => import('./MobileMenu').then((component) => component.MobileMenu), {
    ssr: false,
    loading: () => <HamburgerMenu onClick={undefined} />,
});

export const DeferredMobileMenu: FC = () => {
    const { width: windowWidth } = useGetWindowSize();
    const isDesktop = windowWidth > desktopFirstSizes.tablet;
    const isRecognizingWindowWidth = windowWidth < 0;

    const shouldRender = useDeferredRender('mobile_menu');

    return (
        <div className="order-1 flex cursor-pointer items-center justify-center text-lg lg:hidden">
            {shouldRender && !isRecognizingWindowWidth && !isDesktop ? (
                <MobileMenu />
            ) : (
                <HamburgerMenu onClick={undefined} />
            )}
        </div>
    );
};
