
# Change Log
All notable changes to this project will be documented in this file.
 
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).
 
## [Unreleased] - yyyy-mm-dd
 
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
 
### Исправлено
