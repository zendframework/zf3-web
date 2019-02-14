#!/bin/bash
TOKEN=$1
SITE=$2
COMMIT=$3

curl -k \
    --request POST \
    --header "Authorization: Bearer ${TOKEN}" \
    --header "Content-Type: application/x-zfweb-deploy+json" \
    --data '"site":"'"${SITE}"'","commit":"'"${COMMIT}"'"}' \
    https://zf-node1.zend.com
