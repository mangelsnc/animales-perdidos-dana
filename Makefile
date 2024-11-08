.DEFAULT_GOAL := help

DOCKER_COMPOSE_FILE := docker-compose.yml
SCRIPTS_DIR := ./scripts

# Ayuda
help:  ## Muestra esta ayuda
	@echo "Uso: make [comando]"
	@echo ""
	@echo "Comandos disponibles:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

# Objetivos para despliegue
deploy-code:  ## Ejecuta el script de despliegue de código
	bash $(SCRIPTS_DIR)/deploy-code.sh

deploy-data:  ## Ejecuta el script de despliegue de datos
	bash $(SCRIPTS_DIR)/deploy-data.sh

# Control del entorno en Docker
up:  ## Inicia el entorno Docker con docker-compose
	docker compose -f $(DOCKER_COMPOSE_FILE) up -d

down:  ## Detiene el entorno Docker
	docker compose -f $(DOCKER_COMPOSE_FILE) down

logs:  ## Muestra los logs del entorno Docker
	docker compose -f $(DOCKER_COMPOSE_FILE) logs -f

restart: down up  ## Reinicia el entorno Docker

.PHONY: help deploy-code deploy-data up down logs restart