#!/bin/bash
TOKEN=$1
SITE=$2
COMMIT=$3
DATA='{"site":"'"${SITE}"'","commit":"'"${COMMIT}"'"}'

curl -k \
    --request POST \
    --header "Authorization: Bearer ${TOKEN}" \
    --header "Content-Type: application/x-zfweb-deploy+json" \
    --data $DATA \
    https://zf-node1.zend.com
