# You have to define the values in {}
APP_NAME=pama-app
VERSION=1.1

PORT=9501
DIST_PORT=9501

# DOCKER TASKS
# Build the container
build: ## Build the container
	docker build -t $(APP_NAME) .

build-nc: ## Build the container without caching
	docker build --no-cache -t $(APP_NAME) .

run: ## Run container
	docker run -d --rm --env-file .env -p $(PORT):$(DIST_PORT) --name="$(APP_NAME)" $(APP_NAME)

up: build run ## Run container on port 

cli: ## Run cli container
	docker exec -it $(APP_NAME) sh

stop: ## Stop and remove a running container
	docker stop $(APP_NAME); docker rm $(APP_NAME)
