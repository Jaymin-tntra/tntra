<?php
require 'vendor/autoload.php';

try {
    $excelFile = 'data/submissions.xlsx';
    
    if (!file_exists($excelFile)) {
        die("<h2>No submissions yet</h2><p>Form submissions will appear here once received.</p>");
    }

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelFile);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();
    $headers = array_shift($data);
} catch (Exception $e) {
    die("<h2>Error loading submissions</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form Submissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; max-width: 1200px; margin: 0 auto; }
        h1 { margin-bottom: 30px; text-align: center; }
        .table-responsive { margin: 20px 0; }
        .back-link { display: block; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Form Submissions</h1>
    
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <?php foreach ($headers as $header): ?>
                        <th><?= htmlspecialchars($header) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?= htmlspecialchars($cell) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <a href="index.html" class="back-link">Back to Form</a>
</body>
</html>