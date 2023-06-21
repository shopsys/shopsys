const getCliParameters = (parameters, parameterName) => {
    return parameters.map(parameter => {
        const keyValueParameter = parameter.split('=');
        if (keyValueParameter[0] === parameterName) {
            return keyValueParameter[1];
        }
    }).filter(item => item !== undefined);
};

module.exports = getCliParameters;
