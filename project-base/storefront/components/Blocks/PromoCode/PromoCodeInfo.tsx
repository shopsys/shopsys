import { CrossIcon } from 'components/Basic/Icon/CrossIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';

type PromoCodeInfoProps = {
    promoCode: string;
    onRemovePromoCodeCallback: (promoCode: string) => void;
};

export const PromoCodeInfo: FC<PromoCodeInfoProps> = ({ onRemovePromoCodeCallback, promoCode }) => {
    const { t } = useTranslation();

    const onRemovePromoCodeHandler = () => {
        onRemovePromoCodeCallback(promoCode);
    };

    return (
        <div>
            <div className="text-primary">{t('Your discount with the code has been applied.')}</div>
            <div className="flex items-center font-bold" tid={TIDs.blocks_promocode_promocodeinfo_code}>
                {promoCode}
                <CrossIcon
                    className="mr-1 w-4 cursor-pointer text-greyDark hover:text-primary"
                    onClick={onRemovePromoCodeHandler}
                />
            </div>
            <p>
                {t(
                    'The discount was applied to all non-discounted items to which the promotion applies according to the rules.',
                )}
            </p>
        </div>
    );
};
