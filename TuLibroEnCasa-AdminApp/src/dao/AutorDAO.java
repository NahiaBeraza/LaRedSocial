package dao;

import java.util.List;
import javax.persistence.*;
import model.Autor;


public class AutorDAO {

    private EntityManagerFactory emf;

    public AutorDAO() {
        // Carga la unidad de persistencia definida en persistence.xml
        emf = Persistence.createEntityManagerFactory("somorrostro_pu");
    }

    // INSERTAR
    public void insertar(Autor autor) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        tx.begin();
        em.persist(autor);
        tx.commit();

        em.close();
    }

    // LISTAR
    public List<Autor> listar() {
        EntityManager em = emf.createEntityManager();

        List<Autor> lista = em
                .createQuery("SELECT a FROM Autor a", Autor.class)
                .getResultList();

        em.close();
        return lista;
    }

    // BUSCAR POR ID
    public Autor buscar(int idAutor) {
        EntityManager em = emf.createEntityManager();
        Autor autor = em.find(Autor.class, idAutor);
        em.close();
        return autor;
    }
    
	 // BUSCAR POR NOMBRE (LIKE)
	    public List<Autor> buscarPorNombre(String nombre) {
	        EntityManager em = emf.createEntityManager();
	
	        List<Autor> lista = em
	                .createQuery("SELECT a FROM Autor a WHERE a.nombreAutor LIKE :nombre", Autor.class)
	                .setParameter("nombre", "%" + nombre + "%")
	                .getResultList();
	
	        em.close();
	        return lista;
	    }


    // MODIFICAR
    public void modificar(Autor autor) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        tx.begin();
        em.merge(autor);
        tx.commit();

        em.close();
    }

    // BORRAR
    public boolean borrar(int idAutor) {
        EntityManager em = emf.createEntityManager();
        EntityTransaction tx = em.getTransaction();

        Autor autor = em.find(Autor.class, idAutor);

        if (autor != null) {
            tx.begin();
            em.remove(autor);
            tx.commit();
            em.close();
            return true;
        }

        em.close();
        return false;
    }


   }
