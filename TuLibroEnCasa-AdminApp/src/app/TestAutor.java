package app;

import dao.AutorDAO;
import model.Autor;

public class TestAutor {

    public static void main(String[] args) {

        AutorDAO dao = new AutorDAO();

        // 1. INSERTAR
        Autor nuevo = new Autor("Autor de prueba");
        dao.insertar(nuevo);
        System.out.println("Insertado: " + nuevo);

        // 2. LISTAR
        System.out.println("\nLISTA DE AUTORES:");
        dao.listar().forEach(System.out::println);

        // 3. BUSCAR POR ID
        System.out.println("\nBUSCAR AUTOR CON ID 1:");
        Autor a = dao.buscar(1);
        System.out.println(a);

        // 4. MODIFICAR
        if (a != null) {
            a.setNombreAutor("Nombre modificado");
            dao.modificar(a);
            System.out.println("\nAutor modificado:");
            System.out.println(dao.buscar(a.getIdAutor()));
        }

        // 5. BORRAR
        System.out.println("\nBORRANDO AUTOR CON ID 1...");
        dao.borrar(1);

        System.out.println("\nLISTA FINAL:");
        dao.listar().forEach(System.out::println);

        
    }
}
