import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { SortIcon } from 'components/Basic/Icon/SortIcon';
import { Button } from 'components/Forms/Button/Button';
import { TypeProductReviewOrderingModeEnum } from 'graphql/types';
import { useEffect, useId, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import useClickClosePopup from 'utils/ui/useClickClosePopup';

type ProductReviewsSortingProps = {
    activeOrderingMode: TypeProductReviewOrderingModeEnum;
    onChangeOrderingMode: (orderingMode: TypeProductReviewOrderingModeEnum) => void;
};

export const ProductReviewsSorting: FC<ProductReviewsSortingProps> = ({ activeOrderingMode, onChangeOrderingMode }) => {
    const { t } = useTranslation();
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const mobileMenuId = useId();
    const mobileWrapperRef = useRef<HTMLDivElement>(null);

    const orderingOptions = [
        { value: TypeProductReviewOrderingModeEnum.Newest, label: t('From newest'), desktopLabel: t('Newest') },
        {
            value: TypeProductReviewOrderingModeEnum.HighestRating,
            label: t('From highest rating'),
            desktopLabel: t('Highest rated'),
        },
        {
            value: TypeProductReviewOrderingModeEnum.LowestRating,
            label: t('From lowest rating'),
            desktopLabel: t('Lowest rated'),
        },
    ];
    const activeOrderingOption = orderingOptions.find((option) => option.value === activeOrderingMode);

    useClickClosePopup([mobileWrapperRef], () => setIsMobileMenuOpen(false));

    useEffect(() => {
        if (!isMobileMenuOpen) {
            return;
        }

        const handleEscapeKey = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setIsMobileMenuOpen(false);
                mobileWrapperRef.current?.querySelector('button')?.focus();
            }
        };

        window.addEventListener('keydown', handleEscapeKey);

        return () => window.removeEventListener('keydown', handleEscapeKey);
    }, [isMobileMenuOpen]);

    const handleOrderingModeChange = (orderingMode: TypeProductReviewOrderingModeEnum) => {
        onChangeOrderingMode(orderingMode);
        setIsMobileMenuOpen(false);
    };

    const handleMobileOrderingModeChange = (orderingMode: TypeProductReviewOrderingModeEnum) => {
        handleOrderingModeChange(orderingMode);
        mobileWrapperRef.current?.querySelector('button')?.focus();
    };

    return (
        <>
            <fieldset className="hidden lg:block">
                <legend className="sr-only">{t('Sort reviews')}</legend>

                <div className="flex flex-wrap gap-5">
                    {orderingOptions.map((option) => {
                        const isActive = option.value === activeOrderingMode;

                        return (
                            <button
                                key={option.value}
                                aria-pressed={isActive}
                                className={twJoin(
                                    'relative min-h-11 cursor-pointer rounded-sm px-1 font-secondary text-sm transition-colors after:absolute after:right-1 after:bottom-0 after:left-1 after:h-0.5 after:rounded-full after:transition-colors',
                                    isActive
                                        ? 'font-bold text-text-default after:bg-link-default'
                                        : 'font-semibold text-text-less after:bg-transparent hover:text-text-default',
                                )}
                                type="button"
                                onClick={() => handleOrderingModeChange(option.value)}
                            >
                                {option.desktopLabel}
                            </button>
                        );
                    })}
                </div>
            </fieldset>

            <div ref={mobileWrapperRef} className="relative lg:hidden">
                <Button
                    aria-controls={mobileMenuId}
                    aria-expanded={isMobileMenuOpen}
                    className="w-full justify-between"
                    size="large"
                    variant="secondary"
                    onClick={() => setIsMobileMenuOpen((isOpen) => !isOpen)}
                >
                    <span className="flex min-w-0 items-center gap-2">
                        <SortIcon aria-hidden className="size-5 shrink-0" />
                        <span className="truncate">
                            {t('Sort')}: {activeOrderingOption?.label}
                        </span>
                    </span>

                    <ArrowIcon
                        aria-hidden
                        className={twJoin('size-5 shrink-0 transition-transform', isMobileMenuOpen && 'rotate-180')}
                    />
                </Button>

                <fieldset
                    id={mobileMenuId}
                    className={twJoin(
                        'absolute top-full right-0 left-0 z-above mt-2 flex-col divide-y divide-border-less overflow-hidden rounded-md border border-border-less bg-background-default shadow-lg',
                        isMobileMenuOpen ? 'flex' : 'hidden',
                    )}
                >
                    <legend className="sr-only">{t('Sort reviews')}</legend>

                    {orderingOptions.map((option) => {
                        const isActive = option.value === activeOrderingMode;

                        return (
                            <button
                                key={option.value}
                                aria-pressed={isActive}
                                className={twJoin(
                                    'flex min-h-12 w-full cursor-pointer items-center justify-between gap-3 px-4 py-3 text-left font-secondary font-semibold text-sm hover:bg-background-more',
                                    isActive && 'bg-background-more text-text-default',
                                )}
                                type="button"
                                onClick={() => handleMobileOrderingModeChange(option.value)}
                            >
                                {option.label}

                                {isActive && <CheckmarkIcon aria-hidden className="size-4 shrink-0" />}
                            </button>
                        );
                    })}
                </fieldset>
            </div>
        </>
    );
};
