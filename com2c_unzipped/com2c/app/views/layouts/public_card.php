<?php $app = config('app'); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= e($app['name']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <?php include BASE_PATH . '/app/views/partials/navbar.php'; ?>
  <div class="container">
    <?php include $contentView; ?>
  </div>
  <script src="/assets/js/app.js"></script>
</body>
</html>
