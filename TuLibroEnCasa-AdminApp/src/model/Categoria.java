package model;

import java.io.Serializable;
import javax.persistence.*;
/**
 * The persistent class for the categoria database table.
 * 
 */
@Entity
@Table(name = "Categoria")
public class Categoria implements Serializable {
    private static final long serialVersionUID = 1L;

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id_categoria")
    private int idCategoria;

    @Column(name = "nombre_categoria", nullable = false)
    private String nombreCategoria;

    public Categoria() {}

    public Categoria(String nombreCategoria) {
        this.nombreCategoria = nombreCategoria;
    }

    public int getIdCategoria() {
        return idCategoria;
    }

    public void setIdCategoria(int idCategoria) {
        this.idCategoria = idCategoria;
    }

    public String getNombreCategoria() {
        return nombreCategoria;
    }

    public void setNombreCategoria(String nombreCategoria) {
        this.nombreCategoria = nombreCategoria;
    }

    @Override
    //cambiado para que se vean bien en los desplegables
    /*public String toString() {
        return "Categoria [idCategoria=" + idCategoria + ", nombreCategoria=" + nombreCategoria + "]";
    }*/
    public String toString() {
    	return idCategoria + " - " + nombreCategoria; }
}
