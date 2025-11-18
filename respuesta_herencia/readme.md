# Descripcion de la prueba tecnica realizada para el Grupo Romeu

## Trabajo realizado
Se ha construido una refactorizacion siguiendo los principios de POO, SOLID y la division de responsaibilidades. Ademas he aplicado lo aprendido en mi anterior puesto como para programador backend de apoyo.

## Organizacion
Primero debo defender mi trabajo, , voy a pasar un enlace a mi repositorio donde se podran ver los commits realizados y **se podra ver el proceso seguido de construccion y refinamiento del proyecto**. La ia solo se ha usado como consulta puntual en la organizacion y para arrancar el XAMPP.

Como programador de codigo legado se bien lo inutil que puede ser la ia a la hora de trabajar en codigo legado y el peligro que supone para romper cosas.

[Enlace GitHub de listado de commits](https://github.com/Caradrian14/prueba_tecnica_code_legalicy/commits/master/)

**La estructura** la he realizado siguiendo no solo los principios POO y SOLID si no que tambien me he inspirado en como trabajabamos en mi anterior puesto. 
- La carpeta `datos\` es donde se guardan la información que he considerado como que proviene de la BBDD o que simplemente llega asi a nuestro codigo. 
- la carpeta `src\` que es donde se guarda el codigo siguiendo la estructura de POO y SOLID.
- la carpeta `test` donde se dipositaran los test. 
- el fichero `index.php` que es el que arranca el proceso llamndo a todos los objetos

## Decisiones tecnicas
- la carpeta `datos\`: En mi experiencia esto no se toca, ya que refactorizamos solo una zona concreta y la informacion y los datos nos llega como nos llega, dado que tocar esto corre el riesgo de romper otras partes del codigo en el contexto de un proyecto mayor; lo que hace que en el proceso haya decidido no tocar esta parte, solo para las pruebas.

- la carpeta `src\Cupones\`: He decidido convertir los cupones en objetos hijos del objeto cupon. Esta decision se ha tomado por que se ,en mi experiencia, que estos objetos requieren de mas procesos, por ejemplo llamadas a api, bases de datos o mas comprobaciones. De esta forma cada cupon tiene su funcionalidad separada y de hacer falta algo comun se puede aplicar el objeto cupon para que sea heredado. Si bien en el contexto de la prueba quedan funciones pequeñas y demasiado simples.

- la carpeta `src\Factory` se han colocado objetos para la creacion de los objetos cupon y producto. Esta es una forma de ahorrar codigo que usabamos para no estar siempre repitiendo el constructor en multiples partes. Ademas es mas comodo de usar en otras partes del codigo.

- la carpeta `src\Rules` lo mismo que en la carpeta `src\Cupones\`, se ha construido para dividir responsabilidades y en caso de que las reglas crezcan o haya mas factores a tener en cuenta se podra añadir sin complejidades.

- en el fichero `src\Cupones\ReglaCoupones.php` se ha obtado por usar un switch case. He considerado otras formas como usar arrays asociativos que ejecuten el codigo, pero me he decantado por los switch case debido a que son faciles de visualizar, y permiten ejecutar multiples fucniones o linias de codigo en el en casos particulares de complejidad, algo que al programar en codigo legado yo agradeceria.

- Uso de PHPDoc. Dado que se que hay un debate en el sector con el tema de los comentarios en codigo, he obtado por usar la documentacion para explicar el codigo de est forma, ademas he tratada de seguir las indicaciones de el Clean Code y su "si hay comentarios es que el codigo no esta limipio" yo no me meto. Los comentarios que hay en el codigo son para explicar situaciones puntuales asociadas a que estoy en una prueba y para que sea mas rapido a vosotros ver lo que he hecho.

