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

OLD_APP_VERSION=$(kubectl get service --namespace=${PROJECT_NAME} webserver-php-fpm -o=jsonpath='{.spec.selector.version}') || echo "No running service with old deployment version"
NEW_APP_VERSION="$(cat /proc/sys/kernel/random/uuid)"
NEW_APP_NAME="webserver-php-fpm-${NEW_APP_VERSION}"

# It is probably first deploy (or namespace is empty)
if [ -z $OLD_APP_VERSION ]; then
    OLD_APP_VERSION=$NEW_APP_VERSION
fi
OLD_APP_NAME="webserver-php-fpm-${OLD_APP_VERSION}"

yq write --inplace "${CONFIGURATION_TARGET_PATH}/services/webserver-php-fpm.yaml" "spec.selector.version" $OLD_APP_VERSION
yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/webserver-php-fpm.yaml" "spec.selector.matchLabels.version" $NEW_APP_VERSION
yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/webserver-php-fpm.yaml" "spec.template.metadata.labels.version" $NEW_APP_VERSION
yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/webserver-php-fpm.yaml" "metadata.name" $NEW_APP_NAME
yq write --inplace "${CONFIGURATION_TARGET_PATH}/kustomize/overlays/continuous-deploy/kustomization.yaml" "patchesJson6902[0].target.name" $NEW_APP_NAME
yq write --inplace "${CONFIGURATION_TARGET_PATH}/kustomize/overlays/first-deploy/kustomization.yaml" "patchesJson6902[0].target.name" $NEW_APP_NAME

echo "Apply kubernetes configuration"
if [ $FIRST_DEPLOY -eq "0" ]; then
    # try to release deploy part 2 lock before deployment
    RUNNING_WEBSERVER_PHP_FPM_PODS_STRING=$(kubectl get pods --namespace=${PROJECT_NAME} --field-selector=status.phase=Running -l version=${OLD_APP_VERSION} -o=jsonpath='{.items[*].metadata.name}')
    read -r -a RUNNING_WEBSERVER_PHP_FPM_PODS <<< $RUNNING_WEBSERVER_PHP_FPM_PODS_STRING
    if [ ${#RUNNING_WEBSERVER_PHP_FPM_PODS[@]} -eq 0 ]; then
        echo "Any running php-fpm container to prevent deploy part 2 locking. The lock may be released manually."
    else
        kubectl exec ${RUNNING_WEBSERVER_PHP_FPM_PODS[0]} --namespace=${PROJECT_NAME} ./phing deploy-part-2-lock-release || echo "Deploy part 2 lock could not be released. The lock may be released manually if exists."
    fi

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
kubectl rollout status --namespace=${PROJECT_NAME} deployment/${NEW_APP_NAME} --watch || EXIT_CODE=$?

if [ $EXIT_CODE -eq "0" ]; then
    echo "Deploy succesful"
    if [[ $OLD_APP_VERSION != $NEW_APP_VERSION ]]; then
        kubectl patch service --namespace=${PROJECT_NAME} webserver-php-fpm -p "{\"spec\":{\"selector\":{\"version\":\"${NEW_APP_VERSION}\"}}}"
        kubectl delete deployment $OLD_APP_NAME --namespace=${PROJECT_NAME}
    fi
    DEPLOYED_WEBSERVER_PHP_FPM_PODS_STRING=$(kubectl get pods --namespace=${PROJECT_NAME} --field-selector=status.phase=Running -l version=${NEW_APP_VERSION} -o=jsonpath='{.items[*].metadata.name}')
else
    echo "Deploy failed"
    DEPLOYED_WEBSERVER_PHP_FPM_PODS_STRING=$(kubectl get pods --namespace=${PROJECT_NAME} --field-selector=status.phase=!Running -l version=${NEW_APP_VERSION} -o=jsonpath='{.items[*].metadata.name}')
    kubectl delete deployment $NEW_APP_NAME --namespace=${PROJECT_NAME}
fi

read -r -a DEPLOYED_WEBSERVER_PHP_FPM_PODS <<< $DEPLOYED_WEBSERVER_PHP_FPM_PODS_STRING

for POD_INDEX in "${!DEPLOYED_WEBSERVER_PHP_FPM_PODS[@]}"
do
    POD=${DEPLOYED_WEBSERVER_PHP_FPM_PODS[$POD_INDEX]}
    if [ $POD_INDEX -eq 0 ] && [ $EXIT_CODE -eq "0" ]; then
      kubectl exec ${POD} --namespace=${PROJECT_NAME} ./phing maintenance-off clean-redis-old
    fi

    if [ $FIRST_DEPLOY -eq "1" ]; then
        echo -e "\n\n>>>>>>>>> Echoing logs of init-application container - ${POD}"
        kubectl logs ${POD} --namespace=${PROJECT_NAME} -c init-application
    else
        echo -e "\n\n>>>>>>>>> Echoing logs of upgrade-application container - ${POD}"
        kubectl logs ${POD} --namespace=${PROJECT_NAME} -c upgrade-application
    fi
done

echo "End of deploy.sh"
exit $EXIT_CODE
