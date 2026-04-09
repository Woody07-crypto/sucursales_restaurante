# Matriz de pruebas — API REST Sucursales Restaurante

| Campo | Valor |
|--------|--------|
| **Proyecto** | API REST — gestión de sucursales, catálogo y pedidos |
| **Alcance base** | `/api/v1/*` (prefijo `api` de Laravel) |
| **Versión documento** | 1.0 |
| **Fecha** | abril 2026 |
| **Rama sugerida QA** | `qa/matriz-pruebas` |
| **Especificación OpenAPI** | `public/openapi.yaml` |
| **Rutas** | `routes/api.php` |
| **Automatización** | Pest 4 (`composer test`) |

**Integrantes (completar)**  

| Nombre | Carné | Rol en pruebas |
|--------|-------|----------------|
| _Pendiente_ | | |
| _Pendiente_ | | |

---

## 1. Objetivo y alcance

Este documento define la **matriz de pruebas** para validar el comportamiento de la API según los flujos:

- `flow/sucursales` — CRUD de sucursales, KPIs, auditoría, reglas por rol.
- `flow/catalogo` — health y menú de productos activos.
- `flow/pedidos` — creación de pedidos.
- **Regresión** — coherencia OpenAPI ↔ rutas y disponibilidad de la documentación interactiva.

Incluye trazabilidad a **pruebas automatizadas** (prefijo `MAT-<ID>`) y deja columnas para **prueba manual** (resultado, fecha, observaciones).

---

## 2. Convención de la matriz

| Columna / concepto | Uso |
|--------------------|-----|
| **ID** | Identificador estable: `SUC-xxx`, `CAT-xxx`, `PED-xxx`, `X-xxx`. |
| **Flujo / rama** | `flow/sucursales`, `flow/catalogo`, `flow/pedidos`, o `regresión`. |
| **Documentación** | Debe existir un path equivalente en `public/openapi.yaml` (salvo nota explícita). |
| **Automatización** | Nombre del test en Pest: `test('MAT-<ID> …')`. |
| **Auth** | `No` = sin token; `Bearer` = header `Authorization: Bearer <token>` (Laravel Sanctum). |

### 2.1 Roles usados en pruebas

| Rol (`users.role`) | Descripción breve |
|--------------------|-------------------|
| `gerente_global` | Lista todas las sucursales; crea sucursales; sin `sucursal_id` obligatorio. |
| `gerente_sucursal` | Alcance a su `sucursal_id`; no crea sucursales nuevas; no modifica/elimina otra sucursal (404/403 según caso). |
| `cajero` | Sin acceso a listado/detalle de sucursales; puede menú/pedidos según reglas implementadas. |

### 2.2 Estados de pedido relevantes (sucursales)

Para **borrado de sucursal**, un pedido se considera **activo** si su `estado` **no** es `entregado` ni `cancelado`.

---

## 3. Flujo sucursales (`flow/sucursales`)

**Base path:** `/api/v1/sucursales`

| ID | Caso | Método | Ruta | Auth | Precondiciones | Entrada / acción | Resultado esperado | Automatización (archivo · test) |
|----|------|--------|------|------|----------------|------------------|--------------------|----------------------------------|
| SUC-001 | Health del flujo | GET | `/api/v1/sucursales/health` | No | — | — | `200`, JSON con `flow=sucursales` | `MatrizSucursalesTest.php` · `MAT-SUC-001` |
| SUC-002 | Listar todas las sucursales (GG) | GET | `/api/v1/sucursales` | Bearer GG | ≥2 sucursales en BD | — | `200`, arreglo con todas | `MatrizSucursalesTest.php` · `MAT-SUC-002` |
| SUC-003 | Listar solo sucursal asignada (GS) | GET | `/api/v1/sucursales` | Bearer GS | 2+ sucursales; GS ligado a una | — | `200`, un solo elemento, `id` = sucursal del usuario | `MatrizSucursalesTest.php` · `MAT-SUC-003` |
| SUC-004 | Detalle con KPIs | GET | `/api/v1/sucursales/{id}` | Bearer GG o GS propia | Sucursal con productos y pedidos | `id` válido | `200`, cuerpo incluye `kpis` | `MatrizSucursalesTest.php` · `MAT-SUC-004` |
| SUC-005 | Sin autenticación en listado | GET | `/api/v1/sucursales` | No | — | — | `401` | `MatrizSucursalesTest.php` · `MAT-SUC-005` |
| SUC-006 | Detalle inexistente | GET | `/api/v1/sucursales/{id}` | Bearer GG | — | `id` inexistente | `404` | `MatrizSucursalesTest.php` · `MAT-SUC-006` |
| SUC-007 | Crear sucursal | POST | `/api/v1/sucursales` | Bearer GG | Nombre único | JSON válido | `201` | `MatrizSucursalesTest.php` · `MAT-SUC-007` |
| SUC-008 | Validación al crear | POST | `/api/v1/sucursales` | Bearer GG | — | JSON incompleto | `422` | `MatrizSucursalesTest.php` · `MAT-SUC-008` |
| SUC-009 | Conflicto por nombre duplicado | POST | `/api/v1/sucursales` | Bearer GG | Nombre existente | Mismo `nombre` | `409` | `MatrizSucursalesTest.php` · `MAT-SUC-009` |
| SUC-010 | Crear sucursal prohibido para GS | POST | `/api/v1/sucursales` | Bearer GS | — | Cuerpo válido | `403` | `MatrizSucursalesTest.php` · `MAT-SUC-010` |
| SUC-011 | Actualizar sucursal | PUT | `/api/v1/sucursales/{id}` | Bearer GS propia o GG | Sucursal existente | Campos parciales | `200` | `MatrizSucursalesTest.php` · `MAT-SUC-011` |
| SUC-012 | Actualizar sucursal ajena (GS) | PUT | `/api/v1/sucursales/{id}` | Bearer GS | GS en A; `id` = B | Cambios | `404` | `MatrizSucursalesTest.php` · `MAT-SUC-012` |
| SUC-013 | Auditoría en actualización | PUT | `/api/v1/sucursales/{id}` | Bearer GG | — | Actualización válida | `200` y `audit_logs.action=sucursal.updated` | `MatrizSucursalesTest.php` · `MAT-SUC-013` |
| SUC-014 | Eliminar con pedidos activos | DELETE | `/api/v1/sucursales/{id}` | Bearer GG | Pedido activo en sucursal | `id` | `409` | `MatrizSucursalesTest.php` · `MAT-SUC-014` |
| SUC-015 | Soft delete + auditoría | DELETE | `/api/v1/sucursales/{id}` | Bearer GG | Sin pedidos activos | `id` válido | `200`, soft delete, `sucursal.soft_deleted` | `MatrizSucursalesTest.php` · `MAT-SUC-015` |
| SUC-016 | Eliminar sucursal ajena (GS) | DELETE | `/api/v1/sucursales/{id}` | Bearer GS | GS en A; `id` = B | — | `403` | `MatrizSucursalesTest.php` · `MAT-SUC-016` |

