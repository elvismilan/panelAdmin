#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Module Generator — panelAdmin
 *
 * Genera Controller, Model, Views y Migración para un módulo nuevo,
 * siguiendo exactamente los patrones del framework.
 *
 * Uso básico:
 *   php bin/make-module.php --name=Producto --route=productos --prefix=pro --table=producto
 *
 * Con campos adicionales:
 *   php bin/make-module.php --name=Producto --route=productos --prefix=pro --table=producto \
 *     --fields="pro_precio:decimal:10:2,pro_estado:char:1,pro_stock:int"
 *
 * Módulo solo lectura (index + ver):
 *   php bin/make-module.php --name=Reporte --route=reportes --prefix=rpt --table=reporte --readonly
 *
 * Ver archivos sin crearlos:
 *   php bin/make-module.php --name=Producto ... --dry-run
 *
 * Sobreescribir archivos existentes:
 *   php bin/make-module.php --name=Producto ... --force
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo puede ejecutarse desde la línea de comandos.\n");
    exit(1);
}

define('ROOT', dirname(__DIR__));

// ---------------------------------------------------------------------------

final class ModuleGenerator
{
    // ANSI
    private const GREEN  = "\033[32m";
    private const RED    = "\033[31m";
    private const YELLOW = "\033[33m";
    private const CYAN   = "\033[36m";
    private const BOLD   = "\033[1m";
    private const RESET  = "\033[0m";

    // Mapeo tipo → SQL
    private const SQL_MAP = [
        'string'   => ['tpl' => 'VARCHAR({{sz}}) NOT NULL',             'default' => "''"],
        'char'     => ['tpl' => 'CHAR({{sz}}) NOT NULL DEFAULT \'\'',   'default' => "''"],
        'text'     => ['tpl' => 'TEXT NULL',                            'default' => 'null'],
        'int'      => ['tpl' => 'INT NOT NULL DEFAULT 0',               'default' => '0'],
        'decimal'  => ['tpl' => 'DECIMAL({{p}},{{s}}) NOT NULL DEFAULT 0.00', 'default' => '0'],
        'date'     => ['tpl' => 'DATE NULL',                            'default' => 'null'],
        'datetime' => ['tpl' => 'DATETIME NULL',                        'default' => 'null'],
        'bool'     => ['tpl' => 'TINYINT(1) NOT NULL DEFAULT 0',        'default' => '0'],
    ];

    // Mapeo tipo → regla de validación
    private const RULE_MAP = [
        'string'   => 'required|string|min:2|max:{{sz}}',
        'char'     => 'required|string|min:1|max:{{sz}}',
        'text'     => 'nullable|string',
        'int'      => 'required',
        'decimal'  => 'required',
        'date'     => 'nullable',
        'datetime' => 'nullable',
        'bool'     => 'nullable',
    ];

    // Mapeo tipo → tipo de input HTML
    private const INPUT_MAP = [
        'string'   => 'text',
        'char'     => 'text',
        'text'     => 'textarea',
        'int'      => 'number',
        'decimal'  => 'number',
        'date'     => 'date',
        'datetime' => 'datetime-local',
        'bool'     => 'checkbox',
    ];

    // ---

    private string $name        = '';
    private string $route       = '';
    private string $prefix      = '';
    private string $table       = '';
    private string $migNum      = '';
    private bool   $readonly    = false;
    private bool   $softDeletes = false;
    private bool   $dryRun      = false;
    private bool   $force       = false;

    /** @var list<array{col:string,type:string,size:string,label:string}> */
    private array $fields = [];

    // =========================================================================
    // Entry point
    // =========================================================================

    public function run(array $argv): void
    {
        $this->banner();
        $args = $this->parseArgv($argv);

        if (empty($args) || isset($args['interactive'])) {
            $this->runInteractive();
        } else {
            $this->loadArgs($args);
        }

        $this->resolveDefaults();
        $this->validateConfig();
        $this->generate();
    }

    // =========================================================================
    // Argument handling
    // =========================================================================

    /** @return array<string, string|true> */
    private function parseArgv(array $argv): array
    {
        $result = [];
        foreach (array_slice($argv, 1) as $arg) {
            if (!str_starts_with($arg, '--')) {
                continue;
            }
            $arg = substr($arg, 2);
            if (str_contains($arg, '=')) {
                [$key, $val] = explode('=', $arg, 2);
                $result[trim($key)] = trim($val);
            } else {
                $result[trim($arg)] = true;
            }
        }
        return $result;
    }

    /** @param array<string, string|true> $args */
    private function loadArgs(array $args): void
    {
        $this->name        = (string) ($args['name']          ?? '');
        $this->route       = (string) ($args['route']         ?? '');
        $this->prefix      = (string) ($args['prefix']        ?? '');
        $this->table       = (string) ($args['table']         ?? '');
        $this->migNum      = (string) ($args['migration-num'] ?? '');
        $this->readonly    = isset($args['readonly']);
        $this->softDeletes = isset($args['soft-deletes']) && !$this->readonly;
        $this->dryRun      = isset($args['dry-run']);
        $this->force       = isset($args['force']);

        if (!empty($args['fields'])) {
            $this->fields = $this->parseFields((string) $args['fields']);
        }
    }

