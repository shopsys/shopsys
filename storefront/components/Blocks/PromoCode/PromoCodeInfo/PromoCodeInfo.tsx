import { PromoCodeInfoCouponStyled, PromoCodeInfoStyled, PromoCodeInfoTitleStyled } from './PromoCodeInfo.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';

type PromoCodeInfoProps = {
    promoCode: string;
    onRemovePromoCodeCallback: (promoCode: string) => void;
};

const TEST_IDENTIFIER = 'blocks-promocode-promocodeinfo';

export const PromoCodeInfo: FC<PromoCodeInfoProps> = ({ onRemovePromoCodeCallback, promoCode }) => {
    const t = useTypedTranslationFunction();

    const onRemovePromoCodeHandler = () => {
        onRemovePromoCodeCallback(promoCode);
    };

    return (
        <PromoCodeInfoStyled data-testid={TEST_IDENTIFIER}>
            <PromoCodeInfoTitleStyled data-testid={TEST_IDENTIFIER + '-title'}>
                {t('Your discount with the code has been applied.')}
            </PromoCodeInfoTitleStyled>
            <PromoCodeInfoCouponStyled data-testid={TEST_IDENTIFIER + '-code'}>
                {promoCode}
                <Icon
                    iconType="icon"
                    icon="Cross"
                    onClick={onRemovePromoCodeHandler}
                    width={16}
                    height={16}
                    className="mr-1 cursor-pointer text-greyDark hover:text-primary"
                />
            </PromoCodeInfoCouponStyled>
            <p data-testid={TEST_IDENTIFIER + '-description'}>
                {t(
                    'The discount was applied to all non-discounted items to which the promotion applies according to the rules.',
                )}
            </p>
        </PromoCodeInfoStyled>
    );
};
