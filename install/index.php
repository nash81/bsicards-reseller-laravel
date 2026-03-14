<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);
$envPath = $rootPath . DIRECTORY_SEPARATOR . '.env';
$sqlPath = __DIR__ . DIRECTORY_SEPARATOR . 'bsicards.sql';
$installedMarkerPath = $rootPath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'installed';

$errors = [];
$success = null;
$alreadyInstalled = is_file($installedMarkerPath);

$envValues = readEnvFile($envPath);

$form = [
    'app_url' => $envValues['APP_URL'] ?? '',
    'db_name' => $envValues['DB_DATABASE'] ?? '',
    'db_user' => $envValues['DB_USERNAME'] ?? '',
    'db_pass' => $envValues['DB_PASSWORD'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['app_url'] = trim((string)($_POST['app_url'] ?? ''));
    $form['db_name'] = trim((string)($_POST['db_name'] ?? ''));
    $form['db_user'] = trim((string)($_POST['db_user'] ?? ''));
    $form['db_pass'] = (string)($_POST['db_pass'] ?? '');

    if ($form['app_url'] === '' || filter_var($form['app_url'], FILTER_VALIDATE_URL) === false) {
        $errors[] = 'Please enter a valid Website URL (for example: https://example.com).';
    }

    if ($form['db_name'] === '') {
        $errors[] = 'Database name is required.';
    }

    if ($form['db_user'] === '') {
        $errors[] = 'Database username is required.';
    }

    if (!is_file($envPath) || !is_readable($envPath) || !is_writable($envPath)) {
        $errors[] = '.env file is missing or not writable. Please verify file permissions.';
    }

    if (!is_file($sqlPath) || !is_readable($sqlPath)) {
        $errors[] = 'SQL file not found or not readable: install/bsicards.sql';
    }

    if (empty($errors)) {
        $dbHost = $envValues['DB_HOST'] ?? '127.0.0.1';
        $dbPort = (int)($envValues['DB_PORT'] ?? 3306);

        $connectionError = testDatabaseConnection($dbHost, $dbPort, $form['db_name'], $form['db_user'], $form['db_pass']);

        if ($connectionError !== null) {
            $errors[] = 'Database connection failed: ' . $connectionError;
        } else {
            $envUpdateError = updateEnvFile($envPath, [
                'APP_URL' => $form['app_url'],
                'DB_DATABASE' => $form['db_name'],
                'DB_USERNAME' => $form['db_user'],
                'DB_PASSWORD' => $form['db_pass'],
            ]);

            if ($envUpdateError !== null) {
                $errors[] = 'Unable to update .env file: ' . $envUpdateError;
            } else {
                $importError = importSqlFile($dbHost, $dbPort, $form['db_name'], $form['db_user'], $form['db_pass'], $sqlPath);

                if ($importError !== null) {
                    $errors[] = 'Database import failed: ' . $importError;
                } else {
                    $success = 'Installation completed successfully. .env has been updated and bsicards.sql was imported.';

                    // Write or refresh installed marker for visibility.
                    @file_put_contents($installedMarkerPath, 'Installed at: ' . date('d M Y h:i A'));
                    $alreadyInstalled = true;
                }
            }
        }
    }
}

function readEnvFile(string $envPath): array
{
    if (!is_file($envPath) || !is_readable($envPath)) {
        return [];
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return [];
    }

    $data = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Strip surrounding quotes when reading existing values.
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        $data[$key] = $value;
    }

    return $data;
}

function testDatabaseConnection(string $host, int $port, string $database, string $username, string $password): ?string
{
    $mysqli = @new mysqli($host, $username, $password, $database, $port);

    if ($mysqli->connect_errno) {
        return $mysqli->connect_error;
    }

    $mysqli->set_charset('utf8mb4');
    $mysqli->close();

    return null;
}

