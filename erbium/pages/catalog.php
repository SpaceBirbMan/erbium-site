<?php
global $pdo;
$isAdmin = false;
// Обработка фильтров
$search = $_GET['search'] ?? null;
$type = $_GET['type'] ?? null;
$sort = $_GET['sort'] ?? null;

$queryString = "SELECT * FROM studios WHERE 1=1 AND isActive = 1";
$params = [];

if ($search) {
    $queryString .= " AND name LIKE :search";
    $params['search'] = "%$search%";
}

if ($type) {
    $queryString .= " AND type = :type";
    $params['type'] = $type;
}

if ($sort === 'name_asc') {
    $queryString .= " ORDER BY name ASC";
} elseif ($sort === 'name_desc') {
    $queryString .= " ORDER BY name DESC";
} elseif ($sort === 'rat_asc') {
    $queryString .= " ORDER BY average_rating ASC";
} elseif ($sort === 'rat_desc') {
    $queryString .= " ORDER BY average_rating DESC";
}

$stmt = $pdo->prepare($queryString);
$stmt->execute($params);
$studios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form class = "search-form" method="GET" action="index.php">
    <input type="hidden" name="page" value="catalog">
    <input type="text" name="search" placeholder="Enter name..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <select name="sort">
        <option value="">Sort</option>
        <option value="name_asc" <?= ($_GET['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>(A-Z)</option>
        <option value="name_desc" <?= ($_GET['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>(Z-A)</option>
        <option value="rat_desc" <?= ($_GET['sort'] ?? '') === 'rat_desc' ? 'selected' : '' ?>>High rating first </option>
        <option value="rat_asc" <?= ($_GET['sort'] ?? '') === 'rat_asc' ? 'selected' : '' ?>>Low rating first</option>
    </select>
    <button type="submit">Apply</button>
</form>

<div class="catalog">
    <?php foreach ($studios as $studio): ?>
        <?php renderStudioCard($studio, $isAdmin); ?>
    <?php endforeach; ?>
</div>
