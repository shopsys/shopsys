import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { twJoin } from 'tailwind-merge';

type NavigationItemContentProps = {
    isDropdownTrigger: boolean;
    isMenuOpened: boolean;
    name: string;
};

export const NavigationItemContent: FC<NavigationItemContentProps> = ({ isDropdownTrigger, isMenuOpened, name }) => (
    <>
        {name}
        {isDropdownTrigger && (
            <div
                className={twJoin(
                    'ml-1 flex items-start motion-safe:transition-transform motion-safe:duration-200',
                    isMenuOpened && 'rotate-180',
                )}
            >
                <ArrowIcon
                    className={twJoin(
                        'size-5 text-link-inverted-default transition',
                        isMenuOpened && 'group-hover:text-link-inverted-hovered',
                    )}
                />
            </div>
        )}
    </>
);