### 3.1 Matriz manual (sucursales)

| ID | Ejecutor | Fecha | Resultado | Observaciones |
|----|----------|-------|-----------|---------------|
| SUC-001 … SUC-016 | | | | |

---

## 4. Flujo catálogo (`flow/catalogo`)

| ID | Caso | Método | Ruta | Auth | Resultado esperado | Automatización |
|----|------|--------|------|------|--------------------|----------------|
| CAT-001 | Health | GET | `/api/v1/catalogo/health` | No | `200`, `flow=catalogo` | `MatrizCatalogoTest.php` · `MAT-CAT-001` |
| CAT-002 | Menú activos | GET | `/api/v1/catalogo/menu` | Bearer | GG ve todos los activos; GS solo su sucursal | `MatrizCatalogoTest.php` · `MAT-CAT-002` |

---

## 5. Flujo pedidos (`flow/pedidos`)

| ID | Caso | Método | Ruta | Auth | Resultado esperado | Automatización |
|----|------|--------|------|------|--------------------|----------------|
| PED-001 | Health | GET | `/api/v1/pedidos/health` | No | `200`, `flow=pedidos` | `MatrizPedidosTest.php` · `MAT-PED-001` |
| PED-002 | Crear pedido | POST | `/api/v1/pedidos` | Bearer GG | `201`, fila en `pedidos` | `MatrizPedidosTest.php` · `MAT-PED-002` |

**Nota:** `items` se persiste en JSON en la implementación actual.

---

## 6. Regresión

| ID | Caso | Automatización |
|----|------|----------------|
| X-001 | OpenAPI vs rutas Laravel | `MatrizRegresionTest.php` · `MAT-X-001` |
| X-002 | `/docs/api` y referencia a OpenAPI | `MatrizRegresionTest.php` · `MAT-X-002` |

---

## 7. Inventario `/api/v1`

| Método | Ruta | Auth |
|--------|------|------|
| POST | `/auth/login` | No |
| POST | `/auth/logout` | Bearer |
| GET | `/sucursales/health` | No |
| GET/POST | `/sucursales` | Bearer (POST GG) |
| GET/PUT/DELETE | `/sucursales/{id}` | Bearer |
| GET | `/catalogo/health` | No |
| GET | `/catalogo/menu` | Bearer |
| GET | `/pedidos/health` | No |
| POST | `/pedidos` | Bearer |

---

## 8. Ejecución

```bash
composer test
```

---

## 9. Cobertura automatizada

| Área | Casos | Tests |
|------|-------|-------|
| Sucursales | SUC-001 … SUC-016 | 16 |
| Catálogo | CAT-001 … CAT-002 | 2 |
| Pedidos | PED-001 … PED-002 | 2 |
| Regresión | X-001 … X-002 | 2 |
| **Total** | **22** | **22** |

---

## 10. Control de versiones del documento

| Versión | Fecha | Cambios |
|---------|-------|---------|
| 1.0 | 2026-04 | Matriz completa alineada a API v1 y tests `MAT-*` |
