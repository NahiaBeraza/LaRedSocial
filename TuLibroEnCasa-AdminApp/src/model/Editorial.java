package model;

import java.io.Serializable;
import javax.persistence.*;
/**
 * The persistent class for the categoria database table.
 * 
 */
@Entity
@Table(name = "Editorial")
public class Editorial implements Serializable {
    private static final long serialVersionUID = 1L;

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id_editorial")
    private int idEditorial;

    @Column(name = "nombre_editorial", nullable = false)
    private String nombreEditorial;

    public Editorial() {}

    public Editorial(String nombreEditorial) {
        this.nombreEditorial = nombreEditorial;
    }

    public int getIdEditorial() {
        return idEditorial;
    }

    public void setIdEditorial(int idEditorial) {
        this.idEditorial = idEditorial;
    }

    public String getNombreEditorial() {
        return nombreEditorial;
    }

    public void setNombreEditorial(String nombreEditorial) {
        this.nombreEditorial = nombreEditorial;
    }

    @Override
    //cambiado para que se vean bien en los desplegables
    /*public String toString() {
        return "Editorial [idEditorial=" + idEditorial + ", nombreEditorial=" + nombreEditorial + "]";
    }*/
    public String toString() { 
    	return idEditorial + " - " + nombreEditorial; }
}
