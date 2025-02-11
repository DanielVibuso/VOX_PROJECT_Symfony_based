# Symfony VOX API 

his API simulates a small system for registering and managing companies and their shareholder structure.

# About the Structure
Since the request specifically required creating the API in Symfony, I aimed to apply as many framework features as possible rather than decoupled approaches.

Added a migration that creates an admin user with the ROLE_ADMIN role, allowing them to create other users, as this role is required to access the registration route. <br>
Implemented JWT authentication with an expiration time. <br>
Added functional tests for controllers. <br>
Implemented pagination in listing endpoints. <br>
Added DTOs for input data validation and used the framework's standard normalization and JSON encoder for data output. <br>
Established a bidirectional many-to-many relationship between the Company and Partners entities. <br>

### Tecnologias


- Docker
- Docker compose
- Symfony 7.1
- PHP 8.2
- PHP-CS-Fix para
- PHP-UNIT

# To run the project
Make sure that the ports configured in this project's docker-compose file are not in use, then run the following commands:

docker-compose build <br>
docker-compose up -d <br>
docker-compose exec app composer install <br>
docker-compose exec app php bin/console lexik:jwt:generate-keypair <br>
docker-compose exec app php bin/console doctrine:database:create <br>
docker-compose exec app php bin/console doctrine:migration:migrate <br>


# TESTS

To initialize the test database, run: <br>
docker-compose exec app php bin/console doctrine:database:create --env=test <br>

To run migrations for the test database: <br>
docker-compose exec app php bin/console doctrine:migration:migrate --env=test <br>

To execute the tests, run the following command from the project root: <br>
docker-compose exec app ./vendor/bin/phpunit <br>