<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <h1>Dashboard</h1>
    <p>
        Nombre:
        <strong><?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong>
    </p>
    <p>
        Usuario activo:
        <strong><?= htmlspecialchars($user['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong>
    </p>
    <p>
        Grupo:
        <strong><?= htmlspecialchars($user['group'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong>
    </p>

    <form method="post" action="/logout">
        <button type="submit">Cerrar sesion</button>
    </form>
</body>
</html>