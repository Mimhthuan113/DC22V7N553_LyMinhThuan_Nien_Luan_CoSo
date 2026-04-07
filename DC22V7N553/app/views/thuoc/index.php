<!-- app/views/thuoc/index.php -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách thuốc</title>
    <style>
        table { border-collapse: collapse; width: 80%; margin: 24px auto; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Danh sách thuốc</h1>
    <table>
        <tr>
            <th>Mã</th>
            <th>Tên thuốc</th>
            <th>Giá</th>
            <th>Đơn vị</th>
        </tr>
        <?php foreach ($dsThuoc as $thuoc): ?>
        <tr>
            <td><?= $thuoc['ma_thuoc'] ?></td>
            <td><?= $thuoc['ten_thuoc'] ?></td>
            <td><?= number_format($thuoc['gia']) ?>đ</td>
            <td><?= $thuoc['don_vi'] ?></td>s
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>