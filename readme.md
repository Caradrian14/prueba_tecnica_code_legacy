# Descripción de la prueba técnica realizada para el Grupo Romeu

## Trabajo realizado
Se ha construido una refactorización siguiendo los principios de POO, SOLID y la **división de responsabilidades**. Además, he aplicado lo aprendido en mi anterior puesto como **programador backend de apoyo**.

## Organización
Primero, debo defender mi trabajo. Voy a compartir un enlace a mi repositorio, donde se podrán ver los *commits* realizados y **se podrá observar el proceso seguido de construcción y refinamiento del proyecto**. La IA solo se ha utilizado como consulta puntual para la organización y para arrancar el XAMPP.

Para ejecutar la prueba solo hay que ejecutarla como un script de php normal, empezando esta por el index.php. Para el desarrollo de la prueba he usado XAMPP y Visual Studio Code.

Como programador de código legado, sé lo **inútil que puede ser la IA a la hora de trabajar con código legado** y el peligro que supone para romper funcionalidades.


[Enlace GitHub de listado de commits](https://github.com/Caradrian14/prueba_tecnica_code_legacy/commits/master/)
[Enlace repositorio GitHub](https://github.com/Caradrian14/prueba_tecnica_code_legacy/)
[Video en Youtobe de 4 minutos explicando esto yo mismo](https://youtu.be/L0K9Hu5BsE4) por que asumo que estaran cansados de tanto leer.

**La estructura** la he realizado siguiendo no solo los principios **POO y SOLID**, sino que también me he inspirado en cómo trabajábamos en mi anterior puesto.

- La carpeta **`datos/`** es donde se guarda **la información** que he considerado como proveniente de la **base de datos** o que simplemente llega así a nuestro código.
- La carpeta **`src/`** es donde se almacena el código, siguiendo la estructura de **POO y SOLID**.
- La carpeta **`test/`** es donde se depositarán los *tests*.
- El fichero **`index.php`** es el que inicia el proceso, llamando a todos los objetos necesarios.


## Decisiones técnicas

- **Carpeta `datos/`**: En mi experiencia, esta carpeta no se toca, ya que refactorizamos solo una zona concreta y la información y los datos nos llegan como nos llegan. Tocar esta parte conlleva el riesgo de romper otras áreas del código en el contexto de un proyecto mayor. Por eso, durante el proceso, he decidido no modificarla, utilizando sus datos solo para las pruebas.

- **Carpeta `src/Cupones/`**: He decidido convertir los cupones en objetos hijos de la clase `Cupon`. Esta decisión se basa en mi experiencia, donde estos objetos suelen requerir procesos adicionales, como llamadas a APIs, consultas a bases de datos o comprobaciones más complejas. De esta forma, cada cupón tiene su funcionalidad separada, y si se necesita algo común, se puede aplicar herencia desde el objeto `Cupon`. Aunque en el contexto de esta prueba las funciones resultan pequeñas y simples, esta estructura permite escalabilidad.

- **Carpeta `src/Factory/`**: Aquí se han colocado objetos dedicados a la creación de instancias de `Cupon` y `Producto`. Esta aproximación permite ahorrar código, evitando repetir el constructor en múltiples lugares. Además, facilita su reutilización en otras partes del proyecto.

- **Carpeta `src/Rules/`**: Al igual que en `src/Cupones/`, esta carpeta se ha creado para dividir responsabilidades. Así, si las reglas crecen o surgen nuevos factores, se podrán añadir sin complicaciones.

- **Fichero `src/Cupones/ReglaCupones.php`**: En este caso, he optado por usar un `switch-case`. Aunque consideré alternativas como arrays asociativos para ejecutar código, me decanté por `switch-case` porque son fáciles de visualizar y permiten ejecutar múltiples funciones o líneas de código en casos complejos. En mi experiencia con código legado, valoro especialmente esta claridad.

- **Uso de PHPDoc**: Aunque existe un debate en el sector sobre los comentarios en el código, he optado por usar PHPDoc para documentar las funciones y clases. Aunque sigo las indicaciones de *Clean Code* sobre la limpieza del código, considero que la documentación es útil para explicar decisiones puntuales, especialmente en el contexto de una prueba técnica. Los comentarios adicionales en el código están pensados para facilitar la revisión de lo realizado.

**Agradezco muchisimo la oportunidad, y deseo poder trabajar pronto con ustedes, que pasen un muy ben dia!**
