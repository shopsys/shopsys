#!/bin/bash -e

echo "Start of kubernetes-variables.sh"

assertVariable "RUNNING_PRODUCTION"
assertVariable "CONFIGURATION_TARGET_PATH"

FILES=$( find $CONFIGURATION_TARGET_PATH -type f )
for FILE in $FILES; do
    for VAR in ${VARS[@]}; do
        assertVariable $VAR
        sed -i "s|{{$VAR}}|${!VAR}|g" "$FILE"
    done
done
unset FILES
unset VARS

if [ ${RUNNING_PRODUCTION} -eq "1" ]; then
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yml" metadata.annotations."\"nginx.ingress.kubernetes.io/from-to-www-redirect\"" "\"true\""
else
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yml" metadata.annotations."\"nginx.ingress.kubernetes.io/auth-type\"" basic
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yml" metadata.annotations."\"nginx.ingress.kubernetes.io/auth-secret\"" http-auth
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yml" metadata.annotations."\"nginx.ingress.kubernetes.io/auth-realm\"" "Authentication Required - ok"
fi

echo "End of kubernetes-variables.sh"
