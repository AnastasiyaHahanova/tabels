# Таблички

Небольшой проект, в котором можно создавать таблицы с числовыми значениями для выполнения различного рода математических вычислений<br><br>

###Установка 

1. Склонировать репозиторий в нужную директорию
```
git clone git@gitlab.artvisio.com:anastasiya.h/tables.git
```

2. Зайти в директорию, в которую был склонирован проект, и в консоли запустить следующую команду
```
sh bin/install.sh 
```

3. Прочитать документацию для грамотного использования
```
https://gist.github.com/AnastasiyaHahanova/5b470e8c739fb844dd883e63e8e5982a
```
<br>

###Быстрый старт

1. Создать пользователя с помощью команды
```
php bin/console user:create <YOUR_USERNAME>
```

2. Задать пользователю свой пароль

```
php bin/console change:user:pass
```
   
3. Запустить команду указав USERNAME созданного пользователя и имя хоста, на котором развернут проект
```
php bin/console generate:curl Nastya tables
```
<br>

### Предварительные требования 
- PHP >= 7.4 
- MySQL
- Composer

### Запуск тестов
``
sh bin/test.sh 
``

### Построен с помощью

* Фреймворк **Symfony** (https://symfony.com/what-is-symfony)

### Автор

* **Анастасия Хаханова** - (https://github.com/AnastasiyaHahanova)
