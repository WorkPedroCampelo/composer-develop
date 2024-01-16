# Entorno de desarrollo web para PHP

### Requisitos

- Docker 
- WSL (linux subsystem for windows)


### Contenedores

| Servicio         | Version | Puertos   |
|------------------|---------|-----------|
| PHP 8.2 + Apache | 8.2     | 8000:80   |
| PHP 7.4 + Apache | 7.4     | 8001:80   |
| Composer         | 2       | -         |
| MariaDB          | Latest  | 3306:3306 |


### Docker


#### Como puedo ejecutar este proyecto ? 

Para poder ejecutar este repositorio y poder empezar a desarrollar en PHP deberas ejecutar el siguiente
comando en el directorio raiz.

```cmd
docker compose up -d
```

- **docker compose up** : Inicia los contenedores alojados en el archivo docker-compose.yml
- **-d** : Ejecuta los contenedores en segundo plano.

Con este comando se empezaran a crear los contenedores antes mencionados (si pones el comando con los contenedores ya creados lo unico que hace es iniciarlos cogiendo los cambios). Luego de esta primera ejecucion podras encender los 
contendores de manera normal en el Docker Desktop

#### Como detengo los contenedores?

Para detener los contenedores puedes hacerlo directamente en la aplicacion **Docker Desktop** en el boton de detener.

Hay otras dos maneras, pero son mediante terminal: 

1. Abre una terminal en el directorio raiz del proyecto (a la altura del **docker-compose.yml** y del **Dockerfile**), pones el comando ```docker compose down```. Esto **eliminara** todos los contenedores que esten asociados a ese *docker-compose.yml*.
2. Abre una terminal y escribes ```docker down (nombre_contenedor o id_contenedor)```

#### Donde empiezo a programar?

Para empezar tu aplicacion y poder programar deberas trabajar en la carpeta **app**.

Ya que esta es la carpeta que los dos servicios de PHP (PHP 8.2 y PHP 7.4) sirven la web.

Basicamente montamos la carpeta **app** en **/var/www/html** la carpeta por defecto de Apache para servir las webs.



#### Como maneja Docker los puertos y las conexiones?

##### Puertos

En Docker podemos cambiar los puertos que exponemos para conectarnos, por ejemplo, en este repositorio en **PHP 8.2** tenemos esta configuracion

```yml
ports:
  - "8000:80"
```


Lo que quiere decir que de manera interna el **PHP 8.2** funciona con el puerto 80 (el por defecto para Apache) pero para conectarnos de manera externa a la web deberemos usar el 8000, por ejemplo ``` http://localhost:8000 ```. 

##### Conexiones

En nuestra aplicacion usaremos de manera frecuente conexiones a nuestra base de datos **MariaDB**, asi que es importante explicar como manejar estas conexiones en nuestro entorno **Docker**.

A diferencia de Xamp, en Docker no nos podemos conectar a la base de datos con **PHP** mediante el hostname **localhost**, ya que Docker tiene un servidor DNS interno distinto. Lo recomandable es usar el conector interno de Docker, usando el hostname **host.docker.internal**.

> Archivo config.ini

```ini
[database]
hostname = "host.docker.internal"
puerto = "3306"
usuario = "root"
contrasena = "root"
base_de_datos = "dwcs"
```
Pero de manera externa, si podremos conectarnos mediante **localhost** por ejemplo para usar algun gestor de base de datos, como MySQL Workbench o DataGrip.

<img src="README.assets/image1.png" style="width: 600px"/>

En resumen, para conexiones internas entre los contenedores usar el puerto interno y el hostname **host.docker.internal**, para conexiones externas usar el puerto que expone docker y el hostname **localhost**.

#### Volumenes

En el repositorio tenemos creados varios volumenes importantes.

- MariaDB Volumen: ./data:/var/lib/mysql | Este volumen es el encargado de guardar los datos de la base de datos, para evitar perder la informacion cada vez que apagamos el contenedor.
- PHP 8.2 y 7.2:
  - ./app:/var/www/html | Volumen que monta la web para servirla mediante el servidor Apache
  - ./config/php.ini:/usr/local/etc/php/php.ini | Montar el archivo de configuracion php.ini en el servidor para poder editarlo mas facil
- Composer: ./app:/var/www/html | Donde se monta la instalacion del composer. (Carpeta **./vendor**, composer.json, composer.lock)

#### Errores?

A lo largo del desarrollo en este entorno es posible que al reconstriur nuestros proyectos (```docker compose up --force-recreate --build -d```) tantas veces ocurran algunos errors, o sean por otros motivos dejo aqui unas soluciones a algunos errores que me han ido saliendo.

1. SQLState could not find driver

Este error se da porque en nuestro contenedor no se instalo bien el pdo_mysql.

Para solucionarlo debesmos instalarlo de manera manual, primero debemos ejecutar la consola de nuestro contenedor con ```docker exec -it "nombreContenedor" /bin/bash ```

Ya con la consola abierta podemos ejecutar el comando para instalarlo ```docker-php-ext-install mysqli pdo_mysql ```, 
ya con esto se instala y ahora solo queda habilitarlo ```docker-php-ext-enable mysqli pdo_mysql```. Ahora solo queda reiniciar el contenedor.

### Composer

#### Que es Composer?

Composer es una herramienta para administrar las dependencias en proyectos de PHP. 
En términos simples, ayuda a gestionar las bibliotecas y paquetes de código que tu proyecto PHP pueda necesitar para funcionar.

#### Como usarlo? 

Para poder usar composer e instalar la librerias que necesitemos lo haremos desde el Docker llamado **mycomposer**.

