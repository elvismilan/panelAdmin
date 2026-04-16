<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= htmlspecialchars($title ?? 'Login', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
	<h1>Iniciar Sesion</h1>

	<?php if (!empty($error)): ?>
		<p style="color:#b00020;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
	<?php endif; ?>

	<form method="post" action="/login">
		<?= $csrfField ?>
		<label>
			Usuario
			<input type="text" name="username" required>
		</label>
		<br><br>
		<label>
			Password
			<input type="password" name="password" required>
		</label>
		<br><br>
		<button type="submit">Entrar</button>
	</form>
</body>
</html>
