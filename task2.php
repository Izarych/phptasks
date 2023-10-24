<?php

// Подключение необходимых библиотек и зависимостей
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

// Загрузка конфигурации из файла .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Создание подключения к базе данных PostgreSQL
$dbHost = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// URL, который будем парсить
$url = "https://www.bills.ru";
$client = new Client();

// Отправка GET-запроса
$response = $client->get($url);

if ($response->getStatusCode() == 200) {
    $html = (string)$response->getBody();

    // Создание объекта Crawler для парсинга HTML
    $crawler = new Crawler($html);

    // Поиск всех элементов <tr> с классом 'bizon_api_news_row'
    $rows = $crawler->filter('div.layout_megasoftnews_api_list table.bizon_api_news_table tr.bizon_api_news_row');

    // Подготовка SQL-запроса для создания таблицы
    $createTableSQL = "DROP TABLE IF EXISTS bills_ru_events;
        CREATE TABLE IF NOT EXISTS bills_ru_events (
        id SERIAL PRIMARY KEY,
        date TIMESTAMP,
        title VARCHAR(230),
        url VARCHAR(240) UNIQUE
    )";

    // Создание таблицы
    try {
        $pdo->exec($createTableSQL);
    } catch (PDOException $e) {
        die("Ошибка при создании таблицы: " . $e->getMessage());
    }

    // Извлечение информации из найденных элементов
    $data = [];
    $rows->each(function ($row) use (&$data) {
        $td = $row->filter('tr.bizon_api_news_row');

        // Проверка наличия <span> с классом 'bizon_api_news_original_date'
        if ($td->filter('span.bizon_api_news_original_date')->count() > 0) {
            // Если <span> присутствует, получаем ссылку из <a> внутри <td>
            $url = $td->filter('a')->attr('href');
            $title = $td->filter('a')->text();

            $date = $td->filter('td.news_date')->text(); // Получаем текст <td> как дату

            $data[] = [
                'date' => $date,
                'url' => $url,
                'title' => $title,
            ];
        }
    });

    // Вставка данных в базу данных
    foreach ($data as $item) {
        // Хардкод, каюсь
        $item['date'] = str_replace('мар', 'march', $item['date']);

        // Преобразование даты в правильный формат
        $date = DateTime::createFromFormat('d M Y', $item['date']);
        $formattedDate = $date ? $date->format('Y-m-d H:i:s') : null;
        $insertSQL = "INSERT INTO bills_ru_events (date, url, title) VALUES (:date, :url, :title)";
        $stmt = $pdo->prepare($insertSQL);
        $stmt->bindParam(':date', $formattedDate);
        $stmt->bindParam(':url', $item['url']);
        $stmt->bindParam(':title', $item['title']);
        $stmt->execute();
    }

    echo "Данные успешно сохранены в базу данных.";
} else {
    echo "Не удалось загрузить страницу.";
}
