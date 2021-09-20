import { createContext, FC, useState } from 'react';
import { DropdownItemType, DropdownListLevels } from './types';
import { DropdownMenuListStyled, DropdownMenuStyled, DropdownMenuWrapperStyled } from './DropdownMenu.style';
import { CSSTransition } from 'react-transition-group';
import DropdownSlideLeft from './SlideLeft';
import { getNavigationItems } from 'connectors/navigation/Navigation';
import PrimaryList from './PrimaryList';
import SecondaryList from './SecondaryList';
import SubMenu from './SubMenu';
import TertiaryList from './TertiaryList';

export const DropdownMenuContext = createContext<{
    slideRight: (props: DropdownItemType) => void;
    onMenuToggleHandler: () => void;
}>({
    slideRight: () => undefined,
    onMenuToggleHandler: () => undefined,
});

type DropdownMenuProps = {
    isMenuOpened: boolean;
    onMenuToggleHandler: () => void;
};

const DropdownMenu: FC<DropdownMenuProps> = (props) => {
    const navigationItems = getNavigationItems();
    const [menuLevel, setMenuLevel] = useState<DropdownListLevels | undefined>('primary');
    const [historyOfIndexes, setHistoryOfIndexes] = useState<(number | string | undefined)[]>([]);
    const [slideDirection, setSlideDirection] = useState<'left' | 'right'>('right');
    const [menuHeight, setMenuHeight] = useState<number>();

    if (navigationItems.length === 0) {
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
        <DropdownMenuWrapperStyled>
            <CSSTransition
                in={props.isMenuOpened}
                timeout={500}
                classNames="dropdown"
                onEntering={calcHeight}
                unmountOnExit
            >
                <DropdownMenuContext.Provider
                    value={{ slideRight: slideRight, onMenuToggleHandler: props.onMenuToggleHandler }}
                >
                    <DropdownMenuStyled slideDirection={slideDirection} style={{ height: menuHeight }}>
                        <CSSTransition
                            in={menuLevel === 'primary'}
                            timeout={500}
                            classNames="menu-primary"
                            unmountOnExit
                            onEntering={calcHeight}
                        >
                            <DropdownMenuListStyled>
                                <PrimaryList navigationItems={navigationItems} />
                                <SubMenu />
                            </DropdownMenuListStyled>
                        </CSSTransition>

                        <CSSTransition
                            in={menuLevel === 'secondary'}
                            timeout={500}
                            classNames="menu-secondary"
                            unmountOnExit
                            onEntering={calcHeight}
                        >
                            <DropdownMenuListStyled>
                                <DropdownSlideLeft onClickEvent={slideLeft} goToMenu="primary" />
                                <SecondaryList navigationItems={navigationItems} historyOfIndexes={historyOfIndexes} />
                            </DropdownMenuListStyled>
                        </CSSTransition>

                        <CSSTransition
                            in={menuLevel === 'tertiary'}
                            timeout={500}
                            classNames="menu-tertiary"
                            unmountOnExit
                            onEntering={calcHeight}
                        >
                            <DropdownMenuListStyled>
                                <DropdownSlideLeft onClickEvent={slideLeft} goToMenu="secondary" />
                                <TertiaryList navigationItems={navigationItems} historyOfIndexes={historyOfIndexes} />
                            </DropdownMenuListStyled>
                        </CSSTransition>
                    </DropdownMenuStyled>
                </DropdownMenuContext.Provider>
            </CSSTransition>
        </DropdownMenuWrapperStyled>
    );
};

/* @component */
export default DropdownMenu;
