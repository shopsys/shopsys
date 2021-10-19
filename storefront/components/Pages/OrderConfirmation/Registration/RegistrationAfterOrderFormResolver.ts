import * as Yup from 'yup';
import { Resolver } from 'react-hook-form';
import { TFunction } from 'react-i18next';
import { yupResolver } from '@hookform/resolvers/yup';

export const getRegistrationAfterOrderFormResolver = (t: TFunction): Resolver => {
    return yupResolver(
        Yup.object().shape({
            password: Yup.string()
                .required(t('Please enter password'))
                .min(
                    6,
                    t('Password must be at least {{ count }} characters long', {
                        postProcess: 'interval',
                        count: 6,
                    }),
                ),
            privacyPolicy: Yup.boolean().isTrue(t('You have to agree with our privacy policy')),
        }),
    );
};