A modo de ejemplo mostrare como instalar Krumo (Una herramienta para visualizar mejor los vardump() ) -> https://packagist.org/packages/mmucklo/krumo

1. Entraremos dentro del contenedor **mycomposer** con el siguiente comando.

``` docker exec -it mycomposer /bin/bash ```

Se nos abrira una consola nueva, esta es la consola interna del contenedor **mycomposer**.

<img src="README.assets/image2.png" style="width: 700px"/>

2. Ponemos el comando para instalar el paquete que queramos, en nuestro caso es Krumo el paquete.

``` composer require mmucklo/krumo```

Con esto comenzara la instalacion de nuestro paquete.

<img src="README.assets/image3.png" style="width: 700px"/>

3. Con nuestro paquete instalado es muy recomandable ejecutar el siguiente comando para refrescar el script encargado de cargar la librerias (dentro de la consola de **mycomposer**).

``` composer dump-autoload ```

4. Ya con nuestro paquete instalado solo queda probarlo, dejo aqui un codigo simple para probarlo.

```php
include('./vendor/autoload.php');

$variable = array('a' => 'apple', 'b' => 'banana', 'c' => 'cherry');
krumo($variable);
```

#### Como funciona Composer?

De una manera simple, Composer usa un archivo llamado **autoload.php**, el cual se encarga de cargar todas las librerias y que puedas usarlas en tus scripts.

Para usar las librerias deberas escribir obligatoriamente en el fichero la siguiente linea:

```php 
include('./vendor/autoload.php');
```

Asi de esta manera se cargaran todas las librerias y las podras usar. 
Una buena practica es que cada vez que descarguemos una libreria ejecutemos el comando del paso 3, 
del anterior apartado anterior para actualizar el script que autocarga las librerias. 

### XDebug

#### Que es XDebug?

#### XDebug en VSCode

Para poder usar el xdebug en el VSCode es necesario configurar los parametros de lanzamiento de la session de `Debugging`.

Estos parametros se encuentran en la carpeta oculta **./.vscode** en el archivo **launch.json**. Los parametros mas importantes son los siguientes: 

- Puerto del XDebug `"port": 9003`, este es el puerto por defecto que se usa para la sesion del XDebug
- Ruta de Mapeo del proyecto `"pathMappings": { "/var/www/html/": "${workspaceRoot}/app/" }`, esta configuracion define la ruta para el servidor de depuracion. Si se cambia el nombre de la carpeta del proyecto es importante volver a cambiarlo aqui.
- Directorio de trabajo `cwd": "${workspaceFolder}/app/"`, aqui se establece el directorio donde esta alojado el proyecto.

Por suerte en este repositorio ya viene configurado el launch.json, pero recuerda que si realizas alguna modificacion es importante volver a reflejarlas en el **launch.json**

A parte de esto es importante descargar las siguiente extension.

- PHP Debug -> https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug (Nos permite colocar los puntos de interrupcion en nuestro codigo PHP)

##### Como incion una sesion con XDebug?

Para empezar a realizar una sesion de depuracion con XDebug deberas seguir estos pasos.

1. Poner el **Breakpoint**

<img src="README.assets/image4.png" style="width: 600px"/>

2. Entrar en el apartado de Run and Debug (Ctrl+Shift+D) y hacer click en XDebug

<img src="README.assets/image5.png" style="width: 400px"/>

Tras hacer click nos saldra abajo que el XDebug esta escuchando en puerto 9003

<img src="README.assets/image6.png" style="width: 400px"/>

3. Ya con el XDebug escuchando solo queda abrir nuestra web y ya podremos ver la sesion de depuracion funcionando

<img src="README.assets/image7.png" style="width: 800px"/>


#### XDebug en NetBeans

Para configurar nuestro entorno en Netbeans es algo mas complicado que en Vscode.

Primero debemos cargar nuestro proyecto en NetBeans. 

<img src="README.assets/image8.png" style="width: 570px"/>

<img src="README.assets/image9.png" style="width: 570px"/>

Aqui es importante poner como carpeta del proyecto la misma que montamos en nuestro docker, en este caso la carpeta **app**


<img src="README.assets/image10.png" style="width: 570px"/>

Por ultimo no olvidarse de poner bien nuestra url del proyecto, cuidando especificar el puerto, como en nuestro caso tenemos un PHP 8.2 en el puerto 8000 debemos cambiar el puerto ```http://localhost:8000```

Con esto ya tenemos el nuestro proyecto configurado en Netbeans, con la configuracion por defecto del Netbeans deberia de funcionar sin problemas la depuracion.

##### No me funciona el XDebug :(

En el caso de que no funcione explico aqui como solucionarlo:

- El principal problema para que NetBeans no detecte bien nuestro XDebug es porque la **xdebug.idekey** este mal configurada.

La **xdebug.idekey**  es una opción utilizada por XDebug para especificar la clave del entorno de desarrollo integrado (IDE, por sus siglas en inglés) que está siendo utilizada. 
Esta se especifica en el **php.ini** que en el caso de este repositorio esta alojada en la carpeta **./composer/config** 

```ini
xdebug.idekey="netbeans-xdebug"
```

Aqui la especificamos como "netbeans-xdebug", que es la idekey por defecto de NetBeans.

Asi que lo que tenemos que hacer es comprobar que esta coincida tanto en NetBeans como en el **php.ini**, para verlo en el NetBeans tienes
que acceder a **Tools>Options**. Se abrira la siguiente pestaña e iremos a **PHP** y **Debugging**.

<img src="README.assets/image11.png" style="width: 570px"/>

Aqui comprobamos la Session ID, y tambien el puerto (es el 9003).