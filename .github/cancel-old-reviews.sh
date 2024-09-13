#!/bin/bash

# Get the timestamp for 7 days ago
cutoff_date=$(date -d '7 days ago' +%s)

# List php-fpm containers that contains at least one non numeric character in branch name
docker ps -a --format "{{.Names}} {{.CreatedAt}}" | \
grep -E '^[^-]*[^0-9].*-php-fpm-1' | \
while read name created_at; do
  # Remove the timezone from the creation date (we don't need it)
  created_at_clean=$(echo "$created_at" | sed 's/ +.*//')

  # Convert the cleaned-up creation date to a timestamp
  container_timestamp=$(date -d "$created_at_clean" +%s)

  # Check if the container is older than 7 days
  if [ "$container_timestamp" -lt "$cutoff_date" ]; then
    # Remove the '-php-fpm-1' suffix and print the name
    branch_name=$(echo "$name" | sed 's/-php-fpm-1$//')

    /bin/bash ./.github/cancel-review.sh "$branch_name"
  fi
done
