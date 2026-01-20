package frontendConsola;

import java.util.List;
import java.util.Scanner;

import dao.LibroDAO;
import dao.AutorDAO;
import dao.CategoriaDAO;
import dao.EditorialDAO;

import model.Libro;
import model.Autor;
import model.Categoria;
import model.Editorial;

public class LibroConsola {

    private static LibroDAO libroDAO = new LibroDAO();
    private static AutorDAO autorDAO = new AutorDAO();
    private static CategoriaDAO categoriaDAO = new CategoriaDAO();
    private static EditorialDAO editorialDAO = new EditorialDAO();

    private static Scanner sc = new Scanner(System.in);

    public static void menu() {

        int opcion;

        do {
            System.out.println("\n--- GESTIÓN DE LIBROS ---");
            System.out.println("1. Listar libros");
            System.out.println("2. Añadir libro");
            System.out.println("3. Modificar libro");
            System.out.println("4. Borrar libro");
            System.out.println("0. Volver");
            System.out.print("Opción: ");

            opcion = leerEntero();

            switch (opcion) {
            case 1:
                listar();
                break;
            case 2:
                añadir();
                break;
            case 3:
                modificar();
                break;
            case 4:
                borrar();
                break;
            case 0:
                System.out.println("Volviendo...");
                break;
            default:
                System.out.println("Opción no válida.");
                break;
        }


        } while (opcion != 0);
    }

    private static void listar() {
        List<Libro> libros = libroDAO.listar();

        if (libros.isEmpty()) {
            System.out.println("No hay libros registrados.");
            return;
        }

        System.out.println("\n--- LISTA DE LIBROS ---");
        libros.forEach(l -> {
            System.out.println(
                l.getIsbn() + " | " +
                l.getTitulo() + " | " +
                l.getAutor().getNombreAutor() + " | " +
                l.getCategoria().getNombreCategoria() + " | " +
                l.getEditorial().getNombreEditorial() + " | " +
                l.getPrecioUnitario() + "€ | Stock: " + l.getStock()
            );
        });
    }

    private static void añadir() {
        try {
            System.out.print("ISBN: ");
            String isbn = sc.nextLine();

            System.out.print("Título: ");
            String titulo = sc.nextLine();

            Autor autor = seleccionarAutor();
            Categoria categoria = seleccionarCategoria();
            Editorial editorial = seleccionarEditorial();

            System.out.print("Precio unitario: ");
            double precioUnitario = Double.parseDouble(sc.nextLine());

            System.out.print("Stock: ");
            int stock = Integer.parseInt(sc.nextLine());

            Libro libro = new Libro(isbn, titulo, precioUnitario, stock, autor, categoria, editorial);

            libroDAO.insertar(libro);
            System.out.println("Libro añadido correctamente.");

        } catch (Exception e) {
            System.out.println("Error: datos inválidos.");
        }
    }

    private static void modificar() {

        System.out.print("ISBN del libro a modificar: ");
        String isbn = sc.nextLine();

        Libro libro = libroDAO.buscar(isbn);

        if (libro == null) {
            System.out.println("No existe un libro con ese ISBN.");
            return;
        }

        System.out.print("Nuevo título (" + libro.getTitulo() + "): ");
        String nuevoTitulo = sc.nextLine();
        if (!nuevoTitulo.isBlank()) libro.setTitulo(nuevoTitulo);

        System.out.println("Selecciona nuevo autor:");
        Autor nuevoAutor = seleccionarAutor();
        libro.setAutor(nuevoAutor);

        System.out.println("Selecciona nueva categoría:");
        Categoria nuevaCategoria = seleccionarCategoria();
        libro.setCategoria(nuevaCategoria);

        System.out.println("Selecciona nueva editorial:");
        Editorial nuevaEditorial = seleccionarEditorial();
        libro.setEditorial(nuevaEditorial);

        System.out.print("Nuevo precio (" + libro.getPrecioUnitario() + "): ");
        double nuevoPrecio = Double.parseDouble(sc.nextLine());
        libro.setPrecioUnitario(nuevoPrecio);

        System.out.print("Nuevo stock (" + libro.getStock() + "): ");
        int nuevoStock = Integer.parseInt(sc.nextLine());
        libro.setStock(nuevoStock);

        libroDAO.modificar(libro);
        System.out.println("Libro modificado correctamente.");
    }

    private static void borrar() {

        System.out.print("ISBN del libro a borrar: ");
        String isbn = sc.nextLine();

        boolean borrado = libroDAO.borrar(isbn);

        if (!borrado) {
            System.out.println("No se puede borrar (puede estar relacionado con otros registros).");
        } else {
            System.out.println("Libro borrado correctamente.");
        }
    }

    // ============================
    // MÉTODOS AUXILIARES
    // ============================

    private static Autor seleccionarAutor() {
        List<Autor> autores = autorDAO.listar();

        if (autores.isEmpty()) {
            System.out.println("No hay autores registrados.");
            return null;
        }

        System.out.println("\n--- AUTORES DISPONIBLES ---");
        autores.forEach(a ->
                System.out.println(a.getIdAutor() + " - " + a.getNombreAutor())
        );

        System.out.print("ID del autor: ");
        int id = leerEntero();

        return autorDAO.buscar(id);
    }

    private static Categoria seleccionarCategoria() {
        List<Categoria> categorias = categoriaDAO.listar();

        if (categorias.isEmpty()) {
            System.out.println("No hay categorías registradas.");
            return null;
        }

        System.out.println("\n--- CATEGORÍAS DISPONIBLES ---");
        categorias.forEach(c ->
                System.out.println(c.getIdCategoria() + " - " + c.getNombreCategoria())
        );

        System.out.print("ID de la categoría: ");
        int id = leerEntero();

        return categoriaDAO.buscar(id);
    }

    private static Editorial seleccionarEditorial() {
        List<Editorial> editoriales = editorialDAO.listar();

        if (editoriales.isEmpty()) {
            System.out.println("No hay editoriales registradas.");
            return null;
        }

        System.out.println("\n--- EDITORIALES DISPONIBLES ---");
        editoriales.forEach(e ->
                System.out.println(e.getIdEditorial() + " - " + e.getNombreEditorial())
        );

        System.out.print("ID de la editorial: ");
        int id = leerEntero();

        return editorialDAO.buscar(id);
    }

    private static int leerEntero() {
        try {
            return Integer.parseInt(sc.nextLine());
        } catch (Exception e) {
            return -1;
        }
    }
}
