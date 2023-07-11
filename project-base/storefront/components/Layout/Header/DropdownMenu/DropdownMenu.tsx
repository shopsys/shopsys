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

const TEST_IDENTIFIER = 'layout-header-dropdownmenu';

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
        <div className="transition-all" data-testid={TEST_IDENTIFIER}>
            <CSSTransition in={isMenuOpened} timeout={300} classNames="dropdown" onEntering={calcHeight} unmountOnExit>
                <DropdownMenuContext.Provider value={{ slideRight, onMenuToggleHandler }}>
                    <div
                        className="absolute left-2 right-2 top-0 z-mobileMenu cursor-auto overflow-hidden bg-white shadow-md transition-all"
                        style={{ height: menuHeight }}
                    >
                        <CSSTransition
                            in={menuLevel === 'primary'}
                            timeout={300}
                            classNames={twJoin(
                                'transition-transform',
                                slideDirection === 'right' ? 'menu-primary-right' : 'menu-primary-left',
                            )}
                            unmountOnExit
                            onEntering={calcHeight}
                        >
                            <div className="w-full pt-12">
                                <PrimaryList navigationItems={navigationData.navigation} />
                                <SubMenu />
                            </div>
                        </CSSTransition>

                        <CSSTransition
                            in={menuLevel === 'secondary'}
                            timeout={300}
                            classNames={twJoin(
                                'transition-transform',
                                slideDirection === 'right' ? 'menu-secondary-right' : 'menu-secondary-left',
                            )}
                            unmountOnExit
                            onEntering={calcHeight}
                        >
                            <div className="w-full pt-12">
                                <DropdownSlideLeft onClickEvent={slideLeft} goToMenu="primary" />
                                <SecondaryList
                                    navigationItems={navigationData.navigation}
                                    historyOfIndexes={historyOfIndexes}
                                />
                            </div>
                        </CSSTransition>

                        <CSSTransition
                            in={menuLevel === 'tertiary'}
                            timeout={300}
                            classNames={twJoin(
                                'transition-transform',
                                slideDirection === 'right' ? 'menu-tertiary-right' : 'menu-tertiary-left',
                            )}
                            unmountOnExit
                            onEntering={calcHeight}
                        >
                            <div className="w-full pt-12">
                                <DropdownSlideLeft onClickEvent={slideLeft} goToMenu="secondary" />
                                <TertiaryList
                                    navigationItems={navigationData.navigation}
                                    historyOfIndexes={historyOfIndexes}
                                />
                            </div>
                        </CSSTransition>
                    </div>
                </DropdownMenuContext.Provider>
            </CSSTransition>
        </div>
    );
};
