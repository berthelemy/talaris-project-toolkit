<!doctype html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Talaris') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
<main class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-11 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <?php if (session('error') !== null): ?>
                        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
                    <?php endif; ?>
                    <?php if (session('success') !== null): ?>
                        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
                    <?php endif; ?>
                    <?php if (session('errors') !== null): ?>
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                <?php foreach ((array) session('errors') as $error): ?>
                                    <li><?= esc((string) $error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?= $this->renderSection('content') ?>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
