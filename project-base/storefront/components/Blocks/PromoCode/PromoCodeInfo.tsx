import { Icon } from 'components/Basic/Icon/Icon';
import { Cross } from 'components/Basic/Icon/IconsSvg';
import useTranslation from 'next-translate/useTranslation';

type PromoCodeInfoProps = {
    promoCode: string;
    onRemovePromoCodeCallback: (promoCode: string) => void;
};

const TEST_IDENTIFIER = 'blocks-promocode-promocodeinfo';

export const PromoCodeInfo: FC<PromoCodeInfoProps> = ({ onRemovePromoCodeCallback, promoCode }) => {
    const { t } = useTranslation();

    const onRemovePromoCodeHandler = () => {
        onRemovePromoCodeCallback(promoCode);
    };

    return (
        <div data-testid={TEST_IDENTIFIER}>
            <div className="text-primary" data-testid={TEST_IDENTIFIER + '-title'}>
                {t('Your discount with the code has been applied.')}
            </div>
            <div className="flex items-center font-bold" data-testid={TEST_IDENTIFIER + '-code'}>
                {promoCode}
                <Icon
                    icon={<Cross />}
                    onClick={onRemovePromoCodeHandler}
                    className="mr-1 w-4 cursor-pointer text-greyDark hover:text-primary"
                />
            </div>
            <p data-testid={TEST_IDENTIFIER + '-description'}>
                {t(
                    'The discount was applied to all non-discounted items to which the promotion applies according to the rules.',
                )}
            </p>
        </div>
    );
};
