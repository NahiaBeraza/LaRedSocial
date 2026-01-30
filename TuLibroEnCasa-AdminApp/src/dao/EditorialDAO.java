package dao;

import java.util.List;
import javax.persistence.*;
import model.Editorial;

public class EditorialDAO {

    private EntityManagerFactory emf;

    public EditorialDAO() {
        emf = Persistence.createEntityManagerFactory("somorrostro_pu");
    }

    // INSERTAR
    public void insertar(Editorial editorial) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        tx.begin();
        em.persist(editorial);
        tx.commit();

        em.close();
    }

    // LISTAR
    public List<Editorial> listar() {
        EntityManager em = emf.createEntityManager();

        List<Editorial> lista = em
                .createQuery("SELECT e FROM Editorial e", Editorial.class)
                .getResultList();

        em.close();
        return lista;
    }

    // BUSCAR POR ID
    public Editorial buscar(int idEditorial) {
        EntityManager em = emf.createEntityManager();
        Editorial editorial = em.find(Editorial.class, idEditorial);
        em.close();
        return editorial;
    }

    // BUSCAR POR NOMBRE
    public List<Editorial> buscarPorNombre(String nombre) {
        EntityManager em = emf.createEntityManager();

        List<Editorial> lista = em
                .createQuery("SELECT e FROM Editorial e WHERE e.nombreEditorial LIKE :nombre", Editorial.class)
                .setParameter("nombre", "%" + nombre + "%")
                .getResultList();

        em.close();
        return lista;
    }

    // MODIFICAR
    public void modificar(Editorial editorial) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        tx.begin();
        em.merge(editorial);
        tx.commit();

        em.close();
    }

    // BORRAR (devuelve boolean)
    public boolean borrar(int idEditorial) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        Editorial editorial = em.find(Editorial.class, idEditorial);

        if (editorial != null) {
            tx.begin();
            em.remove(editorial);
            tx.commit();
            em.close();
            return true;
        }

        em.close();
        return false;
    }
}
