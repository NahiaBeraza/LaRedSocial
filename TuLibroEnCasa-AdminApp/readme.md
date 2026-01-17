#  TuLibroEnCasa-AdminApp

Aplicación de administración para la gestión interna del sistema **TuLibroEnCasa**.  
Incluye herramientas para gestionar autores, categorías, editoriales y libros mediante una interfaz gráfica en Java.

Este proyecto utiliza **Hibernate + JPA** para la capa de persistencia, siguiendo una arquitectura DAO limpia y modular, se ha creado con Eclipse JavaSE.

---

##  Tecnologías utilizadas

###  Backend / Persistencia
- **Java 17+**
- **Hibernate ORM**
- **JPA (Jakarta Persistence API)**
- **MySQL 8+**
- **DAO Pattern**
- **JDBC + Hibernate SessionFactory**

###  Frontends disponibles
El proyecto incluye **tres formas de visualizar la aplicación**:

---

## 1️⃣ Modo Consola
Interfaz por terminal, ideal para pruebas rápidas o entornos sin GUI.

**Clase principal:**
frontendConsola.MenuPrincipalConsol


---

## 2️⃣ Frontend Simple (Swing básico)
Interfaz gráfica sencilla, sin estilos avanzados.  
Perfecta para aprendizaje o equipos que prefieren interfaces minimalistas.

**Clase principal:**
frontendSimple.MenuPrincipalSimple


---

## 3️⃣ Frontend Estilizado (Swing moderno)
Interfaz gráfica con diseño más profesional:
- Tarjetas (cards)
- Botones estilizados
- Fondo a pantalla completa
- Colores personalizados

**Clase principal:**
frontend.MenuPrincipal


---

#  Base de datos

La aplicación utiliza MySQL.  
Incluye scripts SQL para:

### ✔ Crear la base de datos

crear_bd.sql

### ✔ Insertar datos de prueba
reset_datos.sql

▶️ Cómo ejecutar la aplicación
Importar el proyecto en Eclipse, IntelliJ o VS Code

Crear la base de datos ejecutando crear_bd.sql

(Opcional) Insertar datos de prueba con reset_datos.sql

Elegir uno de los tres frontends:

Consola

Frontend Simple

Frontend Estilizado

Ejecutar la clase principal correspondiente

📦 Estructura del proyecto

TuLibroEnCasa-AdminApp/
│
├── config/              # Conexión a BD (Hibernate + JDBC)
├── dao/                 # Clases DAO
├── model/               # Entidades JPA
├── frontend/            # Interfaz estilizada
├── frontendSimple/      # Interfaz simple
├── consola/             # Modo consola
├── sql/                 # Scripts SQL
└── README.md            # Este archivo


👨‍💻 RECOMENDACIONES:

#  1) ¿Dónde lo pego?

1. En tu proyecto, crea un archivo llamado **README.md**  
2. Pégalo **tal cual**  
3. Guárdalo

Si usas GitHub:

- Haz commit → `git add README.md`
- `git commit -m "Añadido README profesional"`
- `git push`

GitHub lo mostrará automáticamente en la página principal del repositorio.

---

#  2) ¿Qué más puedes preparar?

Si quieres dejarlo **perfecto**, te recomiendo:

### ✔ Crear carpeta `/sql`  
Meter ahí:
- `crear_bd.sql`
- `reset_datos.sql`

### ✔ Crear carpeta `/imagenes`  
Para capturas de pantalla (opcional).

### ✔ Añadir un `.gitignore`  
Para ignorar:
- `/bin`
- `/target`
- `.classpath`
- `.project`
