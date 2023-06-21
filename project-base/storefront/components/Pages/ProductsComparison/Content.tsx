import { Body } from './Body';
import { ButtonRemoveAll } from './ButtonRemoveAll';
import { Head } from './Head';
import { HeadSticky } from './HeadSticky';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { ComparedProductFragmentApi } from 'graphql/generated';
import { canUseDom } from 'helpers/misc/canUseDom';
import { useHandleCompareTable } from 'hooks/product/useHandleCompareTable';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffect, useMemo } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type ContentProps = {
    productsCompare: ComparedProductFragmentApi[];
};

export const Content: FC<ContentProps> = (props) => {
    const t = useTypedTranslationFunction();
    const {
        isArrowLeftActive,
        isArrowRightActive,
        isArrowLeftShowed,
        isArrowRightShowed,
        handleSlideLeft,
        handleSlideRight,
        calcMaxMarginLeft,
        tableMarginLeft,
    } = useHandleCompareTable(props.productsCompare.length);

    const getParametersDataState = useMemo(() => {
        const parametersData: { name: string; values: string[] }[] = [];
        props.productsCompare.forEach((product) => {
            product.parameters.forEach((parameter) => {
                const indexOfParameter = parametersData.findIndex((item) => item.name === parameter.name);

                if (indexOfParameter === -1) {
                    parametersData.push({ name: parameter.name, values: [] });
                }
            });
        });

        props.productsCompare.forEach((product, productIndex) => {
            product.parameters.forEach((parameter) => {
                const indexOfParameter = parametersData.findIndex((item) => item.name === parameter.name);

                parametersData[indexOfParameter].values.push(parameter.values[0].text);
            });

            for (let i = 0; i < parametersData.length; i++) {
                // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                if (parametersData[i].values[productIndex] === undefined) {
                    parametersData[i].values.push('-');
                }
            }
        });

        return parametersData;
    }, [props.productsCompare]);

    useEffect(() => {
        if (typeof window !== 'undefined') {
            if (canUseDom()) {
                calcMaxMarginLeft();
            }
        }
    }, [calcMaxMarginLeft]);

    return (
        <>
            <div className="mb-8 flex items-end">
                <Heading type="h1" className="mb-0 w-full lg:w-auto lg:flex-1">
                    {t('Product comparison')}&nbsp;({props.productsCompare.length})
                </Heading>
            </div>
            <ButtonRemoveAll displayMobile />
            <div className="relative mb-24 overflow-hidden" id="js-table-compare-wrap">
                <div className="mb-1 flex justify-end gap-3">
                    <ContentArrow
                        isActive={isArrowLeftActive}
                        isShowed={isArrowLeftShowed}
                        onClick={() => handleSlideLeft()}
                    />
                    <ContentArrow
                        isActive={isArrowRightActive}
                        isShowed={isArrowRightShowed}
                        onClick={() => handleSlideRight()}
                        isRight
                    />
                </div>
                <HeadSticky productsCompare={props.productsCompare} tableMarginLeft={tableMarginLeft} />
                <div>
                    <table
                        className="table-fixed border-collapse transition-all"
                        style={{ marginLeft: -tableMarginLeft }}
                        id="js-table-compare"
                    >
                        <Head productsCompare={props.productsCompare} />
                        <Body productsCompare={props.productsCompare} parametersDataState={getParametersDataState} />
                    </table>
                </div>
            </div>
        </>
    );
};

type ContentArrowProps = { onClick: () => void; isActive: boolean; isRight?: boolean; isShowed?: boolean };

const ContentArrow: FC<ContentArrowProps> = ({ isActive, isRight, isShowed, onClick }) => (
    <button
        className={twMergeCustom(
            'absolute right-0 top-40 z-[2] h-10 w-10 cursor-pointer items-center justify-center rounded border border-greenVeryLight bg-greyVeryLight transition-colors vl:static',
            isActive ? 'hover:bg-greyLight' : 'cursor-default border border-greyLight bg-white',

            !isRight && 'right-auto left-0',
            isShowed ? 'flex' : 'hidden',
        )}
        disabled={!isActive}
        onClick={onClick}
    >
        <Icon
            className={twJoin('w-5 text-dark', isRight ? '-rotate-90' : 'rotate-90', !isActive && 'text-greyLight')}
            iconType="icon"
            icon="Arrow"
        />
    </button>
);
