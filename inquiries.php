<?php
// Supabase API Configuration
$SUPABASE_URL = "https://zmriofxctebhbprcmdsl.supabase.co";
$SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InptcmlvZnhjdGViaGJwcmNtZHNsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTQwNDcwMDAsImV4cCI6MjA2OTYyMzAwMH0.AmvRdbVnTShRA4-PfiGmwo_YmNInL0GcQsQ_oZVDHoA";

// Fetch Inquiries from Supabase
$ch = curl_init("$SUPABASE_URL/rest/v1/inquiry?select=*");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $SUPABASE_KEY",
    "Authorization: Bearer $SUPABASE_KEY",
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$inquiries = [];
if ($httpCode === 200) {
    $inquiries = json_decode($response, true);
} else {
    echo "<p>Failed to load inquiries (Status $httpCode)</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submitted Inquiries</title>
  <link rel="stylesheet" href="style.css"> <!-- Use your existing CSS -->
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f8f8;
    }
    .container {
      max-width: 1200px;
      margin: 2rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      margin-bottom: 1rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    th, td {
      padding: 10px 12px;
      border: 1px solid #ccc;
      text-align: left;
    }
    th {
      background-color: #f1f1f1;
    }
    tr:nth-child(even) {
      background-color: #fafafa;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Submitted Inquiries</h2>
    <?php if (count($inquiries) === 0): ?>
      <p>No inquiries found.</p>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Contact</th>
          <th>User Type</th>
          <th>Purpose</th>
          <th>Property Type</th>
          <th>Bedrooms</th>
          <th>Area (sqft)</th>
          <th>Location</th>
          <th>Budget</th>
          <th>Message</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($inquiries as $inq): ?>
          <tr>
            <td><?= htmlspecialchars($inq['full_name']) ?></td>
            <td><?= htmlspecialchars($inq['email']) ?></td>
            <td><?= htmlspecialchars($inq['phone_no']) ?></td>
            <td><?= htmlspecialchars($inq['contact_method']) ?></td>
            <td><?= htmlspecialchars($inq['user_type']) ?></td>
            <td><?= htmlspecialchars($inq['purpose']) ?></td>
            <td><?= htmlspecialchars($inq['property_type']) ?></td>
            <td><?= htmlspecialchars($inq['bedrooms']) ?></td>
            <td><?= htmlspecialchars($inq['sqfarea'] ?? '-') ?></td>
            <td><?= htmlspecialchars($inq['preferred_location']) ?></td>
            <td><?= htmlspecialchars($inq['budget_range']) ?></td>
            <td><?= nl2br(htmlspecialchars($inq['message'])) ?></td>
            <td><?= date('d M Y, H:i', strtotime($inq['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</body>
</html>
