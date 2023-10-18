
# Change Log
All notable changes to this project will be documented in this file.
 
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).


## [Unreleased] - 2023-10-17
 
Добавление фасада синхронизации ONU
 
### Добавлено

*+SyncOnuFacade* — Фасад реализует синхронизацию ONU с биллингом. В настоящий момент одно направлено - из биллинга в юзерсайд
*+Bgb/Db/MysqlDb* — Объект для взаимодействия с БД биллинга.

 
### Изменено

*Model/Config* — В исключение включает название конфиг-фаила.

 
## [0.1.2] - 2023-10-17
 
Выносим состояния в отельный объект Config.
 
### Добавлено

*+Model/Config* — Объект содержащий в себе параметры конфигурации. Возобновляемый, использует в себе кастумный трейт dotEnv. Конструктор может брать второй необязательный параметр, который будет отключать исключение, в случае ошибок.

*+BgbUsFacade->getTaskType($taskId)* — Поведение используется, для получения типа задания, дабы была возможность корректировать поведение статуса *Завершить*. В *$taskId* передаем id задачи.

 
### Изменено

Перенос большинства состояний в Config и .env файлы - статусы, типы заданий, URL и пр.

*CustomDotEnv->dotEnvConfig* - включено игнорирование пустых строк и комментариев. Можно передавать второй необязательный параметр возращаемого значения по умолчанию.

 
### Исправлено

Прическа актуальными id статусами боевой системы.



## [0.1.1] - 2023-10-13
 
Выносим регистрацию абонента в фасад
 
### Добавлено

*+BgbUsFacade->registrationNewCustomer($eventArray)* — интерфейс обработки создания нового абонента. В *$eventArray* передаем массив предоставленый событием.

*+BgbUsFacade->statusNewCustomer* — массив статусов задачи регистрации нового абонента.
 
### Изменено

Перенос обработки события *task_state_change_before* и *task_state_change* в фасад.

*-BgbUsFacade->getContractNumber*

*-BgbUsFacade->removeEquipmentInTask*

*-BgbUsFacade->attachGponSerial*

*-BgbUsFacade->switchToRegular*
