#!/bin/bash

TITLE="<?= $this->get_value("title") ?>"
BODY="<?= $this->get_value("body") ?>"
TOKEN="<?= $this->get_value("token") ?>"
FBAPITOKEN="<?= $this->get_value("fbapitoken") ?>"

curl -X POST --header "Authorization: key=$FBAPITOKEN" \
    --Header "Content-Type: application/json" \
    https://fcm.googleapis.com/fcm/send \
    -d "{\"to\":\"$TOKEN\",\"notification\":{\"title\":\"$TITLE\",\"body\":\"$BODY\"},\"priority\":\"high\"}"


# \"content_available\": true,

