# Logging on Continuous Integration server running Kubernetes
As this [article](../introduction/logging.md) describes, our logs are streamed. Since we want to be able to look at logs on our CI without needing to perform `kubectl` commands on server we need to make simple workaround in order to get logs out of application and containers onto local storage.

## Problem
On our CI, every branch is built in its own Kubernetes namespace for isolation.
As we do not want to have many instances of application running at once on our servers because of heavy load, we delete the whole Kubernetes namespace after each build.
Deleting the namespace removes all running pods in it along with the logs.
So, after a failed build we don't have access to the logs to see what went wrong.

## Our way
We decided to go the simplest way possible. In order to get logs for developers to see easily, we print the output of `kubectl logs` into files saved in jenkins workspace.

We perform this operation every time before we delete the namespace. That way we can get logs really easy with minimum effort.

## Acceptance Tests
Logs of Acceptance Tests cannot be streamed because they export images into application folder.
In this case we use `kubectl cp` which is able to copy files (`/var/log/codeception`) from container to local folder.

## Scripts
In [.ci](https://github.com/shopsys/shopsys/tree/master/.ci) folder you can find [export_logs.sh](https://github.com/shopsys/shopsys/tree/master/.ci/export_logs.sh) file which we use on our CI to export logs.

