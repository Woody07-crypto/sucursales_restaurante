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
| PED-002 | Crear pedido válido | POST | `/api/v1/pedidos` | JSON con `canal`, `sucursal`, `items[]` | 201 con pedido creado | Calcula `total` automáticamente |
| PED-003 | Crear pedido inválido | POST | `/api/v1/pedidos` | Falta `items` o `canal` inválido | 422 validación | Rechaza payload incompleto |
| PED-004 | Listar pedidos | GET | `/api/v1/pedidos` | Query opcional `estado`, `canal`, `sucursal` | 200 lista paginada | Incluye filtros básicos |
| PED-005 | Ver detalle por ID | GET | `/api/v1/pedidos/{id}` | `id` existente/no existente | 200 / 404 | Cobertura de not found |
| PED-006 | Cambiar estado válido | PATCH | `/api/v1/pedidos/{id}/estado` | `{"estado":"en_preparacion"}` | 200 estado actualizado | Respeta flujo de transición |
| PED-007 | Cambiar estado inválido | PATCH | `/api/v1/pedidos/{id}/estado` | Transición no permitida | 422 | Ej: `cancelado -> listo` |
| PED-008 | Eliminar pedido entregado | DELETE | `/api/v1/pedidos/{id}` | Pedido con estado `entregado` | 409 | Regla de negocio |
| PED-009 | Eliminar pedido no entregado | DELETE | `/api/v1/pedidos/{id}` | Pedido estado `pendiente` | 204 | Eliminación exitosa |

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
