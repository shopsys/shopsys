import { useState } from 'react';
import { useGetWindowSize } from 'utils/ui/useGetWindowSize';
import { useComponentUpdate } from 'utils/useComponentUpdate';

export const useComparisonTable = (
    productsCompareCount: number,
): {
    isArrowLeftActive: boolean;
    isArrowRightActive: boolean;
    shouldShowArrows: boolean;
    handleSlideLeft: () => void;
    handleSlideRight: () => void;
    calcMaxMarginLeft: () => void;
    tableFirstColumnWidth: number | undefined;
    tableMarginLeft: number;
} => {
    const [isArrowLeftActive, setArrowLeftActive] = useState(true);
    const [isArrowRightActive, setArrowRightActive] = useState(true);
    const [tableMarginLeft, setTableMarginLeft] = useState(0);
    const [tableMaxMarginLeft, setTableMaxMarginLeft] = useState(0);
    const [tableFirstColumnWidth, setTableFirstColumnWidth] = useState<number>();
    const shouldShowArrows = tableMaxMarginLeft > 0;
    const { width } = useGetWindowSize();
    const getProductColumnWidth = () =>
        document.getElementById('js-table-compare-product')?.getBoundingClientRect().width ?? 0;

    const handleSlideLeft = () => {
        const marginLeft = tableMarginLeft;
        let newMarginLeft = 0;
        let productMarginLeft = 0;

        for (let i = 0; i < productsCompareCount; i++) {
            productMarginLeft += getProductColumnWidth();
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
            productMarginLeft += getProductColumnWidth();
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
            marginLeft = tableMaxMarginLeft;
            setArrowRightActive(false);
        } else {
            setArrowRightActive(true);
        }

        setTableMarginLeft(marginLeft);
    };

    const calcMaxMarginLeft = () => {
        const tableWrapperWidth = document.getElementById('js-table-compare-wrap')?.getBoundingClientRect().width ?? 0;
        const columnsWidth = document.getElementById('js-table-compare')?.getBoundingClientRect().width ?? 0;
        const firstColumnWidth = document
            .getElementById('js-table-compare-head')
            ?.firstElementChild?.getBoundingClientRect().width;

        setTableMaxMarginLeft(Math.max(0, columnsWidth - tableWrapperWidth));
        setTableFirstColumnWidth(firstColumnWidth);
    };

    useComponentUpdate(() => {
        calcMaxMarginLeft();
        setMargin(tableMarginLeft);
    }, [width, tableMaxMarginLeft]);

    return {
        isArrowLeftActive,
        isArrowRightActive,
        shouldShowArrows,
        handleSlideLeft,
        handleSlideRight,
        calcMaxMarginLeft,
        tableFirstColumnWidth,
        tableMarginLeft,
    };
};
