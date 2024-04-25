#!/bin/bash

echo "Generate ID"
generated_id=$(LC_CTYPE=C tr -dc A-Za-z0-9 < /dev/urandom | head -c 32 | xargs)
echo "generated_id=$generated_id" >> $GITHUB_OUTPUT
echo "  Generated ID: $generated_id"
echo "  Done"
