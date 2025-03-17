<?php
session_start();
// Проверка на авторизацию
if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}
// Подключение к базе данных
require 'db.php';
// Получение роли пользователя из базы данных
$username = $_SESSION['username'];
$query = "SELECT role FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();
// Проверка роли
if ($role !== 'moderator') {
    header("Location: mainPage.php"); // Перенаправление на главную для не модераторов
    exit();
}

// Получаем параметры из GET-запроса
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc'; // По умолчанию сортировка A → Z

// Получение всех таблиц из базы данных
$tablesQuery = "SHOW TABLES";
$tablesResult = $conn->query($tablesQuery);

// Инициализируем пустой массив $filteredTables для хранения имён таблиц, которые прошли фильтрацию
$filteredTables = [];

// Проверяем, есть ли в базе данных хотя бы одна таблица
if ($tablesResult->num_rows > 0) {
    // Цикл перебора всех таблиц из результата запроса "SHOW TABLES"
    while ($table = $tablesResult->fetch_array()) {
        // Проверяем два условия:
        // 1. Таблица не должна называться 'user_change_log' (исключаем её из списка)
        // 2. Имя таблицы должно содержать поисковый запрос ($searchTerm), регистр символов не учитывается
        if ($table[0] !== 'user_change_log' && stripos($table[0], $searchTerm) !== false) {
            $filteredTables[] = $table[0];
        }
    }
}

// Сортируем таблицы в зависимости от параметра $order
if ($order === 'asc') {
    sort($filteredTables); // Сортировка A → Z
} elseif ($order === 'desc') {
    rsort($filteredTables); // Сортировка Z → A
}

$tablesData = [];

foreach ($filteredTables as $table) {
    // Экранируем имя таблицы вручную
    $escapedTable = $conn->real_escape_string($table);

    // Формируем запрос с экранированным именем таблицы
    $rowsQuery = "SELECT COUNT(*) as row_count FROM `$escapedTable`";
    $result = $conn->query($rowsQuery);

    if ($result) {
        $rowCount = $result->fetch_assoc()['row_count'];
        $tablesData[] = [
            'name' => $table,
            'rows' => $rowCount
        ];
    } else {
        die("Ошибка выполнения запроса: " . $conn->error);
    }
}

// Получаем данные о статусах заказов
$statusQuery = "SELECT status, COUNT(*) as count FROM requests GROUP BY status";
$statusResult = $conn->query($statusQuery);

$statusData = [];
if ($statusResult->num_rows > 0) {
    while ($row = $statusResult->fetch_assoc()) {
        $statusData[] = [
            'status' => $row['status'],
            'count' => $row['count']
        ];
    }
}

// Закрываем соединение
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель</title>
    <link href="https://fonts.googleapis.com/css2?family=Tenor+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="navbar">
        <ul class="nav-list">
            <li><a href="admin_panel.php">Админ-панель</a></li>
            <li><a href="mainPage.php">Главная</a></li>
        </ul>
    </div>

    <!-- Поисковая форма -->
    <div class="search-bar">
        <form method="GET" action="admin_panel.php">
            <input type="text" name="search" placeholder="Поиск по таблицам..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit">Поиск</button>
        </form>
    </div>

    <div class="sort-dropdown">
        <form method="GET" action="admin_panel.php">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <label for="order">Сортировка:</label>
            <select id="order" name="order" onchange="this.form.submit()">
                <option value="asc" <?php echo $order === 'asc' ? 'selected' : ''; ?>>A → Z</option>
                <option value="desc" <?php echo $order === 'desc' ? 'selected' : ''; ?>>Z → A</option>
            </select>
        </form>
    </div>

    <div class="dashboardData">
        <h2>Управление данными</h2>
        <?php if (!empty($filteredTables)): ?>
            <h3>Список таблиц базы данных:</h3>
            <ul>
                <?php foreach ($filteredTables as $table): ?>
                    <li>
                        <a href="view_table.php?table=<?php echo htmlspecialchars($table); ?>">
                            <?php echo htmlspecialchars($table); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Таблицы не найдены в базе данных.</p>
        <?php endif; ?>

        <div class="table-chart">
            <div style="width: 40%; margin: auto;">
                <canvas id="tablesChart"></canvas>
            </div>

            <div style="width: 30%; margin: auto; margin-top: 50px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Данные для столбчатой диаграммы
        const tablesData = <?php echo json_encode($tablesData); ?>;

        const labels = tablesData.map(table => table.name);
        const rowsData = tablesData.map(table => table.rows);

        const ctx = document.getElementById('tablesChart').getContext('2d');
        const tablesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Количество строк',
                    data: rowsData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgb(53, 126, 126)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Данные для круговой диаграммы
        const statusData = <?php echo json_encode($statusData); ?>;

        const statusLabels = statusData.map(item => item.status);
        const statusCounts = statusData.map(item => item.count);

        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Количество заказов',
                    data: statusCounts,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ],
                    hoverBackgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Распределение заказов по статусам'
                    }
                }
            }
        });
    </script>
</body>

</html>