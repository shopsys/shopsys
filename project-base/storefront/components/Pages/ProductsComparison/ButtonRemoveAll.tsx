import { Icon } from 'components/Basic/Icon/Icon';
import { useHandleCompare } from 'hooks/product/useHandleCompare';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { twJoin } from 'tailwind-merge';

type ButtonRemoveAllProps = {
    displayMobile?: boolean;
};

export const ButtonRemoveAll: FC<ButtonRemoveAllProps> = ({ displayMobile }) => {
    const t = useTypedTranslationFunction();
    const { handleRemoveAllFromComparison } = useHandleCompare('');

    return (
        <div
            className={twJoin(
                'hidden cursor-pointer items-center rounded bg-greyVeryLight py-2 px-4 transition-colors hover:bg-greyLighter sm:inline-flex',
                displayMobile && 'mb-5 inline-flex sm:hidden',
            )}
            onClick={handleRemoveAllFromComparison}
        >
            <span className="mr-3 text-sm">{t('Delete all')}</span>
            <Icon iconType="icon" icon="RemoveThin" className="w-3" />
        </div>
    );
};
