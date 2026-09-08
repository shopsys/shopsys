import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { UndoIcon } from 'components/Basic/Icon/UndoIcon';
import { Button } from 'components/Forms/Button/Button';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'react-toastify';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

export const COMPARISON_UNDO_TOAST_ID = 'comparison-undo';

export const ProductComparisonUndoAction: FC<{ onUndo: () => Promise<boolean> }> = ({ onUndo }) => {
    const { t } = useTranslation();
    const [pending, setPending] = useState(false);
    const mounted = useRef(true);
    const buttonRef = useRef<HTMLDivElement>(null);
    useEffect(() => {
        mounted.current = true;
        return () => {
            mounted.current = false;
        };
    }, []);

    const dismiss = () => {
        if (!mounted.current) {
            return;
        }
        if (buttonRef.current?.contains(document.activeElement)) {
            requestAnimationFrame(() => document.querySelector<HTMLElement>('[data-tid="page_title"]')?.focus());
        }
        toast.dismiss(COMPARISON_UNDO_TOAST_ID);
    };

    const handleUndo = async () => {
        setPending(true);
        toast.update(COMPARISON_UNDO_TOAST_ID, { autoClose: false });
        let success = false;
        try {
            success = await onUndo();
        } catch {
            showErrorMessage(t('Unable to add product to comparison.'));
        } finally {
            if (mounted.current) {
                if (success) {
                    dismiss();
                } else {
                    setPending(false);
                }
            }
        }
    };

    const handleBlur = (event: React.FocusEvent<HTMLButtonElement>) => {
        if (!pending && !event.currentTarget.contains(event.relatedTarget)) {
            toast.play({ id: COMPARISON_UNDO_TOAST_ID });
        }
    };

    return (
        <div className="shrink-0" ref={buttonRef}>
            <Button
                onFocus={() => toast.pause({ id: COMPARISON_UNDO_TOAST_ID })}
                onBlur={handleBlur}
                aria-busy={pending}
                className="text-text-default hover:text-text-default active:text-text-default"
                disabled={pending}
                size="small"
                variant="tertiary"
                onClick={handleUndo}
            >
                {pending ? (
                    <SpinnerIcon aria-hidden="true" className="size-4 animate-spin" />
                ) : (
                    <UndoIcon aria-hidden="true" className="size-4" />
                )}
                {t('Undo')}
            </Button>
        </div>
    );
};
