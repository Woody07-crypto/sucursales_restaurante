## API REST — Sucursales Restaurante

Backend en **Laravel** para el proyecto de cátedra “Restaurante”. Incluye:

- **Autenticación** con **Laravel Sanctum** (Bearer token)
- **Sucursales** (CRUD + KPIs + auditoría)
- **Catálogo** (menú y CRUD de productos)
- **Pedidos** (CRUD parcial + cambio de estado)
- **Documentación interactiva** con **Swagger UI**
- **Suite de pruebas** con **Pest**

<<<<<<< HEAD
### Levantar el servidor local (Windows)

1. Prueba lo habitual: `php artisan serve` → abre [http://127.0.0.1:8000](http://127.0.0.1:8000).
2. Si ves **Failed to listen** en todos los puertos, usa el servidor embebido de PHP (misma app, otro puerto):
   - `composer run serve-native` **o** doble clic en `serve-native.bat`
   - Abre [http://127.0.0.1:8765/docs/api](http://127.0.0.1:8765/docs/api) para Swagger.

### Probar endpoints para la documentación

- **Postman (o Insomnia):** sirve para demostrar en vivo y para pegar **ejemplos reales** de request/response en el informe.
- **Swagger/OpenAPI** (`public/openapi.yaml` y `/docs/api`): es lo que el proyecto pide como **documentación interactiva**; Postman **no lo reemplaza**, pero puedes usarlo junto con capturas o export de colección.

- **Documentación interactiva (Swagger UI):** [http://127.0.0.1:8765/docs/api](http://127.0.0.1:8765/docs/api) con `composer run serve-native`, o [http://127.0.0.1:8000/docs/api](http://127.0.0.1:8000/docs/api) si `php artisan serve` funciona.
- **Especificación OpenAPI:** `public/openapi.yaml`.
- **Matriz de pruebas:** [docs/MATRIZ_PRUEBAS.md](docs/MATRIZ_PRUEBAS.md).
=======
### Enlaces rápidos
>>>>>>> 2bbf1cf86a78e197d1b4092d398275c2cb99ee6c

- **Swagger UI**: `http://localhost:8000/docs/api`
- **OpenAPI**: `public/openapi.yaml`
- **Matriz de pruebas**: `docs/MATRIZ_PRUEBAS.md`

---

## Requisitos

- **PHP**: 8.3+
- **Composer**: 2.x
- **Base de datos**: MySQL 8 (recomendado) o compatible
- (Opcional) **Node.js** 18+ si quieres compilar assets; para solo API no es necesario

---

## Levantamiento (desarrollo local)

### 1) Clonar e instalar dependencias

```bash
composer install
```

### 2) Configurar variables de entorno

```bash
copy .env.example .env
php artisan key:generate
```

Edita `.env` (por defecto el repo trae un ejemplo MySQL):

- **DB_CONNECTION**: `mysql`
- **DB_HOST**: `127.0.0.1`
- **DB_PORT**: `3306`
- **DB_DATABASE**: `restaurante`
- **DB_USERNAME**: `root`
- **DB_PASSWORD**: `1234`

### 3) Migraciones y seed (datos base)

```bash
php artisan migrate
```

Este proyecto incluye seeders/factories para QA. Si quieres datos de ejemplo:

```bash
php artisan db:seed
```

### 4) Levantar servidor

```bash
php artisan serve
```

La API quedará disponible en:

- `http://localhost:8000/api/v1/...`

---

## Documentación Swagger (UI)

Con el servidor corriendo, abre:

- `http://localhost:8000/docs/api`

La especificación OpenAPI se sirve desde:

- `http://localhost:8000/openapi.yaml`

---

## Autenticación (Sanctum)

### Login

`POST /api/v1/auth/login`

Body ejemplo:

```json
{
  "email": "gerente@example.test",
  "password": "password"
}
```

Respuesta (estándar del proyecto):

```json
{
  "message": "Login exitoso",
  "data": {
    "token": "…",
    "token_type": "Bearer",
    "user": { "id": 1, "name": "…", "email": "…", "role": "gerente_global", "sucursal_id": null }
  }
}
```

En requests posteriores, enviar header:

- `Authorization: Bearer <token>`
- `Accept: application/json`

### Usuario autenticado

`GET /api/v1/auth/me`

### Logout

`POST /api/v1/auth/logout` (revoca el token actual)

---

## Endpoints principales (PDF final)

### Catálogo (CRUD)

- `GET /api/v1/catalogo/products`
- `POST /api/v1/catalogo/products`
- `PUT /api/v1/catalogo/products/{id}`
- `DELETE /api/v1/catalogo/products/{id}`

Además:

- `GET /api/v1/catalogo/menu` (solo productos activos)

### Sucursales

- `GET /api/v1/sucursales`
- `POST /api/v1/sucursales`
- `PUT /api/v1/sucursales/{id}`
- `DELETE /api/v1/sucursales/{id}`

Además health checks:

- `GET /api/v1/sucursales/health`
- `GET /api/v1/catalogo/health`
- `GET /api/v1/pedidos/health`

### Pedidos

- `GET /api/v1/pedidos`
- `POST /api/v1/pedidos`
- `GET /api/v1/pedidos/{pedido}`
- `PATCH /api/v1/pedidos/{pedido}/estado`
- `DELETE /api/v1/pedidos/{pedido}`

#### Crear pedido (nota)

El proyecto soporta **dos formatos** para `items[]`:

- **Por producto (recomendado / matriz de pruebas)**: `product_id` + `cantidad`
- **Por nombre/precio (según ejemplo del PDF)**: `nombre` + `precio_unitario` + `cantidad`

---

## Ejecutar pruebas

La suite usa **SQLite in-memory** en testing (ver `phpunit.xml`), no necesitas MySQL para correr tests.

```bash
composer test
```

O directamente:

```bash
php artisan test
```

---

## Scripts útiles

En `composer.json` existen scripts:

- `composer setup`: instala deps, crea `.env`, genera key y migra (incluye pasos npm si tienes Node)
- `composer dev`: levanta server + queue + vite (si aplica)
- `composer test`: corre tests

---

## Ramas (histórico del equipo)

| Rama | Propósito |
|------|-----------|
| `main` | Integración estable |
<<<<<<< HEAD
| `flow/sucursales` | Endpoints de sucursales (`/api/v1/sucursales/*`) |
| `flow/catalogo` | Endpoints de catálogo/menú (`/api/v1/catalogo/*`) |
| `flow/pedidos` | Pedidos + **Flujograma 3** (stock): `POST /api/v1/orders`, `GET /api/v1/inventory/branch/{sucursal}`, `GET /api/v1/analytics/stock-alerts`, compras |
| `qa/matriz-pruebas` | Evolución de la matriz y casos de QA |
| `docs/api-interactiva` | Cambios en OpenAPI y la vista `/docs/api` |
=======
| `qa/matriz-pruebas` | QA + alineación con matriz y PDF |
>>>>>>> 2bbf1cf86a78e197d1b4092d398275c2cb99ee6c

