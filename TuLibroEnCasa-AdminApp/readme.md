# **TuLibroEnCasa‑AdminApp**

Aplicación de administración interna para la gestión del sistema **TuLibroEnCasa**.  
Permite mantener actualizada la base de datos mediante herramientas para gestionar **autores, categorías, editoriales y libros**, utilizando diferentes interfaces desarrolladas en Java.

El proyecto está construido en **Java SE 8**, empleando **Hibernate + JPA** para la persistencia y una arquitectura **DAO** modular y mantenible.

---

# **Tecnologías utilizadas**

## **Backend / Persistencia**
- **Java SE 8**
- **Hibernate ORM**
- **JPA (Jakarta Persistence API)**
- **MySQL 8+**
- **Patrón DAO**
- **JDBC + Hibernate SessionFactory**

---

# **Frontends disponibles**

El proyecto incluye **tres interfaces de usuario**, todas conectadas a la misma capa DAO:

---

## **1️⃣ Modo Consola**
Interfaz basada en texto, ideal para pruebas rápidas o entornos sin GUI.  
Permite navegar mediante menús numéricos.

**Clase principal:**  
`frontendConsola.MenuPrincipalConsola`

---

## **2️⃣ Frontend Simple (Swing básico)**
Interfaz gráfica minimalista construida con componentes estándar de Swing.  
Diseño funcional, sin estilos personalizados.

**Clase principal:**  
`frontendSimple.MenuPrincipalSimple`

---

## **3️⃣ Frontend Estilizado (Swing moderno)**
Interfaz gráfica avanzada con diseño visual mejorado:

- Tarjeta central (card)
- Botones estilizados con colores personalizados
- Efectos hover
- Fondo a pantalla completa
- Tipografías personalizadas

**Clase principal:**  
`frontend.MenuPrincipal`

---

# **Base de datos**

La aplicación utiliza MySQL y proporciona scripts SQL para su configuración:

### ✔ Crear la base de datos  
`crear_bd.sql`

### ✔ Insertar datos de prueba  
`reset_datos.sql`

---

# **Cómo ejecutar la aplicación**

1. Importar el proyecto en **Eclipse** 
2. Crear la base de datos ejecutando `crear_bd.sql`.  
3. (Opcional) Insertar datos de prueba con `reset_datos.sql`.  
4. Elegir uno de los tres frontends:  
   - Modo consola  
   - Frontend simple  
   - Frontend estilizado  
5. Ejecutar la **clase principal** correspondiente.

---

# **Estructura del proyecto**

TuLibroEnCasa-AdminApp/
│
├── dao/                 # Clases DAO (acceso a datos)
├── model/               # Entidades JPA
├── frontend/            # Interfaz Swing estilizada
├── frontendSimple/      # Interfaz Swing básica
├── frontendConsola/     # Modo consola
├── META-INF/            # persistence.xml
├── sql/                 # Scripts SQL (crear_bd.sql, reset_datos.sql)
└── README.md             # Este archivo


---

# **Recomendaciones**

## ✔ Añadir carpeta `/sql`
Incluye:
- `crear_bd.sql`
- `reset_datos.sql`

## ✔ Añadir carpeta `/imagenes`  
Para capturas de pantalla (opcional).

## ✔ Añadir `.gitignore`  
Para ignorar:
- `/bin`
- `/target`
- `.classpath`
- `.project`
