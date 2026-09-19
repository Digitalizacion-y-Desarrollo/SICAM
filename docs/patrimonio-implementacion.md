# Patrimonio: alcance y verificación

Objetivo: Resumen, Bienes, Asignaciones, Movimientos, Categorías e Importaciones funcionales, incluyendo responsables y campos de categorías; CRUD, QR descargable y ficha pública, auditoría, SweetAlert2, bloqueo de acciones repetidas y ayuda Driver.js.

## Decisiones pendientes del usuario

- Identidad y permisos: Accesos o autenticación local; no existe autenticación funcional actualmente.
- Campos autorizados en la ficha pública del QR.
- URL accesible desde teléfonos y referencia de Figma «Sistema de Inventario».
- Formato municipal de importación, si existe; propuesta enviada: Excel/CSV con validación previa.

## Requisitos de aceptación

- [ ] Resumen con métricas consultadas de la base de datos y enlaces funcionales.
- [ ] Bienes: alta, detalle, edición, baja, filtros, fotografía, campos dinámicos, folios y validación.
- [ ] QR automático, descarga y lectura que abra la ficha pública del bien; QR existentes conservan su identidad.
- [ ] Asignaciones: alta, consulta, modificación y cierre/baja con historial, transacciones y control de una asignación vigente por bien.
- [ ] Movimientos: consulta, filtros, detalles y gestión coherente con historial/auditoría.
- [ ] Categorías y campos: CRUD, opciones de selección y protección de relaciones con bienes existentes.
- [ ] Responsables: CRUD y protección de asignaciones vigentes.
- [ ] Importaciones: plantilla, validación previa por fila, confirmación, historial y gestión de registros importados.
- [ ] Todos los avisos y confirmaciones de eliminación usan SweetAlert2.
- [ ] Acciones CRUD bloqueadas con animación y protección ante doble envío.
- [ ] Auditoría en migración propia: autor, fecha, acción, entidad, valores anteriores/nuevos; no registra credenciales.
- [ ] Identidad y permisos comprobados en backend.
- [ ] Ayuda contextual Driver.js en cada sección.
- [ ] Pruebas de éxito, validación, permisos, transacciones, QR, importaciones e idempotencia.
- [ ] Migraciones revisadas/aplicadas, compilación y verificación visual responsive.

## Evidencia inicial

- Registro de bienes, listado, categorías y responsables parcialmente funcionales.
- Seeder municipal con Cómputo, Mobiliario y Telefonía.
- No hay login ni integración Accesos; el encabezado contiene datos ficticios.
- El navegador de la sesión anterior no estuvo disponible; se debe comprobar nuevamente antes de QA visual.
- QR y las rutas de los otros módulos aún no existían al inicio del objetivo.

Este documento registra el alcance; las casillas solo deben marcarse con evidencia comprobada.
