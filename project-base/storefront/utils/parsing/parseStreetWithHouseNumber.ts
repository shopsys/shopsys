type ParsedStreetWithHouseNumber = {
    street: string;
    streetNumber?: string;
};

export const streetWithHouseNumberRegex =
    /^(?:[\u00C0-\u017Fa-zA-Z].*\s(\d+(?:\/\d+)?[\u00C0-\u017Fa-zA-Z]?)|(\d+(?:\/\d+)?[\u00C0-\u017Fa-zA-Z]?)\s.*[\u00C0-\u017Fa-zA-Z])$/;

export const parseStreetWithHouseNumber = (street: string): ParsedStreetWithHouseNumber => {
    const streetWithHouseNumberMatch = street.match(streetWithHouseNumberRegex);
    const streetNumber = streetWithHouseNumberMatch?.[1] ?? streetWithHouseNumberMatch?.[2];

    if (streetNumber === undefined) {
        return { street };
    }

    return {
        street:
            streetWithHouseNumberMatch?.[1] !== undefined
                ? street.slice(0, -streetNumber.length).trim()
                : street.slice(streetNumber.length).trim(),
        streetNumber,
    };
};