    private function runInteractive(): void
    {
        $this->line('Modo interactivo — responde las siguientes preguntas.', 'cyan');
        $this->line('Presiona Enter para aceptar el valor entre [corchetes].', 'cyan');
        $this->line('');

        $this->name   = $this->ask('Nombre del módulo (PascalCase)', '', true);
        $this->route  = $this->ask('Ruta URL plural', strtolower($this->name) . 's');
        $this->prefix = $this->ask('Prefijo de columnas (2-5 letras)', substr(strtolower($this->name), 0, 3));
        $this->table  = $this->ask('Tabla DB (sin prefijo)', strtolower($this->name));

        $rawFields = $this->ask('Campos extra (col:tipo:tam,...) o Enter para omitir', '');
        if ($rawFields !== '') {
            $this->fields = $this->parseFields($rawFields);
        }

        $this->readonly    = $this->askBool('¿Solo lectura (index + ver)?', false);
        $this->softDeletes = $this->readonly ? false : $this->askBool('¿Soft deletes?', false);
        $this->dryRun      = $this->askBool('¿Dry-run (no escribir archivos)?', false);
    }

    private function resolveDefaults(): void
    {
        if (empty($this->fields)) {
            $this->fields[] = [
                'col'   => $this->prefix . '_nombre',
                'type'  => 'string',
                'size'  => '250',
                'label' => 'Nombre',
            ];
        }

        if ($this->migNum === '') {
            $this->migNum = $this->nextMigrationNumber();
        }
    }

    private function validateConfig(): void
    {
        $errors = [];

        if (!preg_match('/^[A-Z][a-zA-Z0-9]+$/', $this->name)) {
            $errors[] = '--name debe ser PascalCase sin espacios (ej: Producto, OrdenVenta).';
        }
        if (!preg_match('/^[a-z][a-z0-9\-]+$/', $this->route)) {
            $errors[] = '--route debe ser minúsculas-con-guiones (ej: productos, ordenes-venta).';
        }
        if (!preg_match('/^[a-z]{2,5}$/', $this->prefix)) {
            $errors[] = '--prefix debe tener 2–5 letras minúsculas (ej: pro, ord, rpt).';
        }
        if (!preg_match('/^[a-z][a-z0-9_]+$/', $this->table)) {
            $errors[] = '--table debe ser snake_case (ej: producto, orden_venta).';
        }

        if (!empty($errors)) {
            $this->line('');
            foreach ($errors as $e) {
                $this->line('  ✗ ' . $e, 'red');
            }
            $this->line('');
            exit(1);
        }
    }

    // =========================================================================
    // Orchestration
    // =========================================================================

    private function generate(): void
    {
        $mode = $this->readonly ? 'readonly' : 'crud';
        $this->line('');
        $this->line(
            sprintf('Módulo: %s  |  Modo: %s%s', $this->name, strtoupper($mode), $this->dryRun ? '  |  DRY-RUN' : ''),
            'bold'
        );
        $this->line(str_repeat('─', 60), 'cyan');

        // Controller
        $this->writeFile(
            "app/Controllers/{$this->name}Controller.php",
            $this->readonly ? $this->buildControllerReadonly() : $this->buildController()
        );

        // Model
        $this->writeFile(
            "app/Models/{$this->name}Model.php",
            $this->buildModel()
        );

        // Views
        $viewDir = 'app/Views/' . strtolower($this->name);

        $this->writeFile("{$viewDir}/index.php", $this->buildViewIndex());

        if ($this->readonly) {
            $this->writeFile("{$viewDir}/ver.php", $this->buildViewVer());
        } else {
            $this->writeFile("{$viewDir}/agregar.php",  $this->buildViewAgregar());
            $this->writeFile("{$viewDir}/editar.php",   $this->buildViewEditar());
            $this->writeFile("{$viewDir}/eliminar.php", $this->buildViewEliminar());
        }

        // Migrations
        $migBase = "core/database/migrations/{$this->migNum}_create_{$this->table}";
        $this->writeFile("{$migBase}.sql",      $this->buildMigrationUp());
        $this->writeFile("{$migBase}_down.sql", $this->buildMigrationDown());

        $this->line('');
        $this->printRoutes();
    }

    private function writeFile(string $relative, string $content): void
    {
        $full   = ROOT . '/' . $relative;
        $exists = file_exists($full);

        if ($exists && !$this->force) {
            $this->line("  ~ {$relative}  (ya existe — usa --force para sobreescribir)", 'yellow');
            return;
        }

        if ($this->dryRun) {
            $this->line("  ○ {$relative}", 'cyan');
            return;
        }

        $dir = dirname($full);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $this->line("  ✗ No se pudo crear el directorio: {$dir}", 'red');
            return;
        }

