#!/bin/bash -e

echo "Start of parameters.sh"

assertVariable "BASE_PATH"
assertVariable "RUNNING_PRODUCTION"

paremeters_filepath="${BASE_PATH}/config/parameters.yaml"

cp "${BASE_PATH}/config/parameters.yaml.dist" $paremeters_filepath

if [ ${RUNNING_PRODUCTION} -eq "1" ]; then
    declare -A ADDITIONAL_PARAMETERS=(
      ["parameters.mailer_delivery_whitelist"]=nullPlaceholder
      ["parameters.mailer_master_email_address"]=nullPlaceholder
    )
else
    declare -A ADDITIONAL_PARAMETERS=(
        ["parameters.mailer_master_email_address"]="no-reply@shopsys.com"
    )
fi

declare -A MERGED_PARAMETERS=()

for key in ${!PARAMETERS[@]}; do
    MERGED_PARAMETERS+=( [${key}]=${PARAMETERS[${key}]} )
done

for key in ${!ADDITIONAL_PARAMETERS[@]}; do
    MERGED_PARAMETERS+=( [${key}]=${ADDITIONAL_PARAMETERS[${key}]} )
done

for PARAMETER_KEY in "${!MERGED_PARAMETERS[@]}"
do
    yq write --inplace $paremeters_filepath $PARAMETER_KEY ${MERGED_PARAMETERS[$PARAMETER_KEY]}
done

sed -i 's/nullPlaceholder/null/' $paremeters_filepath

echo "End of parameters.sh"
