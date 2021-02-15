export const parseNumber = (value) => {
    if (!value) {
        return null;
    }

    const compareValue = value.toString().replace(',', '.');
    const regexpNumber = /^[-+]?[0-9]+((\.|,)?[0-9]+)?$/;
    if (regexpNumber.test(compareValue)) {
        return parseFloat(compareValue);
    } else {
        return null;
    }
};

export const formatDecimalNumber = (value, scale) => value.toFixed(scale);
