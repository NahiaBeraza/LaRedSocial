package frontendConsola;

import java.util.List;
import java.util.Scanner;

import dao.EditorialDAO;
import model.Editorial;

public class EditorialConsola {

    private static EditorialDAO editorialDAO = new EditorialDAO();
    private static Scanner sc = new Scanner(System.in);

    public static void menu() {

        int opcion;

        do {
            System.out.println("\n--- GESTIÓN DE EDITORIALES ---");
            System.out.println("1. Listar editoriales");
            System.out.println("2. Añadir editorial");
            System.out.println("3. Modificar editorial");
            System.out.println("4. Borrar editorial");
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
        List<Editorial> editoriales = editorialDAO.listar();

        if (editoriales.isEmpty()) {
            System.out.println("No hay editoriales registradas.");
            return;
        }

        System.out.println("\n--- LISTA DE EDITORIALES ---");
        editoriales.forEach(e ->
                System.out.println(e.getIdEditorial() + " - " + e.getNombreEditorial())
        );
    }

    private static void añadir() {
        System.out.print("Nombre de la editorial: ");
        String nombre = sc.nextLine();

        if (nombre.isBlank()) {
            System.out.println("Nombre no válido.");
            return;
        }

        editorialDAO.insertar(new Editorial(nombre));
        System.out.println("Editorial añadida correctamente.");
    }

    private static void modificar() {
        System.out.print("ID de la editorial a modificar: ");
        int id = leerEntero();

        Editorial editorial = editorialDAO.buscar(id);

        if (editorial == null) {
            System.out.println("No existe una editorial con ese ID.");
            return;
        }

        System.out.print("Nuevo nombre: ");
        String nuevoNombre = sc.nextLine();

        if (nuevoNombre.isBlank()) {
            System.out.println("Nombre no válido.");
            return;
        }

        editorial.setNombreEditorial(nuevoNombre);
        editorialDAO.modificar(editorial);

        System.out.println("Editorial modificada correctamente.");
    }

    private static void borrar() {
        System.out.print("ID de la editorial a borrar: ");
        int id = leerEntero();

        boolean borrado = editorialDAO.borrar(id);

        if (!borrado) {
            System.out.println("No se puede borrar (puede tener libros asociados).");
        } else {
            System.out.println("Editorial borrada correctamente.");
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
