# BodyStop — Manual de uso y funcionalidades

E-commerce de bodys y prendas de control con roles, carrito con tallas/stock,
recuperación de contraseña y panel de gestión de pedidos.

## Requisitos

- Docker Desktop (con `docker compose`).

## Cómo levantar el proyecto

```bash
docker compose up -d --build
```

| Servicio | Acceso |
|---|---|
| Tienda web | http://localhost:8080 |
| MySQL | puerto `3307`, BD `bodystop`, usuario `root` / `root` |

> La base de datos se crea automáticamente desde `database.sql` la primera vez.
> Si cambias `database.sql`, reconstruye el volumen:
> `docker compose down` → `docker volume rm bodysstop_db_data` → `docker compose up -d --build`

## Credenciales de acceso

| Rol | Correo | Contraseña | Qué puede hacer |
|---|---|---|---|
| **administrador** | `admin@BodyStop.com` | `Admin123!` | Todo: usuarios, productos (incl. stock), pedidos (incl. cambiar estados), dashboard |
| **vendedor** | `vendedor@BodyStop.com` | `Vendedor123!` | Productos (stock solo lectura), pedidos y seguimiento (solo consulta), dashboard |
| **usuario** (cliente) | *(regístrate en la tienda)* | — | Comprar, ver perfil y sus pedidos con seguimiento |

> El registro público solo crea usuarios finales (`usuario`). Los vendedores los crea el administrador.

## Funcionalidades por área

### Público (cualquier visitante)

- **Catálogo** (`/?c=Producto&m=index`): buscador por nombre/descripción, filtro por
  categoría, contador de resultados y paginación.
- **Detalle de producto** (`/?c=Producto&m=ver&id=N`): foto, descripción, selector de
  talla (deshabilitada si está agotada) y cantidad (1–10).
- **Reseñas**: los clientes que compraron el producto pueden calificarlo (1–5 estrellas)
  y comentar. Se muestra el promedio y el historial de reseñas.
- **Registro / Login / Recuperar contraseña**: rutas `/?c=Auth&m=register`,
  `login`, `forgot`. La recuperación envía un enlace con token de 30 min y un solo uso.
- **Modo desarrollo**: sin servidor de correo configurado, el enlace de restablecimiento
  se muestra en la página de login y además queda guardado en `logs/emails/`.

### Cliente (rol `usuario`, con sesión iniciada)

- **Carrito con talla**: al agregar se elige la talla (XS–XL) y se valida el stock.
  El carrito distingue la misma prenda por talla (ej. `1:S` y `1:M`).
- **Carrito persistente**: el carrito se guarda en la BD (`carrito_items`) con cada
  cambio, de modo que sobrevive al cierre de sesión y se restaura al volver a entrar
  (se fusiona con lo que hayas agregado en la sesión actual). Al pagar se vacía.
- **Editar / eliminar del carrito**: botones **+ / −** para cambiar cantidades (con
  tope en el stock) y ✕ para quitar un producto; también se puede vaciar.
- **Checkout**: formulario de envío; al confirmar se **descuenta el stock** de cada
  talla dentro de la misma transacción (si falta stock, se cancela todo).
- **Mi Perfil** (`/?c=Auth&m=perfil`):
  - Editar nombre y correo.
  - Cambiar contraseña (exige la contraseña actual; tras el cambio se emite un token nuevo).
  - Historial de pedidos con estado, tallas y subtotales.
- **Seguimiento del pedido** (en Mi Perfil):
  - Notificación del pedido en curso (preparación / en camino) con entrega estimada y
    cuenta regresiva en vivo.
  - Botón "Rastrear pedido" por pedido: ruta del envío (origen → dirección del cliente),
    barra de avance por etapas y historial de eventos de seguimiento.
  - Aviso de retraso si la fecha estimada ya venció y el pedido aún no se entrega.

### Vendedor y Administrador (panel de gestión)

