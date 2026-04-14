<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Dashboard</h5><span>Panel principal</span>
                </div>
                <div class="card-body">
                    <p>Nombre: <strong><?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <p>Usuario: <strong><?= htmlspecialchars($user['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <p>Grupo: <strong><?= htmlspecialchars($user['group'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <p>
                        <a href="/admin/tareas" class="btn btn-info btn-sm me-2">Ir al modulo de tareas</a>
                        <a href="/admin/modulos" class="btn btn-secondary btn-sm me-2">Ir al modulo de modulos</a>
                        <a href="/admin/personas" class="btn btn-warning btn-sm me-2">Ir al modulo de personas</a>
                        <a href="/admin/usuarios" class="btn btn-danger btn-sm">Ir al modulo de usuarios</a>
                    </p>
                    <form method="post" action="/logout"><button type="submit" class="btn btn-primary">Cerrar sesion</button></form>
                </div>
            </div>
        </div>
    </div>
</div>
