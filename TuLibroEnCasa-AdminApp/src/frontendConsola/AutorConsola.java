package frontendConsola;

import java.util.List;
import java.util.Scanner;

import dao.AutorDAO;
import model.Autor;

public class AutorConsola {

    private static AutorDAO autorDAO = new AutorDAO();
    private static Scanner sc = new Scanner(System.in);

    public static void menu() {

        int opcion;

        do {
            System.out.println("\n--- GESTIÓN DE AUTORES ---");
            System.out.println("1. Listar autores");
            System.out.println("2. Añadir autor");
            System.out.println("3. Modificar autor");
            System.out.println("4. Borrar autor");
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
        List<Autor> autores = autorDAO.listar();

        if (autores.isEmpty()) {
            System.out.println("No hay autores registrados.");
            return;
        }

        System.out.println("\n--- LISTA DE AUTORES ---");
        autores.forEach(a ->
                System.out.println(a.getIdAutor() + " - " + a.getNombreAutor())
        );
    }

    private static void añadir() {
        System.out.print("Nombre del autor: ");
        String nombre = sc.nextLine();

        if (nombre.isBlank()) {//con .isBlank devuelve true si la cadena está vacía o solo tiene espacios con isEmpty() solo si la cadena esta vacía devuelve true
            System.out.println("Nombre no válido.");
            return;
        }

        autorDAO.insertar(new Autor(nombre));
        System.out.println("Autor añadido correctamente.");
    }

    private static void modificar() {
        System.out.print("ID del autor a modificar: ");
        int id = leerEntero();

        Autor autor = autorDAO.buscar(id);

        if (autor == null) {
            System.out.println("No existe un autor con ese ID.");
            return;
        }

        System.out.print("Nuevo nombre: ");
        String nuevoNombre = sc.nextLine();

        if (nuevoNombre.isBlank()) {
            System.out.println("Nombre no válido.");
            return;
        }

        autor.setNombreAutor(nuevoNombre);
        autorDAO.modificar(autor);

        System.out.println("Autor modificado correctamente.");
    }

    private static void borrar() {
        System.out.print("ID del autor a borrar: ");
        int id = leerEntero();

        boolean borrado = autorDAO.borrar(id);

        if (!borrado) {
            System.out.println("No se puede borrar (puede tener libros asociados).");
        } else {
            System.out.println("Autor borrado correctamente.");
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
