package app;

import dao.LibroDAO;
import dao.AutorDAO;
import dao.CategoriaDAO;
import dao.EditorialDAO;

import model.Libro;
import model.Autor;
import model.Categoria;
import model.Editorial;

public class TestLibro {

    public static void main(String[] args) {

        LibroDAO libroDAO = new LibroDAO();
        AutorDAO autorDAO = new AutorDAO();
        CategoriaDAO categoriaDAO = new CategoriaDAO();
        EditorialDAO editorialDAO = new EditorialDAO();

        // Necesitamos objetos reales para las FK
        Autor autor = autorDAO.buscar(1);
        Categoria categoria = categoriaDAO.buscar(1);
        Editorial editorial = editorialDAO.buscar(1);

        if (autor == null || categoria == null || editorial == null) {
            System.out.println("ERROR: Debes tener al menos un autor, categoría y editorial con ID 1.");
            return;
        }

        // 1. INSERTAR
        Libro nuevo = new Libro(
                "1234567890123",
                "Libro de prueba",
                19.95,
                10,
                autor,
                categoria,
                editorial
        );

        libroDAO.insertar(nuevo);
        System.out.println("Insertado: " + nuevo);

        // 2. LISTAR
        System.out.println("\nLISTA DE LIBROS:");
        libroDAO.listar().forEach(System.out::println);

        // 3. BUSCAR POR ISBN
        System.out.println("\nBUSCAR LIBRO CON ISBN 1234567890123:");
        Libro l = libroDAO.buscar("1234567890123");
        System.out.println(l);

        // 4. BUSCAR POR TÍTULO
        System.out.println("\nBUSCAR POR TÍTULO 'pru':");
        libroDAO.buscarPorTitulo("pru").forEach(System.out::println);

        // 5. FILTRAR POR AUTOR
        System.out.println("\nFILTRAR POR AUTOR ID 1:");
        libroDAO.filtrarPorAutor(1).forEach(System.out::println);

        // 6. FILTRAR POR CATEGORIA
        System.out.println("\nFILTRAR POR CATEGORIA ID 1:");
        libroDAO.filtrarPorCategoria(1).forEach(System.out::println);

        // 7. FILTRAR POR EDITORIAL
        System.out.println("\nFILTRAR POR EDITORIAL ID 1:");
        libroDAO.filtrarPorEditorial(1).forEach(System.out::println);

        // 8. MODIFICAR
        if (l != null) {
            l.setPrecioUnitario(25.50);
            l.setStock(20);
            libroDAO.modificar(l);

            System.out.println("\nLibro modificado:");
            System.out.println(libroDAO.buscar(l.getIsbn()));
        }

        // 9. BORRAR
        System.out.println("\nBORRANDO LIBRO CON ISBN 1234567890123...");
        boolean borrado = libroDAO.borrar("1234567890123");

        if (!borrado) {
            System.out.println("No se pudo borrar el libro (puede no existir).");
        }

        System.out.println("\nLISTA FINAL:");
        libroDAO.listar().forEach(System.out::println);
    }
}
