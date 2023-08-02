<?php

require_once 'db.php';

// Получаем список товаров из БД
$mysqli = connect_to_db();
$result = $mysqli->query('SELECT * FROM products');
$products = $result->fetch_all(MYSQLI_ASSOC);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add-to-cart'])) {
        $productId = $_POST['product-id'] - 1;
        $quantity = $_POST['quantity'];

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'name' => $products[$productId]['name'],
                'price' => $products[$productId]['price'],
                'quantity' => $quantity,
                'image' => $products[$productId]['image'],
                'prikol' => $products[$productId]['prikol']
            ];
        }

        $_SESSION['cart-message'] = 'Товар успешно добавлен в корзину';

        $sort = isset($_SESSION['sort']) ? $_SESSION['sort'] : '';
        header('Location: index.php?sort=' . urlencode($sort));
        exit;
    }

    if (isset($_POST['update-cart']) || isset($_POST['remove-from-cart'])) {
        if (isset($_POST['update-cart'])) {
            foreach ($_POST['quantity'] as $productId => $quantity) {
                if ($quantity == 0) {
                    unset($_SESSION['cart'][$productId]);
                } else {
                    $_SESSION['cart'][$productId]['quantity'] = $quantity;
                }
            }
        }
        if (isset($_POST['remove-from-cart'])) {
            $productId = $_POST['product-id'];
            unset($_SESSION['cart'][$productId]);
        }

        $sort = isset($_SESSION['sort']) ? $_SESSION['sort'] : '';
        header('Location: index.php?sort=' . urlencode($sort));
        exit;
    }

    if (isset($_POST['clear-cart'])) {
        unset($_SESSION['cart']);

        header('Location: index.php');
        exit;
    }

    if (isset($_POST['pobeda'])) {
        unset($_SESSION['cart']);

        header('Location: pobeda.html');
        exit;
    }
}

// Выводим корзину
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
    $totalPrice = array_reduce($cart, function ($total, $product) {
        if (isset($product['price'])) {
            return $total + $product['price'] * $product['quantity'];
        }
        return $total;
    }, 0);
} else {
    $cart = [];
    $totalPrice = 0;
}


// Сортируем корзину
$sort = '';
if (isset($_GET['sort'])) {
    $sort = $_GET['sort'];
    $_SESSION['sort'] = $sort;

    $sortField = '';
    $sortDirection = '';

    switch ($sort) {
        case 'name-asc':
            $sortField = 'name';
            $sortDirection = 'ASC';
            break;
        case 'name-desc':
            $sortField = 'name';
            $sortDirection = 'DESC';
            break;
        case 'price-asc':
            $sortField = 'price';
            $sortDirection = 'ASC';
            break;
        case 'price-desc':
            $sortField = 'price';
            $sortDirection = 'DESC';
            break;
        case 'total-asc':
            $sortField = 'price * quantity';
            $sortDirection = 'ASC';
            break;
        case 'total-desc':
            $sortField = 'price * quantity';
            $sortDirection = 'DESC';
            break;
        case 'prikol-asc':
            $sortField = 'prikol';
            $sortDirection = 'ASC';
            break;
        case 'prikol-desc':
            $sortField = 'prikol';
            $sortDirection = 'DESC';
            break;
    }

    if (!empty($sortField) && !empty($sortDirection)) {
        uasort($cart, function ($a, $b) use ($sortField, $sortDirection) {
            if ($sortField === 'price * quantity') {
                $aTotal = $a['price'] * $a['quantity'];
                $bTotal = $b['price'] * $b['quantity'];

                if ($sortDirection === 'ASC') {
                    return $aTotal <=> $bTotal;
                } else {
                    return $bTotal <=> $aTotal;
                }
            } else {
                if ($sortDirection === 'ASC') {
                    return $a[$sortField] <=> $b[$sortField];
                } else {
                    return $b[$sortField] <=> $a[$sortField];
                }
            }
        });
    }
}


// echo "<pre>";
// print_r($_SESSION['cart']);
// echo "</pre>";

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Магазин</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</head>

<style>
    .input-group-append .btn {
        height: 100%;
    }
</style>

