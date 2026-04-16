<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Dashboard</h5><span>Panel principal</span>
                </div>
                <div class="card-body">
                    <p>Nombre: <strong><?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <p>Grupo: <strong><?= htmlspecialchars($user['group'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong></p>
                   
                </div>
            </div>
        </div>
    </div>
</div>
