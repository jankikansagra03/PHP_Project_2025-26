<?php
include_once 'db_config.php';

ob_start();

$select = "select * from products";
$data = mysqli_query($con, $select);
?>

<table class=" table table-bordered table-striped table-res">
    <tr>
        <th>Prodcut ID</th>
        <th>Product Name</th>
        <th>Product Image</th>
        <th>Product Price</th>
        <th>Actions</th>
    </tr>
    <?php
    while ($result = mysqli_fetch_assoc($data)) {
    ?>
        <tr>
            <td><?= $result['id'] ?></td>
            <td><?= $result['name'] ?></td>
            <td>
                <img src="<?= $result['image'] ?>" alt="" class="img-fluid" style="width: 100px; height: 100px; object-fit: cover;">
            </td>
            <td><?= $result['final_price'] ?></td>
            <td>
                <a href="edit_product.php?p_id=<?= $result['id'] ?>">
                    <input type="button" value="Edit" class="btn btn-primary">
                </a>
                <a href="view_product.php">
                    <input type="button" value="View" class="btn btn-secondary">
                </a>
                <a href="delete_product.php">
                    <input type="button" value="Delete" class="btn btn-danger">
                </a>
            </td>
        </tr>
    <?php
    }



    ?>
</table>
<?php
$content = ob_get_clean();
include_once 'layout.php';
