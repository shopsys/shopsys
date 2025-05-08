import { NavigationItem } from './NavigationItem';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { useState } from 'react';

export type NavigationProps = {
    navigation: TypeCategoriesByColumnFragment[];
};

export const Navigation: FC<NavigationProps> = ({ navigation }) => {
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
        <nav>
            <ul className="relative hidden w-full lg:flex" onMouseLeave={handleEnableAnimation}>
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
