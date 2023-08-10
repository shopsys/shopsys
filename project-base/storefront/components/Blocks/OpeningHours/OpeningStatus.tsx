import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { twMergeCustom } from 'helpers/twMerge';

export const OpeningStatus: FC<{ isOpen: boolean }> = ({ isOpen, className }) => {
    const t = useTypedTranslationFunction();

    return (
        <div
            className={twMergeCustom(
                'inline-block rounded py-1 px-3 font-medium uppercase leading-normal text-white',
                isOpen ? 'bg-greenDark' : 'bg-red',
                className,
            )}
        >
            {isOpen ? t('Currently open') : t('Currently close')}
        </div>
    );
};
