import { NavigationItem } from './NavigationItem';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useState, useRef, useEffect } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import useWindowDimensions from 'utils/useWindowDimensions';

export type NavigationProps = {
    navigation: TypeCategoriesByColumnFragment[];
};

export const Navigation: FC<NavigationProps> = ({ navigation }) => {
    const { t } = useTranslation();
    const [isFirstHover, setIsFirstHover] = useState(false);
    const [isAnimationDisabled, setIsAnimationDisabled] = useState(false);
    const showNavigationShadow = useSessionStore((s) => s.showNavigationShadow);
    const setShowNavigationShadow = useSessionStore((s) => s.setShowNavigationShadow);
    const navigationRef = useRef<HTMLUListElement>(null);
    const windowDimensions = useWindowDimensions();

    const checkOverflow = () => {
        if (navigationRef.current) {
            const { scrollWidth, clientWidth, scrollLeft } = navigationRef.current;
            const isScrolledToEnd = Math.abs(scrollWidth - clientWidth - scrollLeft) < 1;

            setShowNavigationShadow(scrollWidth > clientWidth && !isScrolledToEnd);
        }
    };

    useEffect(() => {
        checkOverflow();
    }, [windowDimensions, navigation]);

    const handleScroll = () => {
        checkOverflow();
    };

    const handleAnimations = () => {
        if (!isFirstHover) {
            setIsFirstHover(true);
            return;
        }
        setIsAnimationDisabled(true);
    };

    const handleEnableAnimation = () => {
        setIsAnimationDisabled(false);
        setIsFirstHover(false);
    };

    return (
        <nav aria-label={t('Main navigation')} className="relative" id="main-navigation">
            <ul
                ref={navigationRef}
                className={twJoin(
                    'hide-scrollbar hidden w-full overflow-x-auto overflow-y-hidden lg:flex',
                    showNavigationShadow &&
                        'after:from-background-brand after:z-above transition-all after:absolute after:top-1/2 after:-right-1 after:h-7 after:w-20 after:-translate-y-1/2 after:bg-gradient-to-l after:from-30% after:to-transparent after:to-80% after:blur-xs',
                )}
                onMouseLeave={handleEnableAnimation}
                onScroll={handleScroll}
            >
                {navigation.map((navigationItem, index) => (
                    <NavigationItem
                        key={index}
                        handleAnimations={handleAnimations}
                        isAnimationDisabled={isAnimationDisabled}
                        navigationItem={navigationItem}
                    />
                ))}
            </ul>
        </nav>
    );
};
