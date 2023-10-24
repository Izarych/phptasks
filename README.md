## Запуск

1. Копируем репозиторий командой `git clone https://github.com/Izarych/phptasks.git`
2. Переходим в директорию проекта `cd phptasks`
3. Копируем файл `.env.example в .env` командой `copy .env.example .env`
4. В `.env` файле устанавливаем переменные для базы данных (postgreSQL) (как пример):
```yaml
DB_HOST=localhost
DB_PORT=5432
DB_NAME=yourdbname
DB_USER=yourdbuser
DB_PASSWORD=yourdbpassword
```
5. Для запуска тасков выполняем команды
```shell
composer install
php task1.php
php task2.php
```
6. task3 просто открываем в браузере