function importSqlFile(string $host, int $port, string $database, string $username, string $password, string $sqlPath): ?string
{
    $sqlContent = file_get_contents($sqlPath);
    if ($sqlContent === false || trim($sqlContent) === '') {
        return 'SQL file is empty or could not be read.';
    }

    $mysqli = @new mysqli($host, $username, $password, $database, $port);
    if ($mysqli->connect_errno) {
        return $mysqli->connect_error;
    }

    $mysqli->set_charset('utf8mb4');

    $combinedSql = "SET FOREIGN_KEY_CHECKS=0;\n" . $sqlContent . "\nSET FOREIGN_KEY_CHECKS=1;";

    if (!$mysqli->multi_query($combinedSql)) {
        $error = $mysqli->error;
        $mysqli->close();

        return $error;
    }

    while ($mysqli->more_results()) {
        if (!$mysqli->next_result()) {
            $error = $mysqli->error;
            $mysqli->close();

            return $error;
        }

        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    }

    $mysqli->close();

    return null;
}

function updateEnvFile(string $envPath, array $updates): ?string
{
    $content = file_get_contents($envPath);
    if ($content === false) {
        return 'Unable to read .env file.';
    }

    $backupPath = $envPath . '.install.bak';
    if (@file_put_contents($backupPath, $content) === false) {
        return 'Unable to create .env backup file.';
    }

    foreach ($updates as $key => $rawValue) {
        $value = formatEnvValue((string)$rawValue);
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (preg_match($pattern, $content) === 1) {
            $content = preg_replace($pattern, $key . '=' . $value, $content);
        } else {
            $content .= PHP_EOL . $key . '=' . $value;
        }
    }

    if (@file_put_contents($envPath, $content) === false) {
        return 'Unable to write updates to .env file.';
    }

    return null;
}

function formatEnvValue(string $value): string
{
    if ($value === '') {
        return '""';
    }

    $needsQuotes = strpbrk($value, " \t\r\n#=\"'") !== false;
    $escapedValue = str_replace('"', '\\"', $value);

    return $needsQuotes ? '"' . $escapedValue . '"' : $escapedValue;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSI Cards Installer</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
        }
        .container {
            max-width: 720px;
            margin: 48px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            padding: 28px;
        }
        h1 {
            margin: 0 0 12px;
            font-size: 24px;
        }
        .desc {
            margin: 0 0 24px;
            color: #475569;
            font-size: 14px;
            line-height: 1.5;
        }
        .alert {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-warning {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .field {
            margin-bottom: 14px;
        }
        label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            font-weight: 600;
        }
        input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
        }
        .btn {
            border: none;
            border-radius: 8px;
            background: #0ea5e9;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            padding: 11px 16px;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            background: #0284c7;
        }
        ul {
            margin: 0;
            padding-left: 18px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>BSI Cards Installation</h1>
    <p class="desc">Enter your website and database details. This installer will update <code>.env</code> and import <code>install/bsicards.sql</code>.</p>

    <?php if ($alreadyInstalled): ?>
        <div class="alert alert-warning">An existing install marker was found in <code>storage/installed</code>. Running this again may overwrite database data.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success !== null): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="field">
            <label for="app_url">Website URL</label>
            <input type="url" id="app_url" name="app_url" required value="<?php echo e($form['app_url']); ?>" placeholder="https://example.com">
        </div>

        <div class="field">
            <label for="db_name">Database Name</label>
            <input type="text" id="db_name" name="db_name" required value="<?php echo e($form['db_name']); ?>" placeholder="database_name">
        </div>

        <div class="field">
            <label for="db_user">Database Username</label>
            <input type="text" id="db_user" name="db_user" required value="<?php echo e($form['db_user']); ?>" placeholder="database_user">
        </div>

        <div class="field">
            <label for="db_pass">Database Password</label>
            <input type="password" id="db_pass" name="db_pass" value="<?php echo e($form['db_pass']); ?>" placeholder="database_password">
        </div>

        <button class="btn" type="submit">Install Now</button>
    </form>
</div>
</body>
</html>

