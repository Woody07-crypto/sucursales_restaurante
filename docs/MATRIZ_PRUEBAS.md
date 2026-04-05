# Matriz de pruebas — API Sucursales Restaurante

Documento vivo: actualizar en la rama `qa/matriz-pruebas` y fusionar a `main` cuando se acuerde con el equipo.

## Convención

| Columna        | Uso |
|----------------|-----|
| ID             | Identificador estable del caso (p. ej. `SUC-001`). |
| Flujo / rama   | `flow/sucursales`, `flow/catalogo`, `flow/pedidos`. |
| Documentación  | Debe existir path equivalente en `public/openapi.yaml`. |

## Flujo Sucursales (`flow/sucursales`)

| ID | Caso | Método | Ruta | Entrada | Esperado HTTP | Notas |
|----|------|--------|------|---------|---------------|-------|
| SUC-001 | Health | GET | `/api/v1/sucursales/health` | — | 200, JSON `flow=sucursales` | Placeholder inicial |
| SUC-002 | *Añadir: listar sucursales* | | | | | |
| SUC-003 | *Añadir: detalle sucursal* | | | | | |

## Flujo Catálogo (`flow/catalogo`)

| ID | Caso | Método | Ruta | Entrada | Esperado HTTP | Notas |
|----|------|--------|------|---------|---------------|-------|
| CAT-001 | Health | GET | `/api/v1/catalogo/health` | — | 200, JSON `flow=catalogo` | Placeholder inicial |
| CAT-002 | *Añadir: listado menú* | | | | | |

## Flujo Pedidos (`flow/pedidos`)

| ID | Caso | Método | Ruta | Entrada | Esperado HTTP | Notas |
|----|------|--------|------|---------|---------------|-------|
| PED-001 | Health | GET | `/api/v1/pedidos/health` | — | 200, JSON `flow=pedidos` | Placeholder inicial |
| PED-002 | *Añadir: crear pedido* | | | | | |

## Regresión cruzada

| ID | Caso | Cómo |
|----|------|------|
| X-001 | Documentación vs API | `public/openapi.yaml` coincide con rutas reales en `routes/api.php`. |
| X-002 | UI Swagger | `GET /docs/api` carga y “Try it out” responde contra el mismo origen. |

## Automatización (Pest)

Ejecutar antes de fusionar a `main`:

```bash
composer test
```

*Añadir tests en `tests/Feature/` por ID de caso cuando existan endpoints definitivos.*
