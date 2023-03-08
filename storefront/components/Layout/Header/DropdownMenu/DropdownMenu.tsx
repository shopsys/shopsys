import { DropdownMenuStyled, DropdownMenuWrapperStyled } from './DropdownMenu.style';
import { DropdownMenuContext } from './DropdownMenuContext';
import { PrimaryList } from './PrimaryList/PrimaryList';
import { SecondaryList } from './SecondaryList/SecondaryList';
import { DropdownSlideLeft } from './SlideLeft/DropdownSlideLeft';
import { SubMenu } from './SubMenu/SubMenu';
import { TertiaryList } from './TertiaryList/TertiaryList';
import { useNavigationQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useState } from 'react';
import { CSSTransition } from 'react-transition-group';
import { DropdownItemType, DropdownListLevels } from 'types/dropdown';

type DropdownMenuProps = {
    isMenuOpened: boolean;
    onMenuToggleHandler: () => void;
};

const TEST_IDENTIFIER = 'layout-header-dropdownmenu';

export const DropdownMenu: FC<DropdownMenuProps> = ({ isMenuOpened, onMenuToggleHandler }) => {
    const [{ data: navigationData }] = useQueryError(useNavigationQueryApi());
    const [menuLevel, setMenuLevel] = useState<DropdownListLevels | undefined>('primary');
    const [historyOfIndexes, setHistoryOfIndexes] = useState<(number | string | undefined)[]>([]);
    const [slideDirection, setSlideDirection] = useState<'left' | 'right'>('right');
    const [menuHeight, setMenuHeight] = useState<number>();

    if (navigationData?.navigation === undefined || navigationData.navigation.length === 0) {
        return null;
    }

    const calcHeight = (el: HTMLElement) => {
        setMenuHeight(el.offsetHeight);
    };

    const slideLeft = (props: { goToMenu: DropdownListLevels }) => {
        setMenuLevel(props.goToMenu);
        setSlideDirection('left');

        historyOfIndexes.pop();

        if (historyOfIndexes.length === 0) {
            setHistoryOfIndexes([]);
        } else {
            setHistoryOfIndexes([...historyOfIndexes]);
        }
    };

    const slideRight = (props: DropdownItemType) => {
        setMenuLevel(props.goToMenu);
        setSlideDirection('right');
        setHistoryOfIndexes((oldArray: (number | string | undefined)[]) => [...oldArray, props.index]);
    };

    return (
        <DropdownMenuWrapperStyled data-testid={TEST_IDENTIFIER}>
            <CSSTransition in={isMenuOpened} timeout={500} classNames="dropdown" onEntering={calcHeight} unmountOnExit>
                <DropdownMenuContext.Provider value={{ slideRight, onMenuToggleHandler }}>
                    <DropdownMenuStyled slideDirection={slideDirection} style={{ height: menuHeight }}>
                        <CSSTransition
                            in={menuLevel === 'primary'}
                            timeout={500}
                            classNames="menu-primary"
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
                            timeout={500}
                            classNames="menu-secondary"
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
                            timeout={500}
                            classNames="menu-tertiary"
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
                    </DropdownMenuStyled>
                </DropdownMenuContext.Provider>
            </CSSTransition>
        </DropdownMenuWrapperStyled>
    );
};
