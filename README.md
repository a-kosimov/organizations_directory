    
1. Клонируйте репозиторий:
``` bash
git clone git@github.com:a-kosimov/organizations_directory.git
cd organizations-directory
cp .env.example env
```
2.Конфигурация env

``` bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=33060
DB_DATABASE=laravel_organizations
DB_USERNAME=laravel
DB_PASSWORD=secret
```


3.Запустите контейнеры:

``` bash
docker-compose up -d
```

4.Установите зависимости и сгенерируйте автозагрузку (если не сделано):

``` bash
composer install
composer require "darkaonline/l5-swagger"
php artisan key:generate
```
    

5. Запустите миграции и сидеры:

``` bash
php artisan migrate --seed
```
    
6.Сгенерируйте документацию Swagger:

``` bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
php artisan l5-swagger:generate
```

7. Запуск 

``` bash
php artisan serve
```

Документация доступна по адресу:

http://localhost:8000/api/documentation

тут по хорошему нужны индексы:

Таблица	                 	
organizations - building_id, name	

phone_numbers - organization_id	

buildings - (latitude, longitude)

activities	- parent_id	

activity_organization	-   organization_id, activity_id	
