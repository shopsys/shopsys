#!/bin/bash -e

echo "Start of deploy.sh"

assertVariable "BASE_PATH"
assertVariable "CONFIGURATION_TARGET_PATH"
assertVariable "FIRST_DEPLOY"
assertVariable "DISPLAY_FINAL_CONFIGURATION"
assertVariable "PROJECT_NAME"
assertVariable "S3_API_BUCKET_NAME"

# Replace bucket name for S3 images URL
sed -i "s/S3_BUCKET_NAME/${S3_API_BUCKET_NAME}/g" "${BASE_PATH}/docker/nginx/s3/nginx.conf"

echo "Try create namespace if not exists"
kubectl create namespace ${PROJECT_NAME} || echo "${PROJECT_NAME} namespace already existing"


echo "Delete secret for docker registry if exists"
kubectl delete secret dockerregistry -n ${PROJECT_NAME} || echo "Secret for docker registry does not exist"
echo "Create secret for docker registry"
kubectl create secret docker-registry dockerregistry --docker-server=$CI_REGISTRY --docker-username=$DEPLOY_REGISTER_USER --docker-password=$DEPLOY_REGISTER_PASSWORD -n ${PROJECT_NAME}

if [ ${RUNNING_PRODUCTION} -eq "0" ]; then
    echo "Create secret for http auth"
    kubectl create secret generic http-auth --from-file=auth=${CONFIGURATION_TARGET_PATH}/basicHttpAuth -n ${PROJECT_NAME} || echo "Secret for http auth already exists"
fi

echo "Apply kubernetes configuration"
if [ $FIRST_DEPLOY -eq "0" ]; then
    if [ $DISPLAY_FINAL_CONFIGURATION ]; then
        kustomize build "${CONFIGURATION_TARGET_PATH}/kustomize/overlays/continuous-deploy"
    fi
    kustomize build "${CONFIGURATION_TARGET_PATH}/kustomize/overlays/continuous-deploy" | kubectl apply -f -
else
    if [ $DISPLAY_FINAL_CONFIGURATION ]; then
        kustomize build "${CONFIGURATION_TARGET_PATH}/kustomize/overlays/first-deploy"
    fi

    kustomize build "${CONFIGURATION_TARGET_PATH}/kustomize/overlays/first-deploy" | kubectl apply -f -
fi

EXIT_CODE=0

# wait for new pod to be initialized and if it fails send result to /dev/null and save output code to a varaible.
kubectl rollout status --namespace=${PROJECT_NAME} deployment/webserver-php-fpm --watch || EXIT_CODE=$?

if [ $EXIT_CODE -eq "0" ]; then
    echo "Deploy succesful"
    DEPLOYED_WEBSERVER_PHP_FPM_POD=$(kubectl get pods --namespace=${PROJECT_NAME} --field-selector=status.phase=Running -l app=webserver-php-fpm -o=jsonpath='{.items[0].metadata.name}')
else
    echo "Deploy failed"
    DEPLOYED_WEBSERVER_PHP_FPM_POD=$(kubectl get pods --namespace=${PROJECT_NAME} --field-selector=status.phase!=Running -l app=webserver-php-fpm -o=jsonpath='{.items[0].metadata.name}')
fi

if [ $FIRST_DEPLOY -eq "1" ]; then
    echo "Echoing logs of init-application container"
    kubectl logs ${DEPLOYED_WEBSERVER_PHP_FPM_POD} --namespace=${PROJECT_NAME} -c init-application
else
    echo "Echoing logs of upgrade-application container"
    kubectl logs ${DEPLOYED_WEBSERVER_PHP_FPM_POD} --namespace=${PROJECT_NAME} -c upgrade-application
fi

echo "End of deploy.sh"
exit $EXIT_CODE
