Anton Sidorenko homework_excel  https://gitlab.artvisio.com/anton.sidorenko/homework_excel

1) Можно заменить на setParameters и массивом передать параметры
https://gitlab.artvisio.com/anton.sidorenko/homework_excel/-/blob/master/src/Repository/CellRepository.php#L35

2) В типизации указан класс UserInterface как тип входящего параметра $user, а в условии проверяется на instance User
https://gitlab.artvisio.com/anton.sidorenko/homework_excel/-/blob/master/src/Repository/UserRepository.php#L30

3) Не указан тип входящей переменной
https://gitlab.artvisio.com/anton.sidorenko/homework_excel/-/blob/master/src/Repository/UserRepository.php#L39

4) Не указан тип возвращаемого значения
- https://gitlab.artvisio.com/anton.sidorenko/homework_excel/-/blob/master/src/Repository/UserRepository.php#L39,

- https://gitlab.artvisio.com/anton.sidorenko/homework_excel/-/blob/master/src/Repository/UserRepository.php#L48,

- https://gitlab.artvisio.com/anton.sidorenko/homework_excel/-/blob/master/src/Repository/SheetRepository.php#L40,

- https://gitlab.artvisio.com/anton.sidorenko/homework_excel/-/blob/master/src/Repository/SheetRepository.php#L50,

5) Var Dump
https://gitlab.artvisio.com/anton.sidorenko/homework_excel/-/blob/master/src/EventListener/AuthenticationFailedListener.php#L11

