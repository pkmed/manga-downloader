docker-up:
	docker compose up -d
docker-down:
	docker compose down
docker-connect-ssh:
	docker compose exec -ti manga-loader-php bash
docker-connect-ssh-root:
	docker compose exec -u 0 -ti manga-loader-php bash
