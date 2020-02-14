default:
	@echo "Please run 'make phpcs' for code testing."

phpcs:
	phpcs --standard=PSR2 -n ./app/library/MyCrawler/
