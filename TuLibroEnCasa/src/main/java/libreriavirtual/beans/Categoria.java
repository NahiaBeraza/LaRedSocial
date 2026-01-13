package libreriavirtual.beans;

import java.io.Serializable;

public class Categoria implements Serializable {
    private static final long serialVersionUID = 1L;

    private int idCategoria;
    private String nombreCategoria;

    public Categoria() {}

    public Categoria(int idCategoria, String nombreCategoria) {
        this.idCategoria = idCategoria;
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
}
