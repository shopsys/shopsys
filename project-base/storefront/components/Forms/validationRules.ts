import { VALIDATION_CONSTANTS } from './validationConstants';
import { Translate } from 'next-translate';
import * as Yup from 'yup';
import { BaseSchema } from 'yup';

export const validateEmail = (t: Translate): BaseSchema => {
    return Yup.string()
        .required(t('Please enter email'))
        .email(t('This value is not a valid email'))
        .max(
            VALIDATION_CONSTANTS.emailMaxLength,
            t('Email must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.emailMaxLength }),
        );
};

export const validateCustomer = (): BaseSchema => {
    return Yup.string().oneOf(['commonCustomer', 'companyCustomer']);
};

export const validateTelephone = (t: Translate): BaseSchema => {
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

export const validateTelephoneRequired = (t: Translate): BaseSchema => {
    return validateTelephone(t).required(t('Please enter phone number'));
};

export const validateFirstName = (t: Translate): BaseSchema => {
    return Yup.string()
        .required(t('Please enter first name'))
        .max(
            VALIDATION_CONSTANTS.firstNameMaxLength,
            t('First name must be at most {{ max }} characters', {
                max: VALIDATION_CONSTANTS.firstNameMaxLength,
            }),
        );
};

export const validateLastName = (t: Translate): BaseSchema => {
    return Yup.string()
        .required(t('Please enter last name'))
        .max(
            VALIDATION_CONSTANTS.lastNameMaxLength,
            t('Last name must be at most {{ max }} characters', {
                max: VALIDATION_CONSTANTS.lastNameMaxLength,
            }),
        );
};

export const validateStreet = (t: Translate): BaseSchema => {
    return Yup.string()
        .required(t('Please enter street'))
        .matches(/\D/, t('The street must contain a letter'))
        .matches(/\d/, t('The street must contain a number'))
        .max(
            VALIDATION_CONSTANTS.streetMaxLength,
            t('Street must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.streetMaxLength }),
        );
};

export const validateCity = (t: Translate): BaseSchema => {
    return Yup.string()
        .required(t('Please enter city'))
        .max(
            VALIDATION_CONSTANTS.cityMaxLength,
            t('City must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.streetMaxLength }),
        );
};

export const validatePostcode = (t: Translate): BaseSchema => {
    return Yup.string()
        .required(t('Please enter zip code'))
        .test(
            'less-than-or-equals-5',
            t('Zip code cannot be longer than {{ postcodeLength }} characters', {
                postcodeLength: VALIDATION_CONSTANTS.postcodeLength,
            }),
            (value) => value !== undefined && value.length <= VALIDATION_CONSTANTS.postcodeLength,
        );
};

export const validateCountry = (t: Translate): BaseSchema => {
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

export const validateCompanyName = (t: Translate): BaseSchema => {
    return Yup.string().max(
        VALIDATION_CONSTANTS.companyNameMaxLength,
        t('Company name must be at most {{ max }} characters', {
            max: VALIDATION_CONSTANTS.companyNameMaxLength,
        }),
    );
};

export const validateCompanyNameRequired = (t: Translate): BaseSchema => {
    return validateCompanyName(t).required(t('Please enter company name'));
};

export const validateCompanyNumber = (t: Translate): BaseSchema => {
    return Yup.string()
        .required(t('Please enter identification number'))
        .matches(/^[0-9]*$/, t('Please enter only numbers'))
        .test(
            'equals-8',
            t('This value must be exactly {{ companyNumberLength }} characters', {
                companyNumberLength: VALIDATION_CONSTANTS.companyNumberExactLength,
            }),
            (value) => value !== undefined && value.length === VALIDATION_CONSTANTS.companyNumberExactLength,
        );
};

export const validateCompanyTaxNumber = (t: Translate): BaseSchema => {
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

export const validateFirstPassword = (t: Translate): BaseSchema => {
    return Yup.string()
        .required(t('Please enter first password'))
        .min(
            VALIDATION_CONSTANTS.passwordMinLength,
            t('Password must be at least {{ count }} characters long', {
                count: VALIDATION_CONSTANTS.passwordMinLength,
            }),
        );
};

export const validateSecondPassword = (t: Translate): BaseSchema => {
    return Yup.string()
        .required(t('Please enter second password'))
        .min(
            VALIDATION_CONSTANTS.passwordMinLength,
            t('Password must be at least {{ count }} characters long', {
                count: VALIDATION_CONSTANTS.passwordMinLength,
            }),
        )
        .oneOf([Yup.ref('passwordFirst'), null], t('Passwords must match'));
};