- **Dashboard** (`/?c=AdminPedido&m=dashboard`): total de pedidos, ingresos,
  completados, clientes, pedidos por estado (barras) y productos más vendidos.
- **Pedidos** (`/?c=AdminPedido&m=index`): listado con filtro por estado y búsqueda por
  #/cliente/correo y ver el detalle completo (cliente, dirección, tallas).
  - **Administrador**: además puede cambiar el estado en línea
    (pendiente → pagado → en camino → entregado / cancelado); cada cambio registra
    un evento en el seguimiento que ve el cliente.
  - **Vendedor**: solo consulta el listado y el detalle (no cambia estados).
- **Productos** (`/?c=AdminProducto&m=index`): crear/editar producto con categoría,
  descripción, imagen y **stock por talla**; eliminar (ocultar) con confirmación,
  listado paginado y activar/ocultar desde la lista.
- **Usuarios** (solo administrador, `/?c=AdminUsuario&m=index`): crear vendedores,
  cambiar rol, activar/desactivar y restablecer contraseña; listado paginado.

## Seguridad implementada

- Contraseñas con `bcrypt` (nunca en texto plano).
- Tokens CSRF en todos los formularios y endpoints.
- Tokens de sesión/API (`X-Auth-Token`) exigidos en los endpoints que modifican datos;
  los tokens se guardan solo con hash SHA-256.
- Control de intentos de login: 5 fallos bloquean 15 minutos (`login_intentos`).
- Tokens de recuperación de contraseña: hash SHA-256, expiración 30 min, uso único.
- Consultas con PDO preparado (protección contra SQL injection).
- Validación de formularios en el cliente (`js/validacion.js`) además de la del servidor.
- Charset `utf8mb4` en la conexión (soporta acentos y emojis correctamente).

## Respaldo de la base de datos

Script de PowerShell que genera un dump del contenedor MySQL y conserva los últimos N:

```bash
powershell -ExecutionPolicy Bypass -File scripts/backup-bd.ps1 -Mantener 10
```

Los respaldos se guardan en `backups/` con nombre `bodystop_YYYYMMDD_HHMMSS.sql`.
Para automatizar, agrega el comando al Programador de tareas de Windows.

## Base de datos

Tablas: `usuarios`, `auth_tokens`, `login_intentos`, `password_reset_tokens`,
`categorias`, `productos`, `producto_tallas` (stock por talla), `carrito_items`
(carrito persistente), `pedidos` (con `estado` y `fecha_estimada_entrega`),
`pedido_detalle` (con `talla`), `seguimiento_pedidos` (eventos del rastreo de
envíos), `resenas`.

```
usuarios 1──N auth_tokens            usuarios 1──N pedidos (usuario_id)
usuarios 1──N password_reset_tokens  pedidos 1──N pedido_detalle
categorias 1──N productos            pedidos 1──N seguimiento_pedidos
productos 1──N resenas               usuarios 1──N carrito_items
productos 1──N producto_tallas
usuarios 1──N resenas (usuario_id)
```

## Estructura del proyecto

```
core/          Auth, Security, TokenService, LoginLimiter, Mailer, Router
controllers/   Lógica de cada sección (Auth, Producto, Carrito, Checkout, Resena, Admin*)
repositories/  Acceso a datos (PDO)
models/        Modelos legacy (Producto, ProductoModel)
views/         Plantillas (layouts, auth, productos, carrito, checkout, admin)
js/            cart.js, resenas (inline), validacion.js, admin-usuarios.js
css/           app.css
scripts/       backup-bd.ps1 (respaldo de la BD)
logs/emails/   Correos generados en modo desarrollo
backups/       Respaldos de la BD
```

## Notas

- El rediseño visual de la tienda con el mockup **AURA** (`bodystop/home.html`,
  Tailwind) es una fase pendiente; el storefront actual usa Bootstrap.
- Docker Desktop debe estar iniciado antes de levantar el proyecto.