<body>
    <div class="container">
        <div class="container">
            <?php if (isset($_SESSION['cart-message'])) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="container text-center">
                        <?php echo $_SESSION['cart-message']; ?>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['cart-message']); ?>
            <?php endif; ?>
        </div>
        <div class="container">
            <h1 class="display-1 text-center">Магазин стикеров</h1>
        </div>

        <hr>

        <div class="container">
            <h6 class="display-6 text-center my-4">Купите стикеры. Купите стикеры</h6>
        </div>

        <div class="row">
            <?php foreach ($products as $product) : ?>
                <div class="col-12 col-md-4 g-4">
                    <div class="card mb-3 h-100">
                        <img class="card-img-top p-2" src="images/<?= $product['image'] ?>.jpg" alt="<?= $product['name'] ?>">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="d-flex flex-column align-items-start">
                                <h3 class="card-title"><?= $product['name'] ?></h3>
                                <p class="card-text"><?= $product['description'] ?></p>
                            </div>
                            <form action="index.php" method="post">
                                <h4 class="card-subtitle mb-2 text-muted"><?= $product['price'] ?> руб.</h4>
                                <div class="my-2 fw-bolder">Прикольность: <?= $product['prikol'] ?></div>
                                <input type="hidden" name="product-id" value="<?= $product['id'] ?>">
                                <div class="input-group mb-3">
                                    <label class="input-group-text" for="quantity">Количество:</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1">
                                </div>
                                <button type="submit" class="btn btn-primary align-self-start" name="add-to-cart">Добавить в корзину</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>



        <hr>

        <!-- Кнопка открытия модального окна -->
        <button class="btn btn-primary btn-floating rounded-circle position-fixed bottom-0 end-0 m-3" data-bs-toggle="modal" data-bs-target="#cartModal" style="height: 45px; width: 45px">
            <i class="bi bi-cart"></i>
        </button>

        <!-- Модальное окно -->
        <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cartModalLabel">Корзина</h5>
                        <?php if (!empty($cart)) : ?>
                            <!-- <form method="GET" class="d-flex justify-content-center mx-auto mb-0"> -->
                                <label for="sort-select" class="mx-2 align-self-center">Сортировать по:</label>
                                <div class="d-flex align-items-center">
                                    <select id="sort-select" class="form-select" name="sort">
                                        <option value="name-asc" <?php if ($sort === 'name-asc') echo 'selected'; ?>>Имени (а-я)</option>
                                        <option value="name-desc" <?php if ($sort === 'name-desc') echo 'selected'; ?>>Имени (я-а)</option>
                                        <option value="price-asc" <?php if ($sort === 'price-asc') echo 'selected'; ?>>Цене (по возрастанию)</option>
                                        <option value="price-desc" <?php if ($sort === 'price-desc') echo 'selected'; ?>>Цене (по убыванию)</option>
                                        <option value="total-asc" <?php if ($sort === 'total-asc') echo 'selected'; ?>>Сумме (по возрастанию)</option>
                                        <option value="total-desc" <?php if ($sort === 'total-desc') echo 'selected'; ?>>Сумме (по убыванию)</option>
                                        <option value="prikol-asc" <?php if ($sort === 'prikol-asc') echo 'selected'; ?>>Прикольности (по возрастанию)</option>
                                        <option value="prikol-desc" <?php if ($sort === 'prikol-desc') echo 'selected'; ?>>Прикольности (по убыванию)</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary ms-2" onclick="sortCart()">Сортировать</button>
                                </div>
                            <!-- </form> -->
                        <?php endif; ?>
                        <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center mb-3">
                            <select id="filter-select" class="form-select" name="filter">
                                <option value="" selected>Без фильтра</option>
                                <option value="man">Человек</option>
                                <option value="notman">Не человек</option>
                            </select>
                            <button type="button" class="btn btn-primary ms-2" onclick="filterProducts()">Фильтровать</button>
                        </div>
                        <?php if (empty($cart)) : ?>
                            <p class="lead m-0">Корзина пуста</p>
                        <?php else : ?>
                            <?php foreach ($cart as $productId => $product) : ?>
                                <div class="card mb-3 product-card" data-price="<?= $product['price'] ?>" data-quantity="<?= $product['quantity'] ?>" data-prikol="<?= $product['prikol'] ?>">
                                    <div class="row g-0">
                                        <!-- <?php print_r($product); ?> -->
                                        <div class="col-md-4">
                                            <img src="images/<?= $product['image'] ?>.jpg" alt="<?= $product['name'] ?>" class="img-fluid h-100">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h5 class="card-title display-4"><?= $product['name'] ?></h5>
                                                <p class="card-text m-0"><?= $product['price'] ?> руб.</p>
                                                <div class="mb-2">Прикол: <?= $product['prikol'] ?></div>
                                                <h5 class="card-title">Количество</h5>
                                                <form action="index.php" method="post">
                                                    <input type="hidden" name="product-id" value="<?= $productId ?>">
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="quantity[<?= $productId ?>]" value="<?= $product['quantity'] ?>" min="1">
                                                        <div class="input-group-append">
                                                            <button type="submit" class="btn btn-outline-secondary" name="update-cart"><i class="bi bi-arrow-repeat"></i></button>
                                                            <button type="submit" class="btn btn-outline-danger" name="remove-from-cart"><i class="bi bi-trash"></i></button>
                                                        </div>
                                                    </div>
                                                </form>
                                                <div class="mt-2">Сумма: <?= $product['price'] * $product['quantity'] ?> руб</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <h4>Итого:</h4>
                                </div>
                                <div class="col-12 col-md-6 text-end">
                                    <h4><?= $totalPrice ?> руб.</h4>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <?php if (!empty($_SESSION["cart"])) : ?>
                            <form method="POST">
                                <button type="submit" class="btn btn-danger" name="clear-cart">Очистить корзину</button>
                            </form>
                            <form method="POST">
                                <button type="submit" class="btn btn-primary" name="pobeda" id="checkoutButton">Оформить заказ</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>

</html>