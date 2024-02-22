import { DropdownMenuContext } from './DropdownMenuContext';
import { DropdownSlideLeft } from './DropdownSlideLeft';
import { PrimaryList } from './PrimaryList';
import { SecondaryList } from './SecondaryList';
import { SubMenu } from './SubMenu';
import { TertiaryList } from './TertiaryList';
import { useNavigationQueryApi } from 'graphql/generated';
import { useState } from 'react';
import { CSSTransition } from 'react-transition-group';
import { twJoin } from 'tailwind-merge';
import { DropdownItemType, DropdownListLevels } from 'types/dropdown';

type DropdownMenuProps = {
    isMenuOpened: boolean;
    onMenuToggleHandler: () => void;
};

export const DropdownMenu: FC<DropdownMenuProps> = ({ isMenuOpened, onMenuToggleHandler }) => {
    const [{ data: navigationData }] = useNavigationQueryApi();
    const [menuLevel, setMenuLevel] = useState<DropdownListLevels | undefined>('primary');
    const [historyOfIndexes, setHistoryOfIndexes] = useState<(number | string | undefined)[]>([]);
    const [slideDirection, setSlideDirection] = useState<'left' | 'right'>('right');
    const [menuHeight, setMenuHeight] = useState<number>();

    if (!navigationData?.navigation.length) {
        return null;
    }

    const calcHeight = (el: HTMLElement) => {
        setMenuHeight(el.offsetHeight);
    };

    const slideLeft = (props: { goToMenu: DropdownListLevels }) => {
        setMenuLevel(props.goToMenu);
        setSlideDirection('left');

        historyOfIndexes.pop();

        setHistoryOfIndexes(historyOfIndexes.length === 0 ? [] : [...historyOfIndexes]);
    };

    const slideRight = (props: DropdownItemType) => {
        setMenuLevel(props.goToMenu);
        setSlideDirection('right');
        setHistoryOfIndexes((oldArray: (number | string | undefined)[]) => [...oldArray, props.index]);
    };

    return (
        <div className="transition-all">
            <CSSTransition unmountOnExit classNames="dropdown" in={isMenuOpened} timeout={300} onEntering={calcHeight}>
                <DropdownMenuContext.Provider value={{ slideRight, onMenuToggleHandler }}>
                    <div
                        className="absolute left-2 right-2 top-0 z-mobileMenu cursor-auto overflow-hidden bg-white shadow-md transition-all"
                        style={{ height: menuHeight }}
                    >
                        <CSSTransition
                            unmountOnExit
                            in={menuLevel === 'primary'}
                            timeout={300}
                            classNames={twJoin(
                                'transition-transform',
                                slideDirection === 'right' ? 'menu-primary-right' : 'menu-primary-left',
                            )}
                            onEntering={calcHeight}
                        >
                            <div className="w-full pt-12">
                                <PrimaryList navigationItems={navigationData.navigation} />
                                <SubMenu />
                            </div>
                        </CSSTransition>

                        <CSSTransition
                            unmountOnExit
                            in={menuLevel === 'secondary'}
                            timeout={300}
                            classNames={twJoin(
                                'transition-transform',
                                slideDirection === 'right' ? 'menu-secondary-right' : 'menu-secondary-left',
                            )}
                            onEntering={calcHeight}
                        >
                            <div className="w-full pt-12">
                                <DropdownSlideLeft goToMenu="primary" onClickEvent={slideLeft} />
                                <SecondaryList
                                    historyOfIndexes={historyOfIndexes}
                                    navigationItems={navigationData.navigation}
                                />
                            </div>
                        </CSSTransition>

                        <CSSTransition
                            unmountOnExit
                            in={menuLevel === 'tertiary'}
                            timeout={300}
                            classNames={twJoin(
                                'transition-transform',
                                slideDirection === 'right' ? 'menu-tertiary-right' : 'menu-tertiary-left',
                            )}
                            onEntering={calcHeight}
                        >
                            <div className="w-full pt-12">
                                <DropdownSlideLeft goToMenu="secondary" onClickEvent={slideLeft} />
                                <TertiaryList
                                    historyOfIndexes={historyOfIndexes}
                                    navigationItems={navigationData.navigation}
                                />
                            </div>
                        </CSSTransition>
                    </div>
                </DropdownMenuContext.Provider>
            </CSSTransition>
        </div>
    );
};
