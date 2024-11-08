#!/bin/bash

# Definimos las variables
FILE="../src/index.php"
REMOTE_FILE="dana.php"
REMOTE_USER="root"
REMOTE_HOST="gorogoro.es"
REMOTE_PATH="/var/www/html/"

# Comprobamos si el archivo existe
if [[ ! -f $FILE ]]; then
    echo "Error: El archivo $FILE no existe."
    exit 1
fi

# Subimos el archivo al servidor remoto
scp "$FILE" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/$REMOTE_FILE"

# Comprobamos si la transferencia fue exitosa
if [[ $? -ne 0 ]]; then
    echo "Error: Fallo en la transferencia del archivo."
    exit 1
fi

# Cambiamos la propiedad del archivo en el servidor remoto
ssh "$REMOTE_USER@$REMOTE_HOST" "chown www-data:www-data $REMOTE_PATH$REMOTE_FILE"

# Comprobamos si el cambio de propiedad fue exitoso
if [[ $? -eq 0 ]]; then
    echo "El archivo $FILE se ha subido y la propiedad se ha cambiado exitosamente."
else
    echo "Error: Fallo al cambiar la propiedad del archivo."
    exit 1
fi
