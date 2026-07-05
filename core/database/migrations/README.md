# Migraciones activas

La carpeta `core/database/migrations/` contiene el baseline activo de estructura para las tablas core del sistema.

## Convención actual

- Solo las 15 tablas core usan prefijo fijo `wr_`.
- Los módulos de negocio existentes y nuevos crean tablas sin prefijo.
- Este baseline define estructura.
- Si quieres conservar los datos actuales del sistema, importa un export aparte después de crear la estructura.

## Legacy

Las migraciones históricas anteriores al baseline ordenado se archivan en `core/database/migrations/legacy/`.
