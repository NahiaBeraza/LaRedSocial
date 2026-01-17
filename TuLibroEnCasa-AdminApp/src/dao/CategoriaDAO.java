package dao;

import java.util.List;
import javax.persistence.*;
import model.Categoria;

public class CategoriaDAO {

    private EntityManagerFactory emf;

    public CategoriaDAO() {
        emf = Persistence.createEntityManagerFactory("somorrostro_pu");
    }

    // INSERTAR
    public void insertar(Categoria categoria) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        tx.begin();
        em.persist(categoria);
        tx.commit();

        em.close();
    }

    // LISTAR
    public List<Categoria> listar() {
        EntityManager em = emf.createEntityManager();

        List<Categoria> lista = em
                .createQuery("SELECT c FROM Categoria c", Categoria.class)
                .getResultList();

        em.close();
        return lista;
    }

    // BUSCAR POR ID
    public Categoria buscar(int idCategoria) {
        EntityManager em = emf.createEntityManager();
        Categoria categoria = em.find(Categoria.class, idCategoria);
        em.close();
        return categoria;
    }

    // BUSCAR POR NOMBRE
    public List<Categoria> buscarPorNombre(String nombre) {
        EntityManager em = emf.createEntityManager();

        List<Categoria> lista = em
                .createQuery("SELECT c FROM Categoria c WHERE c.nombreCategoria LIKE :nombre", Categoria.class)
                .setParameter("nombre", "%" + nombre + "%")
                .getResultList();

        em.close();
        return lista;
    }

    // MODIFICAR
    public void modificar(Categoria categoria) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        tx.begin();
        em.merge(categoria);
        tx.commit();

        em.close();
    }

    // BORRAR (devuelve boolean)
    public boolean borrar(int idCategoria) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        Categoria categoria = em.find(Categoria.class, idCategoria);

        if (categoria != null) {
            tx.begin();
            em.remove(categoria);
            tx.commit();
            em.close();
            return true;
        }

        em.close();
        return false;
    }
}
