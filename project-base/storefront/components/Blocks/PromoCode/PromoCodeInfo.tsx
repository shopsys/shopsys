import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { LabelLink } from 'components/Basic/LabelLink/LabelLink';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';

type PromoCodeInfoProps = {
    promoCode: string;
    onRemovePromoCodeCallback: () => void;
};

export const PromoCodeInfo: FC<PromoCodeInfoProps> = ({ onRemovePromoCodeCallback, promoCode }) => {
    const { t } = useTranslation();

    return (
        <div className="flex flex-col gap-2">
            <div className="text-textAccent">{t('Your discount with the code has been applied.')}</div>
            <div className="flex items-center font-bold" tid={TIDs.blocks_promocode_promocodeinfo_code}>
                <LabelLink className="gap-3" onClick={onRemovePromoCodeCallback}>
                    {promoCode}
                    <RemoveIcon className=" w-3" />
                </LabelLink>
            </div>
            <p className="text-textDisabled ">
                {t(
                    'The discount was applied to all non-discounted items to which the promotion applies according to the rules.',
                )}
            </p>
        </div>
    );
};
