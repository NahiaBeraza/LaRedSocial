package frontendConsola;

import java.util.Scanner;

public class MenuPrincipalConsola {

    private static Scanner sc = new Scanner(System.in);

    public static void main(String[] args) {

        int opcion;

        do {
            System.out.println("\n===== LIBRERÍA VIRTUAL (CONSOLA) =====");
            System.out.println("1. Gestionar autores");
            System.out.println("2. Gestionar categorías");
            System.out.println("3. Gestionar editoriales");
            System.out.println("4. Gestionar libros");
            System.out.println("0. Salir");
            System.out.print("Opción: ");

            opcion = leerEntero();

            switch (opcion) {
            case 1:
                AutorConsola.menu();
                break;
            case 2:
                CategoriaConsola.menu();
                break;
            case 3:
                EditorialConsola.menu();
                break;
            case 4:
                LibroConsola.menu();
                break;
            case 0:
                System.out.println("Saliendo...");
                break;
            default:
                System.out.println("Opción no válida.");
                break;
        }


        } while (opcion != 0);

        System.out.println("Hasta luego!");
    }

    // Método seguro para leer enteros
    private static int leerEntero() {
        try {
            return Integer.parseInt(sc.nextLine());
        } catch (Exception e) {
            return -1;
        }
    }
}