        if (file_put_contents($full, $content) !== false) {
            $verb = $exists ? 'sobreescrito' : 'creado';
            $this->line("  ✓ {$relative} [{$verb}]", 'green');
        } else {
            $this->line("  ✗ Error al escribir: {$relative}", 'red');
        }
    }

    private function printRoutes(): void
    {
        $this->line('Agrega estas líneas a routes/web.php:', 'bold');
        $this->line(str_repeat('─', 60), 'cyan');

        $c = $this->name . 'Controller::class';
        $r = $this->route;

        echo self::YELLOW;
        echo "use App\\Controllers\\{$this->name}Controller;\n\n";
        echo "// {$this->name}\n";

        if ($this->readonly) {
            echo "\$router->get('/{$r}',          {$c}, 'index');\n";
            echo "\$router->get('/{$r}/{id}/ver', {$c}, 'ver');\n";
        } else {
            $pad = str_repeat(' ', max(0, 26 - strlen($r)));
            echo "\$router->get( '/{$r}',{$pad}          {$c}, 'index');\n";
            echo "\$router->get( '/{$r}/agregar',{$pad}      {$c}, 'agregar');\n";
            echo "\$router->post('/{$r}/guardar',{$pad}      {$c}, 'guardar');\n";
            echo "\$router->get( '/{$r}/{id}/editar',{$pad}  {$c}, 'editar');\n";
            echo "\$router->post('/{$r}/{id}/actualizar',{$pad}{$c}, 'actualizar');\n";
            echo "\$router->get( '/{$r}/{id}/eliminar',{$pad} {$c}, 'eliminar');\n";
            echo "\$router->post('/{$r}/{id}/borrar',{$pad}   {$c}, 'borrar');\n";
        }

        echo self::RESET . "\n";
    }

    // =========================================================================
    // Builders — Controller
    // =========================================================================

    private function buildController(): string
    {
        $v = $this->vars();

        $tpl = <<<'PHP'
<?php

namespace App\Controllers;

use App\Models\{{Modulo}}Model;
use Core\Auth;
use Core\Controller;
use Core\Validator;
use Throwable;

class {{Modulo}}Controller extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $user    = Auth::user();
        $items   = [];
        $page    = $this->getCurrentPage();
        $perPage = $this->getDefaultPerPage();
        $filters = $this->getQueryParams(['q' => '']);
        $search  = $filters['q'];
        $pagination = $this->buildPagination(0, $page, $perPage, '/{{recursos}}', ['q' => $search]);

        try {
            $model      = new {{Modulo}}Model();
            $totalRows  = $model->countAll($search);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/{{recursos}}', ['q' => $search]);
            $items      = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search);
        } catch (Throwable) {
            $items = [];
        }

        $this->renderAdminModule('{{recurso}}/index', [
            'title'      => '{{TituloPlural}}',
            'user'       => $user,
            'items'      => $items,
            'pagination' => $pagination,
            'search'     => $search,
            'searchConfig' => [
                'action'      => '/{{recursos}}',
                'method'      => 'GET',
                'fields'      => [[
                    'name'        => 'q',
                    'type'        => 'text',
                    'placeholder' => 'Buscar por {{LabelPrincipal}}...',
                    'value'       => $search,
                    'icon'        => 'fa fa-search',
                ]],
                'submitLabel' => 'Buscar',
                'submitIcon'  => 'fa fa-search',
                'clearUrl'    => $search !== '' ? '/{{recursos}}' : '',
            ],
        ]);
    }

    public function agregar(): void
    {
        $this->requireAuth();

        $this->renderAdminModule('{{recurso}}/agregar', [
            'title' => 'Nuevo {{TituloSingular}}',
            'user'  => Auth::user(),
            'error' => null,
            'form'  => [
{{FormDefaults}}
            ],
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model  = new {{Modulo}}Model();
        $params = $this->request->getParams();

        $validator = Validator::make($params, [
{{ValidatorRules}}
        ], [
{{ValidatorLabels}}
        ]);

        $passes    = $validator->passes();
        $principal = (string) $validator->value('{{CampoPrincipal}}', '');

        if (!$passes) {
            $this->renderAdminModule('{{recurso}}/agregar', [
                'title'  => 'Nuevo {{TituloSingular}}',
                'user'   => Auth::user(),
                'error'  => $validator->first(),
                'errors' => $validator->errors(),
                'form'   => $params,
            ]);
            return;
        }

        if ($model->existsByName($principal)) {
            $this->renderAdminModule('{{recurso}}/agregar', [
                'title' => 'Nuevo {{TituloSingular}}',
                'user'  => Auth::user(),
                'error' => 'Ya existe un registro con ese valor.',
                'form'  => $params,
            ]);
            return;
        }

        $model->createRecord($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        $this->flashSuccess('{{TituloSingular}} creado correctamente.');
        $this->redirect('/{{recursos}}');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();

        $item = (new {{Modulo}}Model())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/{{recursos}}');
            return;
        }

        $this->renderAdminModule('{{recurso}}/editar', [
            'title'  => 'Editar {{TituloSingular}}',
            'user'   => Auth::user(),
            'error'  => null,
            'form'   => $item,
            'itemId' => $id,
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model  = new {{Modulo}}Model();
        $params = $this->request->getParams();

        $validator = Validator::make($params, [
{{ValidatorRules}}
        ], [
{{ValidatorLabels}}
        ]);

        $passes    = $validator->passes();
        $principal = (string) $validator->value('{{CampoPrincipal}}', '');

        if (!$passes) {
            $this->renderAdminModule('{{recurso}}/editar', [
                'title'  => 'Editar {{TituloSingular}}',
                'user'   => Auth::user(),
                'error'  => $validator->first(),
                'errors' => $validator->errors(),
                'form'   => $params,
                'itemId' => $id,
            ]);
            return;
        }

        if ($model->existsByName($principal, $id)) {
            $this->renderAdminModule('{{recurso}}/editar', [
                'title'  => 'Editar {{TituloSingular}}',
                'user'   => Auth::user(),
                'error'  => 'Ya existe un registro con ese valor.',
                'form'   => $params,
                'itemId' => $id,
            ]);
            return;
        }

        $model->updateRecord($id, $params);
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        $this->flashSuccess('{{TituloSingular}} actualizado correctamente.');
        $this->redirect('/{{recursos}}');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new {{Modulo}}Model();
        $item  = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/{{recursos}}');
            return;
        }

        $this->renderAdminModule('{{recurso}}/eliminar', [
            'title'  => 'Eliminar {{TituloSingular}}',
            'user'   => Auth::user(),
            'form'   => $item,
            'itemId' => $id,
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new {{Modulo}}Model();
        $item  = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/{{recursos}}');
            return;
        }

        $model->deleteRecord($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        $this->flashSuccess('{{TituloSingular}} eliminado correctamente.');
        $this->redirect('/{{recursos}}');
    }
}
PHP;

        return $this->replace($tpl, $v);
    }

    private function buildControllerReadonly(): string
    {
        $v = $this->vars();

        $tpl = <<<'PHP'
<?php

namespace App\Controllers;

use App\Models\{{Modulo}}Model;
use Core\Auth;
use Core\Controller;
use Throwable;

class {{Modulo}}Controller extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $items   = [];
        $page    = $this->getCurrentPage();
        $perPage = $this->getDefaultPerPage();
        $filters = $this->getQueryParams(['q' => '']);
        $search  = $filters['q'];
        $pagination = $this->buildPagination(0, $page, $perPage, '/{{recursos}}', ['q' => $search]);

        try {
            $model      = new {{Modulo}}Model();
            $totalRows  = $model->countAll($search);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/{{recursos}}', ['q' => $search]);
            $items      = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search);
        } catch (Throwable) {
            $items = [];
        }

        $this->renderAdminModule('{{recurso}}/index', [
            'title'      => '{{TituloPlural}}',
            'user'       => Auth::user(),
            'items'      => $items,
            'pagination' => $pagination,
            'search'     => $search,
            'searchConfig' => [
                'action'      => '/{{recursos}}',
                'method'      => 'GET',
                'fields'      => [[
                    'name'        => 'q',
                    'type'        => 'text',
                    'placeholder' => 'Buscar por {{LabelPrincipal}}...',
                    'value'       => $search,
                    'icon'        => 'fa fa-search',
                ]],
                'submitLabel' => 'Buscar',
                'submitIcon'  => 'fa fa-search',
                'clearUrl'    => $search !== '' ? '/{{recursos}}' : '',
            ],
        ]);
    }

    public function ver(string $id): void
    {
        $this->requireAuth();

        $item = null;
        try {
            $item = (new {{Modulo}}Model())->findById($id);
        } catch (Throwable) {
            $item = null;
        }

        if (!is_array($item)) {
            $this->redirect('/{{recursos}}');
            return;
        }

        $this->renderAdminModule('{{recurso}}/ver', [
            'title'  => 'Detalle {{TituloSingular}}',
            'user'   => Auth::user(),
            'item'   => $item,
            'itemId' => $id,
        ]);
    }
}
PHP;

        return $this->replace($tpl, $v);
    }

    // =========================================================================
    // Builders — Model
    // =========================================================================

    private function buildModel(): string
    {
        $v = $this->vars();

        $softProp  = $this->softDeletes
            ? "\n    protected bool \$softDeletes = true;\n"
            : '';
        $notDeleted = $this->softDeletes
            ? "\n                AND ({$this->table}Table}.deleted_at IS NULL)"
            : '';

        $v['{{SoftDeletesProp}}']   = $softProp;
        $v['{{NotDeletedClause}}']  = $this->softDeletes
            ? "\n                AND {$this->prefix}_deleted_at IS NULL"
            : '';

        $tpl = <<<'PHP'
<?php

namespace App\Models;

use Core\Model;

class {{Modulo}}Model extends Model
{
    private string ${{recurso}}Table;
{{SoftDeletesProp}}
    public function __construct()
    {
        parent::__construct();
        $this->{{recurso}}Table = $this->tableName('{{tabla}}');
        $this->setTable('{{tabla}}');
        $this->setPrimaryKey('{{pre}}_id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT {{SelectColumns}}
                FROM {$this->{{recurso}}Table}
                {$where}
                ORDER BY {{pre}}_id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit',  max(1, $limit),  \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT COUNT(*) AS total FROM {$this->{{recurso}}Table} {$where}";
        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT {{SelectColumns}}
                FROM {$this->{{recurso}}Table}
                WHERE {{pre}}_id = :id{{NotDeletedClause}}
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    public function createRecord(array $data): string
    {
        return $this->create([
{{InsertData}}
        ]);
    }

    public function updateRecord(string $id, array $data): bool
    {
        return $this->update($id, [
{{InsertData}}
        ]);
    }

    public function deleteRecord(string $id): bool
    {
        return $this->delete($id);
    }

    public function existsByName(string $value, ?string $excludeId = null): bool
    {
        return $this->existsIn(
            $this->{{recurso}}Table,
            '{{CampoPrincipal}}',
            trim($value),
            '{{pre}}_id',
            $excludeId
        );
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search): array
    {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $conditions[] = '{{CampoPrincipal}} LIKE :search';
            $params['search'] = '%' . $search . '%';
        }
{{NotDeletedWhereBlock}}
        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }
}
PHP;

        // Bloque de soft-deletes en buildWhere
        $v['{{NotDeletedWhereBlock}}'] = $this->softDeletes
            ? "\n        \$conditions[] = '{$this->prefix}_deleted_at IS NULL';\n"
            : '';

        return $this->replace($tpl, $v);
    }

    // =========================================================================
    // Builders — Views
    // =========================================================================

    private function buildViewIndex(): string
    {
        $v = $this->vars();

        $canCheck = $this->readonly
            ? <<<TPL
        \$canVer = false;
        if (\$groupId !== '') {
            try {
                \$permission = new \Core\Permission();
                \$canVer = \$permission->canAccessRoute(\$groupId, '/{{recursos}}/1/ver', 'ver') === true;
            } catch (\Throwable) {}
        }
TPL
            : <<<TPL
        \$canAgregar  = false;
        \$canEditar   = false;
        \$canEliminar = false;
        if (\$groupId !== '') {
            try {
                \$permission  = new \Core\Permission();
                \$canAgregar  = \$permission->canAccessRoute(\$groupId, '/{{recursos}}/agregar', 'agregar')   === true;
                \$canEditar   = \$permission->canAccessRoute(\$groupId, '/{{recursos}}/1/editar', 'editar')   === true;
                \$canEliminar = \$permission->canAccessRoute(\$groupId, '/{{recursos}}/1/eliminar', 'eliminar') === true;
            } catch (\Throwable) {}
        }
TPL;

        $addButton = $this->readonly ? '' : <<<'TPL'

                    <?php if ($canAgregar): ?>
                        <a href="/{{recursos}}/agregar" class="btn btn-primary btn-sm">
                            <span>Agregar</span>
                        </a>
                    <?php endif; ?>
TPL;

        $actions = $this->readonly
            ? <<<'TPL'
                                        <td class="text-center">
                                            <?php if ($canVer): ?>
                                                <a href="/{{recursos}}/<?= $itemId ?>/ver"
                                                   class="btn btn-info px-2 py-1" title="Ver detalle">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
TPL
            : <<<'TPL'
                                        <td class="text-center">
                                            <?php if ($canEditar): ?>
                                                <a href="/{{recursos}}/<?= $itemId ?>/editar"
                                                   class="btn btn-warning px-2 py-1" title="Editar">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEliminar): ?>
                                                <a href="/{{recursos}}/<?= $itemId ?>/eliminar"
                                                   class="btn btn-danger px-2 py-1" title="Eliminar">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!$canEditar && !$canEliminar): ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
