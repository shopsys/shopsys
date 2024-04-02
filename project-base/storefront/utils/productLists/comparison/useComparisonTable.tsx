import { useState } from 'react';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';
import { useScrollTop } from 'utils/ui/useScrollTop';
import { useComponentUpdate } from 'utils/useComponentUpdate';

export const useComparisonTable = (
    productsCompareCount: number,
): {
    isArrowLeftActive: boolean;
    isArrowRightActive: boolean;
    isArrowLeftShowed: boolean;
    isArrowRightShowed: boolean;
    tableStickyHeadActive: boolean;
    handleSlideLeft: () => void;
    handleSlideRight: () => void;
    calcMaxMarginLeft: () => void;
    tableMarginLeft: number;
} => {
    const [isArrowLeftActive, setArrowLeftActive] = useState(true);
    const [isArrowRightActive, setArrowRightActive] = useState(true);
    const [isArrowLeftShowed, setArrowLeftShowed] = useState(true);
    const [isArrowRightShowed, setArrowRightShowed] = useState(true);
    const [tableMarginLeft, setTableMarginLeft] = useState(0);
    const [tableMaxMarginLeft, setTableMaxMarginLeft] = useState(0);
    const [tableStickyHeadActive, setTableStickyHeadActive] = useState(false);
    const { width } = useGetWindowSize();
    const tableY = useScrollTop('js-table-compare');

    const handleSlideLeft = () => {
        const marginLeft = tableMarginLeft;
        let newMarginLeft = 0;
        let productMarginLeft = 0;

        for (let i = 0; i < productsCompareCount; i++) {
            productMarginLeft += document.getElementById('js-table-compare-product')!.getBoundingClientRect().width;
            if (productMarginLeft < marginLeft) {
                newMarginLeft = productMarginLeft;
            } else {
                break;
            }
        }

        setMargin(newMarginLeft);
    };

    const handleSlideRight = () => {
        const marginLeft = tableMarginLeft;
        let productMarginLeft = 0;

        for (let i = 0; i < productsCompareCount; i++) {
            productMarginLeft += document.getElementById('js-table-compare-product')!.getBoundingClientRect().width;
            if (productMarginLeft > marginLeft) {
                setMargin(productMarginLeft);
                break;
            }
        }
    };

    const setMargin = (marginLeft: number) => {
        if (marginLeft > 0) {
            setArrowLeftActive(true);
        } else {
            setArrowLeftActive(false);
        }

        if (marginLeft > tableMaxMarginLeft) {
            // eslint-disable-next-line no-param-reassign
            marginLeft = tableMaxMarginLeft;
            setArrowRightActive(false);
        } else {
            setArrowRightActive(true);
        }

        setTableMarginLeft(marginLeft);
    };

    const calcMaxMarginLeft = () => {
        const tableWrapperWidth = document.getElementById('js-table-compare-wrap')!.getBoundingClientRect().width;
        const columnsWidth = document.getElementById('js-table-compare')!.getBoundingClientRect().width;
        setTableMaxMarginLeft(Math.max(0, columnsWidth - tableWrapperWidth));
    };

    useComponentUpdate(() => {
        const marginLeft = tableMarginLeft;

        calcMaxMarginLeft();
        setMargin(marginLeft);

        if (tableMaxMarginLeft > 0) {
            setArrowLeftShowed(true);
            setArrowRightShowed(true);
        } else {
            setArrowLeftShowed(false);
            setArrowRightShowed(false);
        }
    }, [width, tableMaxMarginLeft]);

    useComponentUpdate(() => {
        if (tableY < -150) {
            setTableStickyHeadActive(true);
        } else {
            setTableStickyHeadActive(false);
        }
    }, [tableY]);

    return {
        isArrowLeftActive,
        isArrowRightActive,
        isArrowLeftShowed,
        isArrowRightShowed,
        tableStickyHeadActive,
        handleSlideLeft,
        handleSlideRight,
        calcMaxMarginLeft,
        tableMarginLeft,
    };
};
