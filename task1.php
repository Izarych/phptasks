<?php
/**
 * Поиск и вывод названий файлов с расширением ixt в папке /datafiles
 */

// Директория для поиска файлов
$directory = 'datafiles';

// Регулярное выражение (цифры, буквы латинского алфавита, расширение ixt)
// ищет только в нижнем регистре ixt
$pattern = '/^[A-Za-z0-9]+\.[i][x][t]$/';

// Получаем список файлов в /datafiles
$files = scandir($directory);

// init array
$filteredFiles = array();

// Проходим по каждому файлу
foreach ($files as $file) {
    //Проверяем регуляркой
    if (preg_match($pattern, $file)) {
        // добавляем в массив
        $filteredFiles[] = $file;
    }
}

// Сортируем массив
sort($filteredFiles);

// Выводим отсортированные имена файлов в столбик
foreach ($filteredFiles as $filteredFile) {
    echo $filteredFile . PHP_EOL;
}

