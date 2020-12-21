1) Нет ограничения на уникальность сочетания строки, столбца и таблицы<br>
https://gitlab.artvisio.com/evgeniia.shumakova/infinite-sheets-api/-/blob/master/src/Entity/Cell.php#L9

2) Можно вынести в репозиторий<br>
https://gitlab.artvisio.com/evgeniia.shumakova/infinite-sheets-api/-/blob/master/src/Controller/CellController.php#L68

3) В этом методе статус по умолчанию 200, можно в принципе не писать)<br>
https://gitlab.artvisio.com/evgeniia.shumakova/infinite-sheets-api/-/blob/master/src/Controller/CellController.php#L50

4) 4 одинаковых параметра получится<br>
https://gitlab.artvisio.com/evgeniia.shumakova/infinite-sheets-api/-/blob/master/src/Controller/CellController.php#L72

5) Нет total count, количества всех записей<br>
https://gitlab.artvisio.com/evgeniia.shumakova/infinite-sheets-api/-/blob/master/src/Controller/UserController.php#L71