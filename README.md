Установка
---------


Примеры использования
---------------------

**Сотрудники**
Создание:
curl -d '{"name":"Ivanov", "number":666}' -H "Content-Type: application/json" -X POST http://127.0.0.1:8000/employee/new

Информация об одном сотруднике:
curl  -H "Content-Type: application/json" -X GET http://127.0.0.1:8000/employee/1

Информация о всех сотрудниках:
curl -H "Content-Type: application/json" -X GET http://127.0.0.1:8000/employee/

Редактирование:
curl -d '{"name":"Petrov", "note":"The best doctor"}' -H "Content-Type: application/json" -X PUT http://127.0.0.1:8000/employee/1/edit

Удаление:
curl -H "Content-Type: application/json" -X DELETE http://127.0.0.1:8000/employee/1


**Записи в календаре**

_number - это табельный номер врача_

Создание:
curl -d '{"number":1, "status":"holiday", "from":"925574552", "to":"971193752"}' -H "Content-Type: application/json" -X POST http://127.0.0.1:8000/entry/new

Информация об одной записи:
curl  -H "Content-Type: application/json" -X GET http://127.0.0.1:8000/entry/1

Информация о всех записях:
curl -H "Content-Type: application/json" -X GET http://127.0.0.1:8000/entry/

Редактирование:
curl -d '{"number":1, "status":"sick", "from":"1528029333", "to":"1537943333"}' -H "Content-Type: application/json" -X PUT http://127.0.0.1:8000/entry/50/edit

Удаление:
curl -H "Content-Type: application/json" -X DELETE http://127.0.0.1:8000/entry/1