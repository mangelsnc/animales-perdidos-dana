#!/bin/bash

FILE="src/index.php"
REMOTE_FILE="dana.php"
REMOTE_USER="root"
REMOTE_HOST="gorogoro.es"
REMOTE_PATH="/var/www/html/"

if [[ ! -f $FILE ]]; then
    echo "Error: El archivo $FILE no existe."
    exit 1
fi

scp "$FILE" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/$REMOTE_FILE"
scp -r src/stats-img "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/"

if [[ $? -ne 0 ]]; then
    echo "Error: Fallo en la transferencia del archivo."
    exit 1
fi

ssh "$REMOTE_USER@$REMOTE_HOST" "chown www-data:www-data $REMOTE_PATH$REMOTE_FILE"
ssh "$REMOTE_USER@$REMOTE_HOST" "chown -R www-data:www-data $REMOTE_PATH/stats-img"

if [[ $? -eq 0 ]]; then
    echo "El archivo $FILE se ha subido y la propiedad se ha cambiado exitosamente."
else
    echo "Error: Fallo al cambiar la propiedad del archivo."
    exit 1
fi
