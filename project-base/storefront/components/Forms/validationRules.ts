import { VALIDATION_CONSTANTS } from './validationConstants';
import { Translate } from 'next-translate';
import { formatBytes } from 'utils/formaters/formatBytes';
import * as Yup from 'yup';
import { Schema } from 'yup';

export const validateEmail = (t: Translate): Schema => {
    return Yup.string()
        .required(t('Please enter email'))
        .email(t('This value is not a valid email'))
        .max(
            VALIDATION_CONSTANTS.emailMaxLength,
            t('Email must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.emailMaxLength }),
        );
};

export const validateCustomer = (): Schema => {
    return Yup.string().oneOf(['commonCustomer', 'companyCustomer']);
};

export const validateTelephone = (t: Translate): Schema => {
    return Yup.string()
        .matches(/^[0-9+]*$/, t('Please enter only numbers and the + character'))
        .test(
            'more-than-8',
            t('Telephone number cannot be shorter than {{ telephoneMinLength }} characters', {
                telephoneMinLength: VALIDATION_CONSTANTS.telephoneMinLength,
            }),
            (value) => value !== undefined && value.length >= VALIDATION_CONSTANTS.telephoneMinLength,
        )
        .max(
            VALIDATION_CONSTANTS.telephoneMaxLength,
            t('Telephone must be at most {{ max }} characters', {
                max: VALIDATION_CONSTANTS.telephoneMaxLength,
            }),
        );
};

export const validateTelephoneRequired = (t: Translate): Schema => {
    return validateTelephone(t).required(t('Please enter phone number'));
};

export const validateFirstName = (t: Translate): Schema => {
    return Yup.string()
        .required(t('Please enter first name'))
        .max(
            VALIDATION_CONSTANTS.firstNameMaxLength,
            t('First name must be at most {{ max }} characters', {
                max: VALIDATION_CONSTANTS.firstNameMaxLength,
            }),
        );
};

export const validateLastName = (t: Translate): Schema => {
    return Yup.string()
        .required(t('Please enter last name'))
        .max(
            VALIDATION_CONSTANTS.lastNameMaxLength,
            t('Last name must be at most {{ max }} characters', {
                max: VALIDATION_CONSTANTS.lastNameMaxLength,
            }),
        );
};

export const validateStreet = (t: Translate): Schema => {
    return Yup.string()
        .required(t('Please enter street'))
        .matches(/\D/, t('The street must contain a letter'))
        .matches(/\d/, t('The street must contain a number'))
        .max(
            VALIDATION_CONSTANTS.streetMaxLength,
            t('Street must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.streetMaxLength }),
        );
};

export const validateCity = (t: Translate): Schema => {
    return Yup.string()
        .required(t('Please enter city'))
        .max(
            VALIDATION_CONSTANTS.cityMaxLength,
            t('City must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.streetMaxLength }),
        );
};

export const validatePostcode = (t: Translate): Schema => {
    return Yup.string()
        .required(t('Please enter zip code'))
        .test(
            'less-than-or-equals-5',
            t('Zip code cannot be longer than {{ postcodeLength }} characters', {
                postcodeLength: VALIDATION_CONSTANTS.postcodeLength,
            }),
            (value) => value.length <= VALIDATION_CONSTANTS.postcodeLength,
        );
};

export const validateCountry = (t: Translate): Schema => {
    return Yup.object()
        .shape({
            label: Yup.string().required(),
            value: Yup.string().required(),
        })
        .required(t('Please enter country'))
        .test(
            'non-null-or-empty-string',
            t('Please enter country'),
            (value: { label: string; value: string }) => value.value !== '',
        );
};

export const validateRoleGroup = (t: Translate): Schema => {
    return Yup.object()
        .shape({
            label: Yup.string().required(),
            value: Yup.string().required(),
        })
        .required(t('Please select a role group'))
        .test(
            'non-null-or-empty-string',
            t('Please select a role group'),
            (value: { label: string; value: string }) => value.value !== '',
        );
};

export const validateCompanyName = (t: Translate): Schema => {
    return Yup.string().max(
        VALIDATION_CONSTANTS.companyNameMaxLength,
        t('Company name must be at most {{ max }} characters', {
            max: VALIDATION_CONSTANTS.companyNameMaxLength,
        }),
    );
};

export const validateCompanyNameRequired = (t: Translate): Schema => {
    return validateCompanyName(t).required(t('Please enter company name'));
};

export const validateCompanyNumber = (t: Translate): Schema => {
    return Yup.string()
        .required(t('Please enter identification number'))
        .matches(/^[0-9]*$/, t('Please enter only numbers'))
        .test(
            'equals-8',
            t('This value must be exactly {{ companyNumberLength }} characters', {
                companyNumberLength: VALIDATION_CONSTANTS.companyNumberExactLength,
            }),
            (value) => value.length === VALIDATION_CONSTANTS.companyNumberExactLength,
        );
};

export const validateCompanyTaxNumber = (t: Translate): Schema => {
    return Yup.string()
        .defined()
        .matches(/^[0-9A-Z]*([0-9]+[A-Z]+|[A-Z]+[0-9]+)[0-9A-Z]*$/, {
            excludeEmptyString: true,
            message: t('Please check Tax number format'),
        })
        .max(
            VALIDATION_CONSTANTS.companyTaxNumberMaxLength,
            t('Company tax number must be at most {{ max }} characters', {
                max: VALIDATION_CONSTANTS.companyTaxNumberMaxLength,
            }),
        );
};

export const validatePrivacyPolicy = (t: Translate): Schema => {
    return Yup.boolean().isTrue(t('You have to agree with our privacy policy'));
};

const passwordValidationSchema = (t: Translate, requiredMessage: string) => {
    return Yup.string()
        .required(requiredMessage)
        .min(
            VALIDATION_CONSTANTS.passwordMinLength,
            t('Password must be at least {{ count }} characters long', {
                count: VALIDATION_CONSTANTS.passwordMinLength,
            }),
        );
};

const passwordConfirmValidationSchema = (t: Translate, passwordFieldName: string, requiredMessage: string) => {
    return Yup.string()
        .required(requiredMessage)
        .oneOf([Yup.ref(passwordFieldName)], t('Passwords must match'));
};

export const validatePassword = (t: Translate): Schema => {
    return passwordValidationSchema(t, t('Please enter password'));
};

export const validatePasswordConfirm = (t: Translate): Schema => {
    return passwordConfirmValidationSchema(t, 'password', t('Please enter password again'));
};

export const validateNewPassword = (t: Translate): Schema => {
    return passwordValidationSchema(t, t('Please enter new password'));
};

export const validateNewPasswordConfirm = (t: Translate): Schema => {
    return passwordConfirmValidationSchema(t, 'newPassword', t('Please enter new password again'));
};

export const validateImageFile = (t: Translate): Schema => {
    return Yup.array()
        .of(
            Yup.mixed()
                .required(t('Please attach files'))
                .test(
                    'fileSize',
                    t('Maximum file size is {{ max }}', {
                        max: formatBytes(VALIDATION_CONSTANTS.fileMaxSize),
                    }),
                    (value) => {
                        const file = value as File;
                        return file.size <= VALIDATION_CONSTANTS.fileMaxSize && file.size > 0;
                    },
                ),
        )
        .min(1, t('Please attach files'))
        .max(
            VALIDATION_CONSTANTS.maxFilesCount,
            t('Maximum files count is {{ max }}', { max: VALIDATION_CONSTANTS.maxFilesCount }),
        );
};
