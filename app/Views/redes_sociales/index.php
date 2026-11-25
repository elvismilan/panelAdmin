<div class="container-fluid">
    <?php
    $canAgregar = $can('/redes/agregar', 'agregar');
    $canEditar = $can('/redes/1/editar', 'editar');
    $canEliminar = $can('/redes/1/eliminar', 'eliminar');
    ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Redes sociales</h5>
                        <span>Listado de redes del frontend</span>
                    </div>

                    <?php if ($canAgregar): ?>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/redes/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                            <span>Agregar</span>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="card-body pt-3">
                    <?php
                    $listToolbarConfig = [
                        'mode' => 'simple',
                        'searchConfig' => is_array($searchConfig ?? null) ? $searchConfig : [],
                    ];
                    $toolbarView = dirname(__DIR__) . '/components/list-toolbar.php';
                    if (is_file($toolbarView)) {
                        include $toolbarView;
                    }

                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) {
                        include $flashView;
                    }
                    ?>

                    <?php if (!empty($pagination['totalRows'])): ?>
                        <p class="text-muted small mb-2">
                            Mostrando <?= (int) ($pagination['from'] ?? 0) ?>-<?= (int) ($pagination['to'] ?? 0) ?>
                            de <?= (int) ($pagination['totalRows'] ?? 0) ?> registros
                        </p>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Icono</th>
                                    <th>Titulo</th>
                                    <th>Link</th>
                                    <th>Estado</th>
                                    <th class="text-center" style="width: 140px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <?php
                                    $emptyView = dirname(__DIR__) . '/components/admin-empty-state.php';
                                    $emptyTitle = !empty($search) ? 'Sin redes para esta búsqueda' : 'No hay redes sociales registradas';
                                    $emptyMessage = !empty($search)
                                        ? 'Cambia la búsqueda o límpiala para recuperar todo el listado.'
                                        : 'Aquí se mostrarán las redes sociales configuradas para el frontend con su icono, link y estado.';
                                    $emptyActionHref = !empty($search) ? '/redes' : ($canAgregar ? '/redes/agregar' : '');
                                    $emptyActionLabel = !empty($search) ? 'Limpiar búsqueda' : ($canAgregar ? 'Agregar red social' : '');
                                    $emptyColspan = 6;
                                    $emptyAsTableRow = true;
                                    if (is_file($emptyView)) {
                                        include $emptyView;
                                    }
                                    ?>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <?php $itemId = urlencode((string) ($item['res_id'] ?? '')); ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string) ($item['res_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <?php
                                                $iconClass = trim((string) ($item['res_icon'] ?? ''));
                                                $legacyIconMap = [
                                                    'fa-brands fa-facebook' => 'fa fa-facebook',
                                                    'fa-brands fa-facebook-f' => 'fa fa-facebook',
                                                    'fa-brands fa-instagram' => 'fa fa-instagram',
                                                    'fa-brands fa-x-twitter' => 'fa fa-twitter',
                                                    'fa-brands fa-youtube' => 'fa fa-youtube-play',
                                                    'fa-brands fa-tiktok' => 'fa fa-music',
                                                    'fa-brands fa-linkedin-in' => 'fa fa-linkedin',
                                                    'fa-brands fa-whatsapp' => 'fa fa-whatsapp',
                                                    'fa-brands fa-telegram' => 'fa fa-telegram',
                                                    'fa-brands fa-pinterest-p' => 'fa fa-pinterest-p',
                                                    'fa-brands fa-snapchat' => 'fa fa-snapchat-ghost',
                                                    'fa-brands fa-discord' => 'fa fa-comments',
                                                    'fa-brands fa-twitch' => 'fa fa-gamepad',
                                                    'fa-brands fa-reddit-alien' => 'fa fa-reddit-alien',
                                                    'fa-brands fa-github' => 'fa fa-github',
                                                    'fa-brands fa-gitlab' => 'fa fa-gitlab',
                                                    'fa-brands fa-medium' => 'fa fa-medium',
                                                    'fa-brands fa-vimeo-v' => 'fa fa-vimeo',
                                                    'fa-brands fa-spotify' => 'fa fa-spotify',
                                                    'fa-brands fa-apple' => 'fa fa-apple',
                                                    'fa-brands fa-google' => 'fa fa-google',
                                                    'fa-brands fa-threads' => 'fa fa-comments-o',
                                                    'fa-brands fa-line' => 'fa fa-commenting-o',
                                                    'fa-brands fa-weixin' => 'fa fa-wechat',
                                                    'fa-brands fa-skype' => 'fa fa-skype',
                                                    'fa-brands fa-slack' => 'fa fa-slack',
                                                    'fa-brands fa-dribbble' => 'fa fa-dribbble',
                                                    'fa-brands fa-behance' => 'fa fa-behance',
                                                    'fa-brands fa-flickr' => 'fa fa-flickr',
                                                    'fa-brands fa-wordpress' => 'fa fa-wordpress',
                                                    'fa-solid fa-link' => 'fa fa-globe',
                                                ];
                                                $iconClass = $legacyIconMap[$iconClass] ?? $iconClass;
                                                ?>
                                                <?php if ($iconClass !== ''): ?>
                                                    <i class="<?= htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true" style="font-size: 1.1rem;"></i>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($item['res_titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="small text-break"><?= htmlspecialchars((string) ($item['res_link'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            
                                            <td>
                                                <?php $estado = (string) ($item['res_estado'] ?? ''); ?>
                                                <?php if ($estado === 'H'): ?>
                                                    <span class="badge bg-success text-white">Habilitado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary text-white">Deshabilitado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($canEditar): ?>
                                                    <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/redes/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($canEliminar): ?>
                                                    <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/redes/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!$canEditar && !$canEliminar): ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php
                    $paginationAriaLabel = 'Paginacion redes sociales';
                    $paginationClass = 'pagination justify-content-center pagination-primary pagination-sm';
                    $paginationView = dirname(__DIR__) . '/components/pagination.php';
                    if (is_file($paginationView)) {
                        include $paginationView;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
