'use client';

import { NavigationItem } from './NavigationItem';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { useState } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';

export type NavigationProps = {
    navigation: TypeCategoriesByColumnFragment[];
    skeletonType?: PageType;
};

export const Navigation: FC<NavigationProps> = ({ navigation, skeletonType }) => {
    const [isFirstHover, setIsFirstHover] = useState(false);
    const [isAnimationDisabled, setIsAnimationDisabled] = useState(false);

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
        <ul className="relative hidden w-full lg:flex" onMouseLeave={handleEnableAnimation}>
            {navigation.map((navigationItem, index) => (
                <NavigationItem
                    key={index}
                    handleAnimations={handleAnimations}
                    isAnimationDisabled={isAnimationDisabled}
                    navigationItem={navigationItem}
                    skeletonType={skeletonType}
                />
            ))}
        </ul>
    );
};
