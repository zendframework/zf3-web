#!/bin/bash
STATUS=$(curl -sL -w "%{http_code}" -I "https://framework.zend.com" -o /dev/null)
if [ "200" != "${STATUS}" ];then
    exit 1;
fi
