#!/bin/bash
set -e

# Check if the version argument is provided
if [ -z "$1" ]; then
  echo "Error: version is not set. Pass the version as an argument."
  exit 1
fi

version=$1

# Define input and output files
layout_file="upgrade-notes/_layout.md"
output_file="UPGRADE-${version}.md"  # Use the version from the argument

# Function to concatenate files with a given prefix
concatenate_files() {
  local prefix=$1
  local content=""
  local files=(upgrade-notes/${prefix}*)
  local last_index=$((${#files[@]} - 1))

  for index in "${!files[@]}"; do
    if [[ -f ${files[index]} ]]; then
      content+=$(cat "${files[index]}")
      # Add an empty line after each file except the last one
      if [[ $index -ne $last_index ]]; then
        content+=$'\n\n'
      else
        content+=$'\n'
      fi
    fi
  done
  echo "$content"
}

# Read the layout file
layout_content=$(cat "$layout_file")

# Replace placeholders with actual content
backend_notes=$(concatenate_files "backend_")
storefront_notes=$(concatenate_files "storefront_")

layout_content="${layout_content//<backendNotes>/$backend_notes}"
layout_content="${layout_content//<storefrontNotes>/$storefront_notes}"

# Write the result to the output file
echo "$layout_content" > "$output_file"

echo "Output written to $output_file"
