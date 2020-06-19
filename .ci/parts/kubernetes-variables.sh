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
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yaml" metadata.annotations."\"nginx.ingress.kubernetes.io/from-to-www-redirect\"" "\"true\""
else
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yaml" metadata.annotations."\"nginx.ingress.kubernetes.io/auth-type\"" basic
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yaml" metadata.annotations."\"nginx.ingress.kubernetes.io/auth-secret\"" http-auth
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yaml" metadata.annotations."\"nginx.ingress.kubernetes.io/auth-realm\"" "Authentication Required - ok"
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yaml" metadata.annotations."\"nginx.ingress.kubernetes.io/configuration-snippet\"" "satisfy any;
allow 213.151.92.78;
allow 93.185.110.99/32;
allow 93.185.110.100/32;
allow 93.185.110.101/32;
allow 185.198.191.147/32;
allow 204.145.66.226/32;
allow 77.81.119.26/32;
allow 86.105.155.150/32;
allow 185.115.0.0/24;
allow 77.247.124.1/32;
allow 193.165.237.218;
allow 194.213.202.42;
allow 84.242.75.228;
deny all;"
fi

echo "End of kubernetes-variables.sh"