TPL;

        $v['{{CanCheck}}']   = $canCheck;
        $v['{{AddButton}}']  = $addButton;
        $v['{{RowActions}}'] = $actions;

        $tpl = <<<'TPL'
<div class="container-fluid">
    <?php
    $groupId = trim((string) (($user['group'] ?? '')));
{{CanCheck}}
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>{{TituloPlural}}</h5>
                        <span>Listado de {{tituloPlural}}</span>
                    </div>
{{AddButton}}
                </div>
                <div class="card-body pt-3">
                    <?php
                    $searchView = dirname(__DIR__) . '/components/search-form.php';
                    if (is_file($searchView)) { include $searchView; }
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) { include $flashView; }
                    ?>
                    <?php if (!empty($pagination['totalRows'])): ?>
                        <p class="text-muted small mb-2">
                            Mostrando <?= (int) ($pagination['from'] ?? 0) ?>–<?= (int) ($pagination['to'] ?? 0) ?>
                            de <?= (int) ($pagination['totalRows'] ?? 0) ?> registros
                        </p>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
{{TableHeaders}}
                                    <th class="text-center" style="width:100px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="{{ColSpan}}" class="text-center text-muted py-4">
                                        <?= !empty($search)
                                            ? 'No se encontraron registros para la búsqueda.'
                                            : 'No hay registros.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item):
                                    $itemId = urlencode((string) ($item['{{pre}}_id'] ?? ''));
                                ?>
                                    <tr>
                                        <td class="text-muted small"><?= htmlspecialchars((string) ($item['{{pre}}_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
{{TableCells}}
{{RowActions}}
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    $paginationAriaLabel = 'Paginacion {{recursos}}';
                    $paginationClass     = 'pagination justify-content-center pagination-primary pagination-sm';
                    $paginationView      = dirname(__DIR__) . '/components/pagination.php';
                    if (is_file($paginationView)) { include $paginationView; }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
TPL;

        $v['{{ColSpan}}'] = (string) (count($this->fields) + 2); // # + fields + acciones

        return $this->replace($tpl, $v);
    }

    private function buildViewAgregar(): string
    {
        $v = $this->vars();

        $tpl = <<<'TPL'
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nuevo {{TituloSingular}}</h5><span>Agregar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) { include $errorView; }
                    ?>
                    <form method="post" action="/{{recursos}}/guardar">
                        <?= $csrfField ?>
{{FormInputs}}
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="/{{recursos}}" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
TPL;

        return $this->replace($tpl, $v);
    }

    private function buildViewEditar(): string
    {
        $v = $this->vars();

        $tpl = <<<'TPL'
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Editar {{TituloSingular}}</h5><span>Actualizar registro existente</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) { include $errorView; }
                    ?>
                    <form method="post" action="/{{recursos}}/<?= urlencode((string) ($itemId ?? '')) ?>/actualizar">
                        <?= $csrfField ?>
{{FormInputs}}
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="/{{recursos}}" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
TPL;

        return $this->replace($tpl, $v);
    }

    private function buildViewEliminar(): string
    {
        $v = $this->vars();

        $tpl = <<<'TPL'
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Eliminar {{TituloSingular}}</h5><span>Confirmacion de eliminacion</span>
                </div>
                <div class="card-body">
                    <?php
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) { include $flashView; }
                    ?>
                    <p>¿Está seguro que desea eliminar
                        <strong><?= htmlspecialchars((string) ($form['{{CampoPrincipal}}'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>?
                    </p>
                    <div class="row justify-content-start mb-3">
                        <div class="col-sm-7">
                            <div class="alert alert-warning" role="alert">
                                <i class="fa fa-exclamation-triangle me-1" aria-hidden="true"></i>
                                Esta acción no se puede deshacer.
                            </div>
                        </div>
                    </div>
                    <form method="post" action="/{{recursos}}/<?= urlencode((string) ($itemId ?? '')) ?>/borrar">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                        <a href="/{{recursos}}" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
TPL;

        return $this->replace($tpl, $v);
    }

    private function buildViewVer(): string
    {
        $v = $this->vars();

        $tpl = <<<'TPL'
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-xl-9">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Detalle {{TituloSingular}}</h5>
                        <span>Registro #<?= htmlspecialchars((string) ($item['{{pre}}_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <a href="/{{recursos}}" class="btn btn-light btn-sm">
                        <i class="fa fa-arrow-left me-1" aria-hidden="true"></i>
                        Volver
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) { include $flashView; }
                    ?>
                    <dl class="row mb-0">

                        <dt class="col-sm-4 text-muted fw-normal">ID</dt>
                        <dd class="col-sm-8 font-monospace">
                            <?= htmlspecialchars((string) ($item['{{pre}}_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </dd>

{{VerFields}}
                    </dl>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="/{{recursos}}" class="btn btn-light btn-sm">
                        <i class="fa fa-arrow-left me-1" aria-hidden="true"></i>
                        Volver al listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
TPL;

        $v['{{VerFields}}'] = $this->buildVerFields();

        return $this->replace($tpl, $v);
    }

    // =========================================================================
    // Builders — Migration
    // =========================================================================

    private function buildMigrationUp(): string
    {
        $v = $this->vars();

        $tpl = <<<'SQL'
CREATE TABLE IF NOT EXISTS `{{tabla}}` (
    `{{pre}}_id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
{{SqlColumns}}
    `{{pre}}_created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `{{pre}}_updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
{{SoftDeleteColumn}}
    PRIMARY KEY (`{{pre}}_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $v['{{SoftDeleteColumn}}'] = $this->softDeletes
            ? "    `{$this->prefix}_deleted_at` DATETIME NULL DEFAULT NULL,\n"
            : '';

        return $this->replace($tpl, $v);
    }

    private function buildMigrationDown(): string
    {
        $v = $this->vars();
        return $this->replace("DROP TABLE IF EXISTS `{{tabla}}`;\n", $v);
    }

    // =========================================================================
    // Field helpers — code generation
    // =========================================================================

    /**
     * Parsea "pro_nombre:string:250,pro_estado:char:1" en array de campos.
     *
     * @return list<array{col:string,type:string,size:string,label:string}>
     */
    private function parseFields(string $raw): array
    {
        $fields = [];
        foreach (explode(',', $raw) as $entry) {
            $parts = array_map('trim', explode(':', $entry));
            $col   = $parts[0] ?? '';
            if ($col === '') {
                continue;
            }
            $type = $parts[1] ?? 'string';
            // Para decimal: "10:2" → size="10:2"
            $size = isset($parts[3]) ? $parts[2] . ':' . $parts[3] : ($parts[2] ?? '250');

            $fields[] = [
                'col'   => $col,
                'type'  => $type,
                'size'  => $size,
                'label' => $this->inferLabel($col),
            ];
        }
        return $fields;
    }

    private function inferLabel(string $col): string
    {
        // Strip prefix (pro_nombre → nombre)
        $parts = explode('_', $col, 2);
        $name  = count($parts) > 1 ? $parts[1] : $col;
        return ucwords(str_replace('_', ' ', $name));
    }

    private function buildValidatorRules(): string
    {
        $lines = [];
        foreach ($this->fields as $f) {
            $rule = self::RULE_MAP[$f['type']] ?? 'required|string|max:250';
            // Sustituir {{sz}} con el tamaño del campo
            $rule = str_replace('{{sz}}', $f['size'], $rule);
            $lines[] = "            '{$f['col']}' => '{$rule}',";
        }
        return implode("\n", $lines);
    }

    private function buildValidatorLabels(): string
    {
        $lines = [];
        foreach ($this->fields as $f) {
            $lines[] = "            '{$f['col']}' => '{$f['label']}',";
        }
        return implode("\n", $lines);
    }

    private function buildFormDefaults(): string
    {
        $lines = [];
        foreach ($this->fields as $f) {
            $default = self::SQL_MAP[$f['type']]['default'] ?? "''";
            $lines[] = "                '{$f['col']}' => {$default},";
        }
        return implode("\n", $lines);
    }

    private function buildInsertData(): string
    {
        $lines = [];
        foreach ($this->fields as $f) {
            $cast = match ($f['type']) {
                'int'           => "(int) (\$data['{$f['col']}'] ?? 0)",
                'decimal'       => "(string) (\$data['{$f['col']}'] ?? '0')",
                'bool'          => "(int) (\$data['{$f['col']}'] ?? 0)",
                'text', 'date',
                'datetime'      => "(\$data['{$f['col']}'] ?? '') !== '' ? (string) \$data['{$f['col']}'] : null",
                default         => "(string) (\$data['{$f['col']}'] ?? '')",
            };
            $lines[] = "            '{$f['col']}' => {$cast},";
        }
        return implode("\n", $lines);
    }

    private function buildSelectColumns(): string
    {
        $pre  = $this->prefix;
        $cols = ["{$pre}_id"];
        foreach ($this->fields as $f) {
            $cols[] = $f['col'];
        }
        return implode(', ', $cols);
    }

    private function buildTableHeaders(): string
    {
        $lines = [];
        foreach ($this->fields as $f) {
            $lines[] = "                                    <th>{$f['label']}</th>";
        }
        return implode("\n", $lines);
    }

    private function buildTableCells(): string
    {
        $lines = [];
        foreach ($this->fields as $f) {
            $lines[] = "                                        <td><?= htmlspecialchars((string) (\$item['{$f['col']}'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>";
        }
        return implode("\n", $lines);
    }

    private function buildFormInputs(): string
    {
        $lines = [];
        foreach ($this->fields as $f) {
            $inputType = self::INPUT_MAP[$f['type']] ?? 'text';

            if ($inputType === 'textarea') {
                $lines[] = <<<TPL
                        <div class="mb-3">
                            <label for="{$f['col']}" class="form-label">{$f['label']}</label>
                            <textarea id="{$f['col']}" class="form-control" name="{$f['col']}" rows="4"><?= htmlspecialchars((string) (\$form['{$f['col']}'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
TPL;
            } elseif ($inputType === 'checkbox') {
                $lines[] = <<<TPL
                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" id="{$f['col']}" name="{$f['col']}" value="1"
                                   <?= !empty(\$form['{$f['col']}']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="{$f['col']}">{$f['label']}</label>
                        </div>
TPL;
            } else {
                $maxAttr = in_array($f['type'], ['string', 'char'], true)
                    ? "\n                                   maxlength=\"{$f['size']}\""
                    : '';
                $lines[] = <<<TPL
                        <div class="mb-3">
                            <label for="{$f['col']}" class="form-label">{$f['label']}</label>
                            <input id="{$f['col']}" class="form-control" type="{$inputType}" name="{$f['col']}"
                                   value="<?= htmlspecialchars((string) (\$form['{$f['col']}'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"$maxAttr>
                        </div>
TPL;
            }
        }
        return implode("\n", $lines);
    }

    private function buildVerFields(): string
    {
        $lines = [];
        foreach ($this->fields as $f) {
            $lines[] = <<<TPL

                        <dt class="col-sm-4 text-muted fw-normal">{$f['label']}</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars(trim((string) (\$item['{$f['col']}'] ?? '')) ?: '—', ENT_QUOTES, 'UTF-8') ?>
                        </dd>
TPL;
        }
        return implode("\n", $lines);
    }

    private function buildSqlColumns(): string
    {
        $lines = [];
        foreach ($this->fields as $f) {
            $sqlType = $this->fieldToSql($f);
            $padded  = str_pad("`{$f['col']}`", 30);
            $lines[] = "    {$padded} {$sqlType},";
        }
        return implode("\n", $lines);
    }

    private function fieldToSql(array $field): string
    {
        $map = self::SQL_MAP[$field['type']] ?? self::SQL_MAP['string'];
        $tpl = $map['tpl'];

        if ($field['type'] === 'decimal') {
            $parts = explode(':', $field['size']);
            $tpl = str_replace(['{{p}}', '{{s}}'], [$parts[0] ?? '10', $parts[1] ?? '2'], $tpl);
        } else {
            $tpl = str_replace('{{sz}}', $field['size'], $tpl);
        }

        return $tpl;
    }

    // =========================================================================
    // Migration number auto-detection
    // =========================================================================

    private function nextMigrationNumber(): string
    {
        $dir   = ROOT . '/core/database/migrations';
        $last  = 0;

        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if (preg_match('/^(\d{4})_/', $file, $m)) {
                    $last = max($last, (int) $m[1]);
                }
            }
        }

        return str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // Template engine
    // =========================================================================

    /** @return array<string,string> */
    private function vars(): array
    {
        return [
            '{{Modulo}}'          => $this->name,
            '{{recurso}}'         => strtolower($this->name),
            '{{recursos}}'        => $this->route,
            '{{pre}}'             => $this->prefix,
            '{{tabla}}'           => $this->table,
            '{{TituloSingular}}'  => $this->name,
            '{{TituloPlural}}'    => $this->name . 's',
            '{{tituloPlural}}'    => strtolower($this->name) . 's',
            '{{MigNum}}'          => $this->migNum,
            '{{CampoPrincipal}}'  => $this->fields[0]['col'] ?? ($this->prefix . '_nombre'),
            '{{LabelPrincipal}}'  => $this->fields[0]['label'] ?? 'Nombre',
            '{{ValidatorRules}}'  => $this->buildValidatorRules(),
            '{{ValidatorLabels}}' => $this->buildValidatorLabels(),
            '{{FormDefaults}}'    => $this->buildFormDefaults(),
            '{{InsertData}}'      => $this->buildInsertData(),
            '{{SelectColumns}}'   => $this->buildSelectColumns(),
            '{{TableHeaders}}'    => $this->buildTableHeaders(),
            '{{TableCells}}'      => $this->buildTableCells(),
            '{{FormInputs}}'      => $this->buildFormInputs(),
            '{{SqlColumns}}'      => $this->buildSqlColumns(),
        ];
    }

    /** @param array<string,string> $vars */
    private function replace(string $tpl, array $vars): string
    {
        return str_replace(array_keys($vars), array_values($vars), $tpl);
    }

    // =========================================================================
    // CLI output helpers
    // =========================================================================

    private function banner(): void
    {
        echo self::CYAN . self::BOLD;
        echo "╔══════════════════════════════════════════════════════╗\n";
        echo "║         panelAdmin — Module Generator                ║\n";
        echo "║         php bin/make-module.php --help               ║\n";
        echo "╚══════════════════════════════════════════════════════╝\n";
        echo self::RESET . "\n";
    }

    private function line(string $msg, string $color = ''): void
    {
        $map = [
            'green'  => self::GREEN,
            'red'    => self::RED,
            'yellow' => self::YELLOW,
            'cyan'   => self::CYAN,
            'bold'   => self::BOLD,
        ];

        echo ($map[$color] ?? '') . $msg . self::RESET . "\n";
    }

    private function ask(string $question, string $default = '', bool $required = false): string
    {
        $hint = $default !== '' ? " [{$default}]" : ($required ? ' (requerido)' : '');
        echo self::CYAN . "  ? {$question}{$hint}: " . self::RESET;

        $value = trim((string) fgets(STDIN));
        if ($value === '') {
            if ($required) {
                $this->line("  Este campo es obligatorio.", 'red');
                return $this->ask($question, $default, $required);
            }
            return $default;
        }
        return $value;
    }

    private function askBool(string $question, bool $default): bool
    {
        $hint = $default ? '[S/n]' : '[s/N]';
        echo self::CYAN . "  ? {$question} {$hint}: " . self::RESET;

        $value = strtolower(trim((string) fgets(STDIN)));
        if ($value === '') {
            return $default;
        }
        return in_array($value, ['s', 'si', 'sí', 'y', 'yes', '1'], true);
    }
}

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------

$gen = new ModuleGenerator();
$gen->run($argv);
