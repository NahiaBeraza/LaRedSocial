package app;

import dao.CategoriaDAO;
import model.Categoria;

public class TestCategoria {

    public static void main(String[] args) {

        CategoriaDAO dao = new CategoriaDAO();

        // 1. INSERTAR
        Categoria nueva = new Categoria("Categoria de prueba");
        dao.insertar(nueva);
        System.out.println("Insertada: " + nueva);

        // 2. LISTAR
        System.out.println("\nLISTA DE CATEGORIAS:");
        dao.listar().forEach(System.out::println);

        // 3. BUSCAR POR ID
        System.out.println("\nBUSCAR CATEGORIA CON ID 1:");
        Categoria c = dao.buscar(1);
        System.out.println(c);

        // 4. BUSCAR POR NOMBRE
        System.out.println("\nBUSCAR POR NOMBRE 'pru':");
        dao.buscarPorNombre("pru").forEach(System.out::println);

        // 5. MODIFICAR
        if (c != null) {
            c.setNombreCategoria("Nombre modificado");
            dao.modificar(c);
            System.out.println("\nCategoria modificada:");
            System.out.println(dao.buscar(c.getIdCategoria()));
        }

        // 6. BORRAR
        System.out.println("\nBORRANDO CATEGORIA CON ID 1...");
        boolean borrado = dao.borrar(1);

        if (!borrado) {
            System.out.println("No se pudo borrar la categoría (puede tener libros asociados o no existir).");
        }

        System.out.println("\nLISTA FINAL:");
        dao.listar().forEach(System.out::println);
    }
}

