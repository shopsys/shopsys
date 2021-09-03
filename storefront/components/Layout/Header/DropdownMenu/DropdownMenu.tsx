import { DropdownIndexType, DropdownListLevels, DropdownSlideToType } from './types';
import { DropdownMenuListStyled, DropdownMenuStyled, DropdownMenuWrapperStyled } from './DropdownMenu.style';
import { FC, useState } from 'react';
import { CSSTransition } from 'react-transition-group';
import DropdownSlideTo from './SlideTo';
import { getNavigationItems } from '../../../../connectors/navigation/Navigation';
import PrimaryList from './PrimaryList';
import SecondaryList from './SecondaryList';
import SubMenu from './SubMenu';
import TertiaryList from './TertiaryList';
import { useTranslation } from 'react-i18next';

type DropdownMenuProps = {
    isMenuOpened: boolean;
};

type ChangeStateType = {
    goToMenu: DropdownListLevels;
    slideTo: DropdownSlideToType;
    index: DropdownIndexType;
};

const DropdownMenu: FC<DropdownMenuProps> = (props) => {
    const { t } = useTranslation();
    const navigationItems = getNavigationItems();
    const [menuLevel, setMenuLevel] = useState<DropdownListLevels>('primary');
    const [historyOfIndexes, setHistoryOfIndexes] = useState<(number | string)[]>([]);
    const [slideTo, setSlideTo] = useState<DropdownSlideToType>('right');
    const [menuHeight, setMenuHeight] = useState<number>();

    if (navigationItems.length === 0) {
        return null;
    }

    const calcHeight = (el: HTMLElement) => {
        setMenuHeight(el.offsetHeight);
    };

    const onMenuLevel = (props: ChangeStateType) => {
        if (props.goToMenu !== undefined) {
            setMenuLevel(props.goToMenu);
        }
    };

    const onSlideTo = (props: ChangeStateType) => {
        if (props.slideTo !== undefined) {
            setSlideTo(props.slideTo);
        }
    };

    const onSlideToIn = (props: ChangeStateType) => {
        if (props.index !== undefined) {
            setHistoryOfIndexes((oldArray: (number | string)[]) => [...oldArray, props.index]);
        }
    };

    const onSlideToOut = (props: ChangeStateType) => {
        if (props.slideTo === 'left') {
            historyOfIndexes.pop();

            if (historyOfIndexes.length === 0) {
                setHistoryOfIndexes([]);
            } else {
                setHistoryOfIndexes([...historyOfIndexes]);
            }
        }
    };

    const changeState = (props: ChangeStateType) => {
        onMenuLevel(props);
        onSlideTo(props);
        onSlideToIn(props);
        onSlideToOut(props);
    };

    return (
        <DropdownMenuWrapperStyled>
            <CSSTransition
                in={props.isMenuOpened}
                timeout={500}
                classNames="dropdown"
                onEnter={calcHeight}
                unmountOnExit
            >
                <DropdownMenuStyled slideTo={slideTo} style={{ height: menuHeight }}>
                    <CSSTransition
                        in={menuLevel === 'primary'}
                        timeout={500}
                        classNames="menu-primary"
                        unmountOnExit
                        onEnter={calcHeight}
                    >
                        <DropdownMenuListStyled>
                            <PrimaryList navigationItems={navigationItems} changeState={changeState} />
                            <SubMenu />
                        </DropdownMenuListStyled>
                    </CSSTransition>

                    <CSSTransition
                        in={menuLevel === 'secondary'}
                        timeout={500}
                        classNames="menu-secondary"
                        unmountOnExit
                        onEnter={calcHeight}
                    >
                        <DropdownMenuListStyled>
                            <DropdownSlideTo
                                changeState={changeState}
                                goToMenu="primary"
                                slideTo="left"
                                type="stepBack"
                                iconText={t('Back')}
                            />
                            <SecondaryList
                                navigationItems={navigationItems}
                                changeState={changeState}
                                historyOfIndexes={historyOfIndexes}
                            />
                        </DropdownMenuListStyled>
                    </CSSTransition>

                    <CSSTransition
                        in={menuLevel === 'tertiary'}
                        timeout={500}
                        classNames="menu-tertiary"
                        unmountOnExit
                        onEnter={calcHeight}
                    >
                        <DropdownMenuListStyled>
                            <DropdownSlideTo
                                changeState={changeState}
                                goToMenu="secondary"
                                slideTo="left"
                                type="stepBack"
                                iconText={t('Back')}
                            />
                            <TertiaryList
                                navigationItems={navigationItems}
                                changeState={changeState}
                                historyOfIndexes={historyOfIndexes}
                            />
                        </DropdownMenuListStyled>
                    </CSSTransition>
                </DropdownMenuStyled>
            </CSSTransition>
        </DropdownMenuWrapperStyled>
    );
};

/* @component */
export default DropdownMenu;
