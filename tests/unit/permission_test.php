<?php

declare(strict_types=1);

use Core\Permission;
function create_permission_schema(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE wr_elemento (ele_id INTEGER PRIMARY KEY, ele_nombre TEXT, ele_estado TEXT)');
    $pdo->exec('CREATE TABLE wr_grupo (gru_id TEXT PRIMARY KEY, gru_estado TEXT)');
    $pdo->exec('CREATE TABLE wr_tarea (tar_id INTEGER PRIMARY KEY, tar_nombre TEXT)');
    $pdo->exec('CREATE TABLE wr_permiso (pmo_ele_id INTEGER, pmo_tar_id INTEGER NULL, pmo_gru_id TEXT)');
    $pdo->exec('CREATE TABLE wr_parametro (par_id INTEGER PRIMARY KEY, par_clave TEXT UNIQUE, par_valor TEXT, par_tipo TEXT, par_grupo TEXT, par_label TEXT)');
}

test('Permission permite rutas internas de notificaciones', function (): void {
    with_sqlite_database(function (PDO $pdo): void {
        create_permission_schema($pdo);
        $permission = new Permission();

        assert_true($permission->canAccessRoute('ADMIN', '/notificaciones', 'index') === true);
    });
});

test('Permission resuelve ACCEDER en rutas index del modulo', function (): void {
    with_sqlite_database(function (PDO $pdo): void {
        create_permission_schema($pdo);
        $pdo->exec("INSERT INTO wr_elemento (ele_id, ele_nombre, ele_estado) VALUES (1, 'modulos', 'H')");
        $pdo->exec("INSERT INTO wr_grupo (gru_id, gru_estado) VALUES ('ADMIN', 'H')");
        $pdo->exec("INSERT INTO wr_tarea (tar_id, tar_nombre) VALUES (1, 'ACCEDER')");
        $pdo->exec("INSERT INTO wr_permiso (pmo_ele_id, pmo_tar_id, pmo_gru_id) VALUES (1, 1, 'ADMIN')");

        $permission = new Permission();

        assert_true($permission->canAccessRoute('ADMIN', '/modulos', 'index') === true);
        assert_null($permission->canAccessRoute('ADMIN', '/desconocido', 'index'));
    });
});

test('Permission bloquea editar sin tarea y permite al agregar permiso', function (): void {
    with_sqlite_database(function (PDO $pdo): void {
        create_permission_schema($pdo);
        $pdo->exec("INSERT INTO wr_elemento (ele_id, ele_nombre, ele_estado) VALUES (1, 'modulos', 'H')");
        $pdo->exec("INSERT INTO wr_grupo (gru_id, gru_estado) VALUES ('ADMIN', 'H')");
        $pdo->exec("INSERT INTO wr_tarea (tar_id, tar_nombre) VALUES (1, 'ACCEDER')");
        $pdo->exec("INSERT INTO wr_tarea (tar_id, tar_nombre) VALUES (2, 'EDITAR')");
        $pdo->exec("INSERT INTO wr_permiso (pmo_ele_id, pmo_tar_id, pmo_gru_id) VALUES (1, 1, 'ADMIN')");

        $permission = new Permission();
        assert_false($permission->canAccessRoute('ADMIN', '/modulos/5/editar', 'editar') === true);

        $pdo->exec("INSERT INTO wr_permiso (pmo_ele_id, pmo_tar_id, pmo_gru_id) VALUES (1, 2, 'ADMIN')");
        reset_permission_caches();

        $permission = new Permission();
        assert_true($permission->canAccessRoute('ADMIN', '/modulos/5/editar', 'editar') === true);
    });
});
