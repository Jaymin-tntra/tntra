<?php
$data = json_decode(file_get_contents('submissions.json'), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submitted Inquiries</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
  <h2>Submitted Inquiries</h2>
  <table class="table table-bordered">
    <thead>
      <tr>
        <?php if (!empty($data[0])): foreach ($data[0] as $key => $value): ?>
        <th><?php echo htmlspecialchars($key); ?></th>
        <?php endforeach; endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data as $entry): ?>
      <tr>
        <?php foreach ($entry as $value): ?>
        <td><?php echo htmlspecialchars($value); ?></td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
