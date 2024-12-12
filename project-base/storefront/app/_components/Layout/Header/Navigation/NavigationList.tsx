'use client';

import { NavigationItem } from './NavigationItem';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { useState } from 'react';

export type NavigationListProps = {
    navigation: TypeCategoriesByColumnFragment[];
};

export const NavigationList: FC<NavigationListProps> = ({ navigation }) => {
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
        <nav className="hidden lg:block">
            <ul className="relative flex w-full" onMouseLeave={handleEnableAnimation}>
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
