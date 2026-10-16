#!/usr/bin/env bash
set -euo pipefail

if [[ "${CI_ENVIRONMENT_NAME:-}" != "devel-alpha" || ! "${CI_COMMIT_BRANCH:-}" =~ ^alpha(-.*)?$ ]]; then
    echo 'This diagnostic job is restricted to the alpha environment and branch.' >&2
    exit 1
fi

: "${PROJECT_NAME:?Missing alpha namespace}"
: "${KUBE_DEV:?Missing development cluster configuration}"
case "$PROJECT_NAME" in
    *-alpha|*-alpha-*) ;;
    *) echo 'Expected an alpha namespace.' >&2; exit 1 ;;
esac

umask 077
diagnostic_kubeconfig=$(mktemp)
trap 'rm -f "$diagnostic_kubeconfig"' EXIT
printf '%s' "$KUBE_DEV" > "$diagnostic_kubeconfig"
export KUBECONFIG="$diagnostic_kubeconfig"

script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
pods=$(kubectl --request-timeout=15s get pods --namespace="$PROJECT_NAME" \
    --field-selector=status.phase=Running -l app=webserver-php-fpm \
    -o 'jsonpath={range .items[*]}{.metadata.name}{"\n"}{end}')

if [[ -z "$pods" ]]; then
    echo 'No running PHP-FPM pods found; preload has not been verified.' >&2
    exit 1
fi

echo "Inspecting currently running PHP-FPM pods in $PROJECT_NAME (not necessarily the pipeline revision)."
diagnostic_exit_code=0
while IFS= read -r pod; do
    echo "--- $pod ---"
    if ! kubectl --request-timeout=15s get pod "$pod" --namespace="$PROJECT_NAME" \
        -o 'jsonpath={range .status.containerStatuses[?(@.name=="php-fpm")]}image={.image}{"\n"}imageID={.imageID}{"\n"}startedAt={.state.running.startedAt}{"\n"}ready={.ready}{"\n"}restartCount={.restartCount}{"\n"}{end}'; then
        diagnostic_exit_code=1
        continue
    fi

    if ! kubectl --request-timeout=45s exec -i "$pod" --namespace="$PROJECT_NAME" -c php-fpm \
        -- timeout 30s php < "$script_dir/diagnose-php-preload.php"; then
        echo "Could not read PHP-FPM status in $pod; see the error above." >&2
        diagnostic_exit_code=1
    fi
done <<< "$pods"

exit "$diagnostic_exit_code"
