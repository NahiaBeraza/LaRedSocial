package model;

import java.io.Serializable;
import javax.persistence.*;
;

/**
 * The persistent class for the autor database table.
 * 
 */
@Entity
@Table(name = "Autor")
public class Autor implements Serializable {
    private static final long serialVersionUID = 1L;

    @Id//clave primaria
    @GeneratedValue(strategy = GenerationType.IDENTITY)//aunque la base de datos sea autoincremental tenemos que indicarle a Hibernate que lo es para que no lo inserte
    @Column(name = "id_autor")//especificamos que la columna de la base de datos se llama distinta que la propiedad idAutor, en caso contrario no haria falta ponerlo
    private int idAutor;

    @Column(name = "nombre_autor", nullable = false)
    private String nombreAutor;

    public Autor() {}

    public Autor(String nombreAutor) {
        this.nombreAutor = nombreAutor;
    }

    public int getIdAutor() {
        return idAutor;
    }

    public void setIdAutor(int idAutor) {
        this.idAutor = idAutor;
    }

    public String getNombreAutor() {
        return nombreAutor;
    }

    public void setNombreAutor(String nombreAutor) {
        this.nombreAutor = nombreAutor;
    }

    @Override
    //cambiado por que se ve mal en los desplegables
    /*public String toString() {
        return "Autor [idAutor=" + idAutor + ", nombreAutor=" + nombreAutor + "]";
    }*/
    public String toString() {
    	return idAutor + " - " + nombreAutor; }
}
