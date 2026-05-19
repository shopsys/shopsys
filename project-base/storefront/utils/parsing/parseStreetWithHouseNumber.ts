type ParsedStreetWithHouseNumber = {
    street: string;
    streetNumber?: string;
};

export const streetWithHouseNumberRegex =
    /^(?:([\u00C0-\u017Fa-zA-Z].*)\s(\d+(?:\/\d+)?[\u00C0-\u017Fa-zA-Z]?)|(\d+(?:\/\d+)?[\u00C0-\u017Fa-zA-Z]?)\s(.*[\u00C0-\u017Fa-zA-Z])|(\d+\.\s[\u00C0-\u017Fa-zA-Z].*)\s(\d+(?:\/\d+)?[\u00C0-\u017Fa-zA-Z]?))$/;

export const parseStreetWithHouseNumber = (street: string): ParsedStreetWithHouseNumber => {
    const streetWithHouseNumberMatch = street.match(streetWithHouseNumberRegex);
    const streetAtEnd = streetWithHouseNumberMatch?.[1] ?? streetWithHouseNumberMatch?.[5];
    const streetNumberAtEnd = streetWithHouseNumberMatch?.[2] ?? streetWithHouseNumberMatch?.[6];
    const streetNumberAtBeginning = streetWithHouseNumberMatch?.[3];
    const streetNumber = streetNumberAtEnd ?? streetNumberAtBeginning;

    if (streetNumber === undefined) {
        return { street };
    }

    if (streetNumberAtEnd !== undefined && streetAtEnd !== undefined) {
        return {
            street: streetAtEnd.trim(),
            streetNumber,
        };
    }

    return {
        street: streetWithHouseNumberMatch?.[4].trim() ?? street,
        streetNumber,
    };
};
