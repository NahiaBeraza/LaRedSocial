package frontendConsola;

import java.util.List;
import java.util.Scanner;

import dao.CategoriaDAO;
import model.Categoria;

public class CategoriaConsola {

    private static CategoriaDAO categoriaDAO = new CategoriaDAO();
    private static Scanner sc = new Scanner(System.in);

    public static void menu() {

        int opcion;

        do {
            System.out.println("\n--- GESTIÓN DE CATEGORÍAS ---");
            System.out.println("1. Listar categorías");
            System.out.println("2. Añadir categoría");
            System.out.println("3. Modificar categoría");
            System.out.println("4. Borrar categoría");
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
        List<Categoria> categorias = categoriaDAO.listar();

        if (categorias.isEmpty()) {
            System.out.println("No hay categorías registradas.");
            return;
        }

        System.out.println("\n--- LISTA DE CATEGORÍAS ---");
        categorias.forEach(c ->
                System.out.println(c.getIdCategoria() + " - " + c.getNombreCategoria())
        );
    }

    private static void añadir() {
        System.out.print("Nombre de la categoría: ");
        String nombre = sc.nextLine();

        if (nombre.isBlank()) {
            System.out.println("Nombre no válido.");
            return;
        }

        categoriaDAO.insertar(new Categoria(nombre));
        System.out.println("Categoría añadida correctamente.");
    }

    private static void modificar() {
        System.out.print("ID de la categoría a modificar: ");
        int id = leerEntero();

        Categoria categoria = categoriaDAO.buscar(id);

        if (categoria == null) {
            System.out.println("No existe una categoría con ese ID.");
            return;
        }

        System.out.print("Nuevo nombre: ");
        String nuevoNombre = sc.nextLine();

        if (nuevoNombre.isBlank()) {
            System.out.println("Nombre no válido.");
            return;
        }

        categoria.setNombreCategoria(nuevoNombre);
        categoriaDAO.modificar(categoria);

        System.out.println("Categoría modificada correctamente.");
    }

    private static void borrar() {
        System.out.print("ID de la categoría a borrar: ");
        int id = leerEntero();

        boolean borrado = categoriaDAO.borrar(id);

        if (!borrado) {
            System.out.println("No se puede borrar (puede tener libros asociados).");
        } else {
            System.out.println("Categoría borrada correctamente.");
        }
    }

    private static int leerEntero() {
        try {
            return Integer.parseInt(sc.nextLine());
        } catch (Exception e) {
            return -1;
        }
    }
}
