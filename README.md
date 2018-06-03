Установка
---------
1. git clone https://github.com/aristova/atlas-test-task.git
2. В корне проекта выполнить composer install
3. Создать базу данных php bin/console doctrine:database:create
4. Обновить структуру базы данных php bin/console doctrine:schema:update --force
5. Запустить локальный веб-сервер php bin/console server:run


Примеры использования
---------------------

**Сотрудники**
Создание:
curl -d '{"name":"Ivanov", "number":666, "note":"The best doctor"}' -H "Content-Type: application/json" -X POST http://127.0.0.1:8000/employee/new

Информация об одном сотруднике:
curl  -H "Content-Type: application/json" -X GET http://127.0.0.1:8000/employee/1

Информация о всех сотрудниках:
curl -H "Content-Type: application/json" -X GET http://127.0.0.1:8000/employee/

Информация, отфильтрованная по табельному номеру сотрудника:
curl -d '{"number":666}' -H "Content-Type: application/json" -X POST http://127.0.0.1:8000/employee/

Информация, отфильтрованная по фамилии сотрудника:
curl -d '{"name":"Ivanov"}' -H "Content-Type: application/json" -X POST http://127.0.0.1:8000/employee/

Редактирование:
curl -d '{"name":"Petrov", "note":"The best doctor"}' -H "Content-Type: application/json" -X PUT http://127.0.0.1:8000/employee/1/edit

Удаление:
curl -H "Content-Type: application/json" -X DELETE http://127.0.0.1:8000/employee/1


**Записи в календаре**

_number - это табельный номер врача_

Создание:
curl -d '{"number":666, "status":"holiday", "from":"925574552", "to":"971193752"}' -H "Content-Type: application/json" -X POST http://127.0.0.1:8000/entry/new

Информация об одной записи:
curl  -H "Content-Type: application/json" -X GET http://127.0.0.1:8000/entry/1

Информация о всех записях:
curl -H "Content-Type: application/json" -X GET http://127.0.0.1:8000/entry/

Редактирование:
curl -d '{"number":666, "status":"sick", "from":"1528029333", "to":"1537943333"}' -H "Content-Type: application/json" -X PUT http://127.0.0.1:8000/entry/1/edit

Удаление:
curl -H "Content-Type: application/json" -X DELETE http://127.0.0.1:8000/entry/1