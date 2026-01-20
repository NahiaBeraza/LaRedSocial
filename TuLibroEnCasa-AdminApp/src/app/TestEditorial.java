package app;

import dao.EditorialDAO;
import model.Editorial;

public class TestEditorial {

    public static void main(String[] args) {

        EditorialDAO dao = new EditorialDAO();

        // 1. INSERTAR
        Editorial nueva = new Editorial("Editorial de prueba");
        dao.insertar(nueva);
        System.out.println("Insertada: " + nueva);

        // 2. LISTAR
        System.out.println("\nLISTA DE EDITORIALES:");
        dao.listar().forEach(System.out::println);

        // 3. BUSCAR POR ID
        System.out.println("\nBUSCAR EDITORIAL CON ID 1:");
        Editorial e = dao.buscar(1);
        System.out.println(e);

        // 4. BUSCAR POR NOMBRE
        System.out.println("\nBUSCAR POR NOMBRE 'pru':");
        dao.buscarPorNombre("pru").forEach(System.out::println);

        // 5. MODIFICAR
        if (e != null) {
            e.setNombreEditorial("Nombre modificado");
            dao.modificar(e);
            System.out.println("\nEditorial modificada:");
            System.out.println(dao.buscar(e.getIdEditorial()));
        }

        // 6. BORRAR
        System.out.println("\nBORRANDO EDITORIAL CON ID 1...");
        boolean borrado = dao.borrar(1);

        if (!borrado) {
            System.out.println("No se pudo borrar la editorial (puede tener libros asociados o no existir).");
        }

        System.out.println("\nLISTA FINAL:");
        dao.listar().forEach(System.out::println);
    }
}
