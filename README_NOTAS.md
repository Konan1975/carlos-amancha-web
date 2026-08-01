# Modulo de Notas UTPL

## Proposito
Este modulo permite a estudiantes UTPL establecer, calcular y visualizar su proyeccion de notas por materia usando una estructura de ponderaciones compatible con reportes bimestrales.

## Funcionalidades principales
- Simulador de notas sobre 10 por componente y bimestre.
- Resumen automatico con total final, acumulado y estado academico.
- Guardado local de simulaciones por materia en el navegador.
- Panel lateral con acceso rapido a simulaciones guardadas.
- Registro opcional de uso para contabilizar usuarios y simulaciones.
- Vista de impresion/exportacion PDF de la simulacion.

## Flujo recomendado para estudiantes
1. Completa datos de carrera, periodo, asignatura y codigo.
2. Ingresa o ajusta las notas de ambos bimestres.
3. Revisa el total final y el estado academico.
4. Guarda la simulacion para volver a editarla despues.
5. Si deseas, activa el registro opcional para contribuir a estadisticas de adopcion.

## Privacidad del registro opcional
- El uso del simulador no requiere registro.
- El registro opcional envia un evento de guardado al endpoint `notas_registro.php`.
- Para conteo unico, se recomienda correo; se almacena una huella hash, no el correo en texto plano.

## Archivos del modulo
- `notas.html`: interfaz principal del simulador.
- `notas.php`: puente PHP para servir la vista de notas.
- `notas/index.php`: acceso alterno al modulo.
- `notas_registro.php`: endpoint para contador inicial de usuarios/simulaciones.
- `css/estilos.css`: estilos compartidos del sitio y reglas especificas del modulo.
- `data/notas_metrics.json`: almacenamiento JSON de metricas (se crea automaticamente).

## Compatibilidad
- PHP puro, sin framework.
- Funciona en XAMPP local y hosting compartido compatible con escritura de archivos.

## Roadmap sugerido
- Migrar metricas a base de datos para analitica robusta.
- Agregar autenticacion opcional para perfil estudiantil.
- Implementar reportes comparativos entre simulaciones.
