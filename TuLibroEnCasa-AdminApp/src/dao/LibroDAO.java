package dao;

import java.util.List;
import javax.persistence.*;
import model.Libro;

public class LibroDAO {

    private EntityManagerFactory emf;

    public LibroDAO() {
        emf = Persistence.createEntityManagerFactory("somorrostro_pu");
    }

    // INSERTAR
    public void insertar(Libro libro) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        tx.begin();
        em.persist(libro);
        tx.commit();

        em.close();
    }

    // LISTAR
    public List<Libro> listar() {
        EntityManager em = emf.createEntityManager();

        List<Libro> lista = em
                .createQuery("SELECT l FROM Libro l", Libro.class)
                .getResultList();

        em.close();
        return lista;
    }

    // BUSCAR POR ID
    public Libro buscar(String isbn) {
        EntityManager em = emf.createEntityManager();
        Libro libro = em.find(Libro.class, isbn);
        em.close();
        return libro;
    }

    // BUSCAR POR TÍTULO
    public List<Libro> buscarPorTitulo(String titulo) {
        EntityManager em = emf.createEntityManager();

        List<Libro> lista = em
                .createQuery("SELECT l FROM Libro l WHERE l.titulo LIKE :titulo", Libro.class)
                .setParameter("titulo", "%" + titulo + "%")
                .getResultList();

        em.close();
        return lista;
    }

    // FILTROS
    public List<Libro> filtrarPorAutor(int idAutor) {
        EntityManager em = emf.createEntityManager();

        List<Libro> lista = em
                .createQuery("SELECT l FROM Libro l WHERE l.autor.idAutor = :id", Libro.class)
                .setParameter("id", idAutor)
                .getResultList();

        em.close();
        return lista;
    }

    public List<Libro> filtrarPorCategoria(int idCategoria) {
        EntityManager em = emf.createEntityManager();

        List<Libro> lista = em
                .createQuery("SELECT l FROM Libro l WHERE l.categoria.idCategoria = :id", Libro.class)
                .setParameter("id", idCategoria)
                .getResultList();

        em.close();
        return lista;
    }

    public List<Libro> filtrarPorEditorial(int idEditorial) {
        EntityManager em = emf.createEntityManager();

        List<Libro> lista = em
                .createQuery("SELECT l FROM Libro l WHERE l.editorial.idEditorial = :id", Libro.class)
                .setParameter("id", idEditorial)
                .getResultList();

        em.close();
        return lista;
    }

    // MODIFICAR
    public void modificar(Libro libro) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        tx.begin();
        em.merge(libro);
        tx.commit();

        em.close();
    }

    // BORRAR (devuelve boolean)
    public boolean borrar(String isbn) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        Libro libro = em.find(Libro.class, isbn);

        if (libro != null) {
            tx.begin();
            em.remove(libro);
            tx.commit();
            em.close();
            return true;
        }

        em.close();
        return false;
    }
}
